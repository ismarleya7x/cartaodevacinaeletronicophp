<?php

use Core\Conexao;

require_once('Conexao.php');

class CadastroUsuario
{
  private $header;
  private $value;
  private $con;
  public function cadastro()
  {
    //If que permite apenas requisições POST
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
      throw new Exception("Method not alowed");
    }

    //Usado para pegar um cabeçalho específico

    // foreach ($_SERVER as $key => $value) {
    //   if (substr($key, 0, 5) <> 'HTTP_') {
    //     continue;
    //   }
    //   $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
    //   if ($header == "Teste") {
    //     $this->header = $header;
    //     $this->value = $value;
    //   }
    // }

    //Pegar informações do body de uma requisição
    $body = json_decode(($stream = fopen('php://input', 'r')) !== false ? stream_get_contents($stream) : "{}");

    if (!$body)
      throw new Exception("Request Data not found");


    //Dados usuario
    $nome_completo = $body->{'nome_completo'};
    $cpf = $body->{'cpf'};
    $rg = $body->{'rg'};
    $telefone_fixo = $body->{'telefone_fixo'};
    $telefone_celular = $body->{'telefone_celular'};
    $email = $body->{'email'};
    $logradouro = $body->{'logradouro'};
    $numero = $body->{'numero'};
    $complemento = $body->{'complemento'};
    $bairro = $body->{'bairro'};
    $cep = $body->{'cep'};
    $cidade = $body->{'cidade'};
    $uf = $body->{'uf'};
    $data_nascimento = $body->{'data_nascimento'};
    $dependente_de = $body->{'dependente_de'};
    $tipo_usuario_id_tp_usuario = $body->{'id_tp_usuario'};
    $cadastrado_em = date('Y-m-d H:i:s');

    //Altura peso

    $altura = $body->{'altura'};
    $peso = $body->{'peso'};

    //Usuario e senha
    $usuario = $body->{'usuario'};
    $senha = $body->{'senha'};


    $this->con = new Conexao();
    $this->con = $this->con->getConexao();

    $sql = "INSERT INTO dados_usuario(nome_completo,cpf, rg, telefone_fixo,telefone_celular, email, logradouro, numero, complemento, bairro,cep, cidade, uf, data_nascimento, dependente_de, cadastrado_em, id_tp_usuario) VALUES (:nome_completo,:cpf, :rg, :telefone_fixo,:telefone_celular, :email, :logradouro, :numero, :complemento, :bairro, :cep, :cidade, :uf, :data_nascimento, :dependente_de, :cadastrado_em, :tipo_usuario_id_tp_usuario)";


    $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $sql = $this->con->prepare($sql);
    $sql->bindParam('nome_completo', $nome_completo, PDO::PARAM_STR);
    $sql->bindParam('cpf', $cpf, PDO::PARAM_STR);
    $sql->bindParam('rg', $rg, PDO::PARAM_STR);
    $sql->bindParam('telefone_fixo', $telefone_fixo, PDO::PARAM_STR);
    $sql->bindParam('telefone_celular', $telefone_celular, PDO::PARAM_STR);
    $sql->bindParam('email', $email, PDO::PARAM_STR);
    $sql->bindParam('logradouro', $logradouro, PDO::PARAM_STR);
    $sql->bindParam('numero', $numero, PDO::PARAM_STR);
    $sql->bindParam('complemento', $complemento, PDO::PARAM_STR);
    $sql->bindParam('bairro', $bairro, PDO::PARAM_STR);
    $sql->bindParam('cep', $cep, PDO::PARAM_STR);
    $sql->bindParam('cidade', $cidade, PDO::PARAM_STR);
    $sql->bindParam('uf', $uf, PDO::PARAM_STR);
    $sql->bindParam('data_nascimento', $data_nascimento, PDO::PARAM_STR);
    $sql->bindParam('cadastrado_em', $cadastrado_em, PDO::PARAM_STR);
    $sql->bindParam('dependente_de', $dependente_de);
    $sql->bindParam('tipo_usuario_id_tp_usuario', $tipo_usuario_id_tp_usuario, PDO::PARAM_STR);


    if (!$this->checaCpf($cpf))
      throw new Exception("CPF já cadastrado anteriormente!");

    if (!$this->checaUsuario($usuario))
      throw new Exception("Usuário já cadastrado anteriormente!");
    $this->con->beginTransaction();


    try{
      $sql->execute();
    }catch(Exception $e){
      $this->con->rollBack();
      return $e->getMessage();
    }

    $id = $this->con->lastInsertId();


    if (!empty($altura) and !empty($peso)) {
      if (!$this->inserePesoAltura($id, $peso, $altura)) {
        $this->con->rollBack();
        throw new Exception("Falha ao cadastrar usuário 2");
      }
    }

    if ($this->insereUsuarioSenha($id, $usuario, $senha)) {
      $this->con->commit();
      $retorno = array("mensagem" => "Cadastro efetuado com sucesso!", "id_usuario" => $id);
      return $retorno;
    } else {
      $this->con->rollBack();
      throw new Exception("Falha ao cadastrar usuário 3");
    }
  }

  private function inserePesoAltura($id, $peso, $altura)
  {
    $cadastrado_em = date('Y-m-d H:i:s');
    $sql = "INSERT INTO altura_peso(id_dados_usuario, altura, peso, criado_em) VALUES (:id, :altura, :peso, :criado_em)";
    $sql = $this->con->prepare($sql);
    $sql->bindParam('altura', $altura, PDO::PARAM_INT);
    $sql->bindParam('peso', $peso, PDO::PARAM_INT);
    $sql->bindParam('criado_em', $cadastrado_em, PDO::PARAM_STR);
    $sql->bindParam('id', $id, PDO::PARAM_INT);

    return $sql->execute();
  }

  private function insereUsuarioSenha($id, $usuario, $senha)
  {
    $cadastrado_em = date('Y-m-d H:i:s');

    $sql = "INSERT INTO usuario(usuario, senha, criado_em, id_dados_usuario) VALUES (:usuario, :senha, :criado_em, :dados_usuario_id_dados_usuario)";
    $sql = $this->con->prepare($sql);
    $sql->bindParam('usuario', $usuario, PDO::PARAM_STR);
    $sql->bindParam('senha', $senha, PDO::PARAM_STR);
    $sql->bindParam('criado_em', $cadastrado_em, PDO::PARAM_STR);
    $sql->bindParam('dados_usuario_id_dados_usuario', $id, PDO::PARAM_STR);

    return $sql->execute();
  }

  private function checaCpf($cpf)
  {
    $sql = "SELECT 1 FROM dados_usuario WHERE cpf = :cpf";
    $sql = $this->con->prepare($sql);
    $sql->bindParam('cpf', $cpf);
    $sql->execute();

    if ($sql->fetchColumn() > 0) {
      return false;
    } else {
      return true;
    }
  }

  private function checaUsuario($usuario)
  {
    $sql = "SELECT 1 FROM usuario WHERE usuario = :usuario";
    $sql = $this->con->prepare($sql);
    $sql->bindParam('usuario', $usuario);
    $sql->execute();

    if ($sql->fetchColumn() > 0) {
      return false;
    } else {
      return true;
    }
  }
}
