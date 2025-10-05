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

class LoteController extends Controller
{
    use ApiExceptionHandler;

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

    public function show(Lote $lote)
    {
        return new LoteResource($lote);
    }

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
