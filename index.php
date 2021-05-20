<?php
require_once 'class/QueryBuilder.php';


//Создаём соединение к базе
$db = new PDO('mysql:dbname=task2;host=localhost', 'mysql', 'mysql');
//Передаём в конструктор класс бд (что бы не привязываться к конкретной версии и базе)
$queryBuilder = new QueryBuilder($db);

$table = 'users';

//Добавляем запись.
$queryBuilder->insert($table,['name'=>'Valera','surname'=>'surname','email'=>'post@gmail.com','phone'=>'+3809689583','social_link'=>'vk.com','telegram'=>'@telegram']);

//Функция формирует запрос.
$queryBuilder->select($table,'*',['id'=>6,'email'=>'post@gmail.com','surname'=>'Овчаренко','phone'=>'+3809689583','social_link'=>'vk.com','telegram'=>'@telegram']);

//Получает массив данных которые требуется обновить, второй массив какие значения требуется установить.
//В функции для избежания конлфикта реализована функция генерации новых наименований ключей. Иначе получался конфликт.
$queryBuilder->update($table,['id'=>26],['id'=>43]);

$queryBuilder->delete($table,['id'=>43]);



