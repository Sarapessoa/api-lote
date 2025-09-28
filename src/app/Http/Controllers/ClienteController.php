<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Http\Requests\ClienteRequest;
use App\Http\Resources\ClienteResource;

use App\Support\ApiExceptionHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Throwable;

class ClienteController extends Controller
{
    use ApiExceptionHandler;

    public function index(Request $request)
    {
        $q = Cliente::query();

        if ($request->filled('nome')) $q->where('nome', 'ilike', '%'.$request->get('nome').'%');
        if ($request->filled('tipo_pessoa')) $q->where('tipo_pessoa', $request->get('tipo_pessoa'));
        if ($request->filled('cpf')) $q->where('cpf', $request->get('cpf'));
        if ($request->filled('cnpj')) $q->where('cnpj', $request->get('cnpj'));
        if ($request->filled('email')) $q->where('email', 'ilike', '%'.$request->get('email').'%');

        $sort = $request->get('sort', 'nome');
        $dir = $request->get('dir', 'asc');
        if (!in_array($sort, ['nome','tipo_pessoa','created_at'])) $sort = 'nome';
        if (!in_array($dir, ['asc','desc'])) $dir = 'asc';

        $perPage = (int) $request->get('per_page', 15);

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
                'status' => 'sucesso',
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
                'status' => 'sucesso',
                'message' => 'Cliente excluído com sucesso'
            ], 200);
        } catch (Throwable $e) {
            DB::rollBack();
            return $this->apiException($e, 500, 'Erro ao excluir cliente');
        }
    }
}
