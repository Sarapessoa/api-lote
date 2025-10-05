<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Http\Requests\ClienteRequest;
use App\Http\Requests\ClienteIndexRequest;
use App\Http\Resources\ClienteResource;

use App\Support\ApiExceptionHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Throwable;

class ClienteController extends Controller
{
    use ApiExceptionHandler;

    public function index(ClienteIndexRequest $request)
    {

        $v = $request->validated();

        $q = Cliente::query();

        if ($request->filled('nome')) $q->where('nome', 'ilike', '%'.$v('nome').'%');
        if ($request->filled('tipo_pessoa')) $q->where('tipo_pessoa', $v('tipo_pessoa'));
        if ($request->filled('cpf')) $q->where('cpf', $v('cpf'));
        if ($request->filled('cnpj')) $q->where('cnpj', $v('cnpj'));
        if ($request->filled('email')) $q->where('email', 'ilike', '%'.$v('email').'%');

        $sort = $v['sort'] ?? 'nome';
        $dir = $v['dir'] ?? 'asc';
        $perPage = (int)($v['per_page'] ?? 15);

        $clientes = $q->orderBy($sort, $dir)->paginate($perPage);

        return ClienteResource::collection($clientes);
    }

    public function show(Cliente $cliente)
    {
        return new ClienteResource($cliente);
    }

    public function store(ClienteRequest $request)
    {
        DB::beginTransaction();
        try {
            $cliente = Cliente::create($request->validated());
            DB::commit();
            return (new ClienteResource($cliente))->response()->setStatusCode(201);
        } catch (Throwable $e) {
            DB::rollBack();
            return $this->apiException($e, 500, 'Erro ao criar cliente');
        }
    }

    public function update(ClienteRequest $request, Cliente $cliente)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $cliente->update($validated);
            
            DB::commit();

            return response()->json([
                'message' => 'Cliente atualizado',
                'data' => new ClienteResource($cliente)
            ], 200);            
        } catch (Throwable $e) {
            DB::rollBack();
            return $this->apiException($e, 500, 'Erro ao atualizar cliente');
        }
    }

    public function destroy(Cliente $cliente)
    {
        DB::beginTransaction();
        try {
            $cliente->delete();
            DB::commit();
            return response()->json([
                'message' => 'Cliente excluído com sucesso'
            ], 200);
        } catch (Throwable $e) {
            DB::rollBack();
            return $this->apiException($e, 500, 'Erro ao excluir cliente');
        }
    }
}
