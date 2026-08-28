-- =========================================================
-- SISTEMA DE GESTAO DE LAVANDARIA
-- =========================================================

CREATE DATABASE IF NOT EXISTS lavandaria_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE lavandaria_db;

-- ---------------------------------------------------------
-- 1. Perfis e utilizadores
-- ---------------------------------------------------------

CREATE TABLE perfis (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(30) NOT NULL UNIQUE,
    descricao VARCHAR(150) NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE utilizadores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    perfil_id TINYINT UNSIGNED NOT NULL,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(25) NULL,
    estado ENUM('ativo', 'inativo', 'bloqueado') NOT NULL DEFAULT 'ativo',
    tentativas_login TINYINT UNSIGNED NOT NULL DEFAULT 0,
    ultimo_login DATETIME NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_utilizador_perfil
        FOREIGN KEY (perfil_id) REFERENCES perfis(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 2. Clientes
-- ---------------------------------------------------------

CREATE TABLE clientes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    telefone VARCHAR(25) NOT NULL,
    email VARCHAR(150) NULL,
    documento VARCHAR(40) NULL,
    endereco VARCHAR(255) NULL,
    estado ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_cliente_documento (documento),
    INDEX idx_cliente_nome (nome),
    INDEX idx_cliente_telefone (telefone)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 3. Catálogo de peças, tipos de serviço e preços
-- ---------------------------------------------------------

CREATE TABLE tipos_peca (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL UNIQUE,
    descricao VARCHAR(180) NULL,
    estado ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE tipos_servico (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL UNIQUE,
    descricao VARCHAR(180) NULL,
    prazo_horas SMALLINT UNSIGNED NOT NULL DEFAULT 48,
    estado ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE precos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo_peca_id SMALLINT UNSIGNED NOT NULL,
    tipo_servico_id SMALLINT UNSIGNED NOT NULL,
    valor DECIMAL(10,2) UNSIGNED NOT NULL,
    estado ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_preco_peca
        FOREIGN KEY (tipo_peca_id) REFERENCES tipos_peca(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_preco_tipo_servico
        FOREIGN KEY (tipo_servico_id) REFERENCES tipos_servico(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    UNIQUE KEY uk_preco_peca_servico (tipo_peca_id, tipo_servico_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 4. Serviços da lavandaria
-- ---------------------------------------------------------

CREATE TABLE servicos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(25) NOT NULL UNIQUE,
    cliente_id INT UNSIGNED NOT NULL,
    recebido_por INT UNSIGNED NOT NULL,
    estado ENUM(
        'recebido',
        'em_lavagem',
        'em_secagem',
        'em_engomagem',
        'pronto',
        'entregue',
        'cancelado'
    ) NOT NULL DEFAULT 'recebido',
    data_entrada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_prevista DATETIME NOT NULL,
    data_conclusao DATETIME NULL,
    data_entrega DATETIME NULL,
    subtotal DECIMAL(12,2) UNSIGNED NOT NULL DEFAULT 0.00,
    desconto DECIMAL(12,2) UNSIGNED NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) UNSIGNED NOT NULL DEFAULT 0.00,
    observacoes TEXT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_servico_cliente
        FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_servico_utilizador
        FOREIGN KEY (recebido_por) REFERENCES utilizadores(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    INDEX idx_servico_estado (estado),
    INDEX idx_servico_data_entrada (data_entrada),
    INDEX idx_servico_cliente (cliente_id)
) ENGINE=InnoDB;

CREATE TABLE itens_servico (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    servico_id BIGINT UNSIGNED NOT NULL,
    tipo_peca_id SMALLINT UNSIGNED NOT NULL,
    tipo_servico_id SMALLINT UNSIGNED NOT NULL,
    quantidade SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    cor VARCHAR(50) NULL,
    observacoes VARCHAR(255) NULL,
    preco_unitario DECIMAL(10,2) UNSIGNED NOT NULL,
    subtotal DECIMAL(12,2) UNSIGNED NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_item_servico
        FOREIGN KEY (servico_id) REFERENCES servicos(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_item_peca
        FOREIGN KEY (tipo_peca_id) REFERENCES tipos_peca(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_item_tipo_servico
        FOREIGN KEY (tipo_servico_id) REFERENCES tipos_servico(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE historico_estados (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    servico_id BIGINT UNSIGNED NOT NULL,
    utilizador_id INT UNSIGNED NOT NULL,
    estado_anterior VARCHAR(30) NULL,
    novo_estado VARCHAR(30) NOT NULL,
    observacao VARCHAR(255) NULL,
    alterado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_historico_servico
        FOREIGN KEY (servico_id) REFERENCES servicos(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_historico_utilizador
        FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    INDEX idx_historico_servico (servico_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 5. Pagamentos e entregas
-- ---------------------------------------------------------

CREATE TABLE pagamentos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    servico_id BIGINT UNSIGNED NOT NULL,
    recebido_por INT UNSIGNED NOT NULL,
    valor DECIMAL(12,2) UNSIGNED NOT NULL,
    metodo ENUM('dinheiro', 'transferencia', 'tpa', 'multicaixa_express')
        NOT NULL,
    referencia VARCHAR(100) NULL,
    estado ENUM('confirmado', 'anulado') NOT NULL DEFAULT 'confirmado',
    pago_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_pagamento_servico
        FOREIGN KEY (servico_id) REFERENCES servicos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_pagamento_utilizador
        FOREIGN KEY (recebido_por) REFERENCES utilizadores(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    INDEX idx_pagamento_data (pago_em)
) ENGINE=InnoDB;

CREATE TABLE entregas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    servico_id BIGINT UNSIGNED NOT NULL UNIQUE,
    entregue_por INT UNSIGNED NOT NULL,
    recebido_por_nome VARCHAR(120) NOT NULL,
    recebido_por_documento VARCHAR(40) NULL,
    observacao VARCHAR(255) NULL,
    entregue_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_entrega_servico
        FOREIGN KEY (servico_id) REFERENCES servicos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_entrega_utilizador
        FOREIGN KEY (entregue_por) REFERENCES utilizadores(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 6. Auditoria
-- ---------------------------------------------------------

CREATE TABLE logs_auditoria (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilizador_id INT UNSIGNED NULL,
    operacao VARCHAR(30) NOT NULL,
    tabela_afetada VARCHAR(80) NOT NULL,
    registo_id BIGINT UNSIGNED NULL,
    descricao VARCHAR(255) NOT NULL,
    dados_anteriores JSON NULL,
    dados_novos JSON NULL,
    endereco_ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_log_utilizador
        FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    INDEX idx_log_utilizador (utilizador_id),
    INDEX idx_log_tabela (tabela_afetada),
    INDEX idx_log_data (criado_em)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 7. Dados iniciais
-- ---------------------------------------------------------

INSERT INTO perfis (nome, descricao) VALUES
('Administrador', 'Acesso completo ao sistema'),
('Gestor', 'Dashboard, acompanhamento, histórico e relatórios'),
('Atendente', 'Clientes, receção, pagamentos e entregas');

INSERT INTO tipos_peca (nome) VALUES
('Camisa'),
('Calça'),
('Vestido'),
('Fato'),
('Casaco'),
('Saia'),
('Lençol'),
('Cobertor'),
('Toalha');

INSERT INTO tipos_servico (nome, descricao, prazo_horas) VALUES
('Lavagem normal', 'Lavagem e secagem normal', 48),
('Lavagem e engomagem', 'Lavagem, secagem e engomagem', 72),
('Engomagem', 'Apenas engomagem da peça', 24),
('Lavagem expressa', 'Serviço prioritário', 12);

INSERT INTO precos (tipo_peca_id, tipo_servico_id, valor) VALUES
(1,1,1500.00),(1,2,2200.00),(1,3,900.00),(1,4,2800.00),
(2,1,1800.00),(2,2,2500.00),(2,3,1100.00),(2,4,3200.00),
(3,1,2800.00),(3,2,3800.00),(3,3,1600.00),(3,4,4800.00),
(4,1,4500.00),(4,2,6000.00),(4,3,2500.00),(4,4,7500.00),
(5,1,3500.00),(5,2,4700.00),(5,3,2000.00),(5,4,5900.00),
(6,1,1600.00),(6,2,2300.00),(6,3,900.00),(6,4,2900.00),
(7,1,2400.00),(7,2,3200.00),(7,3,1400.00),(7,4,4000.00),
(8,1,5000.00),(8,2,6500.00),(8,3,3000.00),(8,4,8000.00),
(9,1,1000.00),(9,2,1500.00),(9,3,700.00),(9,4,2000.00);

-- O primeiro administrador será criado pela aplicação com password_hash().
