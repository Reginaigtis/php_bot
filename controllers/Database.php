<?php

namespace app\controllers;


class Database
{
    private static $connection;

    public static function connect($config)
    {
        $host = $config['host'];
        $port = $config['port'];
        $dbname = $config['dbname'];
        $user = $config['user'];
        $password = $config['password'];

        self::$connection = pg_connect("host=" . $host . " port=" . $port . " dbname=" . $dbname . " user=" . $user . " password=" . $password);
        if (!self::$connection) {
            var_dump('Ошибка подключения: ' . pg_last_error());
        }
    }

    public static function getConnection()
    {
        return self::$connection;
    }

    public static function closeConnection()
    {
        pg_close(self::$connection);
    }
}
