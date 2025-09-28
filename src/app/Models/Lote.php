<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $table = 'lotes';

    protected $fillable = [
        'nome',
        'num_loteamento',
        'num_lote',
        'num_quadra',
        'area_lote'
    ];

    protected $casts = [
        'num_loteamento' => 'integer',
        'num_lote' => 'integer',
        'num_quadra' => 'integer',
        'area_lote' => 'decimal:2',
    ];
}
