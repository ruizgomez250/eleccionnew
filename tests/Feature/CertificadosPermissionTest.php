<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Tests\TestCase;

class CertificadosPermissionTest extends TestCase
{
    private function userWithPermission(string $name, string $guard = 'web'): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        // Simula el caso donde Spatie no reconoce el nombre exacto.
        $user->shouldReceive('checkPermissionTo')->andReturn(false);
        $user->shouldReceive('getAllPermissions')->andReturn(collect([
            (object) ['name' => $name, 'guard_name' => $guard],
        ]));
        Auth::guard('web')->setUser($user);

        return $user;
    }

    public function test_case_and_whitespace_variants_allow_menu_view_and_save(): void
    {
        foreach (['Carga Certificados', 'carga certificados', 'CARGA CERTIFICADOS', '  Carga   Certificados  '] as $name) {
            $user = $this->userWithPermission($name);
            $this->assertTrue(Gate::forUser($user)->allows('Carga Certificados'));

            foreach (['GET' => '/certificados', 'POST' => '/certificados/guardar'] as $method => $url) {
                $response = (new PermissionMiddleware())->handle(
                    Request::create($url, $method),
                    fn () => response('ok'),
                    'Carga Certificados'
                );
                $this->assertSame(200, $response->getStatusCode());
            }
        }
    }

    public function test_unrelated_permission_does_not_allow_access(): void
    {
        $user = $this->userWithPermission('Listar Usuarios');
        $this->assertFalse(Gate::forUser($user)->allows('Carga Certificados'));
        $this->expectException(UnauthorizedException::class);
        (new PermissionMiddleware())->handle(Request::create('/certificados'), fn () => response('ok'), 'Carga Certificados');
    }

    public function test_other_guard_does_not_allow_access(): void
    {
        $user = $this->userWithPermission('carga certificados', 'api');
        $this->assertFalse(Gate::forUser($user)->allows('Carga Certificados'));
    }
}
