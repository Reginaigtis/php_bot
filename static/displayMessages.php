<!DOCTYPE html>
<html>
<head>
    <title>Все сообщения</title>
    <style>
        .message-container {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }

        .message-container p {
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        .message-container .question {
            font-weight: bold;
        }

        .message-container .answer {
            margin-left: 20px;
        }
    </style>
</head>
<body>
<form class="reply-form" method="POST" action="Messages.php">
    <textarea name="reply" rows="3" cols="30" placeholder="Введите сообщение"></textarea>
    <?php if (isset($update['message'])) : ?>
        <input type="hidden" name="chat_id" value="<?php echo $update['message']['chat']['id']; ?>">
    <?php else : ?>
        <input type="hidden" name="chat_id" value="">
    <?php endif; ?>
    <button type="submit">Отправить</button>
</form>
<div id="messages-container">
    <?php foreach ($messages as $messag) : ?>
        <div class="message-container">
            <p class="question"><strong>Вопрос:</strong> <?php echo $message['request']; ?></p>
            <p class="answer"><strong>Ответ:</strong> <?php echo $message['response']; ?></p>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
