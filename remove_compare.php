<?php
// remove_compare.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['variant_id']) && is_numeric($_POST['variant_id'])) {
        $variant_id = intval($_POST['variant_id']);
        
        if (isset($_SESSION['compare_list']) && in_array($variant_id, $_SESSION['compare_list'])) {
            // 移除該車款
            $_SESSION['compare_list'] = array_values(array_diff($_SESSION['compare_list'], [$variant_id]));
            echo 'success';
        } else {
            echo 'not_found';
        }
    } else {
        echo 'invalid';
    }
} else {
    echo 'invalid_request';
}
?>
