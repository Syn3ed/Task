<?php
require_once '../config.php'; //импорт файла с конфигурацией
function getPDO()
{
    //создаем PHP обьект бд PDO для общения с базой данных, используя константы из конфига
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    return $pdo;//возвращаем cам обьект 
}
