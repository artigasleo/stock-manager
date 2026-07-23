<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use HasFactory, SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $table = 'CATEGORIAS';

    protected $primaryKey = 'ID';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'DELETED_AT';

    protected array $fillable = [
        'NOME',
        'ATIVO',
    ];

    protected function casts(): array
    {
        return [
            'ATIVO' => 'boolean',
        ];
    }
}