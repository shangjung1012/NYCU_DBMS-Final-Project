<?php
// compare.php
include 'db_connection.php';
session_start();

if (!isset($_SESSION['compare_list']) || count($_SESSION['compare_list']) < 1) {
    echo "沒有選擇要比較的車輛。<br><a href='compare_selection.php' class='btn btn-primary mt-3'>返回選擇頁面</a>";
    exit;
}

$variant_ids = array_map('intval', $_SESSION['compare_list']);
$placeholders = implode(',', array_fill(0, count($variant_ids), '?'));

// 構建查詢
$query = "SELECT variants.*, models.model_name, models.year, brands.name as brand_name 
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- 自訂 CSS -->
    <link rel="stylesheet" href="styles.css">
    <style>
        /* 自訂樣式 */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #e9ecef;
        }
        tr:hover {
            background-color: #f1f3f5;
        }
        .back-button {
            padding: 10px 20px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?> <!-- 引入導航欄 -->

    <div class="container mt-5 pt-5">
        <h1 class="mb-4">汽車比較結果</h1>
        <a href="compare_selection.php" class="btn btn-secondary mb-4">返回比較選擇頁面</a>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>品牌</th>
                        <th>車系</th>
                        <th>年份</th>
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
                    if (count($variants) > 0) {
                        foreach ($variants as $variant) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($variant['brand_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($variant['model_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($variant['year']) . "</td>";
                            echo "<td>" . htmlspecialchars($variant['trim_name']) . "</td>";
                            
                            // 處理價格為 0 的情況
                            if ($variant['price'] == 0) {
                                echo "<td>售價未公布</td>";
                            } else {
                                echo "<td>" . htmlspecialchars($variant['price']) . "</td>";
                            }
                            
                            echo "<td>" . htmlspecialchars($variant['body_type']) . "</td>";
                            echo "<td>" . htmlspecialchars($variant['engine_cc']) . "</td>";
                            echo "<td>" . htmlspecialchars($variant['horsepower']) . "</td>";
                            echo "<td>" . htmlspecialchars($variant['fuel_type']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9'>沒有選擇的車輛資料</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
