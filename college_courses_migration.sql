-- Run once in phpMyAdmin to add the requested colleges and course collections
-- to an existing merch_db without deleting existing users, products, or orders.
USE merch_db;

INSERT INTO colleges (code, name) VALUES
  ('CCICT', 'College of Computing, Information and Communications Technology'),
  ('CAS', 'College of Arts and Sciences'),
  ('COE', 'College of Engineering'),
  ('COT', 'College of Technology'),
  ('COED', 'College of Education'),
  ('CME', 'College of Management and Entrepreneurship')
ON DUPLICATE KEY UPDATE name=VALUES(name);

CREATE TABLE IF NOT EXISTS courses (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  college_id INT UNSIGNED NOT NULL,
  name VARCHAR(180) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_courses_college_name (college_id, name),
  CONSTRAINT fk_courses_college FOREIGN KEY (college_id) REFERENCES colleges(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Add course_id only when upgrading an existing products table.
SET @migration_sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products' AND COLUMN_NAME='course_id') = 0,
  'ALTER TABLE products ADD COLUMN course_id INT UNSIGNED NULL',
  'SELECT 1'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @migration_sql = IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS
   WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products' AND INDEX_NAME='idx_products_course') = 0,
  'ALTER TABLE products ADD INDEX idx_products_course (course_id)',
  'SELECT 1'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @migration_sql = IF(
  (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
   WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='products' AND CONSTRAINT_NAME='fk_products_course') = 0,
  'ALTER TABLE products ADD CONSTRAINT fk_products_course FOREIGN KEY (course_id) REFERENCES courses(id) ON UPDATE CASCADE ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

INSERT INTO courses (college_id, name)
SELECT c.id, catalog.course_name
FROM colleges c
JOIN (
  SELECT 'CCICT' college_code, 'Bachelor of Science in Information Systems (BSIS)' course_name UNION ALL
  SELECT 'CCICT', 'Bachelor of Science in Information Technology (BSIT)' UNION ALL
  SELECT 'CCICT', 'Bachelor in Industrial Technology – Computer Technology (BIT-CT)' UNION ALL
  SELECT 'CAS', 'Bachelor of Science in Psychology (BS Psychology)' UNION ALL
  SELECT 'CAS', 'Bachelor of Science in Mathematics (BS Math)' UNION ALL
  SELECT 'CAS', 'Bachelor of Science in Statistics (BS Stat)' UNION ALL
  SELECT 'CAS', 'Bachelor of Science in Development Communication (BSDevCom)' UNION ALL
  SELECT 'CAS', 'Bachelor of Arts in English Language (BAEL)' UNION ALL
  SELECT 'CAS', 'Bachelor of Arts in Literature (BAL)' UNION ALL
  SELECT 'CAS', 'Batsilyer ng Sining sa Filipino (BAF)' UNION ALL
  SELECT 'COE', 'Bachelor of Science in Civil Engineering (BSCE)' UNION ALL
  SELECT 'COE', 'Bachelor of Science in Computer Engineering (BSCpE)' UNION ALL
  SELECT 'COE', 'Bachelor of Science in Electronics Engineering (BSECE)' UNION ALL
  SELECT 'COE', 'Bachelor of Science in Electrical Engineering (BSEE)' UNION ALL
  SELECT 'COE', 'Bachelor of Science in Industrial Engineering (BSIE)' UNION ALL
  SELECT 'COE', 'Bachelor of Science in Mechanical Engineering (BSME)' UNION ALL
  SELECT 'COT', 'Bachelor of Science in Mechatronics (BSMx)' UNION ALL
  SELECT 'COT', 'Bachelor of Science in Graphics and Design (BSGD)' UNION ALL
  SELECT 'COT', 'Bachelor of Science in Technology Management (BSTechM)' UNION ALL
  SELECT 'COT', 'Bachelor of Industrial Technology – Automotive Technology' UNION ALL
  SELECT 'COT', 'Bachelor of Industrial Technology – Civil Technology' UNION ALL
  SELECT 'COT', 'Bachelor of Industrial Technology – Electrical Technology' UNION ALL
  SELECT 'COT', 'Bachelor of Industrial Technology – Electronics Technology' UNION ALL
  SELECT 'COT', 'Bachelor of Industrial Technology – Drafting Technology' UNION ALL
  SELECT 'COED', 'Bachelor in Elementary Education (BEEd)' UNION ALL
  SELECT 'COED', 'Bachelor in Early Childhood Education (BECEd)' UNION ALL
  SELECT 'COED', 'Bachelor in Special Needs Education (BSNEd)' UNION ALL
  SELECT 'COED', 'Bachelor of Secondary Education – Mathematics (BSEd-Math)' UNION ALL
  SELECT 'COED', 'Bachelor of Secondary Education – Science (BSEd-Science)' UNION ALL
  SELECT 'COED', 'Bachelor of Secondary Education – English (BSEd-English)' UNION ALL
  SELECT 'COED', 'Bachelor of Secondary Education – Filipino (BSEd-Filipino)' UNION ALL
  SELECT 'COED', 'Bachelor of Secondary Education – Values Education (BSEd-Values Ed)' UNION ALL
  SELECT 'CME', 'Bachelor of Public Administration (BPA)' UNION ALL
  SELECT 'CME', 'Bachelor of Science in Hospitality Management (BSHM)' UNION ALL
  SELECT 'CME', 'Bachelor of Science in Business Administration – Marketing Management (BSBA-MM)' UNION ALL
  SELECT 'CME', 'Bachelor of Science in Tourism Management (BSTM)'
) catalog ON catalog.college_code=c.code
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- BSCS and Data Science were omitted because they were marked unverified.
