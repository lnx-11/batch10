USE ecommerce_db;

CREATE TABLE IF NOT EXISTS customer_orders (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  recipient_name VARCHAR(150) NOT NULL,
  address TEXT NOT NULL,
  phone VARCHAR(30) NOT NULL,
  total_price BIGINT UNSIGNED NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS customer_order_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id BIGINT UNSIGNED NOT NULL,
  product_id INT(11) NOT NULL,
  product_name VARCHAR(255) NOT NULL,
  unit_price INT(11) NOT NULL,
  quantity INT(11) NOT NULL,
  subtotal BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY order_id (order_id),
  KEY product_id (product_id),
  CONSTRAINT customer_order_items_order_fk
    FOREIGN KEY (order_id) REFERENCES customer_orders (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;