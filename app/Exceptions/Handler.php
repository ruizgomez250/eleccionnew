<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
// 👇 AGREGAR ESTAS DOS LÍNEAS (importar las clases necesarias)
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    // 👇 AGREGAR ESTE MÉTODO COMPLETO (después del método register)
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        // Verificar si es error 419 (Token Mismatch - Página expirada)
        if ($e instanceof TokenMismatchException || 
            ($e instanceof HttpException && $e->getStatusCode() === 419)) {
            
            // Redirigir al home con un mensaje
            return redirect('/home')
                ->with('warning', 'Tu sesión expiró. Por favor, intenta nuevamente.');
        }

        return parent::render($request, $e);
    }
}