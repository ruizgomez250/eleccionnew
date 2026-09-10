<?php

namespace Tests\Feature;

use App\Imports\CertificadosInternasImport;
use App\Models\Internas\VotosMesa;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CertificadosInternasTest extends TestCase
{
    private array $files = [];

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['int_source', 'int_target'] as $name) {
            $file = tempnam(sys_get_temp_dir(), 'cert_int_');
            $this->files[] = $file;
            config(['database.connections.'.$name => ['driver' => 'sqlite', 'database' => $file, 'prefix' => '']]);
            DB::purge($name);
        }
        $columns = [
            'equipo' => '', 'locales_internas' => '', 'partidos' => '',
            'mesas' => ', equipo_id INTEGER', 'candidatos' => ', partido_id INTEGER',
            'veedores' => ', partido_id INTEGER, api_token TEXT',
            'votos_mesa' => ', mesa_id INTEGER, partido_id INTEGER, candidato_id INTEGER, veedor_id INTEGER, user_id INTEGER, cantidad_votos INTEGER, escaneado_por TEXT, cargo TEXT, tipo_voto TEXT, origen TEXT, escaneado_en TEXT, created_at TEXT, updated_at TEXT',
        ];
        foreach ($columns as $table => $sql) {
            DB::connection('int_source')->statement("CREATE TABLE $table (id INTEGER PRIMARY KEY$sql)");
            $extra = $table === 'votos_mesa' ? ', usuario_origen_id INTEGER, CHECK(cantidad_votos >= 0)' : '';
            DB::connection('int_target')->statement("CREATE TABLE internas_$table (id INTEGER PRIMARY KEY$sql$extra)");
        }
        DB::connection('int_target')->statement('CREATE TABLE votos_mesa (id INTEGER PRIMARY KEY, cantidad_votos INTEGER)');
        DB::connection('int_target')->table('votos_mesa')->insert(['id' => 1, 'cantidad_votos' => 99]);
        $source = DB::connection('int_source');
        foreach (['equipo', 'locales_internas', 'partidos'] as $table) $source->table($table)->insert(['id' => 1]);
        $source->table('mesas')->insert(['id' => 1, 'equipo_id' => 1]);
        $source->table('candidatos')->insert(['id' => 1, 'partido_id' => 1]);
        $source->table('veedores')->insert(['id' => 1, 'partido_id' => 1, 'api_token' => 'old-token']);
        $source->table('votos_mesa')->insert(['id' => 1, 'mesa_id' => 1, 'partido_id' => 1, 'candidato_id' => 1,
            'veedor_id' => 1, 'user_id' => 543, 'cantidad_votos' => 12, 'escaneado_por' => 'Operador de origen', 'cargo' => 'intendente', 'tipo_voto' => 'preferencia']);
    }

    protected function tearDown(): void
    {
        foreach (['int_source', 'int_target'] as $name) DB::purge($name);
        foreach ($this->files as $file) unlink($file);
        parent::tearDown();
    }

    private function import(bool $check = false): array
    {
        return app(CertificadosInternasImport::class)->run(DB::connection('int_source'), DB::connection('int_target'), $check);
    }

    public function test_import_keeps_relations_and_does_not_touch_original_results(): void
    {
        $this->assertCount(7, $this->import());
        $target = DB::connection('int_target');
        $record = $target->table('internas_votos_mesa')->first();
        $this->assertSame(1, $record->id);
        $this->assertSame(1, $record->candidato_id);
        $this->assertSame(12, $record->cantidad_votos);
        $this->assertNull($record->user_id);
        $this->assertSame(543, $record->usuario_origen_id);
        $this->assertSame('Operador de origen', $record->escaneado_por);
        $this->assertNull($target->table('internas_veedores')->value('api_token'));
        $this->assertSame(99, $target->table('votos_mesa')->value('cantidad_votos'));
        $this->assertSame(543, DB::connection('int_source')->table('votos_mesa')->value('user_id'));
    }

    public function test_check_only_does_not_copy_and_repeat_import_is_rejected(): void
    {
        $this->import(true);
        $this->assertSame(0, DB::connection('int_target')->table('internas_equipo')->count());
        $this->import();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('ya contiene datos');
        $this->import();
    }

    public function test_invalid_reference_is_rejected_before_any_copy(): void
    {
        DB::connection('int_source')->table('votos_mesa')->update(['candidato_id' => 999]);
        try {
            $this->import();
            $this->fail('Debió rechazar la referencia inexistente.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('referencias sin destino', $e->getMessage());
            $this->assertSame(0, DB::connection('int_target')->table('internas_equipo')->count());
        }
    }

    public function test_failed_insert_rolls_back_all_copied_tables(): void
    {
        DB::connection('int_source')->table('votos_mesa')->update(['cantidad_votos' => -1]);
        try {
            $this->import();
            $this->fail('Debió fallar la restricción del destino.');
        } catch (\Illuminate\Database\QueryException $e) {
            foreach (CertificadosInternasImport::TABLES as $table) {
                $this->assertSame(0, DB::connection('int_target')->table('internas_'.$table)->count());
            }
        }
    }

    public function test_models_and_routes_use_the_independent_module(): void
    {
        $vote = new VotosMesa();
        $this->assertSame('internas_votos_mesa', $vote->getTable());
        foreach (['mesa' => 'mesas', 'partido' => 'partidos', 'candidato' => 'candidatos', 'veedor' => 'veedores'] as $relation => $table) {
            $this->assertSame('internas_'.$table, $vote->$relation()->getRelated()->getTable());
        }
        $this->assertSame('internas_equipo', $vote->mesa()->getRelated()->equipo()->getRelated()->getTable());
        $route = app('router')->getRoutes()->getByName('certificados-internas.guardar');
        $this->assertNotNull($route);
        $this->assertSame('certificados-internas/guardar', $route->uri());
        $this->assertContains('can:Carga Certificados', $route->gatherMiddleware());
    }

    public function test_web_save_update_and_delete_only_change_internal_results(): void
    {
        $this->import();
        config(['database.default' => 'int_target']);
        $user = \Mockery::mock(\App\Models\User::class)->makePartial();
        $user->id = 20;
        $user->name = 'Operador actual';
        $user->shouldReceive('checkPermissionTo')->andReturn(true);
        $this->actingAs($user);
        $this->postJson('/certificados-internas/guardar', [
            'mesa_id' => 1, 'cargo' => 'intendente', 'preferencias' => [1 => [1 => 23]],
        ])->assertOk()->assertJsonPath('success', true);
        $this->assertSame(23, DB::table('internas_votos_mesa')->value('cantidad_votos'));
        $this->assertSame(20, DB::table('internas_votos_mesa')->value('user_id'));
        $this->assertSame(1, DB::table('internas_votos_mesa')->count());
        $this->putJson('/certificados-internas/1', ['cantidad_votos' => 42])->assertOk();
        $this->assertSame(42, DB::table('internas_votos_mesa')->value('cantidad_votos'));
        $this->putJson('/certificados-internas/1', ['cantidad_votos' => -1])->assertUnprocessable();
        $this->delete('/certificados-internas/1')->assertRedirect();
        $this->assertSame(0, DB::table('internas_votos_mesa')->count());
        $this->assertSame(99, DB::table('votos_mesa')->value('cantidad_votos'));
    }
}
