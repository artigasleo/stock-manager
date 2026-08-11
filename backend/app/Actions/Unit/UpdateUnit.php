<?php

namespace App\Actions\Unit;

use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateUnit
{
    public function execute(UpdateUnitRequest $request, Unit $unit): Unit
    {
        return DB::transaction(function () use ($request, $unit) {
            $isDefault = $request->validated('is_default') ?? false;

            if ($unit->is_default && ! $isDefault) {
                throw ValidationException::withMessages([
                    'is_default' => 'Marque outra unidade como padrão antes de desmarcar esta.',
                ]);
            }

            if ($isDefault) {
                Unit::query()->where('id', '!=', $unit->id)->update(['is_default' => false]);
            }

            $unit->fill([
                'name' => $request->validated('name'),
                'address' => $request->validated('address'),
                'is_default' => $isDefault,
                'active' => $request->validated('active') ?? $unit->active,
            ]);

            $unit->save();

            return $unit;
        });
    }
}
