<?php

namespace App\Actions\Unit;

use App\Models\Unit;
use Illuminate\Validation\ValidationException;

class DeleteUnit
{
    public function execute(Unit $unit): void
    {
        if ($unit->is_default) {
            throw ValidationException::withMessages([
                'name' => 'Marque outra unidade como padrão antes de excluir esta.',
            ]);
        }

        $unit->delete();
    }
}
