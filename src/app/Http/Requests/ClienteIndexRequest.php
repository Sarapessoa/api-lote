<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClienteIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = $this->query();

        $allowed = ['nome','tipo_pessoa','cpf','cnpj','email','sort','dir','per_page','page'];
        $unknown = array_diff(array_keys($data), $allowed);

        if (!empty($unknown)) {
            $this->merge(['unknown_params' => implode(', ', $unknown)]);
        }

        if (!empty($data['tipo_pessoa'])) $data['tipo_pessoa'] = strtoupper((string)$data['tipo_pessoa']);
        if (!empty($data['cpf'])) $data['cpf'] = preg_replace('/\D+/', '', (string)$data['cpf']);
        if (!empty($data['cnpj'])) $data['cnpj'] = preg_replace('/\D+/', '', (string)$data['cnpj']);

        $this->merge($data);
    }

    public function rules(): array
    {
        return [
            'nome' => 'sometimes|string|min:2|max:150',
            'tipo_pessoa' => 'sometimes|in:FISICA,JURIDICA',
            'cpf' => 'sometimes|regex:/^[0-9]{11}$/',
            'cnpj' => 'sometimes|regex:/^[0-9]{14}$/',
            'email' => 'sometimes|email|max:150',
            'sort' => 'sometimes|in:nome,tipo_pessoa,created_at',
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
            'tipo_pessoa.in' => 'Tipo de pessoa inválido (use FISICA ou JURIDICA).',
            'cpf.regex' => 'O CPF deve conter 11 dígitos numéricos.',
            'cnpj.regex' => 'O CNPJ deve conter 14 dígitos numéricos.',
            'email.email' => 'Informe um e-mail válido.',
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
