<?php
session_start(); //возобновляем сессию
session_destroy(); //завершаем сессию
header('Location: ../');//переход на главную страницу
