<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();

        $allowed = ['username','password'];
        $unknown = array_diff(array_keys($input), $allowed);
        if (!empty($unknown)) {
            $this->merge(['unknown_fields' => implode(', ', $unknown)]);
        }

        if ($this->has('username') && is_string($this->input('username'))) {
            $this->merge(['username' => trim((string)$this->input('username'))]);
        }
    }

    public function rules(): array
    {
        return [
            'username' => ['bail','required','string','min:1','max:100'],
            'password' => ['bail','required','string','min:5'],
            'unknown_fields' => 'prohibited'
        ];
    }

    public function messages(): array
    {
        return [
            'unknown_fields' => 'Campos não permitidos: :input',
            'username.required' => 'O username é obrigatório.',
            'username.min' => 'O username deve ter pelo menos 1 caractere.',
            'username.max' => 'O username deve ter no máximo 100 caracteres.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 5 caracteres.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Erro de validação',
            'errors' => $validator->errors()->toArray()
        ], 400));
    }
}
