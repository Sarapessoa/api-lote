<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoteRequest extends FormRequest
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

        if ($this->has('num_loteamento')) {
            $data['num_loteamento'] = is_numeric($this->input('num_loteamento')) ? (int)$this->input('num_loteamento') : $this->input('num_loteamento');
        }
        if ($this->has('num_quadra')) {
            $data['num_quadra'] = is_numeric($this->input('num_quadra')) ? (int)$this->input('num_quadra') : $this->input('num_quadra');
        }
        if ($this->has('num_lote')) {
            $data['num_lote'] = is_numeric($this->input('num_lote')) ? (int)$this->input('num_lote') : $this->input('num_lote');
        }
        if ($this->has('area_lote')) {
            $raw = (string)$this->input('area_lote');
            $norm = str_replace(',', '.', $raw);
            $data['area_lote'] = is_numeric($norm) ? (float)$norm : $this->input('area_lote');
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
        $id = $this->route('lote');

        $uniqueLocalizacao = Rule::unique('lotes')
                                ->where(fn($q) => $q
                                    ->where('num_loteamento', $this->input('num_loteamento'))
                                    ->where('num_quadra', $this->input('num_quadra'))
                                    ->where('num_lote', $this->input('num_lote'))
                                )
                                ->ignore($id);

        if ($this->isMethod('put')) {
            return [
                'nome' => ['required','string','max:120'],
                'num_loteamento' => ['required','integer','min:1'],
                'num_quadra' => ['required','integer','min:1'],
                'num_lote' => ['required','integer','min:1',$uniqueLocalizacao],
                'area_lote' => ['sometimes','numeric','decimal:0,2','gt:0']
            ];
        }

        return [
            'nome' => ['sometimes','required','string','max:120'],
            'num_loteamento' => ['sometimes','required','integer','min:1'],
            'num_quadra' => ['sometimes','required','integer','min:1'],
            'num_lote' => ['sometimes','required','integer','min:1',$uniqueLocalizacao],
            'area_lote' => ['sometimes','required','numeric','decimal:0,2','gt:0']
        ];
    }

    public function messages(): array
    {
        return [
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
            'area_lote.required' => 'A área do lote é obrigatória.',
            'area_lote.numeric' => 'A área do lote deve ser numérica.',
            'area_lote.decimal' => 'A área do lote deve ter no máximo duas casas decimais.',
            'area_lote.gt' => 'A área do lote deve ser maior que zero.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $status = 422;

        if (isset($errors['num_lote']) && in_array('Já existe um lote com essa localização (loteamento, quadra e lote).', $errors['num_lote'])) {
            $status = 409;
        } elseif (isset($errors['area_lote']) || isset($errors['num_loteamento']) || isset($errors['num_quadra']) || isset($errors['num_lote'])) {
            $status = 400;
        } elseif (isset($errors['nome'])) {
            $status = 400;
        }

        throw new HttpResponseException(response()->json([
            'status' => 'erro',
            'message' => 'Erro de validação',
            'errors' => $errors
        ], $status));
    }
}
