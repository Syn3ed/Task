<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../scripts/db/connection.php'; //импорт файла для общения с бд

session_start(); //возобновляем сессию

if (!isset($_SESSION['user_id'])) { //проверка авторизации, если нет, то перенаправляем на главную страницу
    header('Location: ../');
}

$user_id = $_SESSION['user_id']; //получаем id пользователя из сессии
$pdo     = getPDO(); //функция из connection.php для общения с бд
$errors  = false;

//c помощью id пользователя получаем его данные для отображения в профиле
$stmt = $pdo->prepare("SELECT login, name, phone, email FROM Users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

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

    // проверка уникальности данных, кроме самого пользователя, иначе выводим ошибку
    $stmt = $pdo->prepare("SELECT id FROM Users WHERE (login = ? OR email = ? OR phone = ?) AND id != ?");
    $stmt->execute([$login, $email, $phone, $user_id]);
    if ($stmt->fetch()) {
        $errors = true;
        echo '<dialog open>
                        <form method="dialog" open>Пользователь с таким логином, email или телефоном уже существует!
                            <button>Ok</button>
                        </form>
                    </dialog>';
    }

    if (!$errors) { // если нет ошибок, то обновляем данные пользователя в бд
        $sql = "UPDATE Users SET name = ?, login = ?, phone = ?, email = ? WHERE id = ?";
        $pdo->prepare($sql)->execute([$name, $login, $phone, $email, $user_id]);

        if (!empty($pass)) { // если пользователь ввел новый пароль, то хешируем его и обновляем в бд
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE Users SET password = ? WHERE id = ?")->execute([$hash, $user_id]);
        }
        // получаем данные пользователя заново для отображения в профиле
        $stmt = $pdo->prepare("SELECT login, name, phone, email FROM Users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    }
    $errors = false;
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
</head>
<body>
    <form method="post">
         <!-- required гарантирует, что поле должно быть заполнено -->
        <p>Имя: <input type="text" name="name" value="<?= $user['name'] ?>" required></p>
        <p>Логин: <input type="text" name="login" value="<?= $user['login'] ?>" required></p>
        <p>Телефон: <input type="tel" name="phone" value="<?= $user['phone'] ?>" required></p>
        <p>Email: <input type="email" name="email" value="<?= $user['email'] ?>" required></p>
        <p>Новый пароль:<input type="password" name="password"></p>
        <p>Повторите:<input type="password" name="password2"></p>

        <button type="submit">Сохранить изменения</button>
        <!-- ссылка на страницу выхода -->
        <a href="logout.php">Выйти</a>
    </form>
</body>
</html>