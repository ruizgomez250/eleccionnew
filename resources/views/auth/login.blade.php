@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
@php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )

@if (config('adminlte.use_route_url', false))
    @php( $login_url = $login_url ? route($login_url) : '' )
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@else
    @php( $login_url = $login_url ? url($login_url) : '' )
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@endif

@section('css')
<style>
    :root {
        --ua-bg: #0b1220;
        --ua-card: #141d2f;
        --ua-card2: #1b2740;
        --ua-input: #0d1424;
        --ua-border: #26334d;
        --ua-border2: #32415f;
        --ua-text: #e6edf7;
        --ua-muted: #8ea3bf;
        --ua-indigo: #6366f1;
        --ua-violet: #8b5cf6;
        --ua-teal: #14b8a6;
        --ua-emerald: #34d399;
        --ua-amber: #fbbf24;
        --ua-rose: #fb7185;
        --ua-grad: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        --ua-radius: 16px;
        --ua-shadow: 0 12px 32px -14px rgba(0, 0, 0, .65);
    }

    html, body {
        background:
            radial-gradient(1100px 520px at 8% -8%, rgba(99, 102, 241, .18), transparent 60%),
            radial-gradient(900px 480px at 108% 0%, rgba(139, 92, 246, .14), transparent 55%),
            var(--ua-bg) !important;
        min-height: 100vh;
        margin: 0;
        padding: 0;
    }

    .login-page {
        background:
            radial-gradient(1100px 520px at 8% -8%, rgba(99, 102, 241, .18), transparent 60%),
            radial-gradient(900px 480px at 108% 0%, rgba(139, 92, 246, .14), transparent 55%),
            var(--ua-bg) !important;
    }

    .login-box {
        width: 420px;
        margin: 0 auto;
    }

    .login-logo {
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .login-logo a {
        color: var(--ua-text);
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: .3px;
        text-decoration: none;
    }

    .login-logo img {
        filter: drop-shadow(0 4px 12px rgba(99, 102, 241, .35));
        transition: transform .3s ease;
    }

    .login-logo img:hover {
        transform: scale(1.05);
    }

    /* --- Card --- */
    .card.login-card {
        border: 1px solid var(--ua-border);
        border-radius: 20px;
        background: linear-gradient(180deg, var(--ua-card2), var(--ua-card));
        box-shadow:
            0 25px 60px rgba(0, 0, 0, .6),
            0 0 80px rgba(99, 102, 241, .08);
        overflow: hidden;
    }

    .card.login-card > .card-header {
        background: linear-gradient(135deg, rgba(99, 102, 241, .14), rgba(139, 92, 246, .10));
        border-bottom: 1px solid var(--ua-border);
        border-radius: 20px 20px 0 0;
        padding: 1.5rem 2rem;
    }

    .card.login-card > .card-header .card-title {
        color: var(--ua-text);
        font-weight: 700;
        font-size: 1.15rem;
        letter-spacing: .3px;
        margin: 0;
    }

    .card.login-card > .card-header .card-title i {
        background: var(--ua-grad);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-right: .4rem;
    }

    .card.login-card > .card-body {
        padding: 1.8rem 2rem;
    }

    .card.login-card > .card-footer {
        background: rgba(13, 20, 36, .45);
        border-top: 1px solid var(--ua-border);
        border-radius: 0 0 20px 20px;
        padding: 1rem 2rem;
        text-align: center;
    }

    .card.login-card > .card-footer a {
        color: var(--ua-violet);
        font-weight: 600;
        text-decoration: none;
        transition: color .15s ease;
    }

    .card.login-card > .card-footer a:hover {
        color: var(--ua-indigo);
        text-decoration: underline;
    }

    /* --- Form inputs --- */
    .ua-login-form label {
        color: var(--ua-muted);
        font-weight: 600;
        font-size: .8rem;
        letter-spacing: .3px;
        margin-bottom: .35rem;
    }

    .ua-login-form .input-group {
        margin-bottom: 1.1rem;
    }

    .ua-login-form .input-group .form-control {
        background: var(--ua-input);
        border: 1px solid var(--ua-border2);
        border-radius: 10px;
        color: var(--ua-text);
        box-shadow: inset 0 2px 6px rgba(0, 0, 0, .35);
        transition: border-color .15s ease, box-shadow .15s ease;
        height: 44px;
        font-size: .92rem;
    }

    .ua-login-form .input-group .form-control:focus {
        border-color: var(--ua-violet);
        box-shadow: 0 0 0 3px rgba(139, 92, 246, .18);
        color: var(--ua-text);
        background: var(--ua-input);
    }

    .ua-login-form .input-group .form-control::placeholder {
        color: rgba(142, 163, 191, .6);
    }

    .ua-login-form .input-group .input-group-append .input-group-text {
        background: rgba(99, 102, 241, .12);
        border: 1px solid var(--ua-border2);
        border-left: none;
        border-radius: 0 10px 10px 0;
        color: var(--ua-indigo);
        font-size: .9rem;
    }

    .ua-login-form .input-group .form-control:not(:placeholder-shown) + .input-group-append .input-group-text,
    .ua-login-form .input-group:focus-within .input-group-append .input-group-text {
        border-color: var(--ua-violet);
    }

    .ua-login-form .input-group .form-control + .input-group-append {
        margin-left: -1px;
    }

    .ua-login-form .input-group .form-control + .input-group-append .input-group-text {
        border-radius: 0 10px 10px 0;
    }

    /* --- Remember me checkbox --- */
    .ua-login-form .icheck-primary label {
        color: var(--ua-muted) !important;
        font-size: .85rem;
    }

    .ua-login-form .icheck-primary input[type="checkbox"]:checked + label::before {
        background-color: var(--ua-indigo);
        border-color: var(--ua-indigo);
    }

    /* --- Submit button --- */
    .ua-login-btn {
        width: 100%;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: .95rem;
        padding: .65rem 1.2rem;
        color: #fff;
        background: var(--ua-grad);
        box-shadow: 0 6px 20px -8px rgba(99, 102, 241, .6);
        transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        letter-spacing: .3px;
    }

    .ua-login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px -10px rgba(99, 102, 241, .7);
        filter: brightness(1.08);
        color: #fff;
    }

    .ua-login-btn:active {
        transform: translateY(0);
    }

    /* --- Errors --- */
    .ua-login-form .invalid-feedback {
        color: var(--ua-rose);
        font-size: .78rem;
        font-weight: 500;
    }

    .ua-login-form .alert {
        border-radius: 10px;
        border: 1px solid rgba(251, 113, 133, .3);
        background: rgba(251, 113, 133, .1);
        color: #fda4af;
    }

    /* --- Gradient icon accent --- */
    .ua-login-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: var(--ua-grad);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        box-shadow: 0 8px 24px rgba(99, 102, 241, .35);
    }

    .ua-login-icon i {
        font-size: 1.6rem;
        color: #fff;
    }

    /* --- Responsive --- */
    @media (max-width: 480px) {
        .login-box {
            width: 100%;
            padding: 0 .75rem;
        }
        .card.login-card > .card-header,
        .card.login-card > .card-body,
        .card.login-card > .card-footer {
            padding-left: 1.2rem;
            padding-right: 1.2rem;
        }
    }
