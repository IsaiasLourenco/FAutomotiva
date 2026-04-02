CREATE DATABASE odontoClinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cargo INT(5) DEFAULT NULL,
    telefone VARCHAR(15) DEFAULT NULL,
    cep VARCHAR(9) DEFAULT NULL,
    rua VARCHAR(100) DEFAULT NULL,
    numero VARCHAR(10) DEFAULT NULL,
    bairro VARCHAR(100) DEFAULT NULL,
    cidade VARCHAR(100) DEFAULT NULL,
    estado VARCHAR(2) DEFAULT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE cargos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

ALTER TABLE
    usuarios
ADD
    CONSTRAINT fk_cargo FOREIGN KEY (cargo) REFERENCES cargos(id) ON DELETE
SET
    NULL ON UPDATE CASCADE;

    ALTER TABLE usuarios 
ADD COLUMN data_admissao DATE DEFAULT NULL COMMENT 'Data de admissão',
ADD COLUMN cargo_funcional VARCHAR(50) DEFAULT NULL COMMENT 'Cargo na clínica',
ADD COLUMN tipo_contrato ENUM('CLT','PJ','Estagiário','Autônomo') DEFAULT 'CLT',
ADD COLUMN observacoes_func TEXT DEFAULT NULL COMMENT 'Observações de RH';

CREATE TABLE configuracoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_sistema VARCHAR(100),
    email_sistema VARCHAR(100),
    cnpj_sistema VARCHAR(20),
    telefone_sistema VARCHAR(20),
    telefone_fixo VARCHAR(20),
    cep_sistema VARCHAR(10),
    rua_sistema VARCHAR(100),
    numero_sistema VARCHAR(10),
    bairro_sistema VARCHAR(100),
    cidade_sistema VARCHAR(100),
    estado_sistema VARCHAR(2),
    instagram_sistema VARCHAR(200),
    tipo_relatorio VARCHAR(10),
    contatoZap VARCHAR(3),
    desenvolvedor VARCHAR(100),
    site_dev VARCHAR(200),
    url_sistema VARCHAR(200),
    chave_pix VARCHAR(100),
    tipo_chave VARCHAR(20),
    logotipo VARCHAR(100),
    icone VARCHAR(100),
    logo_rel VARCHAR(100)
);

CREATE TABLE grupo_acessos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_grupo VARCHAR(25)
);

CREATE TABLE acessos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    chave VARCHAR(50) NOT NULL,
    grupo INT NOT NULL
);

CREATE TABLE permissoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario INT NOT NULL,
    permissao INT NOT NULL
);

CREATE TABLE pacientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    telefone VARCHAR(15) DEFAULT NULL,
    cep VARCHAR(9) DEFAULT NULL,
    rua VARCHAR(100) DEFAULT NULL,
    numero VARCHAR(10) DEFAULT NULL,
    bairro VARCHAR(100) DEFAULT NULL,
    cidade VARCHAR(100) DEFAULT NULL,
    estado VARCHAR(2) DEFAULT NULL
);

CREATE TABLE receber (
    id INT PRIMARY KEY AUTO_INCREMENT,
    descricao VARCHAR(100) NOT NULL,
    paciente INT DEFAULT NULL,
    valor DECIMAL(10, 2) DEFAULT NULL,
    data_vencimento DATE DEFAULT NULL,
    data_pagamento DATE NOT NULL,
    data_lancamento DATE DEFAULT NULL,
    forma_pagamento INT DEFAULT NULL,
    frequencia INT DEFAULT NULL,
    obs VARCHAR(100) DEFAULT NULL,
    arquivo VARCHAR(100) DEFAULT NULL,
    referencia VARCHAR(30) DEFAULT NULL,
    id_referencia INT DEFAULT NULL,
    multa DECIMAL(10, 2) DEFAULT NULL,
    juros DECIMAL(10, 2) DEFAULT NULL,
    desconto DECIMAL(10, 2) DEFAULT NULL,
    subtotal DECIMAL(10, 2) DEFAULT NULL
);

CREATE TABLE forma_pagamento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    taxa INT DEFAULT NULL
);

CREATE TABLE frequencias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    frequencia VARCHAR(50) NOT NULL,
    dias INT DEFAULT NULL
);

ALTER TABLE
    receber
ADD
    COLUMN usuario_lanc INT NULL
AFTER
    obs,
ADD
    COLUMN usuario_pgto INT NULL
AFTER
    usuario_lanc;

ALTER TABLE
    receber
ADD
    COLUMN taxa DECIMAL(10, 2) DEFAULT 0.00
AFTER
    desconto;

CREATE TABLE arquivos_conta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_conta INT NOT NULL,
    nome_arquivo VARCHAR(255),
    tipo_arquivo VARCHAR(50), -- 'pdf', 'jpg', 'png', 'xlsx', 'docx'
    caminho_arquivo VARCHAR(255),
    data_upload DATETIME,
    usuario_upload INT,
    FOREIGN KEY (id_conta) REFERENCES receber(id)
);

CREATE TABLE `fornecedores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefone` varchar(15) DEFAULT NULL,
  `cnpj` varchar(18) NOT NULL,
  `cep` varchar(9) DEFAULT NULL,
  `rua` varchar(100) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `ativo` varchar(5) DEFAULT '1',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `fornecedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cnpj` (`cnpj`);

ALTER TABLE `fornecedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;