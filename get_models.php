<?php
// get_models.php
include 'db_connection.php';

if (isset($_GET['series_id']) && is_numeric($_GET['series_id'])) {
    $series_id = intval($_GET['series_id']);

    // 假設 "車款" 對應於 "variants" 表中的不同配置
    $stmt = $conn->prepare("SELECT * FROM variants WHERE model_id = ? ORDER BY price ASC");
    $stmt->bind_param("i", $series_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<option value=''>-- 選擇車款 --</option>";
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // 顯示完整車款名稱
            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['trim_name']) . " - " . htmlspecialchars($row['price']) . " 萬</option>";
        }
    } else {
        echo "<option value=''>無車款資料</option>";
    }

    $stmt->close();
}

$conn->close();
?>
