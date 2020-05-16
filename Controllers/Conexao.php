<?php

namespace Core;


use Exception;
use PDO;

class Conexao
{

  private static $host = '';
  private static $dbname = '';
  private static $user = '';
  private static $pass = '';
  private static $connect = null;

  private static function Conectar()
  {
    try {
      if (self::$connect == null) {
        self::$connect = new PDO("mysql:host=" . self::$host . ";dbname=" . self::$dbname, self::$user, self::$pass);
      }
    } catch (Exception $ex) {
      echo "Erro: " . $ex->getMessage();

      die();
    }

    return self::$connect;
  }

  public function getConexao()
  {
    return self::Conectar();
  }
}
