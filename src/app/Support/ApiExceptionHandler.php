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
            $status = 422;
            $payload = [
                'status' => 'erro',
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ];
            return response()->json($payload, $status);
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->json([
                'status' => 'erro',
                'message' => 'Recurso não encontrado'
            ], 404);
        }

        if ($e instanceof QueryException) {
            [$sqlState, $driverCode, $driverMsg] = [$e->errorInfo[0] ?? null, $e->errorInfo[1] ?? null, $e->errorInfo[2] ?? null];

            // Postgres
            if ($sqlState === '23505') { // unique_violation
                return response()->json([
                    'status' => 'erro',
                    'message' => $this->friendlyUniqueMessage($driverMsg)
                ], 409);
            }
            if ($sqlState === '23503') { // foreign_key_violation
                return response()->json([
                    'status' => 'erro',
                    'message' => 'Violação de integridade referencial'
                ], 409);
            }
            if ($sqlState === '23502') { // not_null_violation
                return response()->json([
                    'status' => 'erro',
                    'message' => 'Campo obrigatório ausente'
                ], 422);
            }

            if ($sqlState === '23000' && (string)$driverCode === '1062') { // duplicate entry
                return response()->json([
                    'status' => 'erro',
                    'message' => $this->friendlyUniqueMessage($driverMsg)
                ], 409);
            }
            if ($sqlState === '23000' && in_array((string)$driverCode, ['1451','1452'], true)) { // FK
                return response()->json([
                    'status' => 'erro',
                    'message' => 'Violação de integridade referencial'
                ], 409);
            }

            return response()->json([
                'status' => 'erro',
                'message' => 'Erro de banco de dados'
            ], 500);
        }

        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }

        $status = $defaultStatus ?? 500;
        $message = $defaultMessage ?? 'Erro interno do servidor';

        if (config('app.debug')) {
            return response()->json([
                'status' => 'erro',
                'message' => $message,
                'exception' => class_basename($e),
                'error' => $e->getMessage()
            ], $status);
        }

        return response()->json([
            'status' => 'erro',
            'message' => $message
        ], $status);
    }

    private function friendlyUniqueMessage(?string $driverMsg): string
    {
        $msg = 'Registro já existente';
        if (!$driverMsg) return $msg;

        $m = strtolower($driverMsg);
        if (str_contains($m, 'cpf') || str_contains($m, 'clientes_cpf_unique')) {
            return 'CPF já cadastrado';
        }
        if (str_contains($m, 'cnpj') || str_contains($m, 'clientes_cnpj_unique')) {
            return 'CNPJ já cadastrado';
        }
        return $msg;
    }
}
