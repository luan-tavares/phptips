<?php

namespace Core\Database;

use PDO;
use PDOException;

abstract class Connect
{
    private const OPTIONS = [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
    ];

    private static $instance;

    /**
     * @return PDO
     */
    public static function instance(): PDO
    {
        if (empty(self::$instance)) {
            try {
                self::$instance = new PDO(
                    "mysql:host=". CONFIG_DB_HOST .";port=". CONFIG_DB_PORT .";dbname=". CONFIG_DB_DATABASE,
                    CONFIG_DB_USER,
                    CONFIG_DB_PASSWORD,
                    self::OPTIONS
                );
            } catch (PDOException $err) {
                dd("Não foi possível conectar ao banco de dados. Tente mais tarde.");
            }
        }
        return self::$instance;
    }

    final private function __construct()
    {
    }

    final private function __clone()
    {
    }
}
