@extends('layouts.app')

@php
    $statusLabels = [
        'awaiting_payment' => 'Aguardando Pagamento',
        'paid' => 'Paga',
        'invoiced' => 'Faturada',
        'cancelled' => 'Cancelada',
    ];

    $statusColors = [
        'awaiting_payment' => 'bg-amber-100 text-amber-800',
        'paid' => 'bg-green-100 text-green-800',
        'invoiced' => 'bg-blue-100 text-blue-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];

    // "Faturada" só é alcançada automaticamente quando a NFC-e é autorizada
    // (ver UpdateSaleStatus), por isso fica fora das opções escolhíveis aqui.
    $selectableStatusLabels = collect($statusLabels)->except('invoiced');

    $paymentLabels = config('payments.methods');

    // Aceita tanto o código de barras quanto o código interno do produto -
    // muitos produtos ainda não têm código de barras cadastrado.
    $productIdentifiers = $products->reduce(function (array $map, $product) {
        $map[$product->code] = $product->id;

        if ($product->barcode) {
            $map[$product->barcode] = $product->id;
        }

        return $map;
    }, []);

    // Quantidade disponível por unidade, pra atualizar a dica "disp.: X" quando
    // o usuário trocar a unidade da venda.
    $stocksByUnit = [];
    foreach ($products as $product) {
        foreach ($product->stocks as $stock) {
            $stocksByUnit[$stock->unit_id][$product->id] = $stock->quantity;
        }
    }
@endphp

@section('content')
    <div
        x-data="{
            modalOpen: {{ $errors->any() ? 'true' : 'false' }},
            nfceExportModalOpen: false,
            unitId: {{ old('unit_id', $defaultUnit->id) }},
            items: {{ old('items') ? json_encode(old('items')) : "[{ product_id: '', quantity: 1, unit_price: '', discount_percent: 0 }]" }},
            barcodeError: '',
            discountMode: 'none',
            saleDiscountPercent: '',
            payments: {{ old('payments') ? json_encode(old('payments')) : "[{ payment_method: '', amount: '' }]" }},
            usdExchangeRate: @js($usdExchangeRate),
            usdTotal(amount) {
                if (!this.usdExchangeRate) return '0';
                return Math.ceil(Number(amount || 0) / Number(this.usdExchangeRate));
            },
            paymentsSum() {
                return this.payments.reduce((sum, payment) => sum + Number(payment.amount || 0), 0);
            },
            removePayment(index) {
                if (this.payments.length > 1) {
                    this.payments.splice(index, 1);
                }
            },
            productsByIdentifier: @js($productIdentifiers),
            productPrices: @js($products->pluck('sale_price', 'id')),
            stocksByUnit: @js($stocksByUnit),
            availableQuantity(productId) {
                return this.stocksByUnit[this.unitId]?.[productId] ?? 0;
            },
            fillPrice(item) {
                if (item.product_id) {
                    item.unit_price = this.productPrices[item.product_id] ?? '';
                }
            },
            // O percentual efetivamente aplicado depende do modo: nenhum, um só
            // pra venda inteira, ou um por item (ver inputs no template).
            effectiveDiscount(item) {
                if (this.discountMode === 'sale') return Number(this.saleDiscountPercent || 0);
                if (this.discountMode === 'item') return Number(item.discount_percent || 0);
                return 0;
            },
            itemSubtotal(item) {
                const gross = Number(item.quantity || 0) * Number(item.unit_price || 0);
                return (gross * (1 - this.effectiveDiscount(item) / 100)).toFixed(2);
            },
            cartTotal() {
                return this.items
                    .reduce((sum, item) => sum + Number(this.itemSubtotal(item)), 0)
                    .toFixed(2);
            },
            cartGrossTotal() {
                return this.items
                    .reduce((sum, item) => sum + (Number(item.quantity || 0) * Number(item.unit_price || 0)), 0)
                    .toFixed(2);
            },
            // Garante que o que será enviado bate com o modo escolhido, mesmo que o
            // usuário tenha trocado de modo ou adicionado itens depois de digitar.
            syncDiscounts() {
                this.items.forEach((item) => {
                    item.discount_percent = this.effectiveDiscount(item);
                });
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                } else {
                    this.items[0] = { product_id: '', quantity: 1, unit_price: '', discount_percent: 0 };
                }
            },
            addByBarcode(rawCode) {
                const code = rawCode.trim();
                if (!code) return;

                const productId = this.productsByIdentifier[code];

                if (!productId) {
                    this.barcodeError = 'Nenhum produto encontrado com o código ' + code + '.';
                    return;
                }

                this.barcodeError = '';

                const existing = this.items.find((item) => item.product_id == productId);

                if (existing) {
                    existing.quantity = Number(existing.quantity || 0) + 1;
                    return;
                }

                if (this.items.length === 1 && !this.items[0].product_id) {
                    this.items = [];
                }

                this.items.push({
                    product_id: productId,
                    quantity: 1,
                    unit_price: this.productPrices[productId] ?? '',
                    discount_percent: 0,
                });
            },
        }"
    >
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold text-brand-dark">Vendas</h1>

            @can('sales.edit')
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="nfceExportModalOpen = true"
                        class="rounded-md border border-brand text-brand px-4 py-2 text-sm font-medium hover:bg-brand hover:text-brand-cream cursor-pointer"
                    >
                        NFC-e do mês
                    </button>

                    <button
                        type="button"
                        @click="modalOpen = true"
                        class="rounded-md bg-brand text-brand-cream px-4 py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer"
                    >
                        Nova venda
                    </button>
                </div>
            @endcan
        </div>

        <div class="bg-brand-paper rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-stone-100 text-left text-stone-600">
                    <tr>
                        <th class="px-4 py-3">Data</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Itens</th>
                        <th class="px-4 py-3">Pagamento</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Vendedor</th>
                        <th class="px-4 py-3 w-44">Status</th>
                        <th class="px-4 py-3 w-56">Nota Fiscal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($sales as $sale)
                        <tr>
                            <td class="px-4 py-3">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">
                                @can('sales.edit')
                                    <form method="POST" action="{{ route('sales.updateCustomer', $sale) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select
                                            name="customer_id"
                                            onchange="this.form.submit()"
                                            @if ($sale->nfce?->status === 'authorized')
                                                title="A NFC-e já emitida não é alterada — isso só atualiza o registro interno da venda."
                                            @endif
                                            class="rounded-md border border-stone-300 px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-brand"
                                        >
                                            <option value="" @selected(!$sale->customer_id)>Não identificado</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}" @selected($sale->customer_id === $customer->id)>
                                                    {{ $customer->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @else
                                    {{ $sale->customer?->name ?? 'Não identificado' }}
                                @endcan
                            </td>
                            <td class="px-4 py-3">{{ $sale->items->pluck('product.name')->join(', ') }}</td>
                            <td class="px-4 py-3">{{ $sale->payments->map(fn ($p) => $paymentLabels[$p->payment_method] ?? $p->payment_method)->implode(' + ') ?: '—' }}</td>
                            <td class="px-4 py-3">R$ {{ number_format($sale->total, 2, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $sale->seller?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if (in_array($sale->status, ['cancelled', 'invoiced']) || ! auth()->user()->can('sales.edit'))
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $statusColors[$sale->status] }}">
                                        {{ $statusLabels[$sale->status] }}
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('sales.updateStatus', $sale) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select
                                            name="status"
                                            onchange="this.form.submit()"
                                            class="rounded-md border border-stone-300 px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-brand {{ $statusColors[$sale->status] }}"
                                        >
                                            @foreach ($selectableStatusLabels as $value => $label)
                                                <option value="{{ $value }}" @selected($sale->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($sale->nfce?->status === 'authorized')
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-green-100 text-green-800 mb-1">
                                        Autorizada
                                    </span>
                                    <p class="text-xs text-stone-500 break-all">{{ $sale->nfce->chave_acesso }}</p>
                                    @if ($sale->nfce->qrcode_url)
                                        <a
                                            href="{{ $sale->nfce->qrcode_url }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="text-brand hover:underline cursor-pointer text-xs"
                                        >
                                            Ver / Imprimir
                                        </a>
                                    @endif
                                @elseif ($sale->nfce && in_array($sale->nfce->status, ['rejected', 'error']))
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-red-100 text-red-800 mb-1">
                                        {{ $sale->nfce->status === 'rejected' ? 'Rejeitada' : 'Erro' }}
                                    </span>
                                    <p class="text-xs text-stone-500">{{ $sale->nfce->motivo ?? $sale->nfce->erro }}</p>
                                    @can('sales.edit')
                                        <form method="POST" action="{{ route('sales.nfce.emit', $sale) }}" class="mt-1">
                                            @csrf
                                            <button type="submit" class="text-brand hover:underline cursor-pointer text-xs">
                                                Tentar novamente
                                            </button>
                                        </form>
                                    @endcan
                                @elseif ($sale->status !== 'cancelled')
                                    @can('sales.edit')
                                        <form method="POST" action="{{ route('sales.nfce.emit', $sale) }}">
                                            @csrf
                                            <button type="submit" class="rounded-md border border-brand text-brand px-3 py-1 text-xs font-medium hover:bg-brand hover:text-brand-cream cursor-pointer">
                                                Emitir NFC-e
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-stone-500">—</span>
                                    @endcan
                                @else
                                    <span class="text-xs text-stone-500">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-stone-500">
                                Nenhuma venda registrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div
            x-show="modalOpen"
            x-cloak
            class="fixed inset-0 bg-black/40 flex items-start justify-center p-4 overflow-y-auto"
            style="display: none;"
        >
            <div class="w-full max-w-4xl bg-brand-paper rounded-lg shadow p-6 my-8">
                <h2 class="text-lg font-semibold text-brand-dark mb-4">Nova venda</h2>

                <form method="POST" action="{{ route('sales.store') }}" class="space-y-4" @submit="syncDiscounts()">
                    @csrf

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Unidade</label>
                            <select
                                name="unit_id"
                                x-model.number="unitId"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" @selected(old('unit_id', $defaultUnit->id) == $unit->id)>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Cliente (opcional)</label>
                            <select
                                name="customer_id"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                                <option value="" @selected(!old('customer_id'))>Não identificado</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Vendedor</label>
                            <select
                                name="seller_id"
                                class="w-full rounded-md border {{ $errors->has('seller_id') ? 'border-red-500' : 'border-stone-300' }} px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                                <option value="" disabled @selected(!old('seller_id'))>Selecione...</option>
                                @foreach ($sellers as $seller)
                                    <option value="{{ $seller->id }}" @selected(old('seller_id') == $seller->id)>
                                        {{ $seller->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Código de barras ou código do produto</label>
                        <input
                            type="text"
                            placeholder="Bipe o código de barras ou digite o código do produto e pressione Enter"
                            @keydown.enter.prevent="addByBarcode($event.target.value); $event.target.value = ''"
                            class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                        >
                        <p x-show="barcodeError" x-text="barcodeError" x-cloak class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium">Itens</label>
                            <button
                                type="button"
                                @click="items.push({ product_id: '', quantity: 1, unit_price: '', discount_percent: 0 })"
                                class="text-sm text-brand hover:underline cursor-pointer"
                            >
                                + Adicionar item
                            </button>
                        </div>

                        <div class="flex items-center gap-4 mb-3 text-sm">
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="discount_mode_ui" value="none" x-model="discountMode" class="cursor-pointer">
                                Sem desconto
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="discount_mode_ui" value="sale" x-model="discountMode" class="cursor-pointer">
                                Desconto na venda inteira
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="discount_mode_ui" value="item" x-model="discountMode" class="cursor-pointer">
                                Desconto por produto
                            </label>

                            <div x-show="discountMode === 'sale'" x-cloak class="flex items-center gap-1.5 ml-auto">
                                <label class="text-stone-600">Desconto (%)</label>
                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    x-model="saleDiscountPercent"
                                    class="w-20 rounded-md border border-stone-300 px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                                >
                            </div>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="grid grid-cols-12 gap-2 items-start">
                                    <select
                                        :name="`items[${index}][product_id]`"
                                        x-model="item.product_id"
                                        @change="fillPrice(item)"
                                        class="col-span-3 rounded-md border border-stone-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                                    >
                                        <option value="">Selecione...</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>

                                    <div class="col-span-2">
                                        <input
                                            type="number"
                                            min="1"
                                            placeholder="Qtd."
                                            :name="`items[${index}][quantity]`"
                                            x-model="item.quantity"
                                            class="w-full rounded-md border border-stone-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                                        >
                                        <p
                                            x-show="item.product_id"
                                            x-text="'disp.: ' + availableQuantity(item.product_id)"
                                            class="mt-0.5 text-xs text-stone-500"
                                        ></p>
                                    </div>

                                    <input
                                        type="text"
                                        readonly
                                        tabindex="-1"
                                        placeholder="Preço unit."
                                        x-model="item.unit_price"
                                        title="O preço vem do cadastro do produto e não pode ser alterado na venda."
                                        class="col-span-2 rounded-md border border-stone-300 bg-stone-100 px-2 py-1.5 text-sm text-stone-600 cursor-not-allowed"
                                    >

                                    <input
                                        :type="discountMode === 'item' ? 'number' : 'hidden'"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        placeholder="%"
                                        :name="`items[${index}][discount_percent]`"
                                        x-model="item.discount_percent"
                                        class="col-span-1 rounded-md border border-stone-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                                    >

                                    <span class="col-span-3 py-1.5 text-sm text-right pr-2 text-stone-700" x-text="'R$ ' + itemSubtotal(item)"></span>

                                    <button
                                        type="button"
                                        @click="removeItem(index)"
                                        class="col-span-1 py-1.5 text-red-600 hover:underline cursor-pointer text-sm"
                                    >
                                        Remover
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="mt-2 pt-2 border-t border-stone-200">
                            <div class="flex justify-end items-baseline gap-2">
                                <span x-show="discountMode !== 'none'" x-cloak class="text-sm text-stone-400 line-through">
                                    R$ <span x-text="cartGrossTotal()"></span>
                                </span>
                                <span class="text-sm font-semibold text-brand-dark">
                                    Total: R$ <span x-text="cartTotal()"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium">Forma de pagamento</label>
                            <button
                                type="button"
                                @click="payments.push({ payment_method: '', amount: '' })"
                                class="text-sm text-brand hover:underline cursor-pointer"
                            >
                                + Adicionar forma de pagamento
                            </button>
                        </div>

                        <div class="space-y-2" x-effect="payments.length === 1 && (payments[0].amount = cartTotal())">
                            <template x-for="(payment, index) in payments" :key="index">
                                <div class="grid grid-cols-12 gap-2 items-start">
                                    <select
                                        :name="`payments[${index}][payment_method]`"
                                        x-model="payment.payment_method"
                                        class="col-span-6 rounded-md border border-stone-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                                    >
                                        <option value="">Selecione...</option>
                                        @foreach ($paymentLabels as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>

                                    <div class="col-span-4">
                                        <input
                                            type="number"
                                            min="0.01"
                                            step="0.01"
                                            placeholder="Valor (R$)"
                                            :name="`payments[${index}][amount]`"
                                            x-model="payment.amount"
                                            :readonly="payments.length === 1"
                                            :class="payments.length === 1 ? 'bg-stone-100 text-stone-600 cursor-not-allowed' : ''"
                                            class="w-full rounded-md border border-stone-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                                        >
                                        <p
                                            x-show="payment.payment_method === 'cash_usd'"
                                            x-cloak
                                            class="mt-0.5 text-xs text-stone-500"
                                        >
                                            <template x-if="usdExchangeRate">
                                                <span>Cobrar: <strong x-text="'US$ ' + usdTotal(payment.amount)"></strong> (cotação R$ <span x-text="usdExchangeRate"></span>)</span>
                                            </template>
                                            <template x-if="!usdExchangeRate">
                                                <span class="text-red-600">Cotação do dólar não configurada.</span>
                                            </template>
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        x-show="payments.length > 1"
                                        @click="removePayment(index)"
                                        class="col-span-2 py-1.5 text-red-600 hover:underline cursor-pointer text-sm"
                                    >
                                        Remover
                                    </button>
                                </div>
                            </template>
                        </div>

                        <p
                            class="mt-2 text-right text-sm"
                            :class="Math.abs(paymentsSum() - Number(cartTotal())) > 0.01 ? 'text-red-600 font-medium' : 'text-stone-500'"
                        >
                            Soma dos pagamentos: R$ <span x-text="paymentsSum().toFixed(2)"></span>
                        </p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 text-sm cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-md bg-brand text-brand-cream px-4 py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @can('sales.edit')
            <div
                x-show="nfceExportModalOpen"
                x-cloak
                class="fixed inset-0 bg-black/40 flex items-center justify-center p-4"
                style="display: none;"
            >
                <div class="w-full max-w-sm bg-brand-paper rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-brand-dark mb-4">NFC-e do mês</h2>
                    <p class="text-sm text-stone-600 mb-4">Baixa um ZIP com o XML de todas as NFC-e autorizadas no mês escolhido, pra enviar à contabilidade.</p>

                    <form method="GET" action="{{ route('nfce.export') }}" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Mês</label>
                            <input
                                type="month"
                                name="month"
                                value="{{ now()->subMonthNoOverflow()->format('Y-m') }}"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="nfceExportModalOpen = false" class="px-4 py-2 text-sm cursor-pointer">
                                Cancelar
                            </button>
                            <button type="submit" class="rounded-md bg-brand text-brand-cream px-4 py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer">
                                Baixar ZIP
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>
@endsection
