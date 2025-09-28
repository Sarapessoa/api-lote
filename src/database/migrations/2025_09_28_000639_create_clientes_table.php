<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->text('endereco')->nullable();
            $table->string('telefone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('tipo_pessoa', 8); // FISICA ou JURIDICA
            $table->string('cpf', 11)->nullable();
            $table->string('cnpj', 14)->nullable();
            $table->string('responsavel_nome', 150)->nullable();
            $table->string('responsavel_cpf', 11)->nullable();
            $table->timestamps();

            $table->unique('cpf');
            $table->unique('cnpj');
            $table->index(['tipo_pessoa']);
            $table->index(['nome']);
        });

        DB::statement("ALTER TABLE clientes ADD CONSTRAINT clientes_tipo_pessoa_chk CHECK (tipo_pessoa IN ('FISICA','JURIDICA'))");

        DB::statement("ALTER TABLE clientes ADD CONSTRAINT clientes_cpf_fmt_chk CHECK (cpf IS NULL OR cpf ~ '^[0-9]{11}$')");
        DB::statement("ALTER TABLE clientes ADD CONSTRAINT clientes_resp_cpf_fmt_chk CHECK (responsavel_cpf IS NULL OR responsavel_cpf ~ '^[0-9]{11}$')");
        DB::statement("ALTER TABLE clientes ADD CONSTRAINT clientes_cnpj_fmt_chk CHECK (cnpj IS NULL OR cnpj ~ '^[0-9]{14}$')");

        DB::statement("
            ALTER TABLE clientes ADD CONSTRAINT clientes_tipo_regras_chk CHECK (
                (tipo_pessoa = 'FISICA' AND cpf IS NOT NULL AND cnpj IS NULL AND responsavel_nome IS NULL AND responsavel_cpf IS NULL)
                OR
                (tipo_pessoa = 'JURIDICA' AND cnpj IS NOT NULL AND cpf IS NULL AND responsavel_nome IS NOT NULL AND responsavel_cpf IS NOT NULL)
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
