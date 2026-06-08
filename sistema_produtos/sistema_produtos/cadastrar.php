<?php
// cadastrar.php — Incluir Produto (UC01 / RN-01 a RN-07)
require_once 'includes/conexao.php';
session_start();
$pagina = 'produtos';
$pdo    = conectar();
$erros  = [];
$d      = ['cod'=>'','nome'=>'','desc'=>'','preco'=>'','estq'=>'0','cat'=>'','sts'=>'1'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = [
        'cod'  => trim($_POST['cod']  ?? ''),
        'nome' => trim($_POST['nome'] ?? ''),
        'desc' => trim($_POST['desc'] ?? ''),
        'preco'=> str_replace(['.',',' ],['','.' ], trim($_POST['preco']??'')),
        'estq' => trim($_POST['estq'] ?? '0'),
        'cat'  => trim($_POST['cat']  ?? ''),
        'sts'  => trim($_POST['sts']  ?? '1'),
    ];

    // RN-01: Código
    if ($d['cod']==='') $erros['cod']='Código é obrigatório.';
    elseif (!preg_match('/^[A-Za-z0-9\-]{3,20}$/',$d['cod'])) $erros['cod']='3-20 caracteres: letras, números e hífen.';
    else {
        $chk=$pdo->prepare("SELECT idProduto FROM produto WHERE codigoProduto=?");
        $chk->execute([$d['cod']]);
        if ($chk->fetch()) $erros['cod']='Código já existe no sistema.';
    }
    // RN-02: Nome
    if ($d['nome']==='') $erros['nome']='Nome é obrigatório.';
    elseif (mb_strlen($d['nome'])<3||mb_strlen($d['nome'])>100) $erros['nome']='Mínimo 3, máximo 100 caracteres.';
    // RN-03: Categoria
    if ($d['cat']==='') $erros['cat']='Selecione uma categoria.';
    // RN-04: Descrição
    if (mb_strlen($d['desc'])>500) $erros['desc']='Máximo 500 caracteres.';
    // RN-05: Preço
    if ($d['preco']===''||!is_numeric($d['preco'])) $erros['preco']='Preço inválido.';
    elseif ((float)$d['preco']<=0) $erros['preco']='Preço deve ser maior que zero.';
    elseif ((float)$d['preco']>1000000) $erros['preco']='Preço máximo: R$ 1.000.000,00.';
    // Estoque
    if (!ctype_digit((string)$d['estq'])) $erros['estq']='Número inteiro não negativo.';
    elseif ((int)$d['estq']>999999) $erros['estq']='Máximo 999.999.';

    if (empty($erros)) {
        $ins=$pdo->prepare(
            "INSERT INTO produto 
            (codigoProduto,nomeProduto,descricaoProduto,precoProduto,estoqueProduto,
            Categoria_idCategoria,Status_idStatus) 
            VALUES(?,?,?,?,?,?,?)"
            );
        $ins->execute([
            $d['cod'],
            $d['nome'],
            $d['desc']?:null,
            (float)$d['preco'],
            (int)$d['estq'],
            (int)$d['cat'],
            (int)$d['sts']]
            );

            
        auditoria('INCLUSAO',(int)$pdo->lastInsertId(),$d['cod'],'Produto cadastrado.');
        // RN-06: mensagem de sucesso (imagem 4)
        $_SESSION['msg']  = 'Produto salvo com sucesso!';
        $_SESSION['tipo'] = 'success';
        header('Location: produtos.php');
        exit;
    }
}

$cats = $pdo->query("SELECT * FROM categoria ORDER BY deCategoria")->fetchAll();
$stsL = $pdo->query("SELECT * FROM status_produto ORDER BY idStatus")->fetchAll();
require_once 'includes/header.php';
?>

<div class="breadcrumb">
    <a href="index.php"><i class="fa-solid fa-house" style="font-size:11px"></i></a>
    <span class="sep">/</span>
    <a href="produtos.php">Listagem de Produtos</a>
    <span class="sep">/</span>
    <span class="cur">Cadastrar Produto</span>
</div>

