<?php
// compare.php
include 'db_connection.php';
session_start();

if (!isset($_SESSION['compare_list']) || count($_SESSION['compare_list']) < 1) {
    echo "沒有選擇要比較的車輛。";
    exit;
}

$variant_ids = array_map('intval', $_SESSION['compare_list']);
$placeholders = implode(',', array_fill(0, count($variant_ids), '?'));

// 構建查詢
$query = "SELECT variants.*, models.model_name, models.year, brands.name as brand_name 
          FROM variants 
          JOIN models ON variants.model_id = models.id 
          JOIN brands ON models.brand_id = brands.id 
          WHERE variants.id IN ($placeholders)";

$stmt = $conn->prepare($query);

// 動態綁定參數
$types = str_repeat('i', count($variant_ids));
$stmt->bind_param($types, ...$variant_ids);
$stmt->execute();
$result = $stmt->get_result();

// 獲取所有選擇的車輛
$variants = [];
while($row = $result->fetch_assoc()) {
    $variants[] = $row;
}
$stmt->close();

// 清空比較列表
// $_SESSION['compare_list'] = [];
?>

<?php if (count($variants) > 0): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>品牌</th>
                <th>車系</th>
                <th>配置名稱</th>
                <th>價格 (萬)</th>
                <th>車體類型</th>
                <th>引擎排氣量</th>
                <th>馬力</th>
                <th>燃料類型</th>
                <th>詳細資訊</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($variants as $variant): ?>
                <tr>
                    <td><?= htmlspecialchars($variant['brand_name']) ?></td>
                    <td><?= htmlspecialchars($variant['model_name']) ?> (<?= htmlspecialchars($variant['year']) ?>)</td>
                    <td><?= htmlspecialchars($variant['trim_name']) ?></td>
                    <td><?= htmlspecialchars($variant['price']) ?></td>
                    <td><?= htmlspecialchars($variant['body_type']) ?></td>
                    <td><?= htmlspecialchars($variant['engine_cc']) ?></td>
                    <td><?= htmlspecialchars($variant['horsepower']) ?></td>
                    <td><?= htmlspecialchars($variant['fuel_type']) ?></td>
                    <td><a href="<?= htmlspecialchars($variant['url']) ?>" target="_blank">查看詳情</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>沒有選擇的車輛資料。</p>
<?php endif; ?>
