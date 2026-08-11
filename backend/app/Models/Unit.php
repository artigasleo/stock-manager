<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'address', 'is_default', 'active'])]
class Unit extends Model
{
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public static function default(): self
    {
        return static::where('is_default', true)->firstOrFail();
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }
}