<div class="form-wrap">
    <div class="form-title-row">
        <h2 class="form-title">Cadastrar Novo Produto</h2>
        <span class="form-req-note"><span>*</span> Campos obrigatórios</span>
    </div>

    <?php if (!empty($erros)): ?>
    <div class="alert-inline alert-danger">
        <i class="fa-solid fa-circle-xmark"></i>
        <div>
            <strong>Corrija os erros:</strong>
            <ul style="margin-top:5px;list-style:disc;padding-left:16px">
                <?php foreach($erros as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <form method="POST" novalidate>

        <!-- Linha 1: Código + Categoria -->
        <div class="form-grid-2">
            <div class="form-group">
                <label class="flabel" for="cod">Código <span class="req">*</span></label>
                <input id="cod" name="cod" type="text"
                    class="finput <?= isset($erros['cod'])?'err':'' ?>"
                    placeholder="Ex: PROD001" maxlength="20"
                    value="<?= htmlspecialchars($d['cod']) ?>">
                <?php if(isset($erros['cod'])): ?>
                    <span class="fhint err"><?= htmlspecialchars($erros['cod']) ?></span>
                <?php else: ?>
                    <span class="fhint">3-20 caracteres, letras, números e hífen</span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label class="flabel" for="cat">Categoria <span class="req">*</span></label>
                <select id="cat" name="cat" class="fselect <?= isset($erros['cat'])?'err':'' ?>">
                    <option value="">Selecione uma categoria</option>
                    <?php foreach($cats as $c): ?>
                    <option value="<?= $c['idCategoria'] ?>" <?= $d['cat']==$c['idCategoria']?'selected':'' ?>>
                        <?= htmlspecialchars($c['deCategoria']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if(isset($erros['cat'])): ?>
                    <span class="fhint err"><?= htmlspecialchars($erros['cat']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Nome -->
        <div class="form-full">
            <div class="form-group">
                <label class="flabel" for="nome">Nome do Produto <span class="req">*</span></label>
                <input id="nome" name="nome" type="text"
                    class="finput <?= isset($erros['nome'])?'err':'' ?>"
                    placeholder="Digite o nome do produto" maxlength="100"
                    value="<?= htmlspecialchars($d['nome']) ?>">
                <?php if(isset($erros['nome'])): ?>
                    <span class="fhint err"><?= htmlspecialchars($erros['nome']) ?></span>
                <?php else: ?>
                    <span class="fhint">Mínimo 3 caracteres, máximo 100</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Descrição -->
        <div class="form-full">
            <div class="form-group">
                <label class="flabel" for="descricaoProduto">Descrição</label>
                <textarea id="descricaoProduto" name="desc"
                    class="ftextarea <?= isset($erros['desc'])?'err':'' ?>"
                    placeholder="Digite a descrição do produto" maxlength="500"
                    rows="3"><?= htmlspecialchars($d['desc']) ?></textarea>
                <span id="charCount" class="fhint cnt">0/500</span>
            </div>
        </div>

        <!-- Preço + Estoque + Status -->
        <div class="form-grid-3">
            <div class="form-group">
                <label class="flabel" for="preco">Preço (R$) <span class="req">*</span></label>
                <input id="preco" name="preco" type="number"
                    class="finput <?= isset($erros['preco'])?'err':'' ?>"
                    placeholder="0.00" min="0.01" max="1000000" step="0.01"
                    value="<?= htmlspecialchars($d['preco']) ?>">
                <?php if(isset($erros['preco'])): ?>
                    <span class="fhint err"><?= htmlspecialchars($erros['preco']) ?></span>
                <?php else: ?>
                    <span class="fhint">Valor maior que zero</span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label class="flabel" for="estq">Estoque <span class="req">*</span></label>
                <input id="estq" name="estq" type="number"
                    class="finput <?= isset($erros['estq'])?'err':'' ?>"
                    placeholder="0" min="0" max="999999"
                    value="<?= htmlspecialchars($d['estq']) ?>">
                <?php if(isset($erros['estq'])): ?>
                    <span class="fhint err"><?= htmlspecialchars($erros['estq']) ?></span>
                <?php else: ?>
                    <span class="fhint">Número inteiro não negativo</span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label class="flabel" for="sts">Status <span class="req">*</span></label>
                <select id="sts" name="sts" class="fselect">
                    <?php foreach($stsL as $s): ?>
                    <option value="<?= $s['idStatus'] ?>" <?= $d['sts']==$s['idStatus']?'selected':'' ?>>
                        <?= htmlspecialchars($s['deStatus']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Caixa de validação -->
        <div class="vbox">
            <h4><i class="fa-solid fa-circle-check"></i> Regras de Validação</h4>
            <ul>
                <li>Código: único, 3-20 caracteres, apenas letras, números e hífen</li>
                <li>Nome: 3-100 caracteres obrigatórios</li>
                <li>Preço: maior que zero, máximo R$ 1.000.000,00</li>
                <li>Estoque: número inteiro não negativo, máximo 999.999</li>
                <li>Descrição: opcional, máximo 500 caracteres</li>
            </ul>
        </div>

        <!-- RN-07: Botões Salvar e Cancelar -->
        <div class="form-actions">
            <button type="submit" class="btn btn-save">
                <i class="fa-regular fa-floppy-disk"></i> Salvar
            </button>
            <a href="produtos.php" class="btn btn-cancel">
                <i class="fa-solid fa-xmark"></i> Cancelar
            </a>
        </div>

    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
