-- ============================================================
-- SISTEMA DE PRODUTOS — banco.sql
-- Execute no phpMyAdmin: Importar > selecionar este arquivo
-- ============================================================

CREATE DATABASE IF NOT EXISTS sistema_produtos
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sistema_produtos;

CREATE TABLE IF NOT EXISTS categoria (
    idCategoria   INT AUTO_INCREMENT PRIMARY KEY,
    deCategoria   VARCHAR(50)  NOT NULL,
    descricao     VARCHAR(255) DEFAULT NULL,
    dataCadastro  DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS status_produto (
    idStatus  INT AUTO_INCREMENT PRIMARY KEY,
    deStatus  VARCHAR(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS produto (
    idProduto              INT AUTO_INCREMENT PRIMARY KEY,
    codigoProduto          VARCHAR(20)    NOT NULL UNIQUE,
    nomeProduto            VARCHAR(100)   NOT NULL,
    descricaoProduto       VARCHAR(500)   DEFAULT NULL,
    precoProduto           DECIMAL(10,2)  NOT NULL,
    estoqueProduto         INT            NOT NULL DEFAULT 0,
    Categoria_idCategoria  INT            NOT NULL,
    Status_idStatus        INT            NOT NULL DEFAULT 1,
    dataCadastro           DATETIME       DEFAULT CURRENT_TIMESTAMP,
    dataAtualizacao        DATETIME       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cat  FOREIGN KEY (Categoria_idCategoria) REFERENCES categoria(idCategoria),
    CONSTRAINT fk_stat FOREIGN KEY (Status_idStatus)       REFERENCES status_produto(idStatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS auditoria (
    idAuditoria       INT AUTO_INCREMENT PRIMARY KEY,
    tipoOperacao      ENUM('INCLUSAO','ALTERACAO','EXCLUSAO','IMPORTACAO','EXPORTACAO') NOT NULL,
    idProdutoAfetado  INT          DEFAULT NULL,
    codigoProduto     VARCHAR(20)  DEFAULT NULL,
    usuarioResponsavel VARCHAR(100) DEFAULT 'Administrador Master',
    dataHora          DATETIME     DEFAULT CURRENT_TIMESTAMP,
    detalhes          TEXT         DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- DADOS INICIAIS ----
INSERT INTO status_produto (deStatus) VALUES ('Ativo'), ('Inativo');

INSERT INTO categoria (deCategoria) VALUES
    ('Informática'), ('Periféricos'), ('Áudio'), ('Escritório'), ('Smartphones');

INSERT INTO produto (codigoProduto, nomeProduto, descricaoProduto, precoProduto, estoqueProduto, Categoria_idCategoria, Status_idStatus, dataCadastro) VALUES
    ('PROD001', 'Notebook Dell Inspiron',   'Notebook Dell Inspiron 15, i5, 8GB RAM, 256GB SSD', 3500.00, 15, 1, 1, '2026-02-14 00:00:00'),
    ('PROD002', 'Mouse Logitech MX Master', 'Mouse sem fio Logitech MX Master 3, ergonômico',    450.00,  32, 2, 1, '2026-02-19 00:00:00'),
    ('PROD003', 'Teclado Mecânico Keychron','Teclado mecânico Keychron K2, switch Red',          650.00,   8, 2, 1, '2026-02-28 00:00:00'),
    ('PROD004', 'Monitor LG UltraWide',     'Monitor LG 29" UltraWide IPS 75Hz',               1200.00,   5, 1, 1, '2026-03-04 00:00:00'),
    ('PROD005', 'Headset HyperX Cloud',     'Headset HyperX Cloud II, som surround 7.1',         380.00,   0, 3, 2, '2026-01-09 00:00:00');
