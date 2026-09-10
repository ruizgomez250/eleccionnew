<?php

namespace Tests\Feature;

use App\Http\Middleware\AccesoEfectividadElectoral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Tests\TestCase;

class EfectividadAccessTest extends TestCase
{
    private function user(int $id, bool $allowed = true): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = $id;
        $user->shouldReceive('checkPermissionTo')->andReturnUsing(fn ($name) => $name === 'Carga Certificados' && $allowed);
        $user->shouldReceive('getAllPermissions')->andReturn(collect());
        return $user;
    }

    public function test_menu_and_middleware_allow_ids_one_to_four(): void
    {
        foreach ([1, 2, 3, 4] as $id) {
            $user = $this->user($id);
            $this->assertTrue(Gate::forUser($user)->allows('Ver Efectividad Electoral'));
            $request = Request::create('/efectividad');
            $request->setUserResolver(fn () => $user);
            $response = (new AccesoEfectividadElectoral())->handle($request, fn () => response('ok'));
            $this->assertSame(200, $response->getStatusCode());
        }
        $this->assertFalse(Gate::forUser($this->user(1, false))->allows('Ver Efectividad Electoral'));
    }

    public function test_other_users_cannot_access_page_or_any_data_endpoint(): void
    {
        foreach ([0, 5, 10] as $id) {
            $user = $this->user($id);
            $this->actingAs($user);
            $this->assertFalse(Gate::forUser($user)->allows('Ver Efectividad Electoral'));
            $this->getJson('/efectividad')->assertForbidden();
            foreach (['resumen', 'mesa/1', 'ranking', 'comparar', 'candidatos', 'arrastre', 'intendentes', 'arrastre-comite', 'arrastre-completo'] as $path) {
                $this->getJson('/api/efectividad/'.$path)->assertForbidden();
            }
        }
    }

    public function test_guests_cannot_access_page_or_data(): void
    {
        $this->getJson('/efectividad')->assertUnauthorized();
        $this->getJson('/api/efectividad/resumen')->assertUnauthorized();
    }
}
