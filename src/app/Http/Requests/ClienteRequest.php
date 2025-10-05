<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClienteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('tipo_pessoa')) {
            $data['tipo_pessoa'] = strtoupper((string)$this->input('tipo_pessoa'));
        }
        if ($this->has('cpf')) {
            $data['cpf'] = preg_replace('/\D+/', '', (string)$this->input('cpf'));
        }
        if ($this->has('cnpj')) {
            $data['cnpj'] = preg_replace('/\D+/', '', (string)$this->input('cnpj'));
        }
        if ($this->has('responsavel_cpf')) {
            $data['responsavel_cpf'] = preg_replace('/\D+/', '', (string)$this->input('responsavel_cpf'));
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('cliente');

        if ($this->isMethod('post') || $this->isMethod('put')) {
            return [
                'nome' => ['required','string','max:150'],
                'endereco' => ['nullable','string'],
                'telefone' => ['nullable','string','max:30'],
                'email' => ['nullable','email','max:150'],
                'tipo_pessoa' => ['required', Rule::in(['FISICA','JURIDICA'])],
                'cpf' => [
                    Rule::requiredIf(fn() => $this->input('tipo_pessoa') === 'FISICA'),
                    'nullable',
                    'regex:/^[0-9]{11}$/',
                    Rule::unique('clientes','cpf')->ignore($id)->whereNotNull('cpf'),
                    Rule::prohibitedIf(fn() => $this->input('tipo_pessoa') === 'JURIDICA')
                ],
                'cnpj' => [
                    Rule::requiredIf(fn() => $this->input('tipo_pessoa') === 'JURIDICA'),
                    'nullable',
                    'regex:/^[0-9]{14}$/',
                    Rule::unique('clientes','cnpj')->ignore($id)->whereNotNull('cnpj'),
                    Rule::prohibitedIf(fn() => $this->input('tipo_pessoa') === 'FISICA')
                ],
                'responsavel_nome' => [
                    Rule::requiredIf(fn() => $this->input('tipo_pessoa') === 'JURIDICA'),
                    'nullable','string','max:150',
                    Rule::prohibitedIf(fn() => $this->input('tipo_pessoa') === 'FISICA')
                ],
                'responsavel_cpf' => [
                    Rule::requiredIf(fn() => $this->input('tipo_pessoa') === 'JURIDICA'),
                    'nullable','regex:/^[0-9]{11}$/',
                    Rule::prohibitedIf(fn() => $this->input('tipo_pessoa') === 'FISICA')
                ],
            ];
        }

        return [
            'nome' => ['sometimes','required','string','max:150'],
            'endereco' => ['sometimes','nullable','string'],
            'telefone' => ['sometimes','nullable','string','max:30'],
            'email' => ['sometimes','nullable','email','max:150'],
            'tipo_pessoa' => ['sometimes','required', Rule::in(['FISICA','JURIDICA'])],
            'cpf' => [
                'sometimes','nullable','regex:/^[0-9]{11}$/',
                Rule::unique('clientes','cpf')->ignore($id)->whereNotNull('cpf'),
                Rule::prohibitedIf(fn() => $this->input('tipo_pessoa') === 'JURIDICA')
            ],
            'cnpj' => [
                'sometimes','nullable','regex:/^[0-9]{14}$/',
                Rule::unique('clientes','cnpj')->ignore($id)->whereNotNull('cnpj'),
                Rule::prohibitedIf(fn() => $this->input('tipo_pessoa') === 'FISICA')
            ],
            'responsavel_nome' => [
                'sometimes','nullable','string','max:150',
                Rule::prohibitedIf(fn() => $this->input('tipo_pessoa') === 'FISICA')
            ],
            'responsavel_cpf' => [
                'sometimes','nullable','regex:/^[0-9]{11}$/',
                Rule::prohibitedIf(fn() => $this->input('tipo_pessoa') === 'FISICA')
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'tipo_pessoa.in' => 'Tipo de pessoa inválido (use FISICA ou JURIDICA).',
            'cpf.required' => 'O CPF é obrigatório para pessoa física.',
            'cpf.regex' => 'O CPF deve conter 11 dígitos numéricos.',
            'cpf.unique' => 'CPF já cadastrado.',
            'cnpj.required' => 'O CNPJ é obrigatório para pessoa jurídica.',
            'cnpj.regex' => 'O CNPJ deve conter 14 dígitos numéricos.',
            'cnpj.unique' => 'CNPJ já cadastrado.',
            'responsavel_nome.required' => 'O nome do responsável é obrigatório para pessoa jurídica.',
            'responsavel_cpf.required' => 'O CPF do responsável é obrigatório para pessoa jurídica.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $status = 422;

        if (isset($errors['email'])) $status = 400;
        elseif (isset($errors['cpf']) || isset($errors['cnpj'])) $status = 409;
        elseif (isset($errors['tipo_pessoa'])) $status = 422;
        elseif (isset($errors['nome'])) $status = 400;

        throw new HttpResponseException(response()->json([
            'message' => 'Erro de validação',
            'errors' => $errors
        ], $status));
    }
}
