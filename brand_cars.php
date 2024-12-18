<?php
// brand_cars.php
include 'db_connection.php';
session_start();

// 獲取 GET 參數中的 brand_id
if (isset($_GET['brand_id']) && is_numeric($_GET['brand_id'])) {
    $brand_id = intval($_GET['brand_id']);

    // 獲取品牌名稱
    $stmt = $conn->prepare("SELECT name FROM brands WHERE id = ?");
    $stmt->bind_param("i", $brand_id);
    $stmt->execute();
    $brand_result = $stmt->get_result();
    if ($brand_result->num_rows > 0) {
        $brand_name = htmlspecialchars($brand_result->fetch_assoc()['name']);
    } else {
        // 品牌不存在
        echo "選擇的品牌不存在。<br><a href='brands.php' class='btn btn-primary mt-3'>返回所有品牌</a>";
        exit();
    }
    $stmt->close();

    // 獲取該品牌的所有車款（variants）
    $stmt = $conn->prepare("SELECT variants.*, models.model_name, models.year 
                            FROM variants 
                            JOIN models ON variants.model_id = models.id 
                            WHERE models.brand_id = ? 
                            ORDER BY models.model_name ASC, variants.trim_name ASC");
    $stmt->bind_param("i", $brand_id);
    $stmt->execute();
    $cars_result = $stmt->get_result();
    $cars = [];
    while($row = $cars_result->fetch_assoc()) {
        $cars[] = $row;
    }
    $stmt->close();
} else {
    // 未提供有效的 brand_id
    echo "未選擇任何品牌。<br><a href='brands.php' class='btn btn-primary mt-3'>返回所有品牌</a>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title><?= $brand_name ?> - 所有車款 - 汽車比較系統</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC&display=swap" rel="stylesheet">
    <!-- 自訂 CSS -->
    <style>
        body {
            font-family: 'Noto Sans TC', sans-serif;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 0;
        }
        .btn-add {
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?> <!-- 引入導航欄 -->

    <!-- 主要內容 -->
    <div class="container mt-5 pt-5">
        <h1 class="mb-4 text-center"><?= $brand_name ?> - 所有車款</h1>
        <a href="brands.php" class="btn btn-secondary mb-4">返回所有品牌</a>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>車型</th>
                        <th>年份</th>
                        <th>配置名稱</th>
                        <th>價格 (萬)</th>
                        <th>車體類型</th>
                        <th>引擎排氣量</th>
                        <th>馬力</th>
                        <th>燃料類型</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (count($cars) > 0) {
                        foreach ($cars as $car) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($car['model_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($car['year']) . "</td>";
                            echo "<td>" . htmlspecialchars($car['trim_name']) . "</td>";
                            
                            // 處理價格為 0 的情況
                            if ($car['price'] == 0) {
                                echo "<td>售價未公布</td>";
                            } else {
                                echo "<td>" . htmlspecialchars($car['price']) . "</td>";
                            }
                            
                            echo "<td>" . htmlspecialchars($car['body_type']) . "</td>";
                            echo "<td>" . htmlspecialchars($car['engine_cc']) . "</td>";
                            echo "<td>" . htmlspecialchars($car['horsepower']) . "</td>";
                            echo "<td>" . htmlspecialchars($car['fuel_type']) . "</td>";
                            
                            // 操作欄位：加入比較
                            echo "<td>";
                            // 檢查是否已加入比較
                            $is_added = in_array($car['id'], $_SESSION['compare_list']);
                            if ($is_added) {
                                echo "<button class='btn btn-success btn-sm' disabled>已加入</button>";
                            } else {
                                echo "<button class='btn btn-primary btn-sm btn-add' data-id='" . $car['id'] . "'>加入比較</button>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9'>此品牌目前沒有任何車款資料。</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 腳註 -->
    <footer class="footer mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 汽車比較系統. 版權所有.</p>
        </div>
    </footer>

    <!-- Bootstrap JS 和 jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // 加入比較
            $('.btn-add').click(function() {
                var variantId = $(this).data('id');
                var button = $(this);

                // 檢查是否已達到四輛
                if ($('.btn-add').not(':disabled').length >= 4) {
                    alert("最多只能比較四輛車。");
                    return;
                }

                // 發送 AJAX 請求加入比較
                $.ajax({
                    url: 'add_compare.php',
                    type: 'POST',
                    data: { variant_id: variantId },
                    success: function(response) {
                        if (response === 'success') {
                            button.removeClass('btn-primary').addClass('btn-success').text('已加入').prop('disabled', true);
                            alert("車輛已成功加入比較列表。");
                        } else if (response === 'limit') {
                            alert("最多只能比較四輛車。");
                        } else if (response === 'exists') {
                            alert("此車款已加入比較列表。");
                        } else {
                            alert("加入比較時出現未知錯誤。");
                        }
                    },
                    error: function() {
                        alert("加入比較時出現錯誤，請稍後再試。");
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