</style>
@stop

@section('auth_body')
    {{-- Gradient icon --}}
    <div class="ua-login-icon">
        <i class="fas fa-user-lock"></i>
    </div>

    <form action="{{ $login_url }}" method="post" class="ua-login-form">
        @csrf

        {{-- Email field --}}
        <div class="input-group">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="Correo electronico" autofocus>

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>

            @error('email')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Password field --}}
        <div class="input-group">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="Contrasena">

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>

            @error('password')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Remember me + Button --}}
        <div class="row align-items-center">
            <div class="col-7">
                <div class="icheck-primary">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">
                        Recordarme
                    </label>
                </div>
            </div>

            <div class="col-5">
                <button type="submit" class="ua-login-btn">
                    <span class="fas fa-sign-in-alt"></span>
                    Iniciar Sesion
                </button>
            </div>
        </div>

        {{-- Session status --}}
        @if (session('status'))
            <div class="mt-3 text-center" style="font-size:.85rem;">
                {{ session('status') }}
            </div>
        @endif
    </form>
@stop

@section('auth_footer')
    @if($password_reset_url)
        <p class="mb-1">
            <a href="{{ $password_reset_url }}">
                <i class="fas fa-key mr-1"></i> Olvide mi contrasena
            </a>
        </p>
    @endif
@stop
