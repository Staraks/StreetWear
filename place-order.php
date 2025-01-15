<?php
include 'connection.php';

$data = json_decode(file_get_contents('php://input'), true);

$cartItems = $data['cartItems'];
$address = $data['address'];
$userId = isset($data['userId']) ? intval($data['userId']) : null;

if (empty($cartItems) || empty($address) || empty($userId)) {
    echo json_encode(['success' => false, 'message' => 'Пустая корзина, адрес или не указан user_id']);
    exit;
}

// Создаем объект подключения и подключаемся к базе данных
$connection = new DBConnection('localhost', 'root', '', 'Clothing_Store');
$connection->connect();

try {
    // Начинаем транзакцию
    $conn = $connection->getConnection();
    $conn->begin_transaction();

    // Создаем заказ
    $stmt = $conn->prepare("INSERT INTO `order` (user_id, total_amount, shipping_address, status, date) VALUES (?, ?, ?, ?, NOW())");
    $totalAmount = array_sum(array_map(function($item) {
        return $item['price'] * $item['quantity'];
    }, $cartItems));
    $status = 'Pending';
    $stmt->bind_param("idss", $userId, $totalAmount, $address, $status);
    $stmt->execute();

    $orderId = $conn->insert_id;

    // Добавляем товары в детали заказа и уменьшаем количество на складе
    foreach ($cartItems as $item) {
        // Добавляем запись в order_details
        $stmtDetails = $conn->prepare("INSERT INTO order_details (order_id, product_size_id, price, quantity) VALUES (?, ?, ?, ?)");
        $stmtDetails->bind_param("iidi", $orderId, $item['product_size_id'], $item['price'], $item['quantity']);
        if (!$stmtDetails->execute()) {
            throw new Exception("Ошибка добавления в order_details: " . $stmtDetails->error);
        }
        $stmtDetails->close();

        // Уменьшаем количество товара на складе
        $stmtStock = $conn->prepare("UPDATE product_sizes SET stock_quantity = stock_quantity - ? WHERE id = ?");
        $stmtStock->bind_param("ii", $item['quantity'], $item['product_size_id']);
        if (!$stmtStock->execute()) {
            throw new Exception("Ошибка обновления product_sizes: " . $stmtStock->error);
        }
        $stmtStock->close();
    }

    // Фиксируем транзакцию
    $conn->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Откатываем транзакцию в случае ошибки
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    // Закрываем подключение
    $connection->disconnect();
}
?>




