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

use OpenApi\Annotations as OA;

class ClienteController extends Controller
{
    use ApiExceptionHandler;

    /**
     * @OA\Get(
     *   path="/api/clientes",
     *   tags={"Clientes"},
     *   summary="Listar clientes",
     *   @OA\Parameter(name="nome", in="query", @OA\Schema(type="string", minLength=2, maxLength=150)),
     *   @OA\Parameter(name="tipo_pessoa", in="query", @OA\Schema(type="string", enum={"FISICA","JURIDICA"})),
     *   @OA\Parameter(name="cpf", in="query", @OA\Schema(type="string", pattern="^[0-9]{11}$")),
     *   @OA\Parameter(name="cnpj", in="query", @OA\Schema(type="string", pattern="^[0-9]{14}$")),
     *   @OA\Parameter(name="email", in="query", @OA\Schema(type="string", format="email")),
     *   @OA\Parameter(name="sort", in="query", @OA\Schema(type="string", enum={"nome","tipo_pessoa","created_at"})),
     *   @OA\Parameter(name="dir", in="query", @OA\Schema(type="string", enum={"asc","desc"})),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=100)),
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", minimum=1)),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     content={
     *       @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/ClientePage")
     *       )
     *     }
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Filtro inválido",
     *     content={
     *       @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/ErrorResponse")
     *       )
     *     }
     *   )
     * )
     */
    public function index(ClienteIndexRequest $request)
    {

        $v = $request->validated();

        $q = Cliente::query();

        if ($request->filled('nome')) $q->where('nome', 'ilike', '%'.$v['nome'].'%');
        if ($request->filled('tipo_pessoa')) $q->where('tipo_pessoa', $v['tipo_pessoa']);
        if ($request->filled('cpf')) $q->where('cpf', $v['cpf']);
        if ($request->filled('cnpj')) $q->where('cnpj', $v['cnpj']);
        if ($request->filled('email')) $q->where('email', 'ilike', '%'.$v['email'].'%');

        $sort = $v['sort'] ?? 'nome';
        $dir = $v['dir'] ?? 'asc';
        $perPage = (int)($v['per_page'] ?? 15);

        $clientes = $q->orderBy($sort, $dir)->paginate($perPage);

        return ClienteResource::collection($clientes);
    }

    /**
     * @OA\Get(
     *   path="/api/clientes/{id}",
     *   tags={"Clientes"},
     *   summary="Detalhar cliente",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Cliente")),
     *   @OA\Response(response=404, description="Não encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(Cliente $cliente)
    {
        return new ClienteResource($cliente);
    }

    /**
     * @OA\Post(
     *   path="/api/clientes",
     *   tags={"Clientes"},
     *   summary="Criar cliente",
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ClienteStore")),
     *   @OA\Response(response=201, description="Criado", @OA\JsonContent(ref="#/components/schemas/Cliente")),
     *   @OA\Response(response=400, description="Campos mal formatados", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=409, description="Conflito (CPF/CNPJ já existentes)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=422, description="Regra de validação", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
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

    /**
     * @OA\Put(
     *   path="/api/clientes/{id}",
     *   tags={"Clientes"},
     *   summary="Atualizar cliente (total)",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ClienteUpdate")),
     *   @OA\Response(response=200, description="Atualizado", @OA\JsonContent(ref="#/components/schemas/Cliente")),
     *   @OA\Response(response=400, description="Campos mal formatados", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=404, description="Não encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=409, description="Conflito (CPF/CNPJ já existentes)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=422, description="Regra de validação", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     *
     * @OA\Patch(
     *   path="/api/clientes/{id}",
     *   tags={"Clientes"},
     *   summary="Atualizar cliente (parcial)",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ClienteUpdate")),
     *   @OA\Response(response=200, description="Atualizado", @OA\JsonContent(ref="#/components/schemas/Cliente")),
     *   @OA\Response(response=400, description="Campos mal formatados", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=404, description="Não encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=409, description="Conflito (CPF/CNPJ já existentes)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=422, description="Regra de validação", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
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

    /**
     * @OA\Delete(
     *   path="/api/clientes/{id}",
     *   tags={"Clientes"},
     *   summary="Excluir cliente",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Excluído", @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))),
     *   @OA\Response(response=404, description="Não encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
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
