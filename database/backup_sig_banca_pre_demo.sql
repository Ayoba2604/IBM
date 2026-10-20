-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sig_banca
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alunos_trabalhos`
--

DROP TABLE IF EXISTS `alunos_trabalhos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alunos_trabalhos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trabalho_id` int(10) unsigned NOT NULL,
  `aluno_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_aluno_trabalho` (`trabalho_id`,`aluno_id`),
  KEY `idx_at_aluno` (`aluno_id`,`trabalho_id`),
  CONSTRAINT `fk_at_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_at_trabalho` FOREIGN KEY (`trabalho_id`) REFERENCES `trabalhos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alunos_trabalhos`
--

LOCK TABLES `alunos_trabalhos` WRITE;
/*!40000 ALTER TABLE `alunos_trabalhos` DISABLE KEYS */;
INSERT INTO `alunos_trabalhos` VALUES (1,1,4),(2,1,5);
/*!40000 ALTER TABLE `alunos_trabalhos` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_aluno_trabalho_bi BEFORE INSERT ON alunos_trabalhos FOR EACH ROW

BEGIN

    DECLARE v_perfil VARCHAR(30);

    SELECT MAX(perfil) INTO v_perfil FROM usuarios WHERE id = NEW.aluno_id AND ativo = 1;

    IF v_perfil IS NULL OR v_perfil <> 'aluno' THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O vínculo exige um usuário ativo com perfil de aluno.';

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `auditoria`
--

DROP TABLE IF EXISTS `auditoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auditoria` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `acao` varchar(100) NOT NULL,
  `entidade` varchar(60) NOT NULL,
  `entidade_id` int(10) unsigned DEFAULT NULL,
  `detalhes` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_audit_user` (`usuario_id`),
  KEY `idx_audit_entity` (`entidade`,`entidade_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria`
--

LOCK TABLES `auditoria` WRITE;
/*!40000 ALTER TABLE `auditoria` DISABLE KEYS */;
/*!40000 ALTER TABLE `auditoria` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_auditoria_bu BEFORE UPDATE ON auditoria FOR EACH ROW

BEGIN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O registro de auditoria é imutável.';

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_auditoria_bd BEFORE DELETE ON auditoria FOR EACH ROW

BEGIN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O registro de auditoria não pode ser removido.';

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `banca_avaliadores`
--

DROP TABLE IF EXISTS `banca_avaliadores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banca_avaliadores` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `banca_id` int(10) unsigned NOT NULL,
  `avaliador_id` int(10) unsigned NOT NULL,
  `funcao` varchar(60) DEFAULT NULL,
  `status_resposta` enum('pendente','aceito','recusado') NOT NULL DEFAULT 'pendente',
  `respondido_em` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_banca_avaliador` (`banca_id`,`avaliador_id`),
  KEY `idx_ba_avaliador_status` (`avaliador_id`,`status_resposta`),
  CONSTRAINT `fk_ba_avaliador` FOREIGN KEY (`avaliador_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_ba_banca` FOREIGN KEY (`banca_id`) REFERENCES `bancas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banca_avaliadores`
--

LOCK TABLES `banca_avaliadores` WRITE;
/*!40000 ALTER TABLE `banca_avaliadores` DISABLE KEYS */;
INSERT INTO `banca_avaliadores` VALUES (1,1,3,'Avaliadora','aceito','2026-10-20 15:06:04');
/*!40000 ALTER TABLE `banca_avaliadores` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_banca_avaliador_bi BEFORE INSERT ON banca_avaliadores FOR EACH ROW

BEGIN

    DECLARE v_perfil VARCHAR(30);

    SELECT MAX(perfil) INTO v_perfil FROM usuarios WHERE id = NEW.avaliador_id AND ativo = 1;

    IF v_perfil IS NULL OR v_perfil <> 'avaliador' THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A banca exige usuário ativo com perfil de avaliador.';

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `bancas`
--

DROP TABLE IF EXISTS `bancas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bancas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trabalho_id` int(10) unsigned NOT NULL,
  `data_banca` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `modalidade` enum('presencial','online','hibrida') NOT NULL,
  `local` varchar(255) DEFAULT NULL,
  `link_online` varchar(500) DEFAULT NULL,
  `status` enum('AGUARDANDO_CONFIRMACOES','CONFIRMADA','REALIZADA','AGUARDANDO_DOCUMENTOS','AGUARDANDO_CORRECOES','AGUARDANDO_VERSAO_FINAL','AGUARDANDO_APROVACAO','AJUSTES_SOLICITADOS','CONCLUIDA') NOT NULL DEFAULT 'AGUARDANDO_CONFIRMACOES',
  `realizada_em` datetime DEFAULT NULL,
  `criado_por` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_banca_criador` (`criado_por`),
  KEY `idx_banca_data_status` (`data_banca`,`status`),
  KEY `idx_banca_trabalho_data` (`trabalho_id`,`data_banca`),
  CONSTRAINT `fk_banca_criador` FOREIGN KEY (`criado_por`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_banca_trabalho` FOREIGN KEY (`trabalho_id`) REFERENCES `trabalhos` (`id`),
  CONSTRAINT `chk_banca_horario` CHECK (`hora_fim` > `hora_inicio`),
  CONSTRAINT `chk_banca_local` CHECK (`modalidade` not in ('presencial','hibrida') or nullif(trim(`local`),'') is not null),
  CONSTRAINT `chk_banca_link` CHECK (`modalidade` not in ('online','hibrida') or nullif(trim(`link_online`),'') is not null)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bancas`
--

LOCK TABLES `bancas` WRITE;
/*!40000 ALTER TABLE `bancas` DISABLE KEYS */;
INSERT INTO `bancas` VALUES (1,1,CURDATE() + INTERVAL 10 DAY,'19:00:00','20:00:00','hibrida','Sala 12','https://meet.google.com/exemplo','CONFIRMADA',NULL,2,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP);
/*!40000 ALTER TABLE `bancas` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_banca_bi BEFORE INSERT ON bancas FOR EACH ROW

BEGIN

    DECLARE v_perfil VARCHAR(30);

    SELECT MAX(perfil) INTO v_perfil FROM usuarios WHERE id = NEW.criado_por AND ativo = 1;

    IF v_perfil IS NULL OR v_perfil NOT IN ('orientador','administrador') THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A banca deve ser criada por orientador ou administrador ativo.';

    END IF;

    IF NEW.hora_fim <= NEW.hora_inicio THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O horário final deve ser posterior ao horário inicial.';

    END IF;

    IF NEW.modalidade IN ('presencial','hibrida') AND NULLIF(TRIM(NEW.local),'') IS NULL THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Informe local ou sala para banca presencial/híbrida.';

    END IF;

    IF NEW.modalidade IN ('online','hibrida') AND NULLIF(TRIM(NEW.link_online),'') IS NULL THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Informe link para banca online/híbrida.';

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_banca_bu BEFORE UPDATE ON bancas FOR EACH ROW

BEGIN

    IF NEW.hora_fim <= NEW.hora_inicio THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O horário final deve ser posterior ao horário inicial.';

    END IF;

    IF NEW.modalidade IN ('presencial','hibrida') AND NULLIF(TRIM(NEW.local),'') IS NULL THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Informe local ou sala para banca presencial/híbrida.';

    END IF;

    IF NEW.modalidade IN ('online','hibrida') AND NULLIF(TRIM(NEW.link_online),'') IS NULL THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Informe link para banca online/híbrida.';

    END IF;

    IF NEW.status <> OLD.status AND NOT (

        (OLD.status = 'AGUARDANDO_CONFIRMACOES' AND NEW.status = 'CONFIRMADA') OR

        (OLD.status = 'CONFIRMADA' AND NEW.status = 'REALIZADA') OR

        (OLD.status = 'REALIZADA' AND NEW.status IN ('AGUARDANDO_DOCUMENTOS','AGUARDANDO_CORRECOES')) OR

        (OLD.status = 'AGUARDANDO_DOCUMENTOS' AND NEW.status IN ('AGUARDANDO_CORRECOES','AGUARDANDO_VERSAO_FINAL')) OR

        (OLD.status = 'AGUARDANDO_CORRECOES' AND NEW.status = 'AGUARDANDO_VERSAO_FINAL') OR

        (OLD.status = 'AGUARDANDO_VERSAO_FINAL' AND NEW.status = 'AGUARDANDO_APROVACAO') OR

        (OLD.status = 'AJUSTES_SOLICITADOS' AND NEW.status = 'AGUARDANDO_APROVACAO') OR

        (OLD.status = 'AGUARDANDO_APROVACAO' AND NEW.status IN ('AJUSTES_SOLICITADOS','CONCLUIDA'))

    ) THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transição de estado da banca não permitida.';

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_banca_au AFTER UPDATE ON bancas FOR EACH ROW

BEGIN

    IF NEW.status <> OLD.status THEN

        INSERT INTO auditoria (usuario_id, acao, entidade, entidade_id, detalhes)

        VALUES (@sig_usuario_id, 'mudanca_status', 'banca', NEW.id,

                JSON_OBJECT('anterior', OLD.status, 'novo', NEW.status));

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `convites`
--

DROP TABLE IF EXISTS `convites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `convites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `banca_avaliador_id` int(10) unsigned NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expira_em` datetime DEFAULT NULL,
  `status` enum('pendente','aceito','recusado','expirado','substituido') NOT NULL DEFAULT 'pendente',
  `enviado_em` datetime DEFAULT NULL,
  `respondido_em` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `fk_convite_ba` (`banca_avaliador_id`),
  KEY `idx_convite_status` (`status`),
  CONSTRAINT `fk_convite_ba` FOREIGN KEY (`banca_avaliador_id`) REFERENCES `banca_avaliadores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `convites`
--

LOCK TABLES `convites` WRITE;
/*!40000 ALTER TABLE `convites` DISABLE KEYS */;
/*!40000 ALTER TABLE `convites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correcoes`
--

DROP TABLE IF EXISTS `correcoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correcoes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trabalho_id` int(10) unsigned NOT NULL,
  `banca_id` int(10) unsigned DEFAULT NULL,
  `descricao` text NOT NULL,
  `data_liberacao` date NOT NULL,
  `prazo_final` date NOT NULL,
  `status` enum('aberta','entregue','concluida') NOT NULL DEFAULT 'aberta',
  `registrado_por` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_cor_trabalho` (`trabalho_id`),
  KEY `fk_cor_banca` (`banca_id`),
  KEY `fk_cor_user` (`registrado_por`),
  KEY `idx_cor_prazo` (`prazo_final`,`status`),
  CONSTRAINT `fk_cor_banca` FOREIGN KEY (`banca_id`) REFERENCES `bancas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_cor_trabalho` FOREIGN KEY (`trabalho_id`) REFERENCES `trabalhos` (`id`),
  CONSTRAINT `fk_cor_user` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `chk_cor_prazo` CHECK (`prazo_final` = `data_liberacao` + interval 15 day),
  CONSTRAINT `chk_cor_descricao` CHECK (char_length(trim(`descricao`)) > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correcoes`
--

LOCK TABLES `correcoes` WRITE;
/*!40000 ALTER TABLE `correcoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `correcoes` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_correcao_bi BEFORE INSERT ON correcoes FOR EACH ROW

BEGIN

    IF NEW.prazo_final <> DATE_ADD(NEW.data_liberacao, INTERVAL 15 DAY) THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O prazo inicial deve ser de 15 dias corridos após a data-base.';

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `cursos`
--

DROP TABLE IF EXISTS `cursos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cursos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `sigla` varchar(20) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sigla` (`sigla`),
  CONSTRAINT `chk_curso_nome` CHECK (char_length(trim(`nome`)) >= 2),
  CONSTRAINT `chk_curso_sigla` CHECK (char_length(trim(`sigla`)) between 2 and 20)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cursos`
--

LOCK TABLES `cursos` WRITE;
/*!40000 ALTER TABLE `cursos` DISABLE KEYS */;
INSERT INTO `cursos` VALUES (1,'Análise e Desenvolvimento de Sistemas','ADS',1,'2026-10-20 18:06:04'),(2,'Gestão Empresarial','GE',1,'2026-10-20 18:06:04');
/*!40000 ALTER TABLE `cursos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documentos`
--

DROP TABLE IF EXISTS `documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documentos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trabalho_id` int(10) unsigned NOT NULL,
  `banca_id` int(10) unsigned DEFAULT NULL,
  `tipo` enum('ata','autorizacao_ric','trabalho','versao_final','outro') NOT NULL,
  `nome_original` varchar(255) NOT NULL,
  `nome_armazenado` varchar(100) NOT NULL,
  `caminho_armazenado` varchar(255) NOT NULL,
  `mime_type` varchar(120) NOT NULL,
  `tamanho_bytes` int(10) unsigned NOT NULL,
  `status_validacao` enum('pendente','aprovado','correcao') NOT NULL DEFAULT 'pendente',
  `enviado_por` int(10) unsigned NOT NULL,
  `enviado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `validado_por` int(10) unsigned DEFAULT NULL,
  `validado_em` datetime DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome_armazenado` (`nome_armazenado`),
  KEY `fk_doc_banca` (`banca_id`),
  KEY `fk_doc_enviado` (`enviado_por`),
  KEY `fk_doc_validado` (`validado_por`),
  KEY `idx_doc_trabalho_tipo` (`trabalho_id`,`tipo`),
  CONSTRAINT `fk_doc_banca` FOREIGN KEY (`banca_id`) REFERENCES `bancas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_doc_enviado` FOREIGN KEY (`enviado_por`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_doc_trabalho` FOREIGN KEY (`trabalho_id`) REFERENCES `trabalhos` (`id`),
  CONSTRAINT `fk_doc_validado` FOREIGN KEY (`validado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_doc_tamanho` CHECK (`tamanho_bytes` > 0),
  CONSTRAINT `chk_doc_nome` CHECK (char_length(trim(`nome_original`)) > 0)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documentos`
--

LOCK TABLES `documentos` WRITE;
/*!40000 ALTER TABLE `documentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `documentos` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_documento_bi BEFORE INSERT ON documentos FOR EACH ROW

BEGIN

    DECLARE v_trabalho_id INT UNSIGNED;

    IF NEW.banca_id IS NOT NULL THEN

    SELECT MAX(trabalho_id) INTO v_trabalho_id FROM bancas WHERE id = NEW.banca_id;

        IF v_trabalho_id IS NULL OR v_trabalho_id <> NEW.trabalho_id THEN

            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O documento e a banca devem pertencer ao mesmo trabalho.';

        END IF;

    END IF;

    IF NEW.tamanho_bytes = 0 OR CHAR_LENGTH(TRIM(NEW.nome_original)) = 0 THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nome e tamanho do documento são obrigatórios.';

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_documento_bu BEFORE UPDATE ON documentos FOR EACH ROW

BEGIN

    DECLARE v_perfil VARCHAR(30);

    IF NEW.status_validacao <> OLD.status_validacao AND NOT (

        (OLD.status_validacao = 'pendente' AND NEW.status_validacao IN ('aprovado','correcao')) OR

        (OLD.status_validacao = 'correcao' AND NEW.status_validacao = 'aprovado')

    ) THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transição de validação do documento não permitida.';

    END IF;

    IF NEW.status_validacao <> 'pendente' THEN

        SELECT MAX(perfil) INTO v_perfil FROM usuarios WHERE id = NEW.validado_por AND ativo = 1;

        IF v_perfil IS NULL OR v_perfil NOT IN ('orientador','administrador') OR NEW.validado_em IS NULL THEN

            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A validação exige orientador ativo e data de validação.';

        END IF;

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_documento_au AFTER UPDATE ON documentos FOR EACH ROW

BEGIN

    IF NEW.status_validacao <> OLD.status_validacao THEN

        INSERT INTO auditoria (usuario_id, acao, entidade, entidade_id, detalhes)

        VALUES (@sig_usuario_id, 'validacao_documento', 'documento', NEW.id,

                JSON_OBJECT('anterior', OLD.status_validacao, 'novo', NEW.status_validacao));

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `notificacoes`
--

DROP TABLE IF EXISTS `notificacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificacoes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `tipo` varchar(40) NOT NULL,
  `titulo` varchar(160) NOT NULL,
  `mensagem` text NOT NULL,
  `link_interno` varchar(255) DEFAULT NULL,
  `lida_em` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_not_user_lida` (`usuario_id`,`lida_em`),
  CONSTRAINT `fk_not_user` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificacoes`
--

LOCK TABLES `notificacoes` WRITE;
/*!40000 ALTER TABLE `notificacoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `notificacoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trabalhos`
--

DROP TABLE IF EXISTS `trabalhos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trabalhos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `resumo` text DEFAULT NULL,
  `curso_id` int(10) unsigned NOT NULL,
  `orientador_id` int(10) unsigned NOT NULL,
  `ano_letivo` smallint(6) NOT NULL,
  `semestre_letivo` tinyint(4) NOT NULL,
  `status` enum('RASCUNHO','AGUARDANDO_CONFIRMACOES','CONFIRMADA','REALIZADA','AGUARDANDO_DOCUMENTOS','AGUARDANDO_CORRECOES','AGUARDANDO_VERSAO_FINAL','AGUARDANDO_APROVACAO','AJUSTES_SOLICITADOS','CONCLUIDA') NOT NULL DEFAULT 'RASCUNHO',
  `publico` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_trabalho_filtros` (`curso_id`,`ano_letivo`,`semestre_letivo`,`status`),
  KEY `idx_trabalho_orientador_status` (`orientador_id`,`status`),
  CONSTRAINT `fk_trabalho_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`),
  CONSTRAINT `fk_trabalho_orientador` FOREIGN KEY (`orientador_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `chk_trabalho_titulo` CHECK (char_length(trim(`titulo`)) >= 5),
  CONSTRAINT `chk_trabalho_ano` CHECK (`ano_letivo` between 2000 and 2200),
  CONSTRAINT `chk_trabalho_semestre` CHECK (`semestre_letivo` in (1,2))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trabalhos`
--

LOCK TABLES `trabalhos` WRITE;
/*!40000 ALTER TABLE `trabalhos` DISABLE KEYS */;
INSERT INTO `trabalhos` VALUES (1,'Plataforma de Gestão de Bancas','Sistema para organizar etapas de TCC.',1,2,2026,2,'CONFIRMADA',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP);
/*!40000 ALTER TABLE `trabalhos` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_trabalho_bi BEFORE INSERT ON trabalhos FOR EACH ROW

BEGIN

    DECLARE v_perfil VARCHAR(30);

    SELECT MAX(perfil) INTO v_perfil FROM usuarios WHERE id = NEW.orientador_id AND ativo = 1;

    IF v_perfil IS NULL OR v_perfil NOT IN ('orientador','administrador') THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O responsável pelo trabalho precisa ser orientador ativo.';

    END IF;

    IF CHAR_LENGTH(TRIM(NEW.titulo)) < 5 OR NEW.ano_letivo NOT BETWEEN 2000 AND 2200 OR NEW.semestre_letivo NOT IN (1,2) THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Título ou período letivo inválido.';

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_trabalho_bu BEFORE UPDATE ON trabalhos FOR EACH ROW

BEGIN

    DECLARE v_perfil VARCHAR(30);

    SELECT MAX(perfil) INTO v_perfil FROM usuarios WHERE id = NEW.orientador_id AND ativo = 1;

    IF v_perfil IS NULL OR v_perfil NOT IN ('orientador','administrador') THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O responsável pelo trabalho precisa ser orientador ativo.';

    END IF;

    IF CHAR_LENGTH(TRIM(NEW.titulo)) < 5 OR NEW.ano_letivo NOT BETWEEN 2000 AND 2200 OR NEW.semestre_letivo NOT IN (1,2) THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Título ou período letivo inválido.';

    END IF;

    IF NEW.status <> OLD.status AND NOT (

        (OLD.status = 'RASCUNHO' AND NEW.status = 'AGUARDANDO_CONFIRMACOES') OR

        (OLD.status = 'AGUARDANDO_CONFIRMACOES' AND NEW.status = 'CONFIRMADA') OR

        (OLD.status = 'CONFIRMADA' AND NEW.status IN ('REALIZADA','AGUARDANDO_DOCUMENTOS')) OR

        (OLD.status = 'REALIZADA' AND NEW.status = 'AGUARDANDO_DOCUMENTOS') OR

        (OLD.status = 'AGUARDANDO_DOCUMENTOS' AND NEW.status IN ('AGUARDANDO_CORRECOES','AGUARDANDO_VERSAO_FINAL')) OR

        (OLD.status = 'AGUARDANDO_CORRECOES' AND NEW.status = 'AGUARDANDO_VERSAO_FINAL') OR

        (OLD.status = 'AGUARDANDO_VERSAO_FINAL' AND NEW.status = 'AGUARDANDO_APROVACAO') OR

        (OLD.status = 'AJUSTES_SOLICITADOS' AND NEW.status = 'AGUARDANDO_APROVACAO') OR

        (OLD.status = 'AGUARDANDO_APROVACAO' AND NEW.status IN ('AJUSTES_SOLICITADOS','CONCLUIDA'))

    ) THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transição de estado do trabalho não permitida.';

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_trabalho_au AFTER UPDATE ON trabalhos FOR EACH ROW

BEGIN

    IF NEW.status <> OLD.status THEN

        INSERT INTO auditoria (usuario_id, acao, entidade, entidade_id, detalhes)

        VALUES (@sig_usuario_id, 'mudanca_status', 'trabalho', NEW.id,

                JSON_OBJECT('anterior', OLD.status, 'novo', NEW.status));

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(160) NOT NULL,
  `email` varchar(190) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `perfil` enum('aluno','orientador','avaliador','administrador') NOT NULL,
  `matricula` varchar(50) DEFAULT NULL,
  `curso_id` int(10) unsigned DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `matricula` (`matricula`),
  KEY `fk_usuario_curso` (`curso_id`),
  KEY `idx_usuario_perfil` (`perfil`),
  CONSTRAINT `fk_usuario_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_usuario_nome` CHECK (char_length(trim(`nome`)) >= 2),
  CONSTRAINT `chk_usuario_email` CHECK (`email` like '%_@_%._%')
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Ana Coordenadora','coord@fatec.sp.gov.br','$2y$10$13mYwMpPdr7EsiZmTnhTgOPT2ryRc72Cyf7830YKmYgyxpTTW6ktW','administrador','ADM001',1,1,'2026-10-20 18:06:04','2026-10-20 18:06:04'),(2,'Prof. Ricardo Mendes','ricardo@fatec.sp.gov.br','$2y$10$13mYwMpPdr7EsiZmTnhTgOPT2ryRc72Cyf7830YKmYgyxpTTW6ktW','orientador','DOC001',1,1,'2026-10-20 18:06:04','2026-10-20 18:06:04'),(3,'Prof. Marina Souza','marina@fatec.sp.gov.br','$2y$10$13mYwMpPdr7EsiZmTnhTgOPT2ryRc72Cyf7830YKmYgyxpTTW6ktW','avaliador','DOC002',1,1,'2026-10-20 18:06:04','2026-10-20 18:06:04'),(4,'Lucas Almeida','lucas@fatec.sp.gov.br','$2y$10$13mYwMpPdr7EsiZmTnhTgOPT2ryRc72Cyf7830YKmYgyxpTTW6ktW','aluno','2024001',1,1,'2026-10-20 18:06:04','2026-10-20 18:06:04'),(5,'Beatriz Costa','beatriz@fatec.sp.gov.br','$2y$10$13mYwMpPdr7EsiZmTnhTgOPT2ryRc72Cyf7830YKmYgyxpTTW6ktW','aluno','2024002',1,1,'2026-10-20 18:06:04','2026-10-20 18:06:04');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_usuario_bu BEFORE UPDATE ON usuarios FOR EACH ROW

BEGIN

    IF NEW.perfil <> OLD.perfil AND EXISTS (

        SELECT 1 FROM trabalhos WHERE orientador_id = OLD.id

    ) AND NEW.perfil NOT IN ('orientador','administrador') THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O perfil do usuário não pode mudar enquanto ele orienta trabalhos.';

    END IF;

    IF NEW.perfil <> OLD.perfil AND EXISTS (

        SELECT 1 FROM alunos_trabalhos WHERE aluno_id = OLD.id

    ) AND NEW.perfil <> 'aluno' THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O perfil do usuário não pode mudar enquanto ele está vinculado como aluno.';

    END IF;

    IF NEW.perfil <> OLD.perfil AND EXISTS (

        SELECT 1 FROM banca_avaliadores WHERE avaliador_id = OLD.id

    ) AND NEW.perfil <> 'avaliador' THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O perfil do usuário não pode mudar enquanto ele está vinculado como avaliador.';

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `versoes`
--

DROP TABLE IF EXISTS `versoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `versoes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trabalho_id` int(10) unsigned NOT NULL,
  `documento_id` int(10) unsigned NOT NULL,
  `numero_versao` smallint(5) unsigned NOT NULL,
  `enviado_por` int(10) unsigned NOT NULL,
  `enviado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('enviada','em_analise','aprovada','ajustes_solicitados') NOT NULL DEFAULT 'enviada',
  `avaliado_por` int(10) unsigned DEFAULT NULL,
  `avaliado_em` datetime DEFAULT NULL,
  `observacao_orientador` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_versao_trabalho_numero` (`trabalho_id`,`numero_versao`),
  KEY `fk_ver_doc` (`documento_id`),
  KEY `fk_ver_envio` (`enviado_por`),
  KEY `fk_ver_avaliador` (`avaliado_por`),
  CONSTRAINT `fk_ver_avaliador` FOREIGN KEY (`avaliado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ver_doc` FOREIGN KEY (`documento_id`) REFERENCES `documentos` (`id`),
  CONSTRAINT `fk_ver_envio` FOREIGN KEY (`enviado_por`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_ver_trabalho` FOREIGN KEY (`trabalho_id`) REFERENCES `trabalhos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `versoes`
--

LOCK TABLES `versoes` WRITE;
/*!40000 ALTER TABLE `versoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `versoes` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_versao_bi BEFORE INSERT ON versoes FOR EACH ROW

BEGIN

    DECLARE v_trabalho_id INT UNSIGNED;

    DECLARE v_tipo VARCHAR(30);

    SELECT MAX(trabalho_id), MAX(tipo) INTO v_trabalho_id, v_tipo FROM documentos WHERE id = NEW.documento_id;

    IF v_trabalho_id IS NULL OR v_trabalho_id <> NEW.trabalho_id OR v_tipo <> 'versao_final' THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A versão deve apontar para documento final do mesmo trabalho.';

    END IF;

    IF NEW.numero_versao = 0 THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O número da versão deve começar em 1.';

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_versao_bu BEFORE UPDATE ON versoes FOR EACH ROW

BEGIN

    DECLARE v_perfil VARCHAR(30);

    IF NEW.status <> OLD.status AND NOT (

        (OLD.status = 'enviada' AND NEW.status = 'em_analise') OR

        (OLD.status = 'em_analise' AND NEW.status IN ('aprovada','ajustes_solicitados'))

    ) THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transição de estado da versão não permitida.';

    END IF;

    IF NEW.status IN ('aprovada','ajustes_solicitados') THEN

        SELECT MAX(perfil) INTO v_perfil FROM usuarios WHERE id = NEW.avaliado_por AND ativo = 1;

        IF v_perfil IS NULL OR v_perfil NOT IN ('orientador','administrador') OR NEW.avaliado_em IS NULL THEN

            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A avaliação exige orientador ativo e data de avaliação.';

        END IF;

        IF NEW.status = 'ajustes_solicitados' AND CHAR_LENGTH(TRIM(COALESCE(NEW.observacao_orientador,''))) = 0 THEN

            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Registre o motivo dos ajustes solicitados.';

        END IF;

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_versao_au AFTER UPDATE ON versoes FOR EACH ROW

BEGIN

    IF NEW.status <> OLD.status THEN

        INSERT INTO auditoria (usuario_id, acao, entidade, entidade_id, detalhes)

        VALUES (@sig_usuario_id, 'mudanca_status', 'versao', NEW.id,

                JSON_OBJECT('anterior', OLD.status, 'novo', NEW.status));

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Dumping routines for database 'sig_banca'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-20 16:43:18
