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
}
