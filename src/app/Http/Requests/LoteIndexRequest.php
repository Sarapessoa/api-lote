<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoteIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = $this->query();

        $allowed = ['nome','num_loteamento','num_quadra','num_lote','cliente_id','area_lote','sort','dir','per_page','page'];
        $unknown = array_diff(array_keys($data), $allowed);
        if (!empty($unknown)) {
            $this->merge(['unknown_params' => implode(', ', $unknown)]);
        }

        if (isset($data['nome']) && is_string($data['nome'])) $data['nome'] = trim($data['nome']);
        foreach (['num_loteamento','num_quadra','num_lote','cliente_id'] as $f) {
            if (isset($data[$f])) {
                $v = $data[$f];
                $data[$f] = is_numeric($v) ? (int)$v : $v;
            }
        }
        if (isset($data['area_lote'])) {
            $raw = (string)$data['area_lote'];
            $norm = str_replace(',', '.', $raw);
            $data['area_lote'] = is_numeric($norm) ? (float)$norm : $data['area_lote'];
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        return [
            'nome' => 'sometimes|string|min:2|max:120',
            'num_loteamento' => 'sometimes|integer|min:1',
            'num_quadra' => 'sometimes|integer|min:1',
            'num_lote' => 'sometimes|integer|min:1',
            'cliente_id' => 'sometimes|integer|min:1',
            'area_lote' => 'sometimes|numeric|decimal:0,2|min:0',
            'sort' => 'sometimes|in:nome,num_loteamento,num_quadra,num_lote,area_lote,created_at',
            'dir' => 'sometimes|in:asc,desc',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
            'unknown_params' => 'prohibited'
        ];
    }

    public function messages(): array
    {
        return [
            'unknown_params.prohibited' => 'Parâmetros desconhecidos: :input',
            'nome.min' => 'O nome deve ter pelo menos 2 caracteres.',
            'nome.max' => 'O nome deve ter no máximo 120 caracteres.',
            'num_loteamento.integer' => 'O número do loteamento deve ser um inteiro.',
            'num_loteamento.min' => 'O número do loteamento deve ser maior ou igual a 1.',
            'num_quadra.integer' => 'O número da quadra deve ser um inteiro.',
            'num_quadra.min' => 'O número da quadra deve ser maior ou igual a 1.',
            'num_lote.integer' => 'O número do lote deve ser um inteiro.',
            'num_lote.min' => 'O número do lote deve ser maior ou igual a 1.',
            'cliente_id.integer' => 'O cliente deve ser um inteiro.',
            'cliente_id.min' => 'O cliente deve ser maior ou igual a 1.',
            'area_lote.numeric' => 'A área do lote deve ser numérica.',
            'area_lote.decimal' => 'A área do lote deve ter no máximo duas casas decimais.',
            'area_lote.min' => 'A área do lote não pode ser negativa.',
            'sort.in' => 'Campo de ordenação inválido.',
            'dir.in' => 'Direção de ordenação inválida.',
            'per_page.integer' => 'per_page deve ser inteiro.',
            'per_page.min' => 'per_page mínimo é 1.',
            'per_page.max' => 'per_page máximo é 100.',
            'page.integer' => 'page deve ser inteiro.',
            'page.min' => 'page mínimo é 1.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Filtro inválido',
            'errors' => $validator->errors()->toArray()
        ], 422));
    }
}
