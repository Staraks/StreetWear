<?php
require_once 'connection.php';

class ProductDetails {
    private $db_conn;

    public function __construct($db_conn) {
        $this->db_conn = $db_conn;
    }

    public function getProductDetails($productId) {
        $query = "
            SELECT 
                p.id AS product_id,
                pp.name AS product_name,
                pp.color AS product_color,
                pp.image_path AS image,
                p.price AS product_price
            FROM product p
            INNER JOIN link_product_parameters lpp ON p.id = lpp.product_id
            INNER JOIN product_parameters pp ON lpp.parameters_id = pp.id
            WHERE p.id = ?
        ";
        $stmt = $this->db_conn->prepare($query);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        $product = $result->fetch_assoc();

        // Получение доступных размеров
        $sizeQuery = "
            SELECT 
                ps.id AS product_size_id,
                s.size_name AS size
            FROM product_sizes ps
            INNER JOIN sizes s ON ps.size_id = s.id
            WHERE ps.product_id = ?
        ";
        $sizeStmt = $this->db_conn->prepare($sizeQuery);
        $sizeStmt->bind_param("i", $productId);
        $sizeStmt->execute();
        $sizeResult = $sizeStmt->get_result();
        $sizes = [];
        while ($row = $sizeResult->fetch_assoc()) {
            $sizes[] = [
                "id" => $row['product_size_id'],
                "size" => $row['size']
            ];
        }

        return [
            "product" => $product,
            "sizes" => $sizes
        ];
    }
}

$dbConnection = new DBConnection('localhost', 'root', '', 'Clothing_Store');
$dbConnection->connect();
$db_conn = $dbConnection->getConnection();

$productDetails = new ProductDetails($db_conn);
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$data = $productDetails->getProductDetails($productId);

header("Content-Type: application/json");
echo json_encode($data);

$dbConnection->disconnect();
?>

