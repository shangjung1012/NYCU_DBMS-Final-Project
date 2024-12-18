<?php
// get_variant.php
include 'db_connection.php';

if (isset($_GET['variant_id']) && is_numeric($_GET['variant_id'])) {
    $variant_id = intval($_GET['variant_id']);

    // 獲取車輛詳細資料
    $stmt = $conn->prepare("SELECT variants.*, models.model_name, models.year, brands.name as brand_name 
                            FROM variants 
                            JOIN models ON variants.model_id = models.id 
                            JOIN brands ON models.brand_id = brands.id 
                            WHERE variants.id = ?");
    $stmt->bind_param("i", $variant_id);
    $stmt->execute();
    $variant = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($variant) {
        echo "<li class='list-group-item d-flex justify-content-between align-items-center' data-id='" . $variant_id . "'>";
        echo htmlspecialchars($variant['brand_name']) . " " . htmlspecialchars($variant['model_name']) . " (" . htmlspecialchars($variant['year']) . ") - " . htmlspecialchars($variant['trim_name']);
        
        // 判斷價格是否為 0
        if ($variant['price'] == 0) {
            echo " - 售價未公布";
        } else {
            echo " - " . htmlspecialchars($variant['price']) . " 萬";
        }

        echo "<button class='btn btn-danger btn-sm remove-btn' data-id='" . $variant_id . "'>移除</button>";
        echo "</li>";
    }
}

$conn->close();
?>
