<?php
// index.php
include 'db_connection.php';

// 查詢所有品牌
$sql = "SELECT * FROM brands ORDER BY name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>汽車比較查詢系統</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>汽車比較查詢系統</h1>
    <!-- 在 index.php 的適當位置添加 -->
    <a href="compare_selection.php">開始比較</a>

    <h2>品牌列表</h2>
    <ul>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<li><a href='models.php?brand_id=" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</a></li>";
            }
        } else {
            echo "<li>沒有品牌資料</li>";
        }
        ?>
    </ul>
</body>
</html>

<?php
$conn->close();
?>
