<?php

namespace App\Http\Controllers;

use App\Actions\Unit\CreateUnit;
use App\Actions\Unit\DeleteUnit;
use App\Actions\Unit\ListUnit;
use App\Actions\Unit\UpdateUnit;
use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class UnitController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:units.view', only: ['index']),
            new Middleware('permission:units.edit', only: ['store', 'update', 'destroy']),
        ];
    }

    public function index(ListUnit $action): View
    {
        return view('units.index', [
            'units' => $action->execute(),
        ]);
    }

    public function store(
        StoreUnitRequest $request,
        CreateUnit $action
    ): RedirectResponse {
        $action->execute($request);

        return redirect()->route('units.index')->with('success', 'Unidade criada.');
    }

    public function update(
        UpdateUnitRequest $request,
        Unit $unit,
        UpdateUnit $action
    ): RedirectResponse {
        $action->execute($request, $unit);

        return redirect()->route('units.index')->with('success', 'Unidade atualizada.');
    }

    public function destroy(
        Unit $unit,
        DeleteUnit $action
    ): RedirectResponse {
        $action->execute($unit);

        return redirect()->route('units.index')->with('success', 'Unidade excluída.');
    }
}
