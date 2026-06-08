<?php
require_once 'includes/conexao.php';
$pagina = 'home';
$pdo = conectar();
$total    = $pdo->query("SELECT COUNT(*) FROM produto")->fetchColumn();
$ativos   = $pdo->query("SELECT COUNT(*) FROM produto WHERE Status_idStatus=1")->fetchColumn();
$inativos = $pdo->query("SELECT COUNT(*) FROM produto WHERE Status_idStatus=2")->fetchColumn();
$semEst   = $pdo->query("SELECT COUNT(*) FROM produto WHERE estoqueProduto=0")->fetchColumn();
$nCats    = $pdo->query("SELECT COUNT(*) FROM categoria")->fetchColumn();
require_once 'includes/header.php';
?>

<div class="breadcrumb">
    <i class="fa-solid fa-house" style="font-size:11px;color:#aaa"></i>
    <span class="sep">/</span><span class="cur">Página Inicial</span>
</div>

<div class="page-header">
    <div class="page-header-left">
        <h1>Página Inicial</h1>
        <p>Bem-vindo ao Sistema de Produtos</p>
    </div>
    <div class="page-header-right">
        <a href="produtos.php" class="btn btn-primary"><i class="fa-solid fa-tag"></i> Ver Produtos</a>
        <a href="cadastrar.php" class="btn btn-red"><i class="fa-solid fa-plus"></i> Cadastrar Produto</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon si-blue"><i class="fa-solid fa-boxes-stacked"></i></div>
        <div><div class="stat-val"><?= $total ?></div><div class="stat-lbl">Total de Produtos</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-green"><i class="fa-solid fa-circle-check"></i></div>
        <div><div class="stat-val"><?= $ativos ?></div><div class="stat-lbl">Produtos Ativos</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-orange"><i class="fa-solid fa-circle-pause"></i></div>
        <div><div class="stat-val"><?= $inativos ?></div><div class="stat-lbl">Produtos Inativos</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-red"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div><div class="stat-val"><?= $semEst ?></div><div class="stat-lbl">Sem Estoque</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-blue"><i class="fa-solid fa-layer-group"></i></div>
        <div><div class="stat-val"><?= $nCats ?></div><div class="stat-lbl">Categorias</div></div>
    </div>
</div>

<div class="table-card" style="padding:22px">
    <h3 style="font-size:15px;font-weight:700;margin-bottom:14px">Acesso Rápido</h3>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a href="produtos.php" class="btn btn-primary"><i class="fa-solid fa-list"></i> Listagem de Produtos</a>
        <a href="cadastrar.php" class="btn btn-outline"><i class="fa-solid fa-plus"></i> Cadastrar Produto</a>
        <a href="produtos.php?acao=importar" class="btn btn-outline"><i class="fa-solid fa-file-import"></i> Importar CSV</a>
        <a href="produtos.php?acao=exportar" class="btn btn-outline"><i class="fa-solid fa-file-export"></i> Exportar CSV</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
