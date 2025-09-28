<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'nome',
        'endereco',
        'telefone',
        'email',
        'tipo_pessoa',
        'cpf',
        'cnpj',
        'responsavel_nome',
        'responsavel_cpf'
    ];
}
