<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

trait ApiExceptionHandler
{
    protected function apiException(Throwable $e, int $defaultStatus = 500, ?string $defaultMessage = null)
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'Recurso não encontrado'
            ], 404);
        }

        if ($e instanceof QueryException) {
            [$sqlState, $driverCode, $driverMsg] = [$e->errorInfo[0] ?? null, (string)($e->errorInfo[1] ?? ''), $e->errorInfo[2] ?? ''];
            $sqlState = $sqlState ? strtoupper($sqlState) : null;
            $lowerMsg = strtolower($driverMsg);

            // ---- Postgres
            if ($sqlState === '23505') { // unique_violation
                return $this->jsonDb('erro', $this->friendlyUniqueMessage($driverMsg), 409, $sqlState, $driverCode, $driverMsg);
            }
            if ($sqlState === '23503') { // foreign_key_violation
                return $this->jsonDb('erro', 'Violação de integridade referencial', 409, $sqlState, $driverCode, $driverMsg);
            }
            if ($sqlState === '23502') { // not_null_violation
                return $this->jsonDb('erro', 'Campo obrigatório ausente', 422, $sqlState, $driverCode, $driverMsg);
            }
            if ($sqlState === '23514') { // check_violation
                return $this->jsonDb('erro', $this->friendlyCheckMessage($driverMsg), 422, $sqlState, $driverCode, $driverMsg);
            }
            if ($sqlState === '22P02') { // invalid_text_representation (ex: integer inválido)
                return $this->jsonDb('erro', 'Formato de dado inválido', 400, $sqlState, $driverCode, $driverMsg);
            }
            if ($sqlState === '22003') { // numeric_value_out_of_range
                return $this->jsonDb('erro', 'Valor numérico fora do intervalo', 400, $sqlState, $driverCode, $driverMsg);
            }
            if ($sqlState === '22007') { // invalid_datetime_format
                return $this->jsonDb('erro', 'Data/hora em formato inválido', 400, $sqlState, $driverCode, $driverMsg);
            }
            if ($sqlState === '40P01') { // deadlock_detected
                return $this->jsonDb('erro', 'Conflito de concorrência, tente novamente', 409, $sqlState, $driverCode, $driverMsg);
            }

            return $this->jsonDb('erro', 'Erro de banco de dados', 500, $sqlState, $driverCode, $driverMsg);
        }

        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }

        $status = $defaultStatus ?? 500;
        $message = $defaultMessage ?? 'Erro interno do servidor';

        if (config('app.debug')) {
            return response()->json([
                'message' => $message,
                'exception' => class_basename($e),
                'error' => $e->getMessage()
            ], $status);
        }

        return response()->json([
            'message' => $message
        ], $status);
    }

    private function friendlyUniqueMessage(?string $driverMsg): string
    {
        $msg = 'Registro já existente';
        if (!$driverMsg) return $msg;

        $m = strtolower($driverMsg);

        if (str_contains($m, 'cpf') || str_contains($m, 'clientes_cpf_unique')) return 'CPF já cadastrado';
        if (str_contains($m, 'cnpj') || str_contains($m, 'clientes_cnpj_unique')) return 'CNPJ já cadastrado';

        if (str_contains($m, 'lotes_uk_localizacao') || (str_contains($m, 'num_loteamento') && str_contains($m, 'num_quadra') && str_contains($m, 'num_lote'))) {
            return 'Já existe um lote com essa localização (loteamento, quadra e lote)';
        }

        return $msg;
    }

    private function friendlyCheckMessage(?string $driverMsg): string
    {
        if (!$driverMsg) return 'Violação de regra de negócio';

        $m = strtolower($driverMsg);

        if (str_contains($m, 'clientes_tipo_pessoa_chk')) return 'Tipo de pessoa inválido';
        if (str_contains($m, 'clientes_tipo_regras_chk')) return 'Combinação de campos inválida para o tipo de pessoa';
        if (str_contains($m, 'clientes_cpf_fmt_chk') || str_contains($m, 'clientes_resp_cpf_fmt_chk')) return 'CPF em formato inválido';
        if (str_contains($m, 'clientes_cnpj_fmt_chk')) return 'CNPJ em formato inválido';

        return 'Violação de regra de negócio';
    }

    private function jsonDb(string $status, string $message, int $code, ?string $sqlState, ?string $driverCode, ?string $driverMsg)
    {
        $payload = ['message' => $message];

        if (config('app.debug')) {
            $payload['db'] = [
                'sqlstate' => $sqlState,
                'driverCode' => $driverCode,
                'driverMessage' => $driverMsg
            ];
        }

        return response()->json($payload, $code);
    }
}
