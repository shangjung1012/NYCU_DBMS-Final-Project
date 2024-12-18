<?php
// get_series.php
include 'db_connection.php';

if (isset($_GET['brand_id']) && is_numeric($_GET['brand_id'])) {
    $brand_id = intval($_GET['brand_id']);

    // 假設 "車系" 對應於 "models" 表中的 "model_name"
    // 如果有獨立的 "series" 表，請根據實際情況調整
    // 這裡假設每個品牌有多個車系，且車系可以通過某個欄位識別

    // 這裡假設車系即為不同的模型名稱
    $stmt = $conn->prepare("SELECT DISTINCT id, model_name FROM models WHERE brand_id = ? ORDER BY model_name ASC");
    $stmt->bind_param("i", $brand_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<option value=''>-- 選擇車系 --</option>";
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['model_name']) . "</option>";
        }
    } else {
        echo "<option value=''>無車系資料</option>";
    }

    $stmt->close();
}

$conn->close();
?>
