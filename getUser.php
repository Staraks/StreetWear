<?php
header('Content-Type: application/json');
include 'connection.php';

// Проверяем, передан ли ID пользователя
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Не указан ID пользователя.']);
    exit;
}

$userId = intval($_GET['id']);

// Создаем объект подключения и подключаемся к базе данных
$connection = new DBConnection('localhost', 'root', '', 'Clothing_Store');
$connection->connect();

// Запрос данных пользователя
$query = "SELECT id, name, phone_number FROM user WHERE id = ?";
$stmt = $connection->getConnection()->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Проверяем, найден ли пользователь
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не найден.']);
    exit;
}

// Получаем данные пользователя
$user = $result->fetch_assoc();

// Возвращаем данные в формате JSON
echo json_encode(['success' => true, 'data' => $user]);

// Закрываем подключение
$connection->disconnect();
?>


