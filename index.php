<?php

include 'connection.php';
include 'product.php';

// Создание объекта подключения к базе данных
$connection = new DBConnection('localhost', 'root', '', 'Clothing_Store');
$connection->connect();

// Пример работы с продуктами
$product = new Product($connection->getConnection());
$products = $product->getProducts();

// Установка заголовков для ответа
header('Content-Type: application/json');



