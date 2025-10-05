<?php

namespace App\Http\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="PaginationLinks",
 *   type="object",
 *   @OA\Property(property="first", type="string", nullable=true),
 *   @OA\Property(property="last", type="string", nullable=true),
 *   @OA\Property(property="prev", type="string", nullable=true),
 *   @OA\Property(property="next", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="PaginationMeta",
 *   type="object",
 *   @OA\Property(property="current_page", type="integer"),
 *   @OA\Property(property="from", type="integer", nullable=true),
 *   @OA\Property(property="last_page", type="integer"),
 *   @OA\Property(property="path", type="string"),
 *   @OA\Property(property="per_page", type="integer"),
 *   @OA\Property(property="to", type="integer", nullable=true),
 *   @OA\Property(property="total", type="integer")
 * )
 *
 * @OA\Schema(
 *   schema="ErrorResponse",
 *   type="object",
 *   @OA\Property(property="message", type="string", example="Erro de validação"),
 *   @OA\Property(property="errors", type="object", nullable=true, additionalProperties=@OA\Schema())
 * )
 */
final class CommonSchemas {}
