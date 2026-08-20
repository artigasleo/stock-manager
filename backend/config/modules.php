<?php

// Catálogo único dos módulos do sistema: slug => ['label' => ..., 'actions' => [...]].
// Usado pra semear as permissions (`{slug}.{action}`), montar a matriz de
// permissões na tela de Papéis, e decidir o que aparece na Sidebar.
// `actions` é ['view'] pra módulos sem nada pra editar (ex.: Dashboard).
return [
    'dashboard' => ['label' => 'Dashboard', 'actions' => ['view']],
    'reports' => ['label' => 'Relatórios', 'actions' => ['view']],
    'categories' => ['label' => 'Categorias', 'actions' => ['view', 'edit']],
    'products' => ['label' => 'Estoque', 'actions' => ['view', 'edit']],
    'suppliers' => ['label' => 'Fornecedores', 'actions' => ['view', 'edit']],
    'customers' => ['label' => 'Clientes', 'actions' => ['view', 'edit']],
    'sellers' => ['label' => 'Vendedores', 'actions' => ['view', 'edit']],
    'stock' => ['label' => 'Movimentações', 'actions' => ['view', 'edit']],
    'purchases' => ['label' => 'Compras', 'actions' => ['view', 'edit']],
    'sales' => ['label' => 'Vendas', 'actions' => ['view', 'edit']],
    'units' => ['label' => 'Unidades', 'actions' => ['view', 'edit']],
    'users' => ['label' => 'Usuários', 'actions' => ['view', 'edit']],
];
