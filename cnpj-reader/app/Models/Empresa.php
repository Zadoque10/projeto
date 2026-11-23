<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $cnpj
 * @property string|null $razao_social
 * @property string|null $nome_fantasia
 * @property string|null $uf
 * @property string|null $municipio
 * @property array<string, mixed> $dados
 */
#[Fillable(['cnpj', 'razao_social', 'nome_fantasia', 'uf', 'municipio', 'dados'])]
class Empresa extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dados' => 'array',
        ];
    }
}
