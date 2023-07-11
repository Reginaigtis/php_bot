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
        $chatId = $this->telegramBot->getChatId();
        $replyText = $_POST['reply'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['reply'])) {
            $date = date('d-m-Y');
            $quote = MyTelegramBot::getMotivationalQuote();
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $quote,
            ]);

            Database::connect($this->config);
            $connection = Database::getConnection();
            $query = "INSERT INTO Message_text (request, response, chat_id, date) VALUES ('$replyText', '$quote', '$chatId', '$date')";
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

