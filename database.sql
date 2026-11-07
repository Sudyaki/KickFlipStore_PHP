-- ProjectWEB (PHP + MySQL)
-- Import in phpMyAdmin (XAMPP) or: mysql -u root < database.sql
CREATE DATABASE IF NOT EXISTS projectweb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projectweb;

CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(30)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120)  NOT NULL,
    category    VARCHAR(60)   NOT NULL,
    price       DECIMAL(10,2) NOT NULL,
    description TEXT,
    image       VARCHAR(255)  DEFAULT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cart_items (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    product_id INT NOT NULL,
    quantity   INT NOT NULL DEFAULT 1,
    UNIQUE KEY uq_user_product (user_id, product_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    total      DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    order_id     INT NOT NULL,
    product_id   INT NULL,
    product_name VARCHAR(120)  NOT NULL,
    price        DECIMAL(10,2) NOT NULL,
    quantity     INT NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Your shoe products. PRICES ARE PLACEHOLDERS: edit them in Admin > Products.
INSERT INTO products (name, category, price, description, image) VALUES
('Nike Vomero 5 Silver/Pink', 'Nike', 3499.00, 'Silver mesh upper with a pink midsole and black accents.', 'nike-vomero5-silver-pink.jpg'),
('Nike Vomero 5 Cream/Grey', 'Nike', 3499.00, 'Cream mesh upper with grey swoosh and dark sole.', 'nike-vomero5-cream-grey.jpg'),
('Nike Vomero 5 Black/White', 'Nike', 3499.00, 'All-black upper with a bold white swoosh and white sole.', 'nike-vomero5-black-white.jpg'),
('ASICS Court Shoe Black/Silver', 'ASICS', 3299.00, 'Black court shoe with silver stripes and a grippy white sole.', 'asics-court-black-silver.jpg'),
('ASICS Court Shoe White/Purple', 'ASICS', 3299.00, 'White court shoe with purple and peach accents.', 'asics-court-white-purple.jpg'),
('ASICS Court Shoe Blue/Green', 'ASICS', 3299.00, 'Teal blue upper with neon green stripes and midsole.', 'asics-court-blue-green.jpg'),
('ASICS Court Shoe White/Green/Pink', 'ASICS', 3299.00, 'Light white upper with neon green and pink stripes.', 'asics-court-white-green-pink.jpg'),
('ASICS Court Shoe Blue/Pink/Silver', 'ASICS', 3299.00, 'Blue and silver upper with pink stripes and purple heel tabs.', 'asics-court-blue-pink-silver.jpg'),
('Onitsuka Tiger Cream/Burgundy', 'Onitsuka Tiger', 2999.00, 'Cream leather sneaker with burgundy stripes and grey sole.', 'onitsuka-tiger-cream-burgundy.jpg'),
('Adidas Running Texas Tech Edition', 'Adidas', 3199.00, 'White running shoe with black and red stripes, Texas Tech edition.', 'adidas-running-texas-tech.jpg'),
('New Balance 530 White/Black', 'New Balance', 2899.00, 'Classic white mesh runner with black accents.', 'new-balance-530-white-black.jpg'),
('New Balance 530 All Black', 'New Balance', 2899.00, 'All-black mesh and leather runner with silver N logo.', 'new-balance-530-all-black.jpg'),
('On Cloudmonster Black', 'On Running', 3799.00, 'All-black cushioned runner with CloudTec sole.', 'on-cloudmonster-black.jpg'),
('On Cloudmonster White/Orange', 'On Running', 3799.00, 'White cushioned runner with orange band and CloudTec sole.', 'on-cloudmonster-white-orange.jpg');
