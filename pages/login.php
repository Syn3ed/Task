<?php
require_once '../config.php'; //импорт файла с конфигурацией
require_once '../scripts/capcha.php'; //импорт файла с капсей
require_once '../scripts/db/connection.php'; //импорт файла для общения с бд

session_start(); //возобновляем сессию
$pdo = getPDO(); //функция из connection.php для общения с бд

$errors = false;

//проверка что метод запроса совпадает и поля пользоветял преобразеумые в безопасные для работы с ними (защита от XSS) 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginInput = htmlspecialchars($_POST['login']);
    $pass = htmlspecialchars($_POST['password']);
    $token = $_POST['smart-token'];

    if (!check_captcha($token)) { //проверка капчи
        $errors = true;
        echo "<dialog open>
                    <form method='dialog' open> Капча не пройдена!
                        <button>Ok</button>
                    </form>
                </dialog>";
    }

    if (! $errors) {
        // если нет ошибок, то ищем пользователя в бд, сверяя loginInput (может быть телефоном или почтой)
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ? OR phone = ?");
        $stmt->execute([$loginInput, $loginInput]);
        $user = $stmt->fetch(); //получаем данные пользователя

        // проверка пароля с хешем из бд, если совпало, то сохраняем id пользователя в сессию и перенаправляем на страничку профиля, иначе выводим ошибку
        if (password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: profile.php');
        } else {
            $errors = true;
            echo '<dialog open>
                        <form method="dialog" open>Неверный логин/телефон/email или пароль!
                            <button>Ok</button>
                        </form>
                    </dialog>';
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Вход</title>
    <!-- капча от яндекса -->
    <script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
</head>

<body>
    <h1>Вход</h1>
    <form method="post">
        <!-- required гарантирует, что поле должно быть заполнено -->
        <input type="text" name="login" placeholder="Введите телефон или почту" required><br>
        <input type="password" name="password" placeholder="Пароль" required><br>
        <!-- элемент капчи, SMARTCAPTCHA_SERVER_KEY взят из конфига -->
        <div
            style="height: 100px"
            id="captcha-container"
            class="smart-captcha"
            data-sitekey=<?= SMARTCAPTCHA_SERVER_KEY ?>>
        </div>
        <button type="submit">Войти</button>
    </form>
    <a href="/pages/register.php">Регистрация</a>
</body>

</html>