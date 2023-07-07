<?php
require_once "vendor/autoload.php";
require_once "controllers/Database.php";

use app\controllers\Database;
use Telegram\Bot\Api;

$config = parse_ini_file('config.ini');
$telegram = new Api($config['token']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = file_get_contents("php://input");
    $update = json_decode($content, true);
    $replyText = $_POST['reply'];

    if (isset($update['message'])) {
        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $responseText = 'Вы отправили следующее сообщение: ' . $replyText;
        $userId = $message['from']['id'];
        $date = date('d-m-Y');

        $telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $replyText,
        ]);

        Database::connect($config);
        $connection = Database::getConnection();
        $query = "INSERT INTO message (request, response, userId, chatId, date) VALUES ('$replyText', '$responseText', '$userId', '$chatId', '$date')";
        pg_query($connection, $query);
        Database::closeConnection();

        // Перезагрузка страницы
        header('Location: displayMessages.php');
        exit();
    }
}
?>
