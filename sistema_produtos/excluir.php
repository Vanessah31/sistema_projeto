<?php
// excluir.php — Excluir Produto (UC03 / RN-09 a RN-14)
require_once 'includes/conexao.php';
session_start();

$id = (int)($_GET['id'] ?? 0);
$ok = ($_GET['ok'] ?? '') === '1';

if (!$id || !$ok) { header('Location: produtos.php'); exit; }

$pdo  = conectar();
$stmt = $pdo->prepare("SELECT * FROM produto WHERE idProduto=?");
$stmt->execute([$id]);
$prod = $stmt->fetch();

// RN-10: produto deve existir
if (!$prod) {
    $_SESSION['msg']='Produto não encontrado.'; $_SESSION['tipo']='error';
    header('Location: produtos.php'); exit;
}

// RN-11: verificar dependências (estrutura preparada)
// $dep = $pdo->prepare("SELECT COUNT(*) FROM pedido_item WHERE produto_id=?");
// $dep->execute([$id]);
// if ($dep->fetchColumn() > 0) { ... }

// RN-09: executar exclusão com confirmação (confirmação foi feita via JS modal)
$pdo->prepare("DELETE FROM produto WHERE idProduto=?")->execute([$id]);

// RN-12: auditoria
auditoria('EXCLUSAO', $id, $prod['codigoProduto'], "Produto \"{$prod['nomeProduto']}\" excluído.");

// RN-14: mensagem de sucesso (imagem 5)
$_SESSION['msg']  = 'Produto excluído com sucesso!';
$_SESSION['tipo'] = 'success';
header('Location: produtos.php');
exit;
