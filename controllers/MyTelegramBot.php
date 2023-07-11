<?php
require_once "vendor/autoload.php";
require_once "Database.php";

use app\controllers\Database;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;

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

                if ($text === '/start') {
                    self::startCommand();
                } elseif ($text === 'Хочу цитату') {
                    self::bookTableCommand();
                } elseif (isset($message['photo'])) {
                    $photo = end($message['photo']);
                    $photoId = $photo['file_id'];
                    self::sendPhoto($photoId);
                } elseif (isset($message['video'])) {
                    $video = $message['video'];
                    $videoId = $video['file_id'];
                    self::sendVideo($videoId);
                } elseif (isset($message['sticker'])) {
                    $sticker = $message['sticker'];
                    $stickerId = $sticker['file_id'];
                    self::sendSticker($stickerId);
                } elseif (isset($message['animation'])) {
                    $animation = $message['animation'];
                    $gifId = $animation['file_id'];
                    self::sendGif($gifId);
                } else {
                    $responseText = 'Упс, что то пошло нет так!';
                    self::sendMessage($responseText);
                }
            }
        } catch (Exception $e) {
            var_dump('Произошла ошибка: ' . $e->getMessage());
        }
        Database::closeConnection();
        sleep(1);
    }

    public static function startCommand()
    {
        $responseText = 'Добро пожаловать! Чем могу помочь?';
        $keyboard = Keyboard::make([
            'keyboard' => [
                ['Хочу цитату'],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ]);

        self::sendMessage($responseText, $keyboard);
    }

    public static function bookTableCommand()
    {
        $quote = self::getMotivationalQuote();
        self::sendMessage($quote);
    }

    public static function getMotivationalQuote()
    {
        $response = file_get_contents('http://api.forismatic.com/api/1.0/?method=getQuote&format=json&lang=ru');

        if ($response !== false) {
            $quoteData = json_decode($response, true);

            $quoteText = $quoteData['quoteText'];
            $quoteAuthor = $quoteData['quoteAuthor'] ?? '';
            $quote = $quoteText . PHP_EOL;
            if (!empty($quoteAuthor)) {
                $quote .= '— ' . $quoteAuthor;
            }

            return $quote;
        }
        return 'Извините, не удалось получить мотивационную цитату. Попробуйте позже.';
    }

    public static function sendMessage($text, $keyboard = null)
    {
        $date = date('d-m-Y');
        $Text = 'Хочу цитату';

        self::$telegram->sendMessage([
            'chat_id' => self::$chatId,
            'text' => $text,
            'reply_markup' => $keyboard,
        ]);

        $connection = Database::getConnection();
        $query = "INSERT INTO Message_text (request, response, chat_id, date) VALUES ('$Text', '$text', '" . self::$chatId . "', '$date')";
        pg_query($connection, $query);
    }

    public static function sendPhoto($photoId)
    {
        self::$telegram->sendPhoto([
            'chat_id' => self::$chatId,
            'photo' => $photoId,
        ]);

        $connection = Database::getConnection();
        $query = "INSERT INTO Media (media_type, media_id, chat_id) VALUES ('photo', '$photoId', '" . self::$chatId . "')";
        pg_query($connection, $query);
    }

    public static function sendVideo($videoId)
    {
        self::$telegram->sendVideo([
            'chat_id' => self::$chatId,
            'video' => $videoId,
        ]);
        $connection = Database::getConnection();
        $query = "INSERT INTO Media (media_type, media_id, chat_id) VALUES ('video', '$videoId', '" . self::$chatId . "')";
        pg_query($connection, $query);
    }

    public static function sendSticker($stickerId)
    {
        self::$telegram->sendSticker([
            'chat_id' => self::$chatId,
            'sticker' => $stickerId,
        ]);
        $connection = Database::getConnection();
        $query = "INSERT INTO Media (media_type, media_id, chat_id) VALUES ('sticker', '$stickerId', '" . self::$chatId . "')";
        pg_query($connection, $query);
    }

    public static function sendGif($gifId)
    {
        self::$telegram->sendAnimation([
            'chat_id' => self::$chatId,
            'animation' => $gifId,
        ]);

        $connection = Database::getConnection();
        $query = "INSERT INTO Media (media_type, media_id, message_text_id) VALUES ('gif', '$gifId', LASTVAL())";
        pg_query($connection, $query);
    }

    public static function getChatId()
    {
        $config = parse_ini_file('config.ini');
        Database::connect($config);
        $connection = Database::getConnection();

        $query = "SELECT chat_id FROM Message_text ORDER BY id DESC LIMIT 1";
        $result = pg_query($connection, $query);
        $row = pg_fetch_assoc($result);
        $chatId = $row['chat_id'];

        Database::closeConnection();

        return $chatId;
    }
}

$config = parse_ini_file('config.ini');
MyTelegramBot::init($config);
MyTelegramBot::processUpdate();
