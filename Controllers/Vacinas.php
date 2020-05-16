<?php

use Core\Conexao;


require_once('Conexao.php');

class Vacinas
{
  public function getAllVacinas()
  {
    if ($_SERVER['REQUEST_METHOD'] != 'GET') {
      throw new Exception("Method not alowed");
    }

    $con = new Conexao();
    $con = $con->getConexao();

    $sql = "SELECT * FROM vacinas";
    $sql = $con->prepare($sql);
    $sql->execute();

    $resultados = array();

    while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
      $nomeVacina = '{"id_vacina": "","nome_vacina": "", "dados":[{"id_vacinas_tomadas": "", "descricao": "", "tomar_em": null, "tomada_em": null}], "status": false}';

      $nomeVacina = json_decode($nomeVacina);
      $nomeVacina->nome_vacina = $row['nome_vacina'];
      $nomeVacina->id_vacina = $row['id_vacina'];
      $nomeVacina->dados[0]->id_vacinas_tomadas = $row['id_vacina'];
      $nomeVacina->dados[0]->descricao = $row['descricao'];


      $resultados[] = $nomeVacina;
    }

    if (!empty($resultados)) {
      return $resultados;
    } else {
      throw new Exception("Não há vacinas cadastradas!");
    }
  }

  public function getUserVacinas()
  {
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
      throw new Exception("Method not alowed");
    }

    $body = json_decode(($stream = fopen('php://input', 'r')) !== false ? stream_get_contents($stream) : "{}");

    if (!$body)
      throw new Exception("Request Data not found!!");

    $id_usuario = $body->{'id_usuario'};

    $con = new Conexao();
    $con = $con->getConexao();

    $sql = "SELECT b.id_vacina, b.nome_vacina, a.tomar_em, a.tomada_em, b.descricao, a.id_vacinas_tomadas FROM usuarios_vacinas a JOIN vacinas b ON a.vacinas_id_vacina = b.id_vacina WHERE dados_usuario_id_dados_usuario = :id_usuario;";
    $sql = $con->prepare($sql);
    $sql->bindParam('id_usuario', $id_usuario, PDO::PARAM_STR);
    $sql->execute();

    $resultados = array();
    while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
      $nomeVacina = '{"id_vacina": "","nome_vacina": "", "dados":[], "status":""}';
      $dadosVacina = '{"id_vacinas_tomadas": "", "descricao": "", "tomar_em":"", "tomada_em": ""}';
      $nomeVacina = json_decode($nomeVacina);
      $dadosVacina = json_decode($dadosVacina);

      $this->indice = 0;
      $this->achei = false;

      $nomeVacina->id_vacina = $row['id_vacina'];
      $nomeVacina->nome_vacina = $row['nome_vacina'];

      $dadosVacina->id_vacinas_tomadas = $row['id_vacinas_tomadas'];
      $dadosVacina->descricao = $row['nome_vacina'];
      $dadosVacina->tomar_em = $row['tomar_em'];
      $dadosVacina->tomada_em = $row['tomada_em'];

      $nomeVacina->dados[] = $dadosVacina;

      if (sizeOf($resultados) > 0) {
        foreach ($resultados as $vacina) {
          if ($vacina->nome_vacina == $row['nome_vacina']) {
            $this->achei = true;
            break;
          }
          $this->indice++;
        }
      }

      if (!$this->achei) {
        $resultados[] = $nomeVacina;
      } else {
        array_push($resultados[$this->indice]->dados, $dadosVacina);
      }
    }
    

    $resto = $this->filterVacinas($resultados);

    array_splice($resultados, sizeOf($resultados), 0, $resto);

    //return $resultados;
    foreach ($resultados as $vacina) {
      //return $vacina;
      foreach ($vacina->dados as $dados) {
        if ($dados->tomada_em == null) {
          $vacina->status = false;
        } else {
          $vacina->status = true;
        }
      }
    }

    if (!empty($resultados)) {
      return $resultados;
    } else {
      throw new Exception("Não há vacinas para esse usuário!");
    }
  }

  public function filterVacinas($filtro)
  {
    $id_vacina = array();
    foreach ($filtro as $vacina) {
      $id_vacina[] = $vacina->id_vacina;
    }
    $inQuery = implode(',', array_fill(0, count($id_vacina), '?'));
    
    $con = new Conexao();
    $con = $con->getConexao();

    $sql = "SELECT id_vacina, nome_vacina, descricao, null AS 'tomar_em', null AS 'tomada_em' FROM vacinas WHERE id_vacina NOT IN ( ". $inQuery ." )";
    $sql = $con->prepare($sql);
    foreach ($id_vacina as $k => $id)
      $sql->bindValue(($k+1), $id);
    $sql->execute();


    $resultados = array();

    while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
      $nomeVacina = '{"id_vacina": "","nome_vacina": "", "dados":[], "status":""}';
      $dadosVacina = '{"descricao": "", "tomar_em":"", "tomada_em": ""}';
      $nomeVacina = json_decode($nomeVacina);
      $dadosVacina = json_decode($dadosVacina);
      $nomeVacina->id_vacina = $row['id_vacina'];
      $nomeVacina->nome_vacina = $row['nome_vacina'];

      $dadosVacina->descricao = $row['nome_vacina'];
      $dadosVacina->tomar_em = $row['tomar_em'];
      $dadosVacina->tomada_em = $row['tomada_em'];

      $nomeVacina->dados[] = $dadosVacina;
      $resultados[] = $nomeVacina;
    }

    return $resultados;
  }

  public function validarVacina()
  {
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
      throw new Exception("Method not alowed");
    }

    $body = json_decode(($stream = fopen('php://input', 'r')) !== false ? stream_get_contents($stream) : "{}");

    if (!$body)
      throw new Exception("Request Data not found!!");

    $id_usuario = $body->{'id_usuario'};
    $id_vacina = $body->{'id_vacina'};
    $tomada_em = date('Y-m-d H:i:s');

    $con = new Conexao();
    $con = $con->getConexao();

    $getId = "SELECT id_vacinas_tomadas FROM usuarios_vacinas WHERE dados_usuario_id_dados_usuario = :id_usuario AND vacinas_id_vacina = :id_vacina AND tomada_em is null LIMIT 1";

    $getId = $con->prepare($getId);
    $getId->bindParam('id_usuario', $id_usuario, PDO::PARAM_STR);
    $getId->bindParam('id_vacina', $id_vacina, PDO::PARAM_STR);

    if ($getId->execute()) {
      $row = $getId->fetch(PDO::FETCH_ASSOC);
      if ($row) {

        $sql = "UPDATE usuarios_vacinas SET tomada_em = :tomada_em WHERE id_vacinas_tomadas = :id_vacina_tomar";
        $sql = $con->prepare($sql);
        $sql->bindParam('tomada_em', $tomada_em, PDO::PARAM_STR);
        $sql->bindParam('id_vacina_tomar', $row['id_vacinas_tomadas'], PDO::PARAM_STR);

        if ($sql->execute()) {
          $retorno = array("mensagem" => "Vacina validada com sucesso!");

          return $retorno;
        } else {
          throw new Exception("Não conseguiu atualizar a vacina!");
        }
      } else {
        throw new Exception("Não conseguiu localizar a vacina!");
      }
    } else {
      throw new Exception("Não recuperou vacina!");
    }
  }

  public function insertNewVacina()
  {
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
      throw new Exception("Method not alowed");
    }

    $body = json_decode(($stream = fopen('php://input', 'r')) !== false ? stream_get_contents($stream) : "{}");

    if (!$body)
      throw new Exception("Request Data not found!!");

    $id_usuario = $body->{'id_usuario'};
    $id_vacina = $body->{'id_vacina'};
    $tomada_em = date('Y-m-d H:i:s');
    $tomar_em = date('Y-m-d H:i:s');

    $con = new Conexao();
    $con = $con->getConexao();


    $getVacina = "SELECT * FROM vacinas WHERE id_vacina = :id_vacina";
    $getVacina = $con->prepare($getVacina);
    $getVacina->bindParam('id_vacina', $id_vacina, PDO::PARAM_STR);

    $getVacina->execute();
    $row = $getVacina->fetch(PDO::FETCH_ASSOC);

    $doses = $row['num_doses'];

    for ($i = 1; $i <= $doses; $i++) {
      $this->sql = "";
      if ($i == 1) {
        $this->sql = "INSERT INTO usuarios_vacinas (`dados_usuario_id_dados_usuario`, `vacinas_id_vacina`, `tomar_em`, `tomada_em`) VALUES (:id_usuario, :id_vacina, :tomar_em, :tomada_em)";
      } else {
        $this->sql = "INSERT INTO usuarios_vacinas (`dados_usuario_id_dados_usuario`, `vacinas_id_vacina`, `tomar_em`, `tomada_em`)  VALUES (:id_usuario, :id_vacina, DATE_ADD(:tomar_em, INTERVAL :periodo DAY), null)";
      }

      $this->sql = $con->prepare($this->sql);
      if ($i == 1) {
        $this->sql->bindParam('id_usuario', $id_usuario, PDO::PARAM_STR);
        $this->sql->bindParam('tomar_em', $tomar_em, PDO::PARAM_STR);
        $this->sql->bindParam('tomada_em', $tomada_em, PDO::PARAM_STR);
        $this->sql->bindParam('id_vacina', $id_vacina, PDO::PARAM_STR);        
      }else{
        $periodo = $row['periodo'] * ($i -1);
        $this->sql->bindParam('id_usuario', $id_usuario, PDO::PARAM_STR);
        $this->sql->bindParam('id_vacina', $id_vacina, PDO::PARAM_STR);
        $this->sql->bindParam('tomar_em', $tomar_em, PDO::PARAM_STR);
        $this->sql->bindParam('periodo', $periodo, PDO::PARAM_STR);
      }
      try{
        $this->sql->execute();
      }catch(PDOException $e){
        throw new Exception($e->getMessage());
      }
    }
    $retorno = array("mensagem" => "Vacina inserida com sucesso!");
    return $retorno;
  }
}
