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
                self::$chatId = $message['chat']['id']; // Сохраните chat_id в поле класса
                $text = $message['text'];
                $responseText = 'Вы отправили следующее сообщение: ' . $text;
                $userId = $message['from']['id'];
                $date = date('d-m-Y');

                self::$telegram->sendMessage([
                    'chat_id' => self::$chatId, // Используйте сохраненный chat_id
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

    // Добавьте статический метод для получения текущего chat_id
    public static function getChatId()
    {
        return self::$chatId;
    }
}

$config = parse_ini_file('config.ini');
MyTelegramBot::init($config);
MyTelegramBot::processUpdate();
?>
