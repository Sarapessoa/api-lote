<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();

        $allowed = ['nome','num_loteamento','num_quadra','num_lote','cliente_id','area_lote'];
        $unknown = array_diff(array_keys($input), $allowed);
        if (!empty($unknown)) {
            $this->merge(['unknown_fields' => implode(', ', $unknown)]);
        }

        $data = [];

        if ($this->has('nome')) $data['nome'] = is_string($this->input('nome')) ? trim((string)$this->input('nome')) : $this->input('nome');

        foreach (['num_loteamento','num_quadra','num_lote'] as $f) {
            if ($this->has($f)) {
                $v = $this->input($f);
                $data[$f] = is_numeric($v) ? (int)$v : $v;
            }
        }

        if ($this->has('area_lote')) {
            $raw = (string)$this->input('area_lote');
            $norm = str_replace(',', '.', $raw);
            $data['area_lote'] = is_numeric($norm) ? (float)$norm : $this->input('area_lote');
        }

        if (!empty($data)) $this->merge($data);
    }

    public function rules(): array
    {
        $routeParam = $this->route('lote');
        $id = is_object($routeParam) ? ($routeParam->id ?? null) : $routeParam;

        $current = is_object($routeParam) ? $routeParam : null;
        $numLoteamento = $this->has('num_loteamento') ? $this->input('num_loteamento') : ($current->num_loteamento ?? null);
        $numQuadra = $this->has('num_quadra') ? $this->input('num_quadra') : ($current->num_quadra ?? null);
        $numLote = $this->has('num_lote') ? $this->input('num_lote') : ($current->num_lote ?? null);

        $uniqueLocalizacao = Rule::unique('lotes')
            ->where(fn($q) => $q
                ->where('num_loteamento', $numLoteamento)
                ->where('num_quadra', $numQuadra)
                ->where('num_lote', $numLote)
            )
            ->ignore($id);

        if ($this->isMethod('post') || $this->isMethod('put')) {
            return [
                'nome' => ['bail','required','string','max:120'],
                'num_loteamento' => ['bail','required','integer','min:1'],
                'num_quadra' => ['bail','required','integer','min:1'],
                'num_lote' => ['bail','required','integer','min:1',$uniqueLocalizacao],
                'cliente_id' => ['bail','sometimes','nullable','exists:clientes,id'],
                'area_lote' => ['bail','sometimes','nullable','numeric','decimal:0,2','gt:0'],
                'unknown_fields' => 'prohibited'
            ];
        }

        return [
            'nome' => ['bail','sometimes','required','string','max:120'],
            'num_loteamento' => ['bail','sometimes','required','integer','min:1'],
            'num_quadra' => ['bail','sometimes','required','integer','min:1'],
            'num_lote' => ['bail','sometimes','required','integer','min:1',$uniqueLocalizacao],
            'cliente_id' => ['bail','sometimes','nullable','exists:clientes,id'],
            'area_lote' => ['bail','sometimes','nullable','numeric','decimal:0,2','gt:0'],
            'unknown_fields' => 'prohibited'
        ];
    }

    public function messages(): array
    {
        return [
            'unknown_fields' => 'Campos não permitidos: :input',
            'nome.required' => 'O nome do lote é obrigatório.',
            'nome.max' => 'O nome do lote deve ter no máximo 120 caracteres.',
            'num_loteamento.required' => 'O número do loteamento é obrigatório.',
            'num_loteamento.integer' => 'O número do loteamento deve ser um inteiro.',
            'num_loteamento.min' => 'O número do loteamento deve ser maior ou igual a 1.',
            'num_quadra.required' => 'O número da quadra é obrigatório.',
            'num_quadra.integer' => 'O número da quadra deve ser um inteiro.',
            'num_quadra.min' => 'O número da quadra deve ser maior ou igual a 1.',
            'num_lote.required' => 'O número do lote é obrigatório.',
            'num_lote.integer' => 'O número do lote deve ser um inteiro.',
            'num_lote.min' => 'O número do lote deve ser maior ou igual a 1.',
            'num_lote.unique' => 'Já existe um lote com essa localização (loteamento, quadra e lote).',
            'cliente_id.exists' => 'Cliente informado não existe.',
            'area_lote.numeric' => 'A área do lote deve ser numérica.',
            'area_lote.decimal' => 'A área do lote deve ter no máximo duas casas decimais.',
            'area_lote.gt' => 'A área do lote deve ser maior que zero.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $failed = $validator->failed();

        $status = 422;

        $localizacaoUnique = isset($failed['num_lote']['Unique']);
        if ($localizacaoUnique) {
            $status = 409;
        } elseif (isset($errors['unknown_fields'])) {
            $status = 400;
        } elseif (isset($errors['nome']) || isset($errors['num_loteamento']) || isset($errors['num_quadra']) || isset($errors['num_lote']) || isset($errors['area_lote']) || isset($errors['cliente_id'])) {
            $status = 400;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'Erro de validação',
            'errors' => $errors
        ], $status));
    }
}
