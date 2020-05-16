<?php

use Core\Conexao;

require_once('Conexao.php');

class Usuario
{
  private $header;
  private $value;
  public function Login()
  {
    //If que permite apenas requisições POST
    if($_SERVER['REQUEST_METHOD'] != 'POST'){
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

    if(!$body)
      throw new Exception("Request Data not found");

    $usuario = $body->{'usuario'};
    $senha = $body->{'senha'};


    $con = new Conexao();
    $con = $con->getConexao();

    $sql = "SELECT dados_usuario.*, usuario.usuario, usuario.senha FROM dados_usuario join usuario ON dados_usuario.id_dados_usuario = usuario.id_dados_usuario WHERE usuario.usuario = :usuario AND usuario.senha = :senha";
    $sql = $con->prepare($sql);
    $sql->bindParam('usuario', $usuario, PDO::PARAM_STR);
    $sql->bindParam('senha', $senha, PDO::PARAM_STR);
    $sql->execute();

    $resultados = array();

    while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
      $resultados[] = $row;
    }

    if (!empty($resultados)) {
      return $resultados;
    } else {
      throw new Exception("Usuario nao cadastrado");
    }
  }
}
