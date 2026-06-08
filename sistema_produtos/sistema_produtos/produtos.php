<?php
// produtos.php — Listagem de Produtos
// Handles: listar, importar CSV, exportar CSV, feedback de toast
require_once 'includes/conexao.php';
session_start();
$pagina = 'produtos';
$pdo = conectar();

// ================================================================
// EXPORTAR CSV — acontece antes de qualquer output (imagem 8)
// ================================================================
if (isset($_POST['exportar_csv'])) {
    $todos = ($_POST['exportar_todos'] ?? '') === '1';
    $ids   = array_filter(array_map('intval', $_POST['ids'] ?? []));

    if ($todos) {
        $stmt = $pdo->query("
            SELECT p.codigoProduto,p.nomeProduto,c.deCategoria,
                   p.precoProduto,p.estoqueProduto,s.deStatus,
                   p.descricaoProduto,p.dataCadastro
            FROM produto p
            JOIN categoria c ON c.idCategoria=p.Categoria_idCategoria
            JOIN status_produto s ON s.idStatus=p.Status_idStatus
            ORDER BY p.dataCadastro DESC");
    } elseif (!empty($ids)) {
        $ph   = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("
            SELECT p.codigoProduto,p.nomeProduto,c.deCategoria,
                   p.precoProduto,p.estoqueProduto,s.deStatus,
                   p.descricaoProduto,p.dataCadastro
            FROM produto p
            JOIN categoria c ON c.idCategoria=p.Categoria_idCategoria
            JOIN status_produto s ON s.idStatus=p.Status_idStatus
            WHERE p.idProduto IN ($ph)
            ORDER BY p.dataCadastro DESC");
        $stmt->execute($ids);
    } else {
        $_SESSION['msg']  = 'Nenhum produto selecionado para exportar.';
        $_SESSION['tipo'] = 'error';
        header('Location: produtos.php');
        exit;
    }

    $rows = $stmt->fetchAll();
    if (empty($rows)) {
        $_SESSION['msg']  = 'Nenhum produto encontrado para exportar.';
        $_SESSION['tipo'] = 'error';
        header('Location: produtos.php');
        exit;
    }

    // RN-35: auditoria
    auditoria('EXPORTACAO', null, null, count($rows).' produto(s) exportado(s).');

    $fname = 'produtos_'.date('Y-m-d').'.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    header('Pragma: no-cache');
    $out = fopen('php://output','w');
    fputs($out, "\xEF\xBB\xBF"); // BOM para Excel
    fputcsv($out, ['Código','Nome','Categoria','Preço','Estoque','Status','Descrição','Data Cadastro'], ';');
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['codigoProduto'], $r['nomeProduto'], $r['deCategoria'],
            number_format($r['precoProduto'],2,',','.'),
            $r['estoqueProduto'], $r['deStatus'],
            $r['descricaoProduto'] ?? '',
            date('d/m/Y', strtotime($r['dataCadastro'])),
        ], ';');
    }
    fclose($out);
    exit;
}

