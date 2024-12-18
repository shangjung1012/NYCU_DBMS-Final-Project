<?php
// compare.php
include 'db_connection.php';
session_start();

if (!isset($_SESSION['compare_list']) || count($_SESSION['compare_list']) < 1) {
    echo "沒有選擇要比較的車輛。<br><a href='compare_selection.php'>返回選擇頁面</a>";
    exit;
}

$variant_ids = array_map('intval', $_SESSION['compare_list']);
$placeholders = implode(',', array_fill(0, count($variant_ids), '?'));

// 構建查詢
$query = "SELECT variants.*, models.model_name, brands.name as brand_name 
          FROM variants 
          JOIN models ON variants.model_id = models.id 
          JOIN brands ON models.brand_id = brands.id 
          WHERE variants.id IN ($placeholders)";

$stmt = $conn->prepare($query);

// 動態綁定參數
$types = str_repeat('i', count($variant_ids));
$stmt->bind_param($types, ...$variant_ids);
$stmt->execute();
$result = $stmt->get_result();

// 獲取所有選擇的車輛
$variants = [];
while($row = $result->fetch_assoc()) {
    $variants[] = $row;
}
$stmt->close();

// 清空比較列表
// $_SESSION['compare_list'] = [];
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>汽車比較結果</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* 自訂樣式 */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .back-button {
            padding: 10px 20px;
            background-color: #6c757d;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }
        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <h1>汽車比較結果</h1>
    <a href="compare_selection.php">返回比較選擇頁面</a>
    <table>
        <thead>
            <tr>
                <th>品牌</th>
                <th>車系</th>
                <th>配置名稱</th>
                <th>價格 (萬)</th>
                <th>車體類型</th>
                <th>引擎排氣量</th>
                <th>馬力</th>
                <th>燃料類型</th>
                <th>詳細資訊</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (count($variants) > 0) {
                foreach ($variants as $variant) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($variant['brand_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($variant['model_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($variant['trim_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($variant['price']) . "</td>";
                    echo "<td>" . htmlspecialchars($variant['body_type']) . "</td>";
                    echo "<td>" . htmlspecialchars($variant['engine_cc']) . "</td>";
                    echo "<td>" . htmlspecialchars($variant['horsepower']) . "</td>";
                    echo "<td>" . htmlspecialchars($variant['fuel_type']) . "</td>";
                    echo "<td><a href='" . htmlspecialchars($variant['url']) . "' target='_blank'>查看詳情</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9'>沒有選擇的車輛資料</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <button class="back-button" onclick="window.location.href='compare_selection.php'">返回比較選擇頁面</button>
</body>
</html>

<?php
$conn->close();
?>
