<?php
require_once 'conexao.php';

$errors = [];
$form_data = [];
$success_message = '';

if (isset($_GET['sucesso'])) {
    $success_message = "✅ Produto cadastrado com sucesso!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    $codigo = trim($_POST['codigo'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = str_replace(',', '.', $_POST['preco'] ?? '0.00');
    $estoque = (int)($_POST['estoque'] ?? 0);
    $status = $_POST['status'] ?? 'Ativo';

    $form_data = compact('codigo', 'nome', 'descricao', 'preco', 'estoque', 'status');

    // Validações (sem categoria)
    if (empty($codigo)) {
        $errors['codigo'] = "Código é obrigatório.";
    } elseif (strlen($codigo) < 3 || strlen($codigo) > 20) {
        $errors['codigo'] = "Código deve ter entre 3 e 20 caracteres.";
    } elseif (!preg_match('/^[a-zA-Z0-9-]+$/', $codigo)) {
        $errors['codigo'] = "Apenas letras, números e hífen.";
    } else {
        $check = $pdo->prepare("SELECT id FROM produtos WHERE codigo = ?");
        $check->execute([$codigo]);
        if ($check->fetch()) $errors['codigo'] = "Código já existe.";
    }

    if (empty($nome)) {
        $errors['nome'] = "Nome é obrigatório.";
    } elseif (strlen($nome) < 3 || strlen($nome) > 100) {
        $errors['nome'] = "Nome entre 3 e 100 caracteres.";
    }

    $preco_num = filter_var($preco, FILTER_VALIDATE_FLOAT);
    if ($preco_num === false || $preco_num <= 0) {
        $errors['preco'] = "Preço deve ser maior que zero.";
    } elseif ($preco_num > 1000000) {
        $errors['preco'] = "Preço máximo R$ 1.000.000,00.";
    }

    if (!is_numeric($estoque) || $estoque < 0 || $estoque > 999999) {
        $errors['estoque'] = "Estoque inteiro de 0 a 999.999.";
    }

    if (strlen($descricao) > 500) $errors['descricao'] = "Máximo 500 caracteres.";

    if (empty($errors)) {
        // Precisa de uma categoria padrão? Vamos pegar a primeira existente
        $stmt = $pdo->query("SELECT id FROM categorias LIMIT 1");
        $catPadrao = $stmt->fetchColumn();
        if (!$catPadrao) {
            $errors['general'] = "Nenhuma categoria cadastrada. Execute o INSERT de categorias.";
        } else {
            $sql = "INSERT INTO produtos (codigo, categoria_id, nome, descricao, preco, estoque, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$codigo, $catPadrao, $nome, $descricao, $preco_num, $estoque, $status]);
            header("Location: index.php?sucesso=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Produtos</title>
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>
    <aside class="sidebar">
        <div class="logo">SISTEMA DE PRODUTOS</div>
        <nav>
            <a href="#" class="menu-item active">Página Inicial</a>
            <a href="#" class="menu-item">Produtos</a>
            <a href="#" class="menu-item">Pedidos</a>
            <a href="#" class="menu-item">Estoque</a>
            <a href="#" class="menu-item">Categorias</a>
            <a href="#" class="menu-item">Relatórios</a>
            <a href="#" class="menu-item">Configurações</a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="page-title">Cadastrar Novo Produto</div>

        <?php if ($success_message): ?>
            <div class="success-message">☑ <?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" id="produtoForm">
                <div class="form-group">
                    <label>Código</label>
                    <input type="text" name="codigo" id="codigo"
                           value="<?= htmlspecialchars($form_data['codigo'] ?? '') ?>"
                           placeholder="Ex: PROD001"
                           class="<?= isset($errors['codigo']) ? 'error-field' : '' ?>">
                    <?php if (isset($errors['codigo'])): ?>
                        <div class="error-message"><?= $errors['codigo'] ?></div>
                    <?php endif; ?>
                    <small class="help-text">3-20 caracteres, letras, números e hífen</small>
                </div>

                <div class="form-group">
                    <label>Nome do Produto</label>
                    <input type="text" name="nome" id="nome"
                           value="<?= htmlspecialchars($form_data['nome'] ?? '') ?>"
                           placeholder="Digite o nome do produto"
                           class="<?= isset($errors['nome']) ? 'error-field' : '' ?>">
                    <?php if (isset($errors['nome'])): ?>
                        <div class="error-message"><?= $errors['nome'] ?></div>
                    <?php endif; ?>
                    <small class="help-text">Mínimo 3 caracteres, máximo 100</small>
                </div>

                <div class="form-group">
                    <label>Descrição</label>
                    <textarea name="descricao" id="descricao" rows="4"
                              placeholder="Digite a descrição do produto"
                              class="<?= isset($errors['descricao']) ? 'error-field' : '' ?>"><?= htmlspecialchars($form_data['descricao'] ?? '') ?></textarea>
                    <div class="char-counter" id="charCounter">Maximo 500 caracteres (0/500)</div>
                    <?php if (isset($errors['descricao'])): ?>
                        <div class="error-message"><?= $errors['descricao'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Preço (R$) <span class="required-star">*</span></label>
                    <input type="number" step="0.01" name="preco" id="preco"
                           value="<?= htmlspecialchars($form_data['preco'] ?? '0.00') ?>"
                           class="<?= isset($errors['preco']) ? 'error-field' : '' ?>">
                    <?php if (isset($errors['preco'])): ?>
                        <div class="error-message"><?= $errors['preco'] ?></div>
                    <?php endif; ?>
                    <small class="help-text">Valor maior que zero</small>
                </div>

                <div class="form-group">
                    <label>Estoque</label>
                    <input type="number" step="1" name="estoque" id="estoque"
                           value="<?= htmlspecialchars($form_data['estoque'] ?? '0') ?>"
                           class="<?= isset($errors['estoque']) ? 'error-field' : '' ?>">
                    <?php if (isset($errors['estoque'])): ?>
                        <div class="error-message"><?= $errors['estoque'] ?></div>
                    <?php endif; ?>
                    <small class="help-text">Número inteiro não negativo</small>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="Ativo" <?= (($form_data['status'] ?? 'Ativo') == 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                        <option value="Inativo" <?= (($form_data['status'] ?? '') == 'Inativo') ? 'selected' : '' ?>>Inativo</option>
                    </select>
                </div>

                <div class="rules-section">
                    <h3>☑ Regras de Validação</h3>
                    <ul class="rules-list">
                        <li>Código: único, 3-20 caracteres, apenas letras, números e hífen</li>
                        <li>Nome: 3-100 caracteres obrigatórios</li>
                        <li>Preço: maior que zero, máximo R$ 1.000.000,00</li>
                        <li>Estoque: número inteiro não negativo, máximo 999.999</li>
                        <li>Descrição: opcional, máximo 500 caracteres</li>
                    </ul>
                </div>

                <div class="button-group">
                    <button type="submit" name="salvar" class="btn btn-save">Salvar</button>
                    <button type="button" class="btn btn-cancel" onclick="limparFormulario()">Cancelar</button>
                </div>
            </form>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
</body>
</html>