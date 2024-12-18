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
```
