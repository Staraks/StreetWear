<?php

header('Content-Type: application/json');
include 'connection.php';

// Получаем данные
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Данные не переданы.']);
    exit;
}

$name = trim($data['name']);
$phone_number = trim($data['phone_number']);
$password = $data['password'];

// Проверяем формат номера телефона
if (!preg_match('/^\+?\d{10,15}$/', $phone_number)) {
    echo json_encode(['success' => false, 'message' => 'Некорректный номер телефона.']);
    exit;
}

// Хэшируем пароль
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Создаем объект подключения и подключаемся к базе данных
$connection = new DBConnection('localhost', 'root', '', 'Clothing_Store');
$connection->connect();

// Проверка, существует ли пользователь с таким номером телефона
$query = "SELECT id FROM user WHERE phone_number = ?";
$stmt = $connection->getConnection()->prepare($query);
$stmt->bind_param("s", $phone_number);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Пользователь уже существует.']);
    exit;
}

// Добавление пользователя
$query = "INSERT INTO user (name, phone_number, password) VALUES (?, ?, ?)";
$stmt = $connection->getConnection()->prepare($query);
$stmt->bind_param("sss", $name, $phone_number, $hashed_password);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка регистрации.']);
}

// Закрываем подключение
$connection->disconnect();
?>



