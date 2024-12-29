<?php
// manage_vehicles.php
session_start();

// 檢查用戶是否登入且為管理員
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';

// 處理新增和刪除車輛
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        // 獲取並清理輸入
        $brand_id = intval($_POST['brand_id']);
        $model_name = trim($_POST['model_name']);
        $year = intval($_POST['year']);
        $trim_name = trim($_POST['trim_name']);
        $price = floatval($_POST['price']);
        $body_type = trim($_POST['body_type']);
        $engine_cc = trim($_POST['engine_cc']);
        $horsepower = trim($_POST['horsepower']);
        
        $fuel_type = trim($_POST['fuel_type']);
        
        // 插入或查詢車型
        $stmt = $conn->prepare("SELECT id FROM models WHERE brand_id = ? AND model_name = ?");
        $stmt->bind_param("is", $brand_id, $model_name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $model = $result->fetch_assoc();
            $model_id = $model['id'];
        } else {
            // 新增車型
            $stmt_insert_model = $conn->prepare("INSERT INTO models (brand_id, model_name, year) VALUES (?, ?, ?)");
            $stmt_insert_model->bind_param("isi", $brand_id, $model_name, $year);
            $stmt_insert_model->execute();
            $model_id = $stmt_insert_model->insert_id;
            $stmt_insert_model->close();
        }
        $stmt->close();
        
        $stmt_insert_variant = $conn->prepare("INSERT INTO variants (model_id, trim_name, price, body_type, engine_cc, horsepower, fuel_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert_variant->bind_param("isdssss", $model_id, $trim_name, $price, $body_type, $engine_cc, $horsepower, $fuel_type);
        
        if ($stmt_insert_variant->execute()) {
            // 成功新增車輛
            $stmt_insert_variant->close();
            header("Location: manage_vehicles.php?success=1");
            exit();
        } else {
            // 新增失敗，顯示錯誤訊息
            $error = "新增車輛時出錯: " . $stmt_insert_variant->error;
            $stmt_insert_variant->close();
        }
    } elseif ($_POST['action'] === 'delete' && isset($_POST['variant_id'])) {
        $variant_id = intval($_POST['variant_id']);
        
        // 刪除車輛
        $stmt = $conn->prepare("DELETE FROM variants WHERE id = ?");
        $stmt->bind_param("i", $variant_id);
        if ($stmt->execute()) {
            // 成功刪除車輛
            $stmt->close();
            header("Location: manage_vehicles.php?success=2");
            exit();
        } else {
            // 刪除失敗，顯示錯誤訊息
            $error = "刪除車輛時出錯: " . $stmt->error;
            $stmt->close();
        }
    }
}

// 獲取所有品牌
$brands = [];
$sql_brands = "SELECT * FROM brands ORDER BY name ASC";
$result_brands = $conn->query($sql_brands);
if ($result_brands->num_rows > 0) {
    while ($row = $result_brands->fetch_assoc()) {
        $brands[] = $row;
    }
}

// 獲取所有車輛
$vehicles = [];
$sql_vehicles = "SELECT variants.*, models.model_name, models.year, brands.name as brand_name 
                FROM variants 
                JOIN models ON variants.model_id = models.id 
                JOIN brands ON models.brand_id = brands.id 
                ORDER BY brands.name ASC, models.model_name ASC, variants.trim_name ASC";
