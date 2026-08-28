-- Backup LavandariaPro
-- Gerado em 2026-07-25 01:22:06
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) NOT NULL,
  `telefone` varchar(25) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `documento` varchar(40) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `estado` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cliente_documento` (`documento`),
  KEY `idx_cliente_nome` (`nome`),
  KEY `idx_cliente_telefone` (`telefone`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `clientes` (`id`,`nome`,`telefone`,`email`,`documento`,`endereco`,`estado`,`criado_em`,`atualizado_em`) VALUES ('1','John Stewart','900877654','john_stewart@hotmail.com',NULL,'Golfe 2','ativo','2026-07-25 01:11:09','2026-07-25 01:12:41');
INSERT INTO `clientes` (`id`,`nome`,`telefone`,`email`,`documento`,`endereco`,`estado`,`criado_em`,`atualizado_em`) VALUES ('2','Mila Brown','960584736','mila_brown@hotmail.com','004126384LA044','Sequele','ativo','2026-07-25 01:11:37','2026-07-25 01:12:29');
INSERT INTO `clientes` (`id`,`nome`,`telefone`,`email`,`documento`,`endereco`,`estado`,`criado_em`,`atualizado_em`) VALUES ('3','Joanna Panzo','967858456','joanna_panzo@gmail.com',NULL,'Talatona','ativo','2026-07-25 01:12:09','2026-07-25 01:12:09');
INSERT INTO `clientes` (`id`,`nome`,`telefone`,`email`,`documento`,`endereco`,`estado`,`criado_em`,`atualizado_em`) VALUES ('4','Joel Filipe','976543211','joel_filipe@hotmail.com','004746637SA0987','Capolo 2','ativo','2026-07-25 01:13:22','2026-07-25 01:13:22');

DROP TABLE IF EXISTS `entregas`;
CREATE TABLE `entregas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `servico_id` bigint(20) unsigned NOT NULL,
  `entregue_por` int(10) unsigned NOT NULL,
  `recebido_por_nome` varchar(120) NOT NULL,
  `recebido_por_documento` varchar(40) DEFAULT NULL,
  `observacao` varchar(255) DEFAULT NULL,
  `entregue_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `servico_id` (`servico_id`),
  KEY `fk_entrega_utilizador` (`entregue_por`),
  CONSTRAINT `fk_entrega_servico` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_entrega_utilizador` FOREIGN KEY (`entregue_por`) REFERENCES `utilizadores` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `historico_estados`;
CREATE TABLE `historico_estados` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `servico_id` bigint(20) unsigned NOT NULL,
  `utilizador_id` int(10) unsigned NOT NULL,
  `estado_anterior` varchar(30) DEFAULT NULL,
  `novo_estado` varchar(30) NOT NULL,
  `observacao` varchar(255) DEFAULT NULL,
  `alterado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_historico_utilizador` (`utilizador_id`),
  KEY `idx_historico_servico` (`servico_id`),
  CONSTRAINT `fk_historico_servico` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_historico_utilizador` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `historico_estados` (`id`,`servico_id`,`utilizador_id`,`estado_anterior`,`novo_estado`,`observacao`,`alterado_em`) VALUES ('1','1','3',NULL,'recebido','Serviço registado.','2026-07-25 01:15:19');
INSERT INTO `historico_estados` (`id`,`servico_id`,`utilizador_id`,`estado_anterior`,`novo_estado`,`observacao`,`alterado_em`) VALUES ('2','1','3','recebido','em_lavagem',NULL,'2026-07-25 01:17:25');

DROP TABLE IF EXISTS `itens_servico`;
CREATE TABLE `itens_servico` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `servico_id` bigint(20) unsigned NOT NULL,
  `tipo_peca_id` smallint(5) unsigned NOT NULL,
  `tipo_servico_id` smallint(5) unsigned NOT NULL,
  `quantidade` smallint(5) unsigned NOT NULL DEFAULT 1,
  `cor` varchar(50) DEFAULT NULL,
  `observacoes` varchar(255) DEFAULT NULL,
  `preco_unitario` decimal(10,2) unsigned NOT NULL,
  `subtotal` decimal(12,2) unsigned NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_item_servico` (`servico_id`),
  KEY `fk_item_peca` (`tipo_peca_id`),
  KEY `fk_item_tipo_servico` (`tipo_servico_id`),
  CONSTRAINT `fk_item_peca` FOREIGN KEY (`tipo_peca_id`) REFERENCES `tipos_peca` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_item_servico` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_item_tipo_servico` FOREIGN KEY (`tipo_servico_id`) REFERENCES `tipos_servico` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `itens_servico` (`id`,`servico_id`,`tipo_peca_id`,`tipo_servico_id`,`quantidade`,`cor`,`observacoes`,`preco_unitario`,`subtotal`,`criado_em`) VALUES ('1','1','3','4','1','Branco','Manchas','4800.00','4800.00','2026-07-25 01:15:19');
