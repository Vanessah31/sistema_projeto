<?php
// includes/conexao.php
define('DB_HOST',    'localhost');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_NAME',    'sistema_produtos');
define('DB_CHARSET', 'utf8mb4');

function conectar(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function auditoria(string $tipo, ?int $id, ?string $cod, string $det = ''): void {
    try {
        $s = conectar()->prepare(
            "INSERT INTO auditoria (tipoOperacao,idProdutoAfetado,codigoProduto,detalhes)
             VALUES (:t,:i,:c,:d)"
        );
        $s->execute([':t'=>$tipo,':i'=>$id,':c'=>$cod,':d'=>$det]);
    } catch(PDOException $e) {}
}
