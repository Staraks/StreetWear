<?php
require_once 'connection.php'; // Подключение к базе данных

class Product {
    private $db_conn;

    public function __construct($db_conn) {
        $this->db_conn = $db_conn;
    }

    public function getProducts($category = null) {
        $query = "
            SELECT 
                p.id AS product_id,
                pp.name AS product_name,
                pp.color AS product_color,
                pp.image_path AS image,
                p.price AS product_price,
                GROUP_CONCAT(s.size_name) AS available_sizes
            FROM product p
            INNER JOIN link_product_parameters lpp ON p.id = lpp.product_id
            INNER JOIN product_parameters pp ON lpp.parameters_id = pp.id
            INNER JOIN product_sizes ps ON p.id = ps.product_id
            INNER JOIN sizes s ON ps.size_id = s.id
        ";

        // Если передана категория, добавляем условие WHERE
        if ($category) {
            $query .= " WHERE p.product_category = ?";
        }

        $query .= " GROUP BY p.id, pp.name, pp.color, pp.image_path, p.price";

        // Подготовка SQL-запроса
        $stmt = $this->db_conn->prepare($query);

        // Проверяем, удалось ли подготовить запрос
        if (!$stmt) {
            return ["error" => "Ошибка подготовки запроса: " . $this->db_conn->error];
        }

        // Если передана категория, связываем параметр
        if ($category) {
            $stmt->bind_param("s", $category);
        }

        // Выполнение запроса
        $stmt->execute();
        $result = $stmt->get_result();

        // Проверяем результат выполнения
        if ($result) {
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            return ["error" => "Ошибка выполнения запроса: " . $stmt->error];
        }
    }
}

// Подключение к базе данных
$dbConnection = new DBConnection('localhost', 'root', '', 'Clothing_Store');
$dbConnection->connect();
$db_conn = $dbConnection->getConnection();

// Создаем объект продукта и получаем данные
$product = new Product($db_conn);

// Получение категории из GET-параметров
$category = isset($_GET['category']) ? $_GET['category'] : null;

// Получение продуктов
$products = $product->getProducts($category);

// Возвращаем результаты в формате JSON
header("Content-Type: application/json");
echo json_encode($products);

// Закрытие соединения с базой данных
$dbConnection->disconnect();
?>





