# Sistema de Produtos — Instalação XAMPP

## Requisitos
- XAMPP (Apache + MySQL + PHP 8.0+)
- Navegador moderno (Chrome, Edge, Firefox)

---

## Instalação em 3 passos

### 1. Copie os arquivos
```
C:\xampp\htdocs\sistema_produtos\
```
(cole toda a pasta aqui)

### 2. Crie o banco de dados
1. Inicie XAMPP → Apache + MySQL
2. Abra: http://localhost/phpmyadmin
3. Clique **Importar** → selecione `banco.sql` → **Executar**

### 3. Acesse
```
http://localhost/sistema_produtos/
```

---

## Se seu MySQL tiver senha
Edite `includes/conexao.php`:
```php
define('DB_PASS', 'sua_senha');
```

---

## Funcionalidades

| Página | Arquivo | Função |
|---|---|---|
| Página Inicial | index.php | Dashboard com estatísticas |
| Listagem | produtos.php | Lista, busca, importa e exporta |
| Cadastrar | cadastrar.php | Incluir produto (UC01) |
| Editar | editar.php | Alterar produto (UC02) |
| Excluir | excluir.php | Excluir com confirmação (UC03) |

## Importar CSV
- Clique em **Importar CSV** na listagem
- Selecione um arquivo `.csv` com separador `;`
- Formato: `codigo;nome;categoria;preco;estoque;descricao;status`
- Use `modelo_importacao.csv` como referência

## Exportar CSV
- Clique em **Exportar CSV** — baixa todos os produtos
- O arquivo abre direto no Excel (com BOM UTF-8)

## Regras de Negócio Implementadas
- RN-01 ao RN-07: Validações de inclusão
- RN-08: Auditoria de alteração
- RN-09 ao RN-14: Exclusão com confirmação e auditoria
- RN-22 ao RN-29: Importação CSV por lote
- RN-30 ao RN-37: Exportação CSV
- RNF-03: Tabela de auditoria completa
