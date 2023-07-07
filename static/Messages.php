<?php
require_once "vendor/autoload.php";
require_once "controllers/Database.php";
require_once "controllers/MyTelegramBot.php";

use app\controllers\Database;
use app\controllers\GetMessages;
use Telegram\Bot\Api;

class Messages
{
    private $telegram;
    private $telegramBot;
    private $getMessages;
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
        $this->telegram = new Api($config['token']);
        $this->telegramBot = new MyTelegramBot();
        $this->getMessages = new GetMessages();
    }

    public function handle()
    {
        $this->telegramBot->init($this->config);
        $this->telegramBot->processUpdate();
        $chatId = $this->telegramBot->getChatId();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $replyText = $_POST['reply'];
            $responseText = 'Вы отправили следующее сообщение: ' . $replyText;
            $date = date('d-m-Y');

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $replyText,
            ]);

            Database::connect($this->config);
            $connection = Database::getConnection();
            $query = "INSERT INTO message (request, response, userId, chatId, date) VALUES ('$replyText', '$responseText', NULL, '$chatId', '$date')";
            pg_query($connection, $query);
            Database::closeConnection();
        }
    }

    public function displayMessages()
    {
        return $this->getMessages->displayMessages();
    }
}

$config = parse_ini_file('config.ini');
$messages = new Messages($config);
$messages->handle();
$display = $messages->displayMessages();

