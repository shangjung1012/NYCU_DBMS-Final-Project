<?php
// remove_favorite.php
session_start();
include 'db_connection.php';

// 檢查是否登入且為普通用戶
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    echo 'unauthorized';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['variant_id']) && is_numeric($_POST['variant_id'])) {
        $variant_id = intval($_POST['variant_id']);
        $user_id = $_SESSION['user_id'];
        
        // 刪除 favorites 表中的條目
        $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND variant_id = ?");
        $stmt->bind_param("ii", $user_id, $variant_id);
        if ($stmt->execute()) {
            // 從 session 的 favorites_list 中移除
            if (isset($_SESSION['favorites_list'])) {
                $_SESSION['favorites_list'] = array_values(array_diff($_SESSION['favorites_list'], [$variant_id]));
            }
            echo 'success';
        } else {
            echo 'error';
        }
        $stmt->close();
    } else {
        echo 'invalid';
    }
} else {
    echo 'invalid_request';
}

$conn->close();
?>
