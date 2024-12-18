<!-- navbar.php -->
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
          <a class="nav-link <?php echo ($current_page == 'home') ? 'active' : ''; ?>" href="index.php">首頁</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page == 'compare') ? 'active' : ''; ?>" href="compare_selection.php">開始比較</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page == 'brands') ? 'active' : ''; ?>" href="brands.php">所有品牌</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page == 'about_us') ? 'active' : ''; ?>" href="about_us.php">關於我們</a>
        </li>
        <!-- 移除「聯絡我們」連結 -->
      </ul>
    </div>
  </div>
</nav>
