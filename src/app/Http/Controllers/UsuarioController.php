<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Http\Requests\UsuarioRequest;
use App\Http\Requests\UsuarioIndexRequest;
use App\Http\Resources\UsuarioResource;

use App\Support\ApiExceptionHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Throwable;

use OpenApi\Annotations as OA;

class UsuarioController extends Controller
{
    use ApiExceptionHandler;

    /**
     * @OA\Get(
     *   path="/api/usuarios",
     *   tags={"Usuarios"},
     *   summary="Listar usuarios",
     *   @OA\Parameter(name="username", in="query", @OA\Schema(type="string", minLength=1, maxLength=100)),
     *   @OA\Parameter(name="sort", in="query", @OA\Schema(type="string", enum={"username","created_at"})),
     *   @OA\Parameter(name="dir", in="query", @OA\Schema(type="string", enum={"asc","desc"})),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=100)),
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", minimum=1)),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/UsuarioPage")),
     *   @OA\Response(response=422, description="Filtro inválido", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function index(UsuarioIndexRequest $request)
    {
        $v = $request->validated();

        $q = Usuario::query();

        if ($request->filled('username')) {
            $q->where('username', 'ilike', '%'.$v['username'].'%');
        }

        $sort = $v['sort'] ?? 'username';
        $dir = $v['dir'] ?? 'asc';
        $perPage = (int)($v['per_page'] ?? 15);

        $usuarios = $q->orderBy($sort, $dir)->paginate($perPage);

        return UsuarioResource::collection($usuarios);
    }

    /**
     * @OA\Get(
     *   path="/api/usuarios/{id}",
     *   tags={"Usuarios"},
     *   summary="Detalhar usuario",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Usuario")),
     *   @OA\Response(response=404, description="Não encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(Usuario $usuario)
    {
        return new UsuarioResource($usuario);
    }

    /**
     * @OA\Post(
     *   path="/api/usuarios",
     *   tags={"Usuarios"},
     *   summary="Criar usuario",
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UsuarioStore")),
     *   @OA\Response(response=201, description="Criado", @OA\JsonContent(ref="#/components/schemas/Usuario")),
     *   @OA\Response(response=400, description="Campos mal formatados", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=409, description="Conflito (username em uso)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=422, description="Regra de validação", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function store(UsuarioRequest $request)
    {
        DB::beginTransaction();
        try {
            $dados = $request->validated();
            $usuario = Usuario::create($dados);
            DB::commit();
            return (new UsuarioResource($usuario))->response()->setStatusCode(201);
        } catch (Throwable $e) {
            DB::rollBack();
            return $this->apiException($e, 500, 'Erro ao criar usuario');
        }
    }

    /**
     * @OA\Put(
     *   path="/api/usuarios/{id}",
     *   tags={"Usuarios"},
     *   summary="Atualizar usuario (total)",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA.JsonContent(ref="#/components/schemas/UsuarioUpdate")),
     *   @OA\Response(response=200, description="Atualizado", @OA\JsonContent(ref="#/components/schemas/Usuario")),
     *   @OA\Response(response=400, description="Campos mal formatados", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=404, description="Não encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=409, description="Conflito (username em uso)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=422, description="Regra de validação", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     *
     * @OA\Patch(
     *   path="/api/usuarios/{id}",
     *   tags={"Usuarios"},
     *   summary="Atualizar usuario (parcial)",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UsuarioUpdate")),
     *   @OA\Response(response=200, description="Atualizado", @OA\JsonContent(ref="#/components/schemas/Usuario")),
     *   @OA\Response(response=400, description="Campos mal formatados", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=404, description="Não encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=409, description="Conflito (username em uso)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=422, description="Regra de validação", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function update(UsuarioRequest $request, Usuario $usuario)
    {
        DB::beginTransaction();
        try {
            $dados = $request->validated();
            if (array_key_exists('password', $dados) && $dados['password'] !== null && $dados['password'] !== '') {
                $dados['password'] = bcrypt($dados['password']);
            } else {
                unset($dados['password']);
            }
            $usuario->update($dados);
            DB::commit();
            return response()->json([
                'message' => 'Usuario atualizado',
                'data' => new UsuarioResource($usuario)
            ], 200);
        } catch (Throwable $e) {
            DB::rollBack();
            return $this->apiException($e, 500, 'Erro ao atualizar usuario');
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/usuarios/{id}",
     *   tags={"Usuarios"},
     *   summary="Excluir usuario",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Excluído", @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))),
     *   @OA\Response(response=404, description="Não encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function destroy(Usuario $usuario)
    {
        DB::beginTransaction();
        try {
            $usuario->delete();
            DB::commit();
            return response()->json([
                'message' => 'Usuario excluído com sucesso'
            ], 200);
        } catch (Throwable $e) {
            DB::rollBack();
            return $this->apiException($e, 500, 'Erro ao excluir usuario');
        }
    }
}
