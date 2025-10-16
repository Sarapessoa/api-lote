<?php

namespace App\Http\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="Usuario",
 *   type="object",
 *   required={"id","username"},
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="username", type="string", maxLength=100),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="UsuarioStore",
 *   type="object",
 *   required={"username","password"},
 *   @OA\Property(property="username", type="string", maxLength=100, description="Deve ser único"),
 *   @OA\Property(property="password", type="string", minLength=6, writeOnly=true)
 * )
 *
 * @OA\Schema(
 *   schema="UsuarioUpdate",
 *   type="object",
 *   @OA\Property(property="username", type="string", maxLength=100, description="Deve ser único"),
 *   @OA\Property(property="password", type="string", minLength=6, writeOnly=true)
 * )
 *
 * @OA\Schema(
 *   schema="UsuarioPage",
 *   type="object",
 *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Usuario")),
 *   @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *   @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
 * )
 */
final class UsuarioSchemas {}
