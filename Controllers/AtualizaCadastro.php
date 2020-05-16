<?php

use Core\Conexao;

require_once('Conexao.php');

class AtualizaCadastro
{
  private $header;
  private $value;
  private $con;
  public function atualiza()
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
    $id_usuario = $body->{'id_usuario'};
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
    $modificado_em = date('Y-m-d H:i:s');

    //Usuario e senha
    $senha = $body->{'senha'};


    $this->con = new Conexao();
    $this->con = $this->con->getConexao();

    $sql = "UPDATE dados_usuario SET nome_completo = :nome_completo,cpf = :cpf, rg = :rg, telefone_fixo = :telefone_fixo,telefone_celular = :telefone_celular, email =:email, logradouro = :logradouro, numero = :numero, complemento = :complemento, bairro = :bairro,cep = :cep, cidade = :cidade, uf = :uf, data_nascimento = :data_nascimento, modificado_em = :modificado_em WHERE id_dados_usuario = :id";

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
    $sql->bindParam('modificado_em', $modificado_em, PDO::PARAM_STR);
    $sql->bindParam('id', $id_usuario, PDO::PARAM_STR);

    $this->con->beginTransaction();

    if(!$sql->execute()){
      $this->con->rollBack();
      throw new Exception("Falha ao atualizar usuário");
    }
    
    if (!$this->checaUsuario($id_usuario, $senha)) {
      if ($this->atualizaUsuarioSenha($id_usuario, $senha)) {
        $this->con->commit();
        $retorno = array("mensagem" => "Cadastro atualizados com sucesso!", "id_usuario" => $id_usuario, "body" => $body);
        return $retorno;
      } else {
        $this->con->rollBack();
        throw new Exception("Falha ao atualizar usuário");
      }
    }

    $this->con->commit();
    $retorno = array("mensagem" => "Cadastro atualizado com sucesso!", "id_usuario" => $id_usuario,  "body" => $body);
    return $retorno;
  }


  private function atualizaUsuarioSenha($id, $senha)
  {
    $modificado_em = date('Y-m-d H:i:s');

    $sql = "UPDATE usuario SET senha = :senha, modificado_em = :modificado_em WHERE id_dados_usuario = :id_dados_usuario";
    $sql = $this->con->prepare($sql);
    $sql->bindParam('senha', $senha, PDO::PARAM_STR);
    $sql->bindParam('modificado_em', $modificado_em, PDO::PARAM_STR);
    $sql->bindParam('id_dados_usuario', $id, PDO::PARAM_STR);

    return $sql->execute();
  }


  private function checaUsuario($id, $senha)
  {
    $sql = "SELECT senha FROM usuario WHERE id_dados_usuario = :id";
    $sql = $this->con->prepare($sql);
    $sql->bindParam('id', $id);
    $sql->execute();

    $result = $sql->fetch(PDO::FETCH_ASSOC);

    if ($result['senha'] == $senha) {
      return true;
    } else {
      return false;
    }
  }
}
