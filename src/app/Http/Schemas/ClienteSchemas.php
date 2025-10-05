<?php

namespace App\Http\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="Cliente",
 *   type="object",
 *   required={"id","nome","tipo_pessoa"},
 *   @OA\Property(property="id", type="integer", format="int64"),
 *   @OA\Property(property="nome", type="string", maxLength=150),
 *   @OA\Property(property="endereco", type="string", nullable=true),
 *   @OA\Property(property="telefone", type="string", maxLength=30, nullable=true),
 *   @OA\Property(property="email", type="string", format="email", nullable=true, maxLength=150),
 *   @OA\Property(property="tipo_pessoa", type="string", enum={"FISICA","JURIDICA"}),
 *   @OA\Property(property="cpf", type="string", nullable=true, description="11 dígitos"),
 *   @OA\Property(property="cnpj", type="string", nullable=true, description="14 dígitos"),
 *   @OA\Property(property="responsavel_nome", type="string", nullable=true, maxLength=150),
 *   @OA\Property(property="responsavel_cpf", type="string", nullable=true, description="11 dígitos"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="ClienteStore",
 *   type="object",
 *   required={"nome","tipo_pessoa"},
 *   @OA\Property(property="nome", type="string", maxLength=150),
 *   @OA\Property(property="endereco", type="string", nullable=true),
 *   @OA\Property(property="telefone", type="string", maxLength=30, nullable=true),
 *   @OA\Property(property="email", type="string", format="email", nullable=true, maxLength=150),
 *   @OA\Property(property="tipo_pessoa", type="string", enum={"FISICA","JURIDICA"}),
 *   @OA\Property(property="cpf", type="string", nullable=true, description="Obrigatório se FISICA; 11 dígitos"),
 *   @OA\Property(property="cnpj", type="string", nullable=true, description="Obrigatório se JURIDICA; 14 dígitos"),
 *   @OA\Property(property="responsavel_nome", type="string", nullable=true, description="Obrigatório se JURIDICA"),
 *   @OA\Property(property="responsavel_cpf", type="string", nullable=true, description="Obrigatório se JURIDICA; 11 dígitos")
 * )
 *
 * @OA\Schema(
 *   schema="ClienteUpdate",
 *   type="object",
 *   @OA\Property(property="nome", type="string", maxLength=150),
 *   @OA\Property(property="endereco", type="string", nullable=true),
 *   @OA\Property(property="telefone", type="string", maxLength=30, nullable=true),
 *   @OA\Property(property="email", type="string", format="email", nullable=true, maxLength=150),
 *   @OA\Property(property="tipo_pessoa", type="string", enum={"FISICA","JURIDICA"}),
 *   @OA\Property(property="cpf", type="string", nullable=true),
 *   @OA\Property(property="cnpj", type="string", nullable=true),
 *   @OA\Property(property="responsavel_nome", type="string", nullable=true),
 *   @OA\Property(property="responsavel_cpf", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="ClientePage",
 *   type="object",
 *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Cliente")),
 *   @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *   @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
 * )
 */
final class ClienteSchemas {}