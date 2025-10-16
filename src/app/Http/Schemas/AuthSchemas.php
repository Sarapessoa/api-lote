<?php

namespace App\Http\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="LoginStore",
 *   type="object",
 *   required={"username","password"},
 *   @OA\Property(property="username", type="string", maxLength=100),
 *   @OA\Property(property="password", type="string", minLength=6, writeOnly=true)
 * )
 *
 * @OA\Schema(
 *   schema="LoginResponse",
 *   type="object",
 *   @OA\Property(property="token", type="string", description="Bearer token (Sanctum)")
 * )
 *
 * @OA\Schema(
 *   schema="LogoutResponse",
 *   type="object",
 *   @OA\Property(property="message", type="string", example="Logout realizado com sucesso")
 * )
 */
final class AuthSchemas {}
