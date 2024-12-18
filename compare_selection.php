<?php
// compare_selection.php
include 'db_connection.php';
session_start();

// 初始化比較列表
if (!isset($_SESSION['compare_list'])) {
    $_SESSION['compare_list'] = [];
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>汽車比較查詢系統 - 選擇比較車款</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- 自訂 CSS -->
    <link rel="stylesheet" href="styles.css">
    <style>
        /* 自訂樣式 */
        .compare-list ul {
            list-style-type: none;
            padding: 0;
        }
        .compare-list li {
            background: #fff;
            margin: 5px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .compare-button {
            padding: 10px 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">汽車比較查詢系統</h1>
        <a href="index.php" class="btn btn-secondary mb-4">返回首頁</a>
        <h2>選擇車款進行比較</h2>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="brand" class="form-label">車廠：</label>
                <select id="brand" class="form-select">
                    <option value="">-- 選擇車廠 --</option>
                    <?php
                    // 載入所有品牌
                    $sql = "SELECT * FROM brands ORDER BY name ASC";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="series" class="form-label">車系：</label>
                <select id="series" class="form-select" disabled>
                    <option value="">-- 選擇車系 --</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="model" class="form-label">車款：</label>
                <select id="model" class="form-select" disabled>
                    <option value="">-- 選擇車款 --</option>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <button id="addToCompare" class="btn btn-primary" disabled>加入比較</button>
        </div>

        <div class="compare-list">
            <h3>已選擇的車輛（最多四輛）</h3>
            <ul id="compareList" class="list-group">
                <?php
                if (!empty($_SESSION['compare_list'])) {
                    foreach ($_SESSION['compare_list'] as $variant_id) {
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

                        echo "<li class='list-group-item d-flex justify-content-between align-items-center' data-id='" . $variant_id . "'>";
                        echo htmlspecialchars($variant['brand_name']) . " " . htmlspecialchars($variant['model_name']) . " (" . htmlspecialchars($variant['year']) . ") - " . htmlspecialchars($variant['trim_name']);
                        echo "<button class='btn btn-danger btn-sm remove-btn' data-id='" . $variant_id . "'>移除</button>";
                        echo "</li>";
                    }
                }
                ?>
            </ul>
            <button id="compareButton" class="btn btn-success mt-3" <?php echo (count($_SESSION['compare_list']) < 1) ? 'disabled' : ''; ?>>開始比較</button>
        </div>
    </div>

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // 動態載入車系
            $('#brand').change(function() {
                var brandId = $(this).val();
                $('#series').html('<option value="">-- 選擇車系 --</option>');
                $('#model').html('<option value="">-- 選擇車款 --</option>');
                $('#series').prop('disabled', true);
                $('#model').prop('disabled', true);
                $('#addToCompare').prop('disabled', true);

                if (brandId) {
                    $.ajax({
                        url: 'get_series.php',
                        type: 'GET',
                        data: { brand_id: brandId },
                        success: function(response) {
                            $('#series').html(response);
                            $('#series').prop('disabled', false);
                        },
                        error: function() {
                            alert("載入車系時出現錯誤，請稍後再試。");
                        }
                    });
                }
            });

            // 動態載入車款
            $('#series').change(function() {
                var seriesId = $(this).val();
                $('#model').html('<option value="">-- 選擇車款 --</option>');
                $('#model').prop('disabled', true);
                $('#addToCompare').prop('disabled', true);

                if (seriesId) {
                    $.ajax({
                        url: 'get_models.php',
                        type: 'GET',
                        data: { series_id: seriesId },
                        success: function(response) {
                            $('#model').html(response);
                            $('#model').prop('disabled', false);
                        },
                        error: function() {
                            alert("載入車款時出現錯誤，請稍後再試。");
                        }
                    });
                }
            });

            // 啟用加入比較按鈕
            $('#model').change(function() {
                var modelId = $(this).val();
                if (modelId) {
                    $('#addToCompare').prop('disabled', false);
                } else {
                    $('#addToCompare').prop('disabled', true);
                }
            });

            // 加入比較
            $('#addToCompare').click(function() {
                var variantId = $('#model').val();
                if (!variantId) return;

                // 檢查是否已達到四輛
                if ($('#compareList li').length >= 4) {
                    alert("最多只能比較四輛車。");
                    return;
                }

                // 檢查是否已選擇
                var exists = $('#compareList li[data-id="' + variantId + '"]').length > 0;
                if (exists) {
                    alert("此車款已加入比較列表。");
                    return;
                }

                // 添加到比較列表
                $.ajax({
                    url: 'add_compare.php',
                    type: 'POST',
                    data: { variant_id: variantId },
                    success: function(response) {
                        if (response === 'success') {
                            // 獲取車輛詳細資料並添加到列表
                            $.ajax({
                                url: 'get_variant.php',
                                type: 'GET',
                                data: { variant_id: variantId },
                                success: function(data) {
                                    $('#compareList').append(data);
                                    updateCompareButton();
                                },
                                error: function() {
                                    alert("載入車輛資料時出現錯誤。");
                                }
                            });
                        } else if (response === 'limit') {
                            alert("最多只能比較四輛車。");
                        } else if (response === 'exists') {
                            alert("此車款已加入比較列表。");
                        }
                    },
                    error: function() {
                        alert("加入比較時出現錯誤，請稍後再試。");
                    }
                });
            });

            // 移除比較車輛
            $(document).on('click', '.remove-btn', function() {
                var variantId = $(this).data('id');
                $.ajax({
                    url: 'remove_compare.php',
                    type: 'POST',
                    data: { variant_id: variantId },
                    success: function(response) {
                        if (response === 'success') {
                            $('li[data-id="' + variantId + '"]').remove();
                            updateCompareButton();
                        }
                    },
                    error: function() {
                        alert("移除車輛時出現錯誤，請稍後再試。");
                    }
                });
            });

            // 更新比較按鈕狀態
            function updateCompareButton() {
                if ($('#compareList li').length > 0) {
                    $('#compareButton').prop('disabled', false);
                } else {
                    $('#compareButton').prop('disabled', true);
                }
            }

            // 開始比較
            $('#compareButton').click(function() {
                window.location.href = 'compare.php';
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
