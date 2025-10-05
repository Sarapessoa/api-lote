<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Http\Requests\LoteRequest;
use App\Http\Requests\LoteIndexRequest;
use App\Http\Resources\LoteResource;

use App\Support\ApiExceptionHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Throwable;

use OpenApi\Annotations as OA;

class LoteController extends Controller
{
    use ApiExceptionHandler;

    /**
     * @OA\Get(
     *   path="/api/lotes",
     *   tags={"Lotes"},
     *   summary="Listar lotes",
     *   @OA\Parameter(name="nome", in="query", @OA\Schema(type="string", minLength=2, maxLength=120)),
     *   @OA\Parameter(name="num_loteamento", in="query", @OA\Schema(type="integer", minimum=1)),
     *   @OA\Parameter(name="num_quadra", in="query", @OA\Schema(type="integer", minimum=1)),
     *   @OA\Parameter(name="num_lote", in="query", @OA\Schema(type="integer", minimum=1)),
     *   @OA\Parameter(name="cliente_id", in="query", @OA\Schema(type="integer", minimum=1)),
     *   @OA\Parameter(name="area_lote", in="query", @OA\Schema(type="number", format="float", minimum=0)),
     *   @OA\Parameter(name="sort", in="query", @OA\Schema(type="string", enum={"nome","num_loteamento","num_quadra","num_lote","area_lote","created_at"})),
     *   @OA\Parameter(name="dir", in="query", @OA\Schema(type="string", enum={"asc","desc"})),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=100)),
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", minimum=1)),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/LotePage")),
     *   @OA\Response(response=422, description="Filtro inválido", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function index(LoteIndexRequest $request)
    {

        $v = $request->validated();

        $q = Lote::query();

        if ($request->filled('nome')) $q->where('nome', 'ilike', '%'.$v['nome'].'%');
        if ($request->filled('num_loteamento')) $q->where('num_loteamento', (int)$v['num_loteamento']);
        if ($request->filled('num_quadra')) $q->where('num_quadra', (int)$v['num_quadra']);
        if ($request->filled('num_lote')) $q->where('num_lote', (int)$v['num_lote']);

        if ($request->filled('area_min')) $q->where('area_lote', '>=', (float)str_replace(',', '.', $v['area_min']));
        if ($request->filled('area_max')) $q->where('area_lote', '<=', (float)str_replace(',', '.', $v['area_max']));

        $sort = $v['sort'] ?? 'nome';
        $dir = $v['dir'] ?? 'asc';
        $perPage = (int)($v['per_page'] ?? 15);

        $lotes = $q->orderBy($sort, $dir)->paginate($perPage);

        return LoteResource::collection($lotes);
    }

    /**
     * @OA\Get(
     *   path="/api/lotes/{id}",
     *   tags={"Lotes"},
     *   summary="Detalhar lote",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Lote")),
     *   @OA\Response(response=404, description="Não encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(Lote $lote)
    {
        return new LoteResource($lote);
    }

    /**
     * @OA\Post(
     *   path="/api/lotes",
     *   tags={"Lotes"},
     *   summary="Criar lote",
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/LoteStore")),
     *   @OA\Response(response=201, description="Criado", @OA\JsonContent(ref="#/components/schemas/Lote")),
     *   @OA\Response(response=400, description="Campos mal formatados", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=409, description="Conflito de localização", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=422, description="Regra de validação", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function store(LoteRequest $request)
    {
        DB::beginTransaction();
        try {
            $lote = Lote::create($request->validated());
            DB::commit();
            return (new LoteResource($lote))->response()->setStatusCode(201);
        } catch (Throwable $e) {
            DB::rollBack();
            return $this->apiException($e, 500, 'Erro ao criar lote');
        }
    }

    /**
     * @OA\Put(
     *   path="/api/lotes/{id}",
     *   tags={"Lotes"},
     *   summary="Atualizar lote (total)",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/LoteUpdate")),
     *   @OA\Response(response=200, description="Atualizado", @OA\JsonContent(ref="#/components/schemas/Lote")),
     *   @OA\Response(response=400, description="Campos mal formatados", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=404, description="Não encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=409, description="Conflito de localização", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=422, description="Regra de validação", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     *
     * @OA\Patch(
     *   path="/api/lotes/{id}",
     *   tags={"Lotes"},
     *   summary="Atualizar lote (parcial)",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/LoteUpdate")),
     *   @OA\Response(response=200, description="Atualizado", @OA\JsonContent(ref="#/components/schemas/Lote")),
     *   @OA\Response(response=400, description="Campos mal formatados", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=404, description="Não encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=409, description="Conflito de localização", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *   @OA\Response(response=422, description="Regra de validação", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function update(LoteRequest $request, Lote $lote)
    {
        DB::beginTransaction();
        try {
            $lote->update($request->validated());
            DB::commit();
            return response()->json([
                'message' => 'Lote atualizado',
                'data' => new LoteResource($lote)
            ], 200);
        } catch (Throwable $e) {
            DB::rollBack();
            return $this->apiException($e, 500, 'Erro ao atualizar lote');
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/lotes/{id}",
     *   tags={"Lotes"},
     *   summary="Excluir lote",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Excluído", @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))),
     *   @OA\Response(response=404, description="Não encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function destroy(Lote $lote)
    {
        DB::beginTransaction();
        try {
            $lote->delete();
            DB::commit();
            return response()->json([
                'message' => 'Lote excluído com sucesso'
            ], 200);
        } catch (Throwable $e) {
            DB::rollBack();
            return $this->apiException($e, 500, 'Erro ao excluir lote');
        }
    }
}
