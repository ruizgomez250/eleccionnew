<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class SessionAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
    }

    private function configureUser(): User
    {
        $user = new User();
        $user->forceFill(['id' => 123, 'email' => 'session@example.test', 'remember_token' => 'existing-token']);
        $provider = Mockery::mock(UserProvider::class);
        $provider->shouldReceive('retrieveByCredentials')->andReturn($user);
        $provider->shouldReceive('validateCredentials')->andReturnUsing(fn ($user, $credentials) => $credentials['password'] === 'correct-password');
        $provider->shouldReceive('retrieveById')->andReturn($user);
        $provider->shouldReceive('updateRememberToken')->andReturnNull();
        Auth::guard('web')->setProvider($provider);

        return $user;
    }

    public function test_missing_access_table_does_not_block_login(): void
    {
        $user = $this->configureUser();
        Log::shouldReceive('warning')->once()->with('No se pudo registrar el acceso de sesión.', Mockery::type('array'));

        $this->post('/login', ['email' => $user->email, 'password' => 'correct-password'])->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function test_missing_access_table_does_not_block_logout_or_session_invalidation(): void
    {
        $user = $this->configureUser();
        Log::shouldReceive('warning')->once()->with('No se pudo cerrar el acceso de sesión.', Mockery::type('array'));

        $this->actingAs($user)->withSession(['private_marker' => 'remove-me'])
            ->post('/logout')->assertRedirect('/login')->assertSessionMissing('private_marker');
        $this->assertGuest();
    }

    public function test_invalid_credentials_remain_rejected(): void
    {
        $user = $this->configureUser();
        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_access_history_is_still_recorded_when_table_exists(): void
    {
        Schema::create('accesos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('login_at')->nullable();
            $table->timestamp('logout_at')->nullable();
            $table->integer('duracion_segundos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
        });
        $user = $this->configureUser();
        $this->post('/login', ['email' => $user->email, 'password' => 'correct-password'])->assertRedirect('/home');
        $this->assertDatabaseHas('accesos', ['user_id' => $user->id, 'logout_at' => null]);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $access = DB::table('accesos')->sole();
        $this->assertNotNull($access->logout_at);
        $this->assertGreaterThanOrEqual(0, $access->duracion_segundos);
    }
}
