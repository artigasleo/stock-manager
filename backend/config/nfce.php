<?php

return [
    'environment' => env('NFCE_ENVIRONMENT', 'homologacao'),
    'certificate_path' => env('NFCE_CERTIFICATE_PATH'),
    'certificate_password' => env('NFCE_CERTIFICATE_PASSWORD'),
    'csc' => env('NFCE_CSC'),
    'csc_id' => env('NFCE_CSC_ID'),

    'emitente' => [
        'razao_social' => 'GO & DO COMERCIAL LTDA',
        'cnpj' => '69393876000141',
        'ie' => '162380847117',
        'crt' => 1, // 1 = Simples Nacional
        'cnae' => '4712100',
        'endereco' => [
            'logradouro' => "Pde Antonio D'Angelo",
            'numero' => '65',
            'bairro' => 'Casa Verde',
            'municipio' => 'São Paulo',
            'codigo_municipio_ibge' => '3550308',
            'uf' => 'SP',
            'cep' => '02516040',
        ],
    ],

    'defaults' => [
        'cfop' => '5102',
        'csosn' => '102',
        'unidade_comercial' => 'UN',
        'origem' => 0,
    ],
];
