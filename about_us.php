<?php
// about_us.php
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>關於我們 - 汽車比較系統</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC&display=swap" rel="stylesheet">
    <!-- 自訂 CSS -->
    <style>
        body {
            font-family: 'Noto Sans TC', sans-serif;
            padding-top: 70px; /* 確保內容不被固定導航欄遮擋 */
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 0;
            margin-top: 40px;
        }
        .team-members {
            list-style-type: none;
            padding: 0;
        }
        .team-members li {
            background: #fff;
            margin: 5px 0;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
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
              <a class="nav-link" href="brands.php">所有品牌</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="about_us.php">關於我們</a> <!-- 新增的導航連結 -->
            </li>
            <!-- 移除「聯絡我們」連結 -->
          </ul>
        </div>
      </div>
    </nav>

    <!-- 主要內容 -->
    <div class="container mt-5 pt-5">
        <h1 class="mb-4 text-center">關於我們</h1>
        <div class="row">
            <div class="col-md-12">
                <h3>課程名稱</h3>
                <p>Introduction to Database Systems</p>

                <h3>專案名稱</h3>
                <p>Comprehensive Car Comparison Platform</p>

                <h3>團隊成員</h3>
                <ul class="team-members">
                    <li>蔡尚融</li>
                    <li>李羿昕</li>
                    <li>葉哲睿</li>
                    <li>林彥宇</li>
                    <li>鄭武謙</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 腳註 -->
    <footer class="footer">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 汽車比較系統. 版權所有.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
