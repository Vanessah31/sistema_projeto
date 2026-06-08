<?php
// includes/header.php
if (!isset($pagina)) $pagina = '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistema de Produtos</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- TOPBAR -->
<header class="topbar">
    <span class="topbar-brand">Sistema de Produtos</span>
    <div class="topbar-right">
        <div class="topbar-user">
            <i class="fa-regular fa-circle-user"></i>
            <span>Tipo de acesso: <strong>Administrador Master</strong></span>
        </div>
        <a href="#" class="topbar-sair">
            <i class="fa-solid fa-right-from-bracket"></i> Sair
        </a>
    </div>
</header>

<div class="layout">
<!-- SIDEBAR -->
<aside class="sidebar">
    <nav class="sidebar-nav">
        <a href="index.php"   class="nav-item <?= $pagina==='home'    ?'active':'' ?>"><i class="fa-solid fa-house"></i> Página Inicial</a>
        <a href="produtos.php"class="nav-item <?= $pagina==='produtos'?'active':'' ?>"><i class="fa-solid fa-tag"></i> Produtos</a>
        <a href="#"           class="nav-item <?= $pagina==='pedidos' ?'active':'' ?>"><i class="fa-solid fa-cart-shopping"></i> Pedidos</a>
        <a href="#"           class="nav-item <?= $pagina==='estoque' ?'active':'' ?>"><i class="fa-solid fa-warehouse"></i> Estoque</a>
        <a href="#"           class="nav-item <?= $pagina==='cats'    ?'active':'' ?>"><i class="fa-solid fa-layer-group"></i> Categorias</a>
        <a href="#"           class="nav-item <?= $pagina==='rel'     ?'active':'' ?>"><i class="fa-regular fa-file-lines"></i> Relatórios</a>
        <a href="#"           class="nav-item <?= $pagina==='config'  ?'active':'' ?>"><i class="fa-solid fa-gear"></i> Configurações</a>
    </nav>
</aside>

<!-- MAIN -->
<main class="main">
