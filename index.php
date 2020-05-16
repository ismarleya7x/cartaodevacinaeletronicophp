<?php

header('Content-Type= application/json; charset= utf-8');

require_once('./Controllers/Usuario.php');
require_once('./Controllers/Vacinas.php');
require_once('./Controllers/CadastroUsuario.php');
require_once('./Controllers/AtualizaCadastro.php');

class Rest
{
  public static function open($req)
  {
    $url = explode('/', $req['url']);

    $classe = ucfirst($url[0]);
    array_shift($url);

    $metodo = $url[0];
    array_shift($url);

    $parametros = array();
    $parametros = $url;
    //return $classe . " - " . $metodo;
    try {
      if (class_exists($classe)) {
        if (method_exists($classe, $metodo)) {
          $retorno = call_user_func_array(array(new $classe, $metodo), $parametros);
          return json_encode(array('status_code' => 200, 'data' => $retorno));
        } else {
          return json_encode(array('status_code' => 404, 'status_message' => 'Método inexistente'));
        }
      } else {
        return json_encode(array('status_code' => 404, 'status_message' => 'Classe inexistente'));
      }
    } catch (Exception $e) {
      return json_encode(array('status_code' => 404, 'status_message' => $e->getMessage()));      
    }
  }
}

if (isset($_REQUEST)) {
  echo Rest::open($_REQUEST);
}
