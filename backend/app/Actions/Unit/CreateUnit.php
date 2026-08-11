<?php

namespace App\Actions\Unit;

use App\Http\Requests\Unit\StoreUnitRequest;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class CreateUnit
{
    public function execute(StoreUnitRequest $request): Unit
    {
        return DB::transaction(function () use ($request) {
            $isDefault = $request->validated('is_default') ?? false;

            if ($isDefault) {
                Unit::query()->update(['is_default' => false]);
            }

            return Unit::create([
                'name' => $request->validated('name'),
                'address' => $request->validated('address'),
                'is_default' => $isDefault,
                'active' => $request->validated('active') ?? true,
            ]);
        });
    }
}
