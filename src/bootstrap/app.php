<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: null, // sem rotas web (evita sessão/CSRF por engano)
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Defina explicitamente o grupo API como stateless
        $middleware->group('api', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Http\Middleware\SetCacheHeaders::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \App\Http\Middleware\ForceJsonResponse::class,
            \App\Http\Middleware\BlockDisallowedHttpMethods::class,
            // IMPORTANTE: não adicionar StartSession, VerifyCsrfToken,
            // nem EnsureFrontendRequestsAreStateful aqui.
        ]);

        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 404
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            return response()->json(['message' => 'Endpoint não encontrado'], 404);
        });

        // 405
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            return response()->json(['message' => 'Método não permitido para este endpoint'], 405);
        });

        // 401 (não autenticado)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->noContent(401);
        });

        // 403 (autenticado, mas sem permissão)
        $exceptions->render(function (AuthorizationException|AccessDeniedHttpException $e, Request $request) {
            return response()->noContent(403);
        });

        // 422 (validação)
        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        });

        // Propaga respostas explícitas
        $exceptions->render(function (HttpResponseException $e, Request $request) {
            return $e->getResponse();
        });
    })
    ->create();
