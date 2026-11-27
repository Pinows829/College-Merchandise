-- College Merchandise database
-- Import this file in phpMyAdmin to create merch_db and its starter data.

CREATE DATABASE IF NOT EXISTS merch_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE merch_db;

CREATE TABLE IF NOT EXISTS colleges (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(30) NOT NULL,
  name VARCHAR(150) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_colleges_code (code)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS courses (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  college_id INT UNSIGNED NOT NULL,
  name VARCHAR(180) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_courses_college_name (college_id, name),
  CONSTRAINT fk_courses_college FOREIGN KEY (college_id) REFERENCES colleges(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_id VARCHAR(40) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  college_id INT UNSIGNED NOT NULL,
  role ENUM('student','admin') NOT NULL DEFAULT 'student',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_student_id (student_id),
  UNIQUE KEY uq_users_email (email),
  CONSTRAINT fk_users_college FOREIGN KEY (college_id) REFERENCES colleges(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  college_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NULL,
  name VARCHAR(160) NOT NULL,
  category ENUM('Shirts','Jackets','Accessories') NOT NULL DEFAULT 'Shirts',
  description TEXT NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (id),
  KEY idx_products_name (name),
  KEY idx_products_college (college_id),
  KEY idx_products_course (course_id),
  CONSTRAINT fk_products_college FOREIGN KEY (college_id) REFERENCES colleges(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_products_course FOREIGN KEY (course_id) REFERENCES courses(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT chk_products_price CHECK (price >= 0)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS product_sizes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT UNSIGNED NOT NULL,
  size VARCHAR(30) NOT NULL,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_product_size (product_id, size),
  CONSTRAINT fk_sizes_product FOREIGN KEY (product_id) REFERENCES products(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  order_code VARCHAR(40) NULL,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  payment_method ENUM('GCash','Cash on Pickup') NOT NULL,
  gcash_ref VARCHAR(100) NOT NULL DEFAULT '',
  status ENUM('Pending','Paid','Preparing','Ready for Pickup','Completed') NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_orders_order_code (order_code),
  KEY idx_orders_user (user_id),
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT UNSIGNED NOT NULL,
  product_size_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_order_items_order (order_id),
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_order_items_size FOREIGN KEY (product_size_id) REFERENCES product_sizes(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO colleges (code, name) VALUES
  ('CCICT', 'College of Computing, Information and Communications Technology'),
  ('CAS', 'College of Arts and Sciences'),
  ('COE', 'College of Engineering'),
  ('COT', 'College of Technology'),
  ('COED', 'College of Education'),
  ('CME', 'College of Management and Entrepreneurship');

INSERT INTO products (college_id, name, category, description, price)
SELECT id, CONCAT(code, ' College Shirt'), 'Shirts', 'Comfortable college shirt for everyday wear.', 350.00
FROM colleges WHERE code IN ('CCICT','CAS','COE','COT','COED','CME');

INSERT INTO product_sizes (product_id, size, stock)
SELECT p.id, sizes.size, sizes.stock FROM products p
JOIN colleges c ON c.id=p.college_id
CROSS JOIN (SELECT 'S' size,10 stock UNION ALL SELECT 'M',10 UNION ALL SELECT 'L',10 UNION ALL SELECT 'XL',5) sizes
WHERE p.name=CONCAT(c.code, ' College Shirt');
