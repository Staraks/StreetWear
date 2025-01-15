<?php
header('Content-Type: application/json');
include 'connection.php';

// Получаем данные
$data = json_decode(file_get_contents('php://input'), true);

$phone_number = $data['phone_number'];
$password = $data['password'];

// Создаем объект подключения и подключаемся к базе данных
$connection = new DBConnection('localhost', 'root', '', 'Clothing_Store');
$connection->connect();

// Проверка данных пользователя
$query = "SELECT id, password FROM user WHERE phone_number = ?";
$stmt = $connection->getConnection()->prepare($query);
$stmt->bind_param("s", $phone_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Неверный телефон или пароль.']);
    exit;
}

$user = $result->fetch_assoc();

if (password_verify($password, $user['password'])) {
    echo json_encode(['success' => true, 'userId' => $user['id']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный телефон или пароль.']);
}

// Закрываем подключение
$connection->disconnect();
?>