INSERT INTO `itens_servico` (`id`,`servico_id`,`tipo_peca_id`,`tipo_servico_id`,`quantidade`,`cor`,`observacoes`,`preco_unitario`,`subtotal`,`criado_em`) VALUES ('2','1','5','2','1','Preta',NULL,'4700.00','4700.00','2026-07-25 01:15:19');

DROP TABLE IF EXISTS `logs_auditoria`;
CREATE TABLE `logs_auditoria` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `utilizador_id` int(10) unsigned DEFAULT NULL,
  `operacao` varchar(30) NOT NULL,
  `tabela_afetada` varchar(80) NOT NULL,
  `registo_id` bigint(20) unsigned DEFAULT NULL,
  `descricao` varchar(255) NOT NULL,
  `dados_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_anteriores`)),
  `dados_novos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_novos`)),
  `endereco_ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_log_utilizador` (`utilizador_id`),
  KEY `idx_log_tabela` (`tabela_afetada`),
  KEY `idx_log_data` (`criado_em`),
  CONSTRAINT `fk_log_utilizador` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('6','1','CREATE','utilizadores','1','Administrador inicial criado.',NULL,'{\"nome\":\"John Doe\",\"email\":\"john_doe@gmail.com\",\"perfil\":\"Administrador\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-24 00:41:16');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('7','1','LOGIN','utilizadores','1','Início de sessão realizado com sucesso.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-24 00:41:29');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('8','1','BACKUP','base_de_dados',NULL,'Backup criado: backup_2026-07-24_00-45-10.sql',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-24 00:45:10');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('9','1','LOGOUT','utilizadores','1','Sessão terminada.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-24 00:56:16');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('10','1','LOGIN','utilizadores','1','Início de sessão realizado com sucesso.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-24 22:49:19');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('11','1','EXPORT','servicos',NULL,'Relatório de serviços exportado em PDF.',NULL,'{\"inicio\":\"2026-07-01\",\"fim\":\"2026-07-24\",\"estado\":\"\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-24 22:50:05');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('13','1','LOGIN','utilizadores','1','Início de sessão realizado com sucesso.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-24 23:52:20');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('14','1','UPDATE','utilizadores','2','Utilizador atualizado.','{\"id\":2,\"perfil_id\":2,\"nome\":\"Divan Pedro\",\"email\":\"divan_pedro@gmail.com\",\"telefone\":\"900555444\",\"estado\":\"ativo\",\"ultimo_login\":null,\"criado_em\":\"2026-07-24 22:52:52\",\"perfil\":\"Gestor\"}','{\"perfil_id\":2,\"nome\":\"Divan Pedro\",\"email\":\"divan_pedro@gmail.com\",\"telefone\":\"900555444\",\"estado\":\"ativo\",\"id\":2}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-24 23:52:46');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('15','1','LOGOUT','utilizadores','1','Sessão terminada.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-24 23:52:55');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('16','2','LOGIN','utilizadores','2','Início de sessão realizado com sucesso.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-24 23:53:04');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('17','2','LOGOUT','utilizadores','2','Sessão terminada.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-24 23:53:27');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('18','1','LOGIN','utilizadores','1','Início de sessão realizado com sucesso.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-24 23:53:35');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('19','1','CREATE','utilizadores','3','Utilizador criado.',NULL,'{\"perfil_id\":3,\"nome\":\"Ana Dias\",\"email\":\"ana_dias@gmail.com\",\"telefone\":\"\",\"estado\":\"ativo\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-24 23:55:31');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('20','1','LOGOUT','utilizadores','1','Sessão terminada.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 00:03:29');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('21','2','LOGIN','utilizadores','2','Início de sessão realizado com sucesso.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 00:03:40');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('22','2','LOGOUT','utilizadores','2','Sessão terminada.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 00:05:50');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('23','3','LOGIN','utilizadores','3','Início de sessão realizado com sucesso.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 00:05:59');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('24','3','LOGIN','utilizadores','3','Início de sessão realizado com sucesso.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 00:39:41');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('25','3','LOGOUT','utilizadores','3','Sessão terminada.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 00:41:59');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('26','3','LOGIN','utilizadores','3','Início de sessão realizado com sucesso.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 01:10:06');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('27','3','CREATE','clientes','1','Cliente cadastrado.',NULL,'{\"nome\":\"John Stewart\",\"telefone\":\"900877654\",\"email\":\"john_stewart@hotmail.com\",\"documento\":\"\",\"endereco\":\"\",\"estado\":\"ativo\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 01:11:09');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('28','3','CREATE','clientes','2','Cliente cadastrado.',NULL,'{\"nome\":\"Mila Brown\",\"telefone\":\"960584736\",\"email\":\"\",\"documento\":\"004126384LA044\",\"endereco\":\"\",\"estado\":\"ativo\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 01:11:37');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('29','3','CREATE','clientes','3','Cliente cadastrado.',NULL,'{\"nome\":\"Joanna Panzo\",\"telefone\":\"967858456\",\"email\":\"joanna_panzo@gmail.com\",\"documento\":\"\",\"endereco\":\"Talatona\",\"estado\":\"ativo\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 01:12:09');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('30','3','UPDATE','clientes','2','Dados do cliente atualizados.','{\"id\":2,\"nome\":\"Mila Brown\",\"telefone\":\"960584736\",\"email\":null,\"documento\":\"004126384LA044\",\"endereco\":null,\"estado\":\"ativo\",\"criado_em\":\"2026-07-25 01:11:37\",\"atualizado_em\":\"2026-07-25 01:11:37\"}','{\"nome\":\"Mila Brown\",\"telefone\":\"960584736\",\"email\":\"mila_brown@hotmail.com\",\"documento\":\"004126384LA044\",\"endereco\":\"Sequele\",\"estado\":\"ativo\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 01:12:29');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('31','3','UPDATE','clientes','1','Dados do cliente atualizados.','{\"id\":1,\"nome\":\"John Stewart\",\"telefone\":\"900877654\",\"email\":\"john_stewart@hotmail.com\",\"documento\":null,\"endereco\":null,\"estado\":\"ativo\",\"criado_em\":\"2026-07-25 01:11:09\",\"atualizado_em\":\"2026-07-25 01:11:09\"}','{\"nome\":\"John Stewart\",\"telefone\":\"900877654\",\"email\":\"john_stewart@hotmail.com\",\"documento\":\"\",\"endereco\":\"Golfe 2\",\"estado\":\"ativo\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 01:12:41');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('32','3','CREATE','clientes','4','Cliente cadastrado.',NULL,'{\"nome\":\"Joel Filipe\",\"telefone\":\"976543211\",\"email\":\"joel_filipe@hotmail.com\",\"documento\":\"004746637SA0987\",\"endereco\":\"Capolo 2\",\"estado\":\"ativo\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 01:13:22');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('33','3','CREATE','servicos','1','Serviço registado.',NULL,'{\"cliente_id\":3,\"itens\":2,\"desconto\":0}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 01:15:19');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('34','3','UPDATE','servicos','1','Dados gerais do serviço atualizados.','{\"id\":1,\"codigo\":\"LAV-2026-000001\",\"cliente_id\":3,\"recebido_por\":3,\"estado\":\"recebido\",\"data_entrada\":\"2026-07-25 01:15:19\",\"data_prevista\":\"2026-07-28 01:15:19\",\"data_conclusao\":null,\"data_entrega\":null,\"subtotal\":\"9500.00\",\"desconto\":\"0.00\",\"total\":\"9500.00\",\"observacoes\":\"Manchas\",\"criado_em\":\"2026-07-25 01:15:19\",\"atualizado_em\":\"2026-07-25 01:15:19\",\"cliente\":\"Joanna Panzo\",\"cliente_telefone\":\"967858456\",\"cliente_email\":\"joanna_panzo@gmail.com\",\"atendente\":\"Ana Dias\",\"pago\":\"0.00\"}','{\"data_prevista\":\"2026-07-28 01:15\",\"desconto\":0}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 01:16:20');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('35','3','CREATE','pagamentos','1','Pagamento registado.',NULL,'{\"servico_id\":1,\"valor\":9500,\"metodo\":\"tpa\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 01:16:41');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('36','3','UPDATE','servicos','1','Estado do serviço alterado.','{\"estado\":\"recebido\"}','{\"estado\":\"em_lavagem\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 01:17:25');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('37','3','LOGOUT','utilizadores','3','Sessão terminada.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 01:17:52');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('38','2','LOGIN','utilizadores','2','Início de sessão realizado com sucesso.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 01:18:02');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('39','2','EXPORT','servicos',NULL,'Relatório de serviços exportado em PDF.',NULL,'{\"inicio\":\"2026-07-01\",\"fim\":\"2026-07-25\",\"estado\":\"\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 01:20:26');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('40','2','LOGOUT','utilizadores','2','Sessão terminada.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 01:21:03');
INSERT INTO `logs_auditoria` (`id`,`utilizador_id`,`operacao`,`tabela_afetada`,`registo_id`,`descricao`,`dados_anteriores`,`dados_novos`,`endereco_ip`,`user_agent`,`criado_em`) VALUES ('41','1','LOGIN','utilizadores','1','Início de sessão realizado com sucesso.',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-25 01:21:10');

DROP TABLE IF EXISTS `pagamentos`;
CREATE TABLE `pagamentos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `servico_id` bigint(20) unsigned NOT NULL,
  `recebido_por` int(10) unsigned NOT NULL,
  `valor` decimal(12,2) unsigned NOT NULL,
  `metodo` enum('dinheiro','transferencia','tpa','multicaixa_express') NOT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `estado` enum('confirmado','anulado') NOT NULL DEFAULT 'confirmado',
  `pago_em` datetime NOT NULL DEFAULT current_timestamp(),
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_pagamento_servico` (`servico_id`),
  KEY `fk_pagamento_utilizador` (`recebido_por`),
  KEY `idx_pagamento_data` (`pago_em`),
  CONSTRAINT `fk_pagamento_servico` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pagamento_utilizador` FOREIGN KEY (`recebido_por`) REFERENCES `utilizadores` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pagamentos` (`id`,`servico_id`,`recebido_por`,`valor`,`metodo`,`referencia`,`estado`,`pago_em`,`criado_em`) VALUES ('1','1','3','9500.00','tpa',NULL,'confirmado','2026-07-25 01:16:41','2026-07-25 01:16:41');

DROP TABLE IF EXISTS `perfis`;
CREATE TABLE `perfis` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(30) NOT NULL,
  `descricao` varchar(150) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `perfis` (`id`,`nome`,`descricao`,`criado_em`) VALUES ('1','Administrador','Acesso completo ao sistema','2026-07-24 00:11:46');
INSERT INTO `perfis` (`id`,`nome`,`descricao`,`criado_em`) VALUES ('2','Gestor','Dashboard, acompanhamento, histórico e relatórios','2026-07-24 00:11:46');
INSERT INTO `perfis` (`id`,`nome`,`descricao`,`criado_em`) VALUES ('3','Atendente','Clientes, receção, pagamentos e entregas','2026-07-24 00:11:46');

DROP TABLE IF EXISTS `precos`;
CREATE TABLE `precos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tipo_peca_id` smallint(5) unsigned NOT NULL,
  `tipo_servico_id` smallint(5) unsigned NOT NULL,
  `valor` decimal(10,2) unsigned NOT NULL,
  `estado` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_preco_peca_servico` (`tipo_peca_id`,`tipo_servico_id`),
  KEY `fk_preco_tipo_servico` (`tipo_servico_id`),
  CONSTRAINT `fk_preco_peca` FOREIGN KEY (`tipo_peca_id`) REFERENCES `tipos_peca` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_preco_tipo_servico` FOREIGN KEY (`tipo_servico_id`) REFERENCES `tipos_servico` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('1','1','1','1500.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('2','1','2','2200.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('3','1','3','900.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('4','1','4','2800.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('5','2','1','1800.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('6','2','2','2500.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('7','2','3','1100.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('8','2','4','3200.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('9','3','1','2800.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('10','3','2','3800.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('11','3','3','1600.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('12','3','4','4800.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('13','4','1','4500.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('14','4','2','6000.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('15','4','3','2500.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('16','4','4','7500.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('17','5','1','3500.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('18','5','2','4700.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('19','5','3','2000.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('20','5','4','5900.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('21','6','1','1600.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('22','6','2','2300.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('23','6','3','900.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('24','6','4','2900.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('25','7','1','2400.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('26','7','2','3200.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('27','7','3','1400.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('28','7','4','4000.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('29','8','1','5000.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('30','8','2','6500.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('31','8','3','3000.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('32','8','4','8000.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('33','9','1','1000.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('34','9','2','1500.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('35','9','3','700.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');
INSERT INTO `precos` (`id`,`tipo_peca_id`,`tipo_servico_id`,`valor`,`estado`,`criado_em`,`atualizado_em`) VALUES ('36','9','4','2000.00','ativo','2026-07-24 00:11:46','2026-07-24 00:11:46');

DROP TABLE IF EXISTS `servicos`;
CREATE TABLE `servicos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(25) NOT NULL,
  `cliente_id` int(10) unsigned NOT NULL,
  `recebido_por` int(10) unsigned NOT NULL,
  `estado` enum('recebido','em_lavagem','em_secagem','em_engomagem','pronto','entregue','cancelado') NOT NULL DEFAULT 'recebido',
  `data_entrada` datetime NOT NULL DEFAULT current_timestamp(),
  `data_prevista` datetime NOT NULL,
  `data_conclusao` datetime DEFAULT NULL,
  `data_entrega` datetime DEFAULT NULL,
  `subtotal` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
  `desconto` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
  `observacoes` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `fk_servico_utilizador` (`recebido_por`),
  KEY `idx_servico_estado` (`estado`),
  KEY `idx_servico_data_entrada` (`data_entrada`),
  KEY `idx_servico_cliente` (`cliente_id`),
  CONSTRAINT `fk_servico_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_servico_utilizador` FOREIGN KEY (`recebido_por`) REFERENCES `utilizadores` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `servicos` (`id`,`codigo`,`cliente_id`,`recebido_por`,`estado`,`data_entrada`,`data_prevista`,`data_conclusao`,`data_entrega`,`subtotal`,`desconto`,`total`,`observacoes`,`criado_em`,`atualizado_em`) VALUES ('1','LAV-2026-000001','3','3','em_lavagem','2026-07-25 01:15:19','2026-07-28 01:15:00',NULL,NULL,'9500.00','0.00','9500.00','Manchas','2026-07-25 01:15:19','2026-07-25 01:17:25');

DROP TABLE IF EXISTS `tipos_peca`;
CREATE TABLE `tipos_peca` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(80) NOT NULL,
  `descricao` varchar(180) DEFAULT NULL,
  `estado` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tipos_peca` (`id`,`nome`,`descricao`,`estado`,`criado_em`) VALUES ('1','Camisa',NULL,'ativo','2026-07-24 00:11:46');
INSERT INTO `tipos_peca` (`id`,`nome`,`descricao`,`estado`,`criado_em`) VALUES ('2','Calça',NULL,'ativo','2026-07-24 00:11:46');
INSERT INTO `tipos_peca` (`id`,`nome`,`descricao`,`estado`,`criado_em`) VALUES ('3','Vestido',NULL,'ativo','2026-07-24 00:11:46');
INSERT INTO `tipos_peca` (`id`,`nome`,`descricao`,`estado`,`criado_em`) VALUES ('4','Fato',NULL,'ativo','2026-07-24 00:11:46');
INSERT INTO `tipos_peca` (`id`,`nome`,`descricao`,`estado`,`criado_em`) VALUES ('5','Casaco',NULL,'ativo','2026-07-24 00:11:46');
INSERT INTO `tipos_peca` (`id`,`nome`,`descricao`,`estado`,`criado_em`) VALUES ('6','Saia',NULL,'ativo','2026-07-24 00:11:46');
INSERT INTO `tipos_peca` (`id`,`nome`,`descricao`,`estado`,`criado_em`) VALUES ('7','Lençol',NULL,'ativo','2026-07-24 00:11:46');
INSERT INTO `tipos_peca` (`id`,`nome`,`descricao`,`estado`,`criado_em`) VALUES ('8','Cobertor',NULL,'ativo','2026-07-24 00:11:46');
INSERT INTO `tipos_peca` (`id`,`nome`,`descricao`,`estado`,`criado_em`) VALUES ('9','Toalha',NULL,'ativo','2026-07-24 00:11:46');

DROP TABLE IF EXISTS `tipos_servico`;
CREATE TABLE `tipos_servico` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(80) NOT NULL,
  `descricao` varchar(180) DEFAULT NULL,
  `prazo_horas` smallint(5) unsigned NOT NULL DEFAULT 48,
  `estado` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tipos_servico` (`id`,`nome`,`descricao`,`prazo_horas`,`estado`,`criado_em`) VALUES ('1','Lavagem normal','Lavagem e secagem normal','48','ativo','2026-07-24 00:11:46');
INSERT INTO `tipos_servico` (`id`,`nome`,`descricao`,`prazo_horas`,`estado`,`criado_em`) VALUES ('2','Lavagem e engomagem','Lavagem, secagem e engomagem','72','ativo','2026-07-24 00:11:46');
INSERT INTO `tipos_servico` (`id`,`nome`,`descricao`,`prazo_horas`,`estado`,`criado_em`) VALUES ('3','Engomagem','Apenas engomagem da peça','24','ativo','2026-07-24 00:11:46');
INSERT INTO `tipos_servico` (`id`,`nome`,`descricao`,`prazo_horas`,`estado`,`criado_em`) VALUES ('4','Lavagem expressa','Serviço prioritário','12','ativo','2026-07-24 00:11:46');

DROP TABLE IF EXISTS `utilizadores`;
CREATE TABLE `utilizadores` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perfil_id` tinyint(3) unsigned NOT NULL,
  `nome` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` varchar(25) DEFAULT NULL,
  `estado` enum('ativo','inativo','bloqueado') NOT NULL DEFAULT 'ativo',
  `tentativas_login` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ultimo_login` datetime DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_utilizador_perfil` (`perfil_id`),
  CONSTRAINT `fk_utilizador_perfil` FOREIGN KEY (`perfil_id`) REFERENCES `perfis` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `utilizadores` (`id`,`perfil_id`,`nome`,`email`,`senha`,`telefone`,`estado`,`tentativas_login`,`ultimo_login`,`criado_em`,`atualizado_em`) VALUES ('1','1','John Doe','john_doe@gmail.com','$2y$10$jo2aQwvbYBNlry.PNZBGaOkpduxmU73piCkFSpnlFDUSLkP1hG07S',NULL,'ativo','0','2026-07-25 01:21:10','2026-07-24 00:41:16','2026-07-25 01:21:10');
INSERT INTO `utilizadores` (`id`,`perfil_id`,`nome`,`email`,`senha`,`telefone`,`estado`,`tentativas_login`,`ultimo_login`,`criado_em`,`atualizado_em`) VALUES ('2','2','Divan Pedro','divan_pedro@gmail.com','$2y$10$S5/SwakKdArhEi3wqfOF4eJwNcB/TKDqslQRks/kqt128zZVcDn8O','900555444','ativo','0','2026-07-25 01:18:02','2026-07-24 22:52:52','2026-07-25 01:18:02');
INSERT INTO `utilizadores` (`id`,`perfil_id`,`nome`,`email`,`senha`,`telefone`,`estado`,`tentativas_login`,`ultimo_login`,`criado_em`,`atualizado_em`) VALUES ('3','3','Ana Dias','ana_dias@gmail.com','$2y$10$eS0aHlJvm5BUFdAMwum/POBK1UBGDtW8FWAzLU3/wP4L37.oXM4v2',NULL,'ativo','0','2026-07-25 01:10:06','2026-07-24 23:55:31','2026-07-25 01:10:06');

SET FOREIGN_KEY_CHECKS=1;
