<?php
// compare_selection.php
include 'db_connection.php';
session_start();

// 初始化比較清單
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
            border: 1px solid #dee2e6;
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

        <!-- 品牌、車系、車款選擇 -->
        <div class="row mb-3">
            <div class="col-md-4 mb-3 mb-md-0">
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

            <div class="col-md-4 mb-3 mb-md-0">
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

        <!-- 加入比較按鈕 -->
        <div class="mb-4">
            <button id="addToCompare" class="btn btn-primary w-100" disabled>加入比較</button>
        </div>

        <!-- 比較清單和重製按鈕 -->
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

                        // 判斷價格是否為 0
                        if ($variant['price'] == 0) {
                            echo " - 售價未公布";
                        } else {
                            echo " - " . htmlspecialchars($variant['price']) . " 萬";
                        }

                        echo "<button class='btn btn-danger btn-sm remove-btn' data-id='" . $variant_id . "'>移除</button>";
                        echo "</li>";
                    }
                }
                ?>
            </ul>
            <div class="d-flex justify-content-between mt-3">
                <button id="compareButton" class="btn btn-success" <?php echo (count($_SESSION['compare_list']) < 1) ? 'disabled' : ''; ?>>開始比較</button>
                <button id="resetCompareList" class="btn btn-warning" <?php echo (count($_SESSION['compare_list']) < 1) ? 'disabled' : ''; ?>>重製比較</button>
            </div>
        </div>
    </div>

    <!-- 載入指示器 -->
    <div id="loading" class="d-none text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">載入中...</span>
        </div>
    </div>

    <!-- Bootstrap JS 和 jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // 載入車系
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
                        beforeSend: function() {
                            $('#loading').removeClass('d-none');
                        },
                        success: function(response) {
                            $('#series').html(response);
                            $('#series').prop('disabled', false);
                        },
                        error: function() {
                            alert("載入車系時出現錯誤，請稍後再試。");
                        },
                        complete: function() {
                            $('#loading').addClass('d-none');
                        }
                    });
                }
            });

            // 載入車款
            $('#series').change(function() {
                var seriesId = $(this).val();
                $('#model').html('<option value="">-- 選擇車款 --</option>');
                $('#model').prop('disabled', true);
                $('#addToCompare').prop('disabled', true);

                if (seriesId) {
                    $.ajax({
                        url: 'get_models.php',
                        type: 'GET',
                        data: { 
                            series_id: seriesId,
                            min_price: $('#minPrice').val(), // 如果有篩選條件
                            max_price: $('#maxPrice').val()  // 如果有篩選條件
                        },
                        beforeSend: function() {
                            $('#loading').removeClass('d-none');
                        },
                        success: function(response) {
                            $('#model').html(response);
                            $('#model').prop('disabled', false);
                        },
                        error: function() {
                            alert("載入車款時出現錯誤，請稍後再試。");
                        },
                        complete: function() {
                            $('#loading').addClass('d-none');
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

                // 添加到比較清單
                $.ajax({
                    url: 'add_compare.php',
                    type: 'POST',
                    data: { variant_id: variantId },
                    beforeSend: function() {
                        $('#loading').removeClass('d-none');
                    },
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
                                    alert("車輛已成功加入比較列表。");
                                },
                                error: function() {
                                    alert("載入車輛資料時出現錯誤。");
                                }
                            });
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
                    },
                    complete: function() {
                        $('#loading').addClass('d-none');
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
                        } else {
                            alert("移除車輛時出現錯誤。");
                        }
                    },
                    error: function() {
                        alert("移除車輛時出現錯誤，請稍後再試。");
                    }
                });
            });

            // 重製比較清單
            $('#resetCompareList').click(function() {
                // 顯示確認對話框
                if (confirm("確定要重製比較清單嗎？這將移除所有已選擇的車輛。")) {
                    $.ajax({
                        url: 'reset_compare.php',
                        type: 'POST',
                        beforeSend: function() {
                            $('#loading').removeClass('d-none');
                        },
                        success: function(response) {
                            if (response === 'success') {
                                $('#compareList').empty();
                                updateCompareButton();
                                alert("比較清單已成功重製。");
                            } else {
                                alert("重製比較清單時出現錯誤，請稍後再試。");
                            }
                        },
                        error: function() {
                            alert("重製比較清單時出現錯誤，請稍後再試。");
                        },
                        complete: function() {
                            $('#loading').addClass('d-none');
                        }
                    });
                }
            });

            // 更新比較按鈕狀態
            function updateCompareButton() {
                var compareCount = $('#compareList li').length;
                if (compareCount > 0) {
                    $('#compareButton').prop('disabled', false);
                    $('#resetCompareList').prop('disabled', false);
                } else {
                    $('#compareButton').prop('disabled', true);
                    $('#resetCompareList').prop('disabled', true);
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