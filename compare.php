<?php
// compare.php
include 'db_connection.php';

if (!isset($_GET['variant_ids']) || !is_array($_GET['variant_ids'])) {
    echo "請選擇至少一個變種進行比較。";
    exit;
}

$variant_ids = array_map('intval', $_GET['variant_ids']);
$ids_placeholder = implode(',', array_fill(0, count($variant_ids), '?'));

// 構建查詢
$stmt = $conn->prepare("SELECT variants.*, models.model_name, brands.name as brand_name FROM variants 
                        JOIN models ON variants.model_id = models.id 
                        JOIN brands ON models.brand_id = brands.id 
                        WHERE variants.id IN ($ids_placeholder)");

// 動態綁定參數
$types = str_repeat('i', count($variant_ids));
$stmt->bind_param($types, ...$variant_ids);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>變種比較</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>變種比較</h1>
    <a href="javascript:history.back()">返回上一頁</a>
    <table>
        <thead>
            <tr>
                <th>品牌</th>
                <th>模型</th>
                <th>配置名稱</th>
                <th>價格 (萬)</th>
                <th>車體類型</th>
                <th>引擎排氣量</th>
                <th>馬力</th>
                <th>燃料類型</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['brand_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['model_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['trim_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['price']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['body_type']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['engine_cc']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['horsepower']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['fuel_type']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>沒有選擇的變種資料</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
