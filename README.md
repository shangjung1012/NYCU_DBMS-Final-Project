# NYCU_DBMS-Final-Project

## 1. 專案概述
**NYCU_DBMS-Final-Project** 是一個基於 PHP 和 MySQL 的汽車比較系統，允許用戶查看各品牌的車型，進行比較，並將喜愛的車輛加入收藏列表。專案包括用戶認證、資料管理、篩選與排序等功能。


## 2. 環境準備
### 安裝必備軟體
1. **XAMPP**：包含 Apache、MySQL、PHP 等。
2. **Git**：用於複製 GitHub 倉庫。
3. **網頁瀏覽器**：如 Chrome、Firefox 等。

### 安裝指導
- **XAMPP**：
  - 前往 [XAMPP 官方網站](https://www.apachefriends.org/) 下載適用於你系統的版本。
  - 安裝後啟動 Apache 和 MySQL 服務。
- **Git**：
  - 前往 [Git 官方網站](https://git-scm.com/) 下載適用於你系統的版本。
  - 安裝並確認 `git` 指令可正常使用。

## 3. 複製 GitHub 倉庫
在下列路徑
```
C:\xampp\htdocs\
```
在終端機執行以下指令複製專案：
```bash
git clone --recurse-submodules https://github.com/shangjung1012/NYCU_DBMS-Final-Project.git
```

## 4. 建立和組態資料庫
### 創建資料庫
開啟 phpMyAdmin，登入後選擇 New 建立新的資料庫。
在 Database name 欄位輸入 car_data，選擇編碼為 utf8mb4_general_ci，點擊 Create。

### 創建資料表
執行以下 SQL 語句來創建所需資料表: http://localhost/phpmyadmin/

```sql
CREATE TABLE brands (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE models (
  id INT AUTO_INCREMENT PRIMARY KEY,
  brand_id INT NOT NULL,
  model_name VARCHAR(255) NOT NULL,
  year INT NOT NULL,
  price_range VARCHAR(50),
  url VARCHAR(255),
  FOREIGN KEY (brand_id) REFERENCES `brands`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE variants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  model_id INT NOT NULL,
  trim_name VARCHAR(255) NOT NULL,
  price DECIMAL(10,2),
  body_type VARCHAR(50),
  engine_cc VARCHAR(50),
  horsepower VARCHAR(50),
  fuel_type VARCHAR(50),
  FOREIGN KEY (model_id) REFERENCES `models`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user'
);


CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    variant_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES variants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, variant_id)
);
```

## 5. 導入資料
### 導入車輛資料
訪問以下 URL 來導入車輛資料：
```bash
http://localhost/NYCU_DBMS-Final-Project/import_data.php
```

### 設置管理員帳號
訪問以下 URL 創建預設管理員帳號：
```bash
http://localhost/NYCU_DBMS-Final-Project/admin_setup.php
```
