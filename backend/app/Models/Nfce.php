<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sale_id',
    'unit_id',
    'status',
    'environment',
    'serie',
    'numero',
    'chave_acesso',
    'protocolo_autorizacao',
    'cstat',
    'motivo',
    'xml_path',
    'qrcode_url',
    'erro',
])]
class Nfce extends Model
{
    protected $table = 'nfce';

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
