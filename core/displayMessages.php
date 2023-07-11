<?php
use app\controllers\GetMessages;
$getMessages = new GetMessages();
$messages = $getMessages->displayMessages();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
    <title>Все сообщения</title>
</head>
<body>
<section class="section">
    <script>
        const fileInput = document.querySelector('.file-input');
        const fileName = document.querySelector('#file-name');

        fileInput.addEventListener('change', function() {
            fileName.textContent = this.files[0].name;
        });
    </script>
    <div class="container">
        <h1 class="title">Все сообщения</h1>

        <form class="reply-form" method="POST" action="Messages.php" enctype="multipart/form-data">

            <div class="field">
                <label class="label">Прикрепить файл</label>
                <div class="file">
                    <label class="file-label">
                        <input class="file-input" type="file" name="attachment">
                        <span class="file-cta">
                <span class="file-icon">
                    <i class="fas fa-upload"></i>
                </span>
                <span class="file-label">
                    Выберите файл
                </span>
            </span>
                    </label>
                </div>
                <p id="file-name" class="file-name"></p>
            </div>
            <div class="field">
                <label class="label">Введите сообщение</label>
                <div class="control">
                    <textarea class="textarea" name="reply" rows="3" placeholder="Введите сообщение"></textarea>
                </div>
            </div>
            <div class="field">
                <div class="control">
                    <button class="button is-primary" type="submit">Отправить</button>
                </div>
            </div>
        </form>

        <div id="messages-container">
            <?php foreach ($messages as $message) : ?>
                <div class="message-container">
                    <div class="box">
                        <p class="question"><strong>Вопрос:</strong> <?php echo $message['request']; ?></p>
                        <p class="answer"><strong>Ответ:</strong> <?php echo $message['response']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
</body>
</html>