<?php

namespace App\Http\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="Lote",
 *   type="object",
 *   required={"id","nome","num_loteamento","num_quadra","num_lote"},
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="nome", type="string", maxLength=120),
 *   @OA\Property(property="num_loteamento", type="integer", minimum=1),
 *   @OA\Property(property="num_quadra", type="integer", minimum=1),
 *   @OA\Property(property="num_lote", type="integer", minimum=1),
 *   @OA\Property(property="cliente_id", type="integer", nullable=true),
 *   @OA\Property(property="area_lote", type="number", format="float", nullable=true),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="LoteStore",
 *   type="object",
 *   required={"nome","num_loteamento","num_quadra","num_lote"},
 *   @OA\Property(property="nome", type="string", maxLength=120),
 *   @OA\Property(property="num_loteamento", type="integer", minimum=1),
 *   @OA\Property(property="num_quadra", type="integer", minimum=1),
 *   @OA\Property(property="num_lote", type="integer", minimum=1),
 *   @OA\Property(property="cliente_id", type="integer", nullable=true),
 *   @OA\Property(property="area_lote", type="number", format="float", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="LoteUpdate",
 *   type="object",
 *   @OA\Property(property="nome", type="string", maxLength=120),
 *   @OA\Property(property="num_loteamento", type="integer", minimum=1),
 *   @OA\Property(property="num_quadra", type="integer", minimum=1),
 *   @OA\Property(property="num_lote", type="integer", minimum=1),
 *   @OA\Property(property="cliente_id", type="integer", nullable=true),
 *   @OA\Property(property="area_lote", type="number", format="float", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="LotePage",
 *   type="object",
 *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Lote")),
 *   @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *   @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
 * )
 */
final class LoteSchemas {}
