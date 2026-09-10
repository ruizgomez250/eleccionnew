<?php

namespace Tests\Feature;

use App\Models\User;
use App\Reports\ParticipacionGeneralReport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class ParticipacionGeneralReportTest extends TestCase
{
    private function fixtures(): void
    {
        $mysql = getenv('PARTICIPACION_TEST_MYSQL') === '1';
        if (!$mysql) {
            config(['database.default' => 'report_test', 'database.connections.report_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ]]);
        }
        DB::purge();
        $this->assertSame($mysql ? 'mysql' : 'sqlite', DB::connection()->getDriverName());
        // En MySQL las tablas temporales sólo existen en esta conexión de prueba.
        $create = $mysql ? 'CREATE TEMPORARY TABLE ' : 'CREATE TABLE ';
        DB::statement($create.'equipo (id INTEGER, sist INTEGER)');
        DB::statement($create.'dirigente (id INTEGER, id_equipo INTEGER, nombre TEXT)');
        DB::statement($create.'puntero (id INTEGER, id_dirigente INTEGER, nombre TEXT)');
        DB::statement($create.'votante (idpuntero INTEGER, cedula TEXT)');
        DB::statement($create.'votos (cedula '.($mysql ? 'VARCHAR(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci' : 'TEXT').')');
        DB::table('equipo')->insert([['id' => 1, 'sist' => 1], ['id' => 2, 'sist' => 2]]);
        DB::table('dirigente')->insert([
            ['id' => 1, 'id_equipo' => 1, 'nombre' => 'Dirigente A'],
            ['id' => 2, 'id_equipo' => 2, 'nombre' => 'Dirigente externo'],
            ['id' => 3, 'id_equipo' => 1, 'nombre' => 'Dirigente A'],
        ]);
        DB::table('puntero')->insert([
            ['id' => 1, 'id_dirigente' => 1, 'nombre' => 'Puntero A'],
            ['id' => 2, 'id_dirigente' => 1, 'nombre' => 'Puntero B'],
            ['id' => 3, 'id_dirigente' => 2, 'nombre' => 'Puntero externo'],
            ['id' => 4, 'id_dirigente' => 3, 'nombre' => 'Puntero vacío'],
        ]);
        DB::table('votante')->insert([
            ['idpuntero' => 1, 'cedula' => '100001'], ['idpuntero' => 1, 'cedula' => '100001'],
            ['idpuntero' => 1, 'cedula' => '100002'], ['idpuntero' => 2, 'cedula' => '100001'],
            ['idpuntero' => 3, 'cedula' => '999999'], ['idpuntero' => 2, 'cedula' => ''],
        ]);
        DB::table('votos')->insert([['cedula' => '100001'], ['cedula' => '100001'], ['cedula' => '999999']]);
    }

    private function loginWithReportPermission(bool $allowed): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 10;
        $user->sistema = 1;
        $user->shouldReceive('checkPermissionTo')->with('Reportes', null)->andReturn($allowed);
        $this->actingAs($user);
    }

    public function test_counts_are_unique_scoped_and_only_aggregate_fields_leave_service(): void
    {
        $this->fixtures();
        $data = app(ParticipacionGeneralReport::class)->generate(1);
        $this->assertSame(['total' => 2, 'registrados' => 1, 'sin_registro' => 1, 'porcentaje' => 50.0], $data['resumen']);
        $this->assertCount(2, $data['dirigentes']);
        $this->assertCount(3, $data['punteros']);
        $this->assertSame(2, $data['dirigentes'][0]['total']);
        $this->assertSame(0, $data['punteros'][2]['total']);
        $this->assertSame(['nombre', 'dirigente', 'total', 'registrados', 'sin_registro', 'porcentaje'], array_keys($data['punteros'][0]));
        $this->assertStringNotContainsString('100001', json_encode($data));
        $this->assertStringNotContainsString('externo', json_encode($data));
    }

    public function test_empty_system_returns_zero_without_dividing_by_zero(): void
    {
        $this->fixtures();
        $data = app(ParticipacionGeneralReport::class)->generate(999);
        $this->assertSame(0, $data['resumen']['total']);
        $this->assertSame(0, $data['resumen']['porcentaje']);
        $this->assertSame([], $data['punteros']);
    }

    public function test_both_routes_require_report_permission(): void
    {
        $this->loginWithReportPermission(false);
        $this->get('/reportes/participacion-general')->assertForbidden();
        $this->getJson('/reportes/participacion-general/data')->assertForbidden();
    }

    public function test_data_uses_assigned_system_and_reuses_cached_aggregates(): void
    {
        $this->fixtures();
        $this->loginWithReportPermission(true);
        Cache::flush();
        $this->getJson('/reportes/participacion-general/data?sistema_id=2')->assertOk()->assertJsonPath('resumen.total', 2);
        DB::table('votos')->insert(['cedula' => '100002']);
        $this->getJson('/reportes/participacion-general/data')->assertOk()->assertJsonPath('resumen.registrados', 1);
    }

    public function test_shared_person_across_dirigentes_is_not_added_twice_to_summary(): void
    {
        $this->fixtures();
        DB::table('votante')->insert(['idpuntero' => 4, 'cedula' => '100001']);
        $data = app(ParticipacionGeneralReport::class)->generate(1);
        $this->assertSame(2, $data['resumen']['total']);
        $this->assertSame(1, $data['resumen']['registrados']);
        $this->assertSame(3, array_sum(array_column($data['dirigentes'], 'total')));
    }

    public function test_errors_have_a_reference_without_exposing_sql(): void
    {
        $this->loginWithReportPermission(true);
        Cache::shouldReceive('remember')->once()->andReturnUsing(fn ($key, $ttl, $build) => $build());
        $report = Mockery::mock(ParticipacionGeneralReport::class);
        $report->shouldReceive('generate')->once()->with(1)->andThrow(new \RuntimeException('Private SQL details'));
        $this->app->instance(ParticipacionGeneralReport::class, $report);
        Log::shouldReceive('error')->once()->withArgs(fn ($message, $context) =>
            $message === 'Error en participacion-general' && isset($context['referencia']));
        $response = $this->getJson('/reportes/participacion-general/data');
        $response->assertStatus(500);
        $this->assertStringStartsWith('No se pudo generar el reporte. Referencia:', $response->json('message'));
        $response->assertDontSee('Private SQL details');
    }
}
