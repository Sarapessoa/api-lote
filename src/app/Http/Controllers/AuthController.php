<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthLoginRequest;
use App\Models\Usuario;
use App\Models\RefreshToken;
use App\Support\ApiExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    use ApiExceptionHandler;

    /**
     * @OA\Post(
     *   path="/api/auth/login",
     *   tags={"Auth"},
     *   summary="Autenticar e gerar Bearer Token + Refresh Token",
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/LoginStore")),
     *   @OA\Response(response=200, description="Autenticado", @OA\JsonContent(ref="#/components/schemas/LoginResponse")),
     *   @OA\Response(response=400, description="Campos mal formatados", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=401, description="Credenciais inválidas", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function login(AuthLoginRequest $request)
    {
        try {

            $usuario = Usuario::where('username', $request->username)->first();

            if (!$usuario || !Hash::check($request->password, $usuario->password)) {
                return response()->json([
                    'message' => 'Credenciais inválidas'
                ], 401);
            }

            // Access Token (Sanctum)
            $accessToken = $usuario->createToken('api_token')->plainTextToken;

            // Refresh Token
            $plainRefresh = Str::random(64);
            $hash = hash('sha256', $plainRefresh);

            RefreshToken::create([
                'usuario_id' => $usuario->id,
                'token_hash' => $hash,
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'ip_address' => $request->ip(),
                'expires_at' => now()->addDays(30)
            ]);

            return response()->json([
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration'),
                'refresh_token' => $plainRefresh,
                'refresh_expires_in' => 60 * 24 * 30
            ], 200);

        } catch (Throwable $e) {
            return $this->apiException($e, 500, 'Erro ao autenticar');
        }
    }

    /**
     * @OA\Post(
     *   path="/api/auth/refresh",
     *   tags={"Auth"},
     *   summary="Gerar novo Bearer Token usando Refresh Token",
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *      type="object",
     *      @OA\Property(property="refresh_token", type="string")
     *   )),
     *   @OA\Response(response=200, description="Novo token gerado", @OA\JsonContent(ref="#/components/schemas/LoginResponse")),
     *   @OA\Response(response=401, description="Refresh token inválido", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function refresh(Request $request)
    {
        try {
            $request->validate([
                'refresh_token' => ['required', 'string', 'min:40']
            ]);

            $hash = hash('sha256', $request->refresh_token);

            $stored = RefreshToken::where('token_hash', $hash)->first();

            if (!$stored || !$stored->isActive()) {
                return response()->noContent(401);
            }

            $usuario = $stored->usuario;

            // Rotaciona o refresh token
            $stored->update(['revoked_at' => now()]);

            $newPlainRefresh = Str::random(64);
            $newHash = hash('sha256', $newPlainRefresh);

            RefreshToken::create([
                'usuario_id' => $usuario->id,
                'token_hash' => $newHash,
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'ip_address' => $request->ip(),
                'expires_at' => now()->addDays(30)
            ]);

            // Novo access token
            $accessToken = $usuario->createToken('api_token')->plainTextToken;

            return response()->json([
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration'),
                'refresh_token' => $newPlainRefresh,
                'refresh_expires_in' => 60 * 24 * 30
            ], 200);

        } catch (Throwable $e) {
            return $this->apiException($e, 500, 'Erro ao renovar token');
        }
    }

    /**
     * @OA\Post(
     *   path="/api/auth/logout",
     *   tags={"Auth"},
     *   summary="Invalidar tokens do usuário autenticado",
     *   security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Logout realizado", @OA\JsonContent(ref="#/components/schemas/LogoutResponse")),
     *   @OA\Response(response=401, description="Não autenticado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function logout(Request $request)
    {
        try {

            RefreshToken::where('usuario_id', $user->id)
                    ->whereNull('revoked_at')
                    ->update(['revoked_at' => now()]);
                    
            $request->user()->tokens()->delete();

            return response()->json([
                'message' => 'Logout realizado com sucesso'
            ], 200);

        } catch (Throwable $e) {
            return $this->apiException($e, 500, 'Erro ao efetuar logout');
        }
    }
}
