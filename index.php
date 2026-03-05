
<?php
session_start();// старт/возобновление сессии 

if (isset($_SESSION['user_id'])) { //проверка авторизации
    header('Location: pages/profile.php');
} else {
    header('Location: pages/login.php');
}
?>

