<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Http\Requests\LoteRequest;
use App\Http\Resources\LoteResource;
use App\Support\ApiExceptionHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Throwable;

class LoteController extends Controller
{
    use ApiExceptionHandler;

    public function index(Request $request)
    {
        $q = Lote::query();

        if ($request->filled('nome')) $q->where('nome', 'ilike', '%'.$request->get('nome').'%');
        if ($request->filled('num_loteamento')) $q->where('num_loteamento', (int)$request->get('num_loteamento'));
        if ($request->filled('num_quadra')) $q->where('num_quadra', (int)$request->get('num_quadra'));
        if ($request->filled('num_lote')) $q->where('num_lote', (int)$request->get('num_lote'));

        if ($request->filled('area_min')) $q->where('area_lote', '>=', (float)str_replace(',', '.', $request->get('area_min')));
        if ($request->filled('area_max')) $q->where('area_lote', '<=', (float)str_replace(',', '.', $request->get('area_max')));

        $sort = $request->get('sort', 'nome');
        $dir = $request->get('dir', 'asc');
        $allowedSort = ['nome','num_loteamento','num_quadra','num_lote','area_lote','created_at'];
        if (!in_array($sort, $allowedSort)) $sort = 'nome';
        if (!in_array($dir, ['asc','desc'])) $dir = 'asc';

        $perPage = (int)$request->get('per_page', 15);

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
                'status' => 'sucesso',
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
                'status' => 'sucesso',
                'message' => 'Lote excluído com sucesso'
            ], 200);
        } catch (Throwable $e) {
            DB::rollBack();
            return $this->apiException($e, 500, 'Erro ao excluir lote');
        }
    }
}
