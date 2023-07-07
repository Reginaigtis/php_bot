<?php
require_once "vendor/autoload.php";
require_once "Database.php";

use app\controllers\Database;
use Telegram\Bot\Api;

class MyTelegramBot
{
    private static $telegram;
    private static $chatId;

    public static function init($config)
    {
        Database::connect($config);

        self::$telegram = new Api($config['token']);
        self::$telegram->setWebhook(['url' => $config['webhook']]);
    }

    public static function processUpdate()
    {
        $content = file_get_contents("php://input");
        $update = json_decode($content, true);
        try {
            if (isset($update['message'])) {
                $message = $update['message'];
                self::$chatId = $message['chat']['id'];
                $text = $message['text'];
                $responseText = 'Вы отправили следующее сообщение: ' . $text;
                $userId = $message['from']['id'];

                self::$telegram->sendMessage([
                    'chat_id' => self::$chatId,
                    'text' => $responseText,
                ]);

                $connection = Database::getConnection();
                $query = "INSERT INTO message (request, response, userId, chatId, date) VALUES ('$text', '$responseText', '$userId', '" . self::$chatId . "', '$date')";
                pg_query($connection, $query);
            }
        } catch (Exception $e) {
            var_dump('Произошла ошибка: ' . $e->getMessage());
        }

        Database::closeConnection();
        sleep(1);
    }


    public static function getChatId()
    {
        $config = parse_ini_file('config.ini');
        Database::connect($config);
        $connection = Database::getConnection();

        $query = "SELECT chatId FROM message ORDER BY id DESC LIMIT 1";
        $result = pg_query($connection, $query);
        $row = pg_fetch_assoc($result);
        $chatId = end($row);

        Database::closeConnection();

        return $chatId;
    }
}

$config = parse_ini_file('config.ini');
MyTelegramBot::init($config);
MyTelegramBot::processUpdate();