// ================================================================
// IMPORTAR CSV (imagem 7)
// ================================================================
$importResult = null;
if (isset($_FILES['arquivoCSV']) && $_FILES['arquivoCSV']['error'] === UPLOAD_ERR_OK) {
    $arq  = $_FILES['arquivoCSV'];
    $ext  = strtolower(pathinfo($arq['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, ['csv'])) {
        $importResult = ['tipo'=>'error','msg'=>'Apenas arquivos .csv são aceitos.'];
    } else {
        $handle    = fopen($arq['tmp_name'],'r');
        fgetcsv($handle, 1000, ';'); // cabeçalho

        // mapa de categorias por nome
        $catRows = $pdo->query("SELECT idCategoria,deCategoria FROM categoria")->fetchAll();
        $catMap  = [];
        foreach ($catRows as $c) $catMap[mb_strtolower(trim($c['deCategoria']))] = $c['idCategoria'];

        $ok = 0; $errs = []; $ln = 1;
        while (($row = fgetcsv($handle,1000,';')) !== false) {
            $ln++;
            if (count($row) < 5) { $errs[] = "Linha $ln: colunas insuficientes."; continue; }

            $cod    = trim($row[0]??'');
            $nome   = trim($row[1]??'');
            $catNm  = mb_strtolower(trim($row[2]??''));
            $preco  = str_replace(['.',',' ],['','.' ], trim($row[3]??''));
            $estq   = trim($row[4]??'0');
            $desc   = trim($row[5]??'');
            $sts    = strtolower(trim($row[6]??'ativo'))==='inativo' ? 2 : 1;

            // Validações (RN-22 a RN-25)
            if (!$cod||!$nome||!$catNm||$preco==='') { $errs[]="Linha $ln: campos obrigatórios vazios."; continue; }
            if (!preg_match('/^[A-Za-z0-9\-]{3,20}$/',$cod)) { $errs[]="Linha $ln [$cod]: código inválido."; continue; }
            $chk=$pdo->prepare("SELECT idProduto FROM produto WHERE codigoProduto=?"); $chk->execute([$cod]);
            if ($chk->fetch()) { $errs[]="Linha $ln [$cod]: código já cadastrado."; continue; }
            if (!is_numeric($preco)||(float)$preco<=0) { $errs[]="Linha $ln [$cod]: preço inválido."; continue; }
            if (!ctype_digit((string)$estq)) { $errs[]="Linha $ln [$cod]: estoque inválido."; continue; }
            if (!isset($catMap[$catNm])) { $errs[]="Linha $ln [$cod]: categoria \"$catNm\" não encontrada."; continue; }

            try {
                $ins=$pdo->prepare("INSERT INTO produto (codigoProduto,nomeProduto,descricaoProduto,precoProduto,estoqueProduto,Categoria_idCategoria,Status_idStatus) VALUES(?,?,?,?,?,?,?)");
                $ins->execute([$cod,$nome,$desc?:null,(float)$preco,(int)$estq,$catMap[$catNm],$sts]);
                $ok++;
            } catch(PDOException $e){ $errs[]="Linha $ln [$cod]: erro de banco."; }
        }
        fclose($handle);
        auditoria('IMPORTACAO',null,null,"$ok importado(s), ".count($errs)." erro(s).");
        $importResult = ['tipo'=> $ok>0?'success':'error', 'ok'=>$ok, 'errs'=>$errs];
    }
}

// ================================================================
// FLASH MESSAGE (vindas de excluir.php, cadastrar.php, editar.php)
// ================================================================
$flashMsg  = $_SESSION['msg']  ?? null;
$flashTipo = $_SESSION['tipo'] ?? 'success';
unset($_SESSION['msg'], $_SESSION['tipo']);

// ================================================================
// BUSCAR PRODUTOS
// ================================================================
$produtos = $pdo->query("
    SELECT p.*, c.deCategoria, s.deStatus
    FROM produto p
    JOIN categoria c ON c.idCategoria=p.Categoria_idCategoria
    JOIN status_produto s ON s.idStatus=p.Status_idStatus
    ORDER BY p.dataCadastro DESC
")->fetchAll();

require_once 'includes/header.php';
?>

<?php /* ---- FLASH TOAST ---- */
if ($flashMsg): ?>
<script>
document.addEventListener('DOMContentLoaded',()=>toast(<?= json_encode($flashMsg) ?>, <?= json_encode($flashTipo) ?>));
</script>
<?php endif; ?>

<?php /* ---- IMPORT RESULT ---- */
if ($importResult): ?>
<script>
document.addEventListener('DOMContentLoaded',()=>{
<?php if (isset($importResult['ok'])): ?>
    toast('Importação concluída! <?= $importResult['ok'] ?> produto(s) cadastrado(s), <?= count($importResult['errs']) ?> com erro.', '<?= $importResult['tipo'] ?>', 5000);
<?php else: ?>
    toast(<?= json_encode($importResult['msg']) ?>, 'error');
<?php endif; ?>
});
</script>
<?php endif; ?>

<!-- BREADCRUMB -->
<div class="breadcrumb">
    <a href="index.php"><i class="fa-solid fa-house" style="font-size:11px"></i></a>
    <span class="sep">/</span>
    <span class="cur">Listagem de Produtos</span>
</div>

<!-- PAGE HEADER -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Produtos</h1>
        <p>Lista de Produtos do sistema</p>
    </div>
    <div class="page-header-right">
        <!-- IMPORTAR CSV (abre file dialog) -->
        <form method="POST" action="produtos.php" enctype="multipart/form-data" id="formImport" style="display:none">
            <input type="file" name="arquivoCSV" id="inputCSV" accept=".csv">
        </form>
        <button class="btn btn-import" onclick="document.getElementById('inputCSV').click()">
            <i class="fa-solid fa-file-import"></i> Importar CSV
        </button>

        <!-- EXPORTAR CSV (exporta todos) -->
        <form method="POST" action="produtos.php" id="formExport" style="display:none">
            <input type="hidden" name="exportar_csv" value="1">
            <input type="hidden" name="exportar_todos" value="1">
        </form>
        <button class="btn btn-export" onclick="document.getElementById('formExport').submit()">
            <i class="fa-solid fa-file-export"></i> Exportar CSV
        </button>

        <!-- CADASTRAR -->
        <a href="cadastrar.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Cadastrar Produto
        </a>
    </div>
</div>

<!-- BUSCA (imagem 6) -->
<div class="search-bar">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" id="busca" placeholder="Pesquisar por código, nome, categoria ou status...">
</div>

<!-- TABELA -->
<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Preço</th>
                <th>Estoque</th>
                <th>Status</th>
                <th>Data Cadastro</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($produtos)): ?>
            <tr><td colspan="8"><div class="empty"><i class="fa-solid fa-box-open"></i><p>Nenhum produto cadastrado.</p></div></td></tr>
        <?php else: ?>
            <?php foreach ($produtos as $p):
                $statusCls = strtolower($p['deStatus']) === 'ativo' ? 'ativo' : 'inativo';
                $search    = implode(' ', [$p['codigoProduto'], $p['nomeProduto'], $p['deCategoria'], $p['deStatus']]);
            ?>
            <tr class="prow" data-search="<?= htmlspecialchars($search) ?>">
                <td class="cod"><?= htmlspecialchars($p['codigoProduto']) ?></td>
                <td class="nome"><?= htmlspecialchars($p['nomeProduto']) ?></td>
                <td><?= htmlspecialchars($p['deCategoria']) ?></td>
                <td class="preco">R$ <?= number_format($p['precoProduto'],2,',','.') ?></td>
                <td><?= (int)$p['estoqueProduto'] ?></td>
                <td><span class="badge badge-<?= $statusCls ?>"><?= htmlspecialchars($p['deStatus']) ?></span></td>
                <td style="color:#888;font-size:12.5px"><?= date('d/m/Y', strtotime($p['dataCadastro'])) ?></td>
                <td>
                    <div class="actions-wrap">
                        <a href="editar.php?id=<?= $p['idProduto'] ?>" class="btn btn-edit">
                            <i class="fa-regular fa-pen-to-square"></i> Editar
                        </a>
                        <button class="btn btn-excluir"
                            onclick="confirmarExclusao(<?= $p['idProduto'] ?>, <?= json_encode($p['nomeProduto']) ?>)">
                            <i class="fa-regular fa-trash-can"></i> Excluir
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr id="emptyRow" style="display:none">
                <td colspan="8"><div class="empty"><i class="fa-solid fa-magnifying-glass"></i><p>Nenhum resultado encontrado.</p></div></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Auto-submit import form when file chosen -->
<script>
document.getElementById('inputCSV').addEventListener('change', function(){
    if (this.files.length) document.getElementById('formImport').submit();
});
</script>

<?php require_once 'includes/footer.php'; ?>
