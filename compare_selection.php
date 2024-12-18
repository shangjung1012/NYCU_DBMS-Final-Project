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
    <link rel="stylesheet" href="styles.css">
    <style>
        /* 自訂樣式 */
        .selection-container {
            margin: 20px 0;
        }
        .compare-list {
            margin: 20px 0;
        }
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
        }
        .compare-button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .compare-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <h1>汽車比較查詢系統</h1>
    <a href="index.php">返回首頁</a>
    <h2>選擇車款進行比較</h2>

    <div class="selection-container">
        <label for="brand">車廠：</label>
        <select id="brand">
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

    <div class="selection-container">
        <label for="series">車系：</label>
        <select id="series" disabled>
            <option value="">-- 選擇車系 --</option>
        </select>
    </div>

    <div class="selection-container">
        <label for="model">車款：</label>
        <select id="model" disabled>
            <option value="">-- 選擇車款 --</option>
        </select>
    </div>

    <div class="selection-container">
        <button id="addToCompare" class="compare-button" disabled>加入比較</button>
    </div>

    <div class="compare-list">
        <h3>已選擇的車輛（最多四輛）</h3>
        <ul id="compareList">
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

                    echo "<li data-id='" . $variant_id . "'>";
                    echo htmlspecialchars($variant['brand_name']) . " " . htmlspecialchars($variant['model_name']) . " (" . htmlspecialchars($variant['year']) . ") - " . htmlspecialchars($variant['trim_name']);
                    echo " <button class='remove-btn' data-id='" . $variant_id . "'>移除</button>";
                    echo "</li>";
                }
            }
            ?>
        </ul>
        <button id="compareButton" class="compare-button" <?php echo (count($_SESSION['compare_list']) < 1) ? 'disabled' : ''; ?>>開始比較</button>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                                }
                            });
                        } else if (response === 'limit') {
                            alert("最多只能比較四輛車。");
                        } else if (response === 'exists') {
                            alert("此車款已加入比較列表。");
                        }
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
