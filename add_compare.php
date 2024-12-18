<?php
// add_compare.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['variant_id']) && is_numeric($_POST['variant_id'])) {
        $variant_id = intval($_POST['variant_id']);
        
        // 初始化比較清單
        if (!isset($_SESSION['compare_list'])) {
            $_SESSION['compare_list'] = [];
        }
        
        // 記錄比較清單內容
        error_log("Current compare_list: " . implode(", ", $_SESSION['compare_list']));
        
        // 檢查是否已存在
        if (in_array($variant_id, $_SESSION['compare_list'])) {
            error_log("Variant ID $variant_id already exists in compare_list.");
            echo 'exists';
            exit();
        }
        
        // 檢查是否已達到限制
        if (count($_SESSION['compare_list']) >= 4) {
            error_log("Compare list limit reached.");
            echo 'limit';
            exit();
        }
        
        // 添加到比較清單
        $_SESSION['compare_list'][] = $variant_id;
        error_log("Added Variant ID $variant_id to compare_list.");
        echo 'success';
    } else {
        error_log("Invalid variant_id: " . json_encode($_POST['variant_id']));
        echo 'invalid';
    }
} else {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo 'invalid_request';
}
?>
