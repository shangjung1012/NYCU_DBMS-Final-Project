# NYCU_DBMS-Final-Project

git clone --recurse-submodules https://github.com/shangjung1012/NYCU_DBMS-Final-Project.git

```
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
  FOREIGN KEY (brand_id) REFERENCES brands`(id`) ON DELETE CASCADE
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
  FOREIGN KEY (model_id) REFERENCES models`(id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- SHA-256 hash 長度為 64
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


// not use
CREATE TABLE series (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE
);

```
