<?php
// brands.php
include 'db_connection.php';
session_start();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>所有品牌 - 汽車比較系統</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC&display=swap" rel="stylesheet">
    <!-- 自訂 CSS -->
    <style>
        body {
            font-family: 'Noto Sans TC', sans-serif;
        }
        .brand-card {
            transition: transform 0.2s;
        }
        .brand-card:hover {
            transform: scale(1.05);
        }
        .brand-logo {
            height: 150px;
            object-fit: contain;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 0;
        }
    </style>
</head>
<body>
    <!-- 導航欄 -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
      <div class="container">
        <a class="navbar-brand" href="index.php">汽車比較系統</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="切換導航">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="index.php">首頁</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="compare_selection.php">開始比較</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="brands.php">所有品牌</a> <!-- 當前頁面 -->
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">關於我們</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">聯繫我們</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- 主要內容 -->
    <div class="container mt-5 pt-5">
        <h1 class="mb-4 text-center">所有品牌</h1>
        <div class="row">
            <?php
            // 獲取所有品牌
            $sql = "SELECT * FROM brands ORDER BY name ASC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($brand = $result->fetch_assoc()) {
                    echo "<div class='col-md-3 mb-4'>";
                    echo "<div class='card brand-card h-100'>";
                    
                    // 假設每個品牌有一個 logo 圖片，存放在 'images/brands/' 目錄，文件名為 brand_id.png
                    $logoPath = "images/brands/" . $brand['id'] . ".png";
                    if (!file_exists($logoPath)) {
                        $logoPath = "images/brands/default.jpg"; // 預設圖片
                    }

                    echo "<img src='" . htmlspecialchars($logoPath) . "' class='card-img-top brand-logo' alt='" . htmlspecialchars($brand['name']) . " Logo'>";
                    echo "<div class='card-body d-flex flex-column'>";
                    echo "<h5 class='card-title'>" . htmlspecialchars($brand['name']) . "</h5>";
                    // echo "<p class='card-text'>" . htmlspecialchars($brand['description']) . "</p>";
                    echo "<a href='brand_cars.php?brand_id=" . $brand['id'] . "' class='btn btn-primary mt-auto'>選擇品牌</a>"; // 修改為 brand_cars.php
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p>目前沒有任何品牌資料。</p>";
            }
            ?>
        </div>
    </div>

    <!-- 腳註 -->
    <footer class="footer mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 汽車比較系統. 版權所有.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
