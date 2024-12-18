<?php
// add_compare.php
session_start();

if (!isset($_SESSION['compare_list'])) {
    $_SESSION['compare_list'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['variant_id']) && is_numeric($_POST['variant_id'])) {
    $variant_id = intval($_POST['variant_id']);

    // 檢查是否已存在
    if (in_array($variant_id, $_SESSION['compare_list'])) {
        echo 'exists';
        exit;
    }

    // 檢查是否已達到四輛
    if (count($_SESSION['compare_list']) >= 4) {
        echo 'limit';
        exit;
    }

    // 添加到比較列表
    $_SESSION['compare_list'][] = $variant_id;
    echo 'success';
}
?>
