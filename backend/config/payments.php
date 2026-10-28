<?php

// Fonte única dos métodos de pagamento aceitos — usado na validação da venda,
// nas labels exibidas (Vendas, Relatórios, Dashboard) e no código de forma de
// pagamento (tPag) enviado na NFC-e.
return [
    'methods' => [
        'cash' => 'Dinheiro/Real',
        'cash_usd' => 'Dinheiro/Dólar',
        'pix' => 'PIX',
        'debit_card' => 'Cartão de Débito',
        'credit_card' => 'Cartão de Crédito',
        'other' => 'Outro',
    ],

    // Tabela de formas de pagamento da SEFAZ (tPag) — dólar em espécie ainda é
    // "dinheiro" pra SEFAZ, a nota sempre sai em reais independente da moeda
    // física recebida.
    'nfce_codes' => [
        'cash' => '01',
        'cash_usd' => '01',
        'pix' => '17',
        'debit_card' => '04',
        'credit_card' => '03',
        'other' => '99',
    ],
];
