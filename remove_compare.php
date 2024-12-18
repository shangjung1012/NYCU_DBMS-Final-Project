<?php
// remove_compare.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['variant_id']) && is_numeric($_POST['variant_id'])) {
    $variant_id = intval($_POST['variant_id']);

    if (($key = array_search($variant_id, $_SESSION['compare_list'])) !== false) {
        unset($_SESSION['compare_list'][$key]);
        // 重新索引陣列
        $_SESSION['compare_list'] = array_values($_SESSION['compare_list']);
        echo 'success';
    }
}
?>
