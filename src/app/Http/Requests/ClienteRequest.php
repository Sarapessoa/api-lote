<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {

        $input = $this->all();

        $allowedFields = [
            'nome','endereco','telefone','email',
            'tipo_pessoa','cpf','cnpj',
            'responsavel_nome','responsavel_cpf'
        ];

        $unknown = array_diff(array_keys($input), $allowedFields);

        if (!empty($unknown)) {
            $this->merge(['unknown_fields' => implode(', ', $unknown)]);
        }

        $data = [];

        foreach (['nome','endereco','telefone','email','responsavel_nome'] as $f) {
            if ($this->has($f)) $data[$f] = is_string($this->input($f)) ? trim((string)$this->input($f)) : $this->input($f);
        }

        if ($this->has('tipo_pessoa')) $data['tipo_pessoa'] = strtoupper((string)$this->input('tipo_pessoa'));
        if ($this->has('cpf')) $data['cpf'] = preg_replace('/\D+/', '', (string)$this->input('cpf'));
        if ($this->has('cnpj')) $data['cnpj'] = preg_replace('/\D+/', '', (string)$this->input('cnpj'));
        if ($this->has('responsavel_cpf')) $data['responsavel_cpf'] = preg_replace('/\D+/', '', (string)$this->input('responsavel_cpf'));
        if ($this->has('email') && $this->input('email') !== null) $data['email'] = strtolower($data['email']);

        if (!empty($data)) $this->merge($data);
    }

    public function rules(): array
    {
        $routeParam = $this->route('cliente');
        $id = is_object($routeParam) ? ($routeParam->id ?? null) : $routeParam;

        if ($this->isMethod('post') || $this->isMethod('put')) {
            return [
                'nome' => ['bail','required','string','max:150'],
                'endereco' => ['bail','nullable','string'],
                'telefone' => ['bail','nullable','string','max:30'],
                'email' => ['bail','nullable','email','max:150'],
                'tipo_pessoa' => ['bail','required', Rule::in(['FISICA','JURIDICA'])],
                'unknown_fields' => 'prohibited',
                'cpf' => [
                    'bail',
                    Rule::requiredIf(fn() => $this->input('tipo_pessoa') === 'FISICA'),
                    'nullable',
                    'digits:11',
                    Rule::unique('clientes','cpf')->ignore($id)->whereNotNull('cpf'),
                    Rule::prohibitedIf(fn() => $this->input('tipo_pessoa') === 'JURIDICA')
                ],

                'cnpj' => [
                    'bail',
                    Rule::requiredIf(fn() => $this->input('tipo_pessoa') === 'JURIDICA'),
                    'nullable',
                    'digits:14',
                    Rule::unique('clientes','cnpj')->ignore($id)->whereNotNull('cnpj'),
                    Rule::prohibitedIf(fn() => $this->input('tipo_pessoa') === 'FISICA')
                ],

                'responsavel_nome' => [
                    'bail',
                    Rule::requiredIf(fn() => $this->input('tipo_pessoa') === 'JURIDICA'),
                    'nullable','string','max:150',
                    Rule::prohibitedIf(fn() => $this->input('tipo_pessoa') === 'FISICA')
                ],

                'responsavel_cpf' => [
                    'bail',
                    Rule::requiredIf(fn() => $this->input('tipo_pessoa') === 'JURIDICA'),
                    'nullable','digits:11',
                    Rule::prohibitedIf(fn() => $this->input('tipo_pessoa') === 'FISICA')
                ],
            ];
        }

        return [
            'nome' => ['bail','sometimes','required','string','max:150'],
            'endereco' => ['bail','sometimes','nullable','string'],
            'telefone' => ['bail','sometimes','nullable','string','max:30'],
            'email' => ['bail','sometimes','nullable','email','max:150'],
            'tipo_pessoa' => ['bail','sometimes','required', Rule::in(['FISICA','JURIDICA'])],
            'unknown_fields' => 'prohibited',
            'cpf' => [
                'bail','sometimes','nullable','digits:11',
                Rule::unique('clientes','cpf')->ignore($id)->whereNotNull('cpf'),
                Rule::prohibitedIf(fn() => $this->input('tipo_pessoa') === 'JURIDICA')
            ],
            'cnpj' => [
                'bail','sometimes','nullable','digits:14',
                Rule::unique('clientes','cnpj')->ignore($id)->whereNotNull('cnpj'),
                Rule::prohibitedIf(fn() => $this->input('tipo_pessoa') === 'FISICA')
            ],
            'responsavel_nome' => [
                'bail','sometimes','nullable','string','max:150',
                Rule::prohibitedIf(fn() => $this->input('tipo_pessoa') === 'FISICA')
            ],
            'responsavel_cpf' => [
                'bail','sometimes','nullable','digits:11',
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
            'cpf.digits' => 'O CPF deve conter 11 dígitos numéricos.',
            'cpf.unique' => 'CPF já cadastrado.',
            'cnpj.required' => 'O CNPJ é obrigatório para pessoa jurídica.',
            'cnpj.digits' => 'O CNPJ deve conter 14 dígitos numéricos.',
            'cnpj.unique' => 'CNPJ já cadastrado.',
            'responsavel_nome.required' => 'O nome do responsável é obrigatório para pessoa jurídica.',
            'responsavel_cpf.required' => 'O CPF do responsável é obrigatório para pessoa jurídica.',
            'unknown_fields' => 'Campos não permitidos: :input',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $failed = $validator->failed();

        $status = 422;

        if (isset($errors['email']) || isset($errors['nome']) || isset($errors['unknown_fields'])) {
            $status = 400;
        }

        $cpfHasUnique = isset($failed['cpf']['Unique']);
        $cnpjHasUnique = isset($failed['cnpj']['Unique']);
        if ($cpfHasUnique || $cnpjHasUnique) {
            $status = 409;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'Erro de validação',
            'errors' => $errors
        ], $status));
    }
}
