<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UsuarioIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = $this->query();

        $allowed = ['username','sort','dir','per_page','page'];
        $unknown = array_diff(array_keys($data), $allowed);
        if (!empty($unknown)) {
            $this->merge(['unknown_params' => implode(', ', $unknown)]);
        }

        if (isset($data['username']) && is_string($data['username'])) {
            $data['username'] = trim($data['username']);
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        return [
            'username' => 'sometimes|string|min:1|max:100',
            'sort' => 'sometimes|in:username,created_at',
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
            'username.min' => 'O username deve ter pelo menos 1 caractere.',
            'username.max' => 'O username deve ter no máximo 100 caracteres.',
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
