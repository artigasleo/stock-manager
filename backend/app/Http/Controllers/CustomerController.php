<?php

namespace App\Http\Controllers;

use App\Actions\Customer\CreateCustomer;
use App\Actions\Customer\DeleteCustomer;
use App\Actions\Customer\ListCustomer;
use App\Actions\Customer\UpdateCustomer;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(ListCustomer $action): View
    {
        return view('customers.index', [
            'customers' => $action->execute(),
        ]);
    }

    public function store(
        StoreCustomerRequest $request,
        CreateCustomer $action
    ): RedirectResponse {
        $action->execute($request);

        return redirect()->route('customers.index')->with('success', 'Cliente criado.');
    }

    public function update(
        UpdateCustomerRequest $request,
        Customer $customer,
        UpdateCustomer $action
    ): RedirectResponse {
        $action->execute($request, $customer);

        return redirect()->route('customers.index')->with('success', 'Cliente atualizado.');
    }

    public function destroy(
        Customer $customer,
        DeleteCustomer $action
    ): RedirectResponse {
        $action->execute($customer);

        return redirect()->route('customers.index')->with('success', 'Cliente excluído.');
    }
}
