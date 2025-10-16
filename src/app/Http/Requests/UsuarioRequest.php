<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UsuarioRequest extends FormRequest
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

        $data = [];
        if ($this->has('username')) {
            $data['username'] = is_string($this->input('username')) ? trim((string)$this->input('username')) : $this->input('username');
        }
        if ($this->has('password')) {
            $data['password'] = $this->input('password');
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    public function rules(): array
    {
        $routeParam = $this->route('usuario');
        $id = is_object($routeParam) ? ($routeParam->id ?? null) : $routeParam;

        $uniqueUsername = Rule::unique('usuarios', 'username')->ignore($id);

        if ($this->isMethod('post') || $this->isMethod('put')) {
            return [
                'username' => ['bail','required','string','max:100',$uniqueUsername],
                'password' => ['bail','required','string','min:6'],
                'unknown_fields' => 'prohibited'
            ];
        }

        // PATCH
        return [
            'username' => ['bail','sometimes','required','string','max:100',$uniqueUsername],
            'password' => ['bail','sometimes','required','string','min:6'],
            'unknown_fields' => 'prohibited'
        ];
    }

    public function messages(): array
    {
        return [
            'unknown_fields' => 'Campos não permitidos: :input',
            'username.required' => 'O username é obrigatório.',
            'username.max' => 'O username deve ter no máximo 100 caracteres.',
            'username.unique' => 'Já existe um usuário com esse username.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $failed = $validator->failed();

        $status = 422;

        $usernameUnique = isset($failed['username']['Unique']);
        if ($usernameUnique) {
            $status = 409;
        } elseif (isset($errors['unknown_fields'])) {
            $status = 400;
        } elseif (isset($errors['username']) || isset($errors['password'])) {
            $status = 400;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'Erro de validação',
            'errors' => $errors
        ], $status));
    }
}
