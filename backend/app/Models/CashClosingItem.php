<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['cash_closing_id', 'payment_method', 'expected_amount', 'counted_amount'])]
class CashClosingItem extends Model
{
    protected function casts(): array
    {
        return [
            'expected_amount' => 'decimal:2',
            'counted_amount' => 'decimal:2',
        ];
    }

    public function cashClosing(): BelongsTo
    {
        return $this->belongsTo(CashClosing::class);
    }

    protected function difference(): Attribute
    {
        return Attribute::make(
            get: fn () => round((float) $this->counted_amount - (float) $this->expected_amount, 2),
        );
    }
}
