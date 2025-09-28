<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'endereco' => $this->endereco,
            'telefone' => $this->telefone,
            'email' => $this->email,
            'tipo_pessoa' => $this->tipo_pessoa,
            'cpf' => $this->cpf,
            'cnpj' => $this->cnpj,
            'responsavel_nome' => $this->responsavel_nome,
            'responsavel_cpf' => $this->responsavel_cpf,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