$result_vehicles = $conn->query($sql_vehicles);
if ($result_vehicles->num_rows > 0) {
    while ($row = $result_vehicles->fetch_assoc()) {
        $vehicles[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>管理車輛 - 管理員後台 - 汽車比較系統</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans TC', sans-serif;
            padding-top: 70px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 0;
            margin-top: 40px;
        }
        .table-responsive {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php
    $current_page = 'admin_dashboard';
    include 'navbar.php';
    ?>
    
    <div class="container mt-5 pt-5">
        <h1 class="mb-4">管理車輛</h1>
        
        <!-- 顯示成功訊息 -->
        <?php if (isset($_GET['success'])): ?>
            <?php if ($_GET['success'] == 1): ?>
                <div class="alert alert-success">車輛已成功新增。</div>
            <?php elseif ($_GET['success'] == 2): ?>
                <div class="alert alert-success">車輛已成功刪除。</div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- 顯示錯誤訊息 -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- 新增車輛表單 -->
        <div class="card mb-4">
            <div class="card-header">
                新增車輛
            </div>
            <div class="card-body">
                <form method="POST" action="manage_vehicles.php">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="brand_id" class="form-label">品牌</label>
                        <select class="form-select" id="brand_id" name="brand_id" required>
                            <option value="">選擇品牌</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?= htmlspecialchars($brand['id']) ?>"><?= htmlspecialchars($brand['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="model_name" class="form-label">車型名稱</label>
                        <input type="text" class="form-control" id="model_name" name="model_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="year" class="form-label">年份</label>
                        <input type="number" class="form-control" id="year" name="year" min="1900" max="2100" required>
                    </div>
                    <div class="mb-3">
                        <label for="trim_name" class="form-label">配置名稱</label>
                        <input type="text" class="form-control" id="trim_name" name="trim_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">價格 (萬)</label>
                        <input type="number" class="form-control" id="price" name="price" min="0" step="0.1" required>
                    </div>
                    <div class="mb-3">
                        <label for="body_type" class="form-label">車體類型</label>
                        <input type="text" class="form-control" id="body_type" name="body_type" required>
                    </div>
                    <div class="mb-3">
                        <label for="engine_cc" class="form-label">引擎排氣量 (cc)</label>
                        <!-- 修改輸入類型為 text，允許輸入字串 -->
                        <input type="text" class="form-control" id="engine_cc" name="engine_cc" required>
                    </div>
                    <div class="mb-3">
                        <label for="horsepower" class="form-label">馬力</label>
                        <!-- 修改輸入類型為 text，允許輸入字串 -->
                        <input type="text" class="form-control" id="horsepower" name="horsepower" required>
                    </div>
                    <div class="mb-3">
                        <label for="fuel_type" class="form-label">燃料類型</label>
                        <input type="text" class="form-control" id="fuel_type" name="fuel_type" required>
                    </div>
                    <button type="submit" class="btn btn-primary">新增車輛</button>
                </form>
            </div>
        </div>
        
        <!-- 車輛列表 -->
        <h2>所有車輛</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-secondary">
                    <tr>
                        <th>品牌</th>
                        <th>車型名稱</th>
                        <th>年份</th>
                        <th>配置名稱</th>
                        <th>價格 (萬)</th>
                        <th>車體類型</th>
                        <th>引擎排氣量 (cc)</th>
                        <th>馬力</th>
                        <th>燃料類型</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($vehicles) > 0): ?>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <tr>
                                <td><?= htmlspecialchars($vehicle['brand_name']) ?></td>
                                <td><?= htmlspecialchars($vehicle['model_name']) ?></td>
                                <td><?= htmlspecialchars($vehicle['year']) ?></td>
                                <td><?= htmlspecialchars($vehicle['trim_name']) ?></td>
                                
                                <!-- 處理價格為 0 的情況 -->
                                <td><?= ($vehicle['price'] == 0) ? '售價未公布' : htmlspecialchars($vehicle['price']) ?></td>
                                
                                <td><?= htmlspecialchars($vehicle['body_type']) ?></td>
                                <!-- *** 修改部分開始 *** -->
                                <!-- 引擎排氣量和馬力作為字串顯示 -->
                                <td><?= htmlspecialchars($vehicle['engine_cc']) ?></td>
                                <td><?= htmlspecialchars($vehicle['horsepower']) ?></td>
                                <!-- *** 修改部分結束 *** -->
                                <td><?= htmlspecialchars($vehicle['fuel_type']) ?></td>
                                
                                <td>
                                    <!-- 刪除按鈕 -->
                                    <form method="POST" action="manage_vehicles.php" style="display:inline;" onsubmit="return confirm('確定要刪除此車輛嗎？');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="variant_id" value="<?= htmlspecialchars($vehicle['id']) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">刪除</button>
                                    </form>
                                    <!-- 編輯按鈕可以類似實作 -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">目前沒有任何車輛資料。</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 汽車比較系統. 版權所有.</p>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
