<?php

namespace app\controllers;
use app\controllers\Database;
class GetMessages
{

    public function displayMessages()
    {
        $config = parse_ini_file('config.ini');
        Database::connect($config);
        $connection = Database::getConnection();
        $date = date('d-m-Y');
        $query = "SELECT * FROM message WHERE date = '$date'";
        $result = pg_query($connection, $query);
        $messages = pg_fetch_all($result);

        Database::closeConnection();

        return $messages;
    }
}
?>
