<?php
require_once '../scripts/db/connection.php';//импорт файла для общения с бд

session_start(); //возобновляем сессию
$pdo = getPDO(); //функция из connection.php для общения с бд

$errors = false;

//проверка что метод запроса совпадает и поля пользоветял преобразеумые в безопасные для работы с ними (защита от XSS) 
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $name  = htmlspecialchars($_POST['name']);
    $login = htmlspecialchars($_POST['login']);
    $phone = htmlspecialchars($_POST['phone']);
    $email = htmlspecialchars($_POST['email']);
    $pass  = htmlspecialchars($_POST['password']);
    $pass2 = htmlspecialchars($_POST['password2']);

    if ($pass !== $pass2) { //проверка что пароли совпадают
        $errors = true;
        echo '<dialog open>
                    <form method="dialog" open>Пароли не совпадают!
                        <button>Ok</button>
                    </form>
                </dialog>';
    }

    // проверка что нет другого пользователя с таким же логином, телефоном или почтой иначе выводим ошибку
    $stmt = $pdo->prepare("SELECT id FROM Users WHERE login = ? OR email = ? OR phone = ?");
    $stmt->execute([$login, $email, $phone]); //такое обращение к бд позволяет избежать SQL инъекций 
    if ($stmt->fetch()) {
        $errors = true;
        echo '<dialog open>
                    <form method="dialog" open>Пользователь с таким логином, email или телефоном уже существует!
                        <button>Ok</button>
                    </form>
                </dialog>';
    }

    if (!$errors) { // если нет ошибок, то хешируем пароль и сохраняем нового пользователя в бд, после чего перенаправляем на страницу для входа
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO Users (name, login, phone, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $login, $phone, $email, $hash]);

        header('Location: login.php');
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Регистрация</title>
</head>
<body>
    <h1>Регистрация</h1>
    <form method="post">
        <div class="form-group">
            <!-- required гарантирует, что поле должно быть заполнено -->
            <input type="text" name="name" placeholder="Имя" required>
            <input type="text" name="login" placeholder="Логин" required>
            <input type="tel" name="phone" placeholder="Телефон" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <input type="password" name="password2" placeholder="Повторите пароль" required>
            <button type="submit">Зарегистрироваться</button>
        </div>
    </form>
</body>
</html>