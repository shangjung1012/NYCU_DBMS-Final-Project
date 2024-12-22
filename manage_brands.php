<?php
// manage_brands.php
session_start();

// 檢查用戶是否登入且為管理員
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';

// 初始化錯誤和成功訊息
$error = '';
$success = '';

// 處理新增、編輯和刪除品牌的請求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF 保護
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "無效的 CSRF 令牌。";
    } else {
        // 新增品牌
        if (isset($_POST['action']) && $_POST['action'] === 'add') {
            $brand_name = trim($_POST['brand_name']);
            
            if (empty($brand_name)) {
                $error = "品牌名稱不可為空。";
            } else {
                // 插入新品牌
                $stmt = $conn->prepare("INSERT INTO brands (name) VALUES (?)");
                $stmt->bind_param("s", $brand_name);
                
                if ($stmt->execute()) {
                    $success = "品牌 '$brand_name' 已成功新增。";
                } else {
                    if ($conn->errno === 1062) { // Duplicate entry
                        $error = "品牌名稱 '$brand_name' 已存在。";
                    } else {
                        $error = "新增品牌時出錯：" . $stmt->error;
                    }
                }
                $stmt->close();
            }
        }
        
        // 編輯品牌
        if (isset($_POST['action']) && $_POST['action'] === 'edit') {
            $brand_id = intval($_POST['brand_id']);
            $brand_name = trim($_POST['brand_name']);
            
            if (empty($brand_name)) {
                $error = "品牌名稱不可為空。";
            } else {
                // 更新品牌名稱
                $stmt = $conn->prepare("UPDATE brands SET name = ? WHERE id = ?");
                $stmt->bind_param("si", $brand_name, $brand_id);
                
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $success = "品牌已成功更新為 '$brand_name'。";
                    } else {
                        $error = "沒有任何變更。";
                    }
                } else {
                    if ($conn->errno === 1062) { // Duplicate entry
                        $error = "品牌名稱 '$brand_name' 已存在。";
                    } else {
                        $error = "更新品牌時出錯：" . $stmt->error;
                    }
                }
                $stmt->close();
            }
        }
        
        // 刪除品牌
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $brand_id = intval($_POST['brand_id']);
            
            // 刪除品牌
            $stmt = $conn->prepare("DELETE FROM brands WHERE id = ?");
            $stmt->bind_param("i", $brand_id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $success = "品牌已成功刪除。";
                } else {
                    $error = "品牌不存在或已被刪除。";
                }
            } else {
                $error = "刪除品牌時出錯：" . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// 生成新的 CSRF 令牌
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// 獲取所有品牌
$brands = [];
$sql = "SELECT * FROM brands ORDER BY name ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $brands[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>管理品牌 - 管理員後台 - 汽車比較系統</title>
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
        <h1 class="mb-4">管理品牌</h1>
        
        <!-- 顯示錯誤和成功訊息 -->
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <!-- 新增品牌表單 -->
        <div class="card mb-4">
            <div class="card-header">
                新增品牌
            </div>
            <div class="card-body">
                <form method="POST" action="manage_brands.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="brand_name" class="form-label">品牌名稱</label>
                        <input type="text" class="form-control" id="brand_name" name="brand_name" required>
                    </div>
                    <button type="submit" class="btn btn-primary">新增品牌</button>
                </form>
            </div>
        </div>
        
        <!-- 品牌列表 -->
        <h2>所有品牌</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-secondary">
                    <tr>
                        <th>ID</th>
                        <th>品牌名稱</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($brands) > 0): ?>
                        <?php foreach ($brands as $brand): ?>
                            <tr>
                                <td><?= htmlspecialchars($brand['id']) ?></td>
                                <td><?= htmlspecialchars($brand['name']) ?></td>
                                <td>
                                    <!-- 編輯按鈕 -->
                                    <button class="btn btn-warning btn-sm edit-btn" data-id="<?= htmlspecialchars($brand['id']) ?>" data-name="<?= htmlspecialchars($brand['name']) ?>">編輯</button>
                                    
                                    <!-- 刪除按鈕 -->
                                    <form method="POST" action="manage_brands.php" style="display:inline;" onsubmit="return confirm('確定要刪除這個品牌嗎？這將刪除所有相關的車型和車輛。');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="brand_id" value="<?= htmlspecialchars($brand['id']) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">刪除</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">目前沒有任何品牌資料。</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- 編輯品牌模態框 -->
        <div class="modal fade" id="editBrandModal" tabindex="-1" aria-labelledby="editBrandModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form method="POST" action="manage_brands.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editBrandModalLabel">編輯品牌</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="關閉"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" id="edit_brand_id" name="brand_id" value="">
                        <div class="mb-3">
                            <label for="edit_brand_name" class="form-label">品牌名稱</label>
                            <input type="text" class="form-control" id="edit_brand_name" name="brand_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary">更新品牌</button>
                    </div>
                </div>
            </form>
          </div>
        </div>
        
    </div>
    
    <!-- Footer -->
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
            // 當點擊編輯按鈕時，填充模態框並顯示
            $('.edit-btn').click(function() {
                var brandId = $(this).data('id');
                var brandName = $(this).data('name');
                
                $('#edit_brand_id').val(brandId);
                $('#edit_brand_name').val(brandName);
                
                $('#editBrandModal').modal('show');
            });
        });
    </script>
</body>
</html>