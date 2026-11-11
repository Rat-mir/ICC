-- ICC: физическая модель MySQL 8.4. Запускать в отдельной пустой БД.
-- Служебные таблицы Laravel включены для сопоставления с миграциями.
SET NAMES utf8mb4;
CREATE TABLE users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL,
 email_verified_at TIMESTAMP NULL, password VARCHAR(255) NOT NULL,
 remember_token VARCHAR(100) NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
 role VARCHAR(20) NOT NULL DEFAULT 'user', is_blocked BOOLEAN NOT NULL DEFAULT FALSE,
 UNIQUE KEY users_email_unique(email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE password_reset_tokens (email VARCHAR(255) PRIMARY KEY,token VARCHAR(255) NOT NULL,created_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE sessions (
 id VARCHAR(255) PRIMARY KEY,user_id BIGINT UNSIGNED NULL,ip_address VARCHAR(45) NULL,user_agent TEXT NULL,payload LONGTEXT NOT NULL,last_activity INT NOT NULL,
 KEY sessions_user_id_index(user_id),KEY sessions_last_activity_index(last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE personal_access_tokens (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,tokenable_type VARCHAR(255) NOT NULL,tokenable_id BIGINT UNSIGNED NOT NULL,
 name TEXT NOT NULL,token VARCHAR(64) NOT NULL,abilities TEXT NULL,last_used_at TIMESTAMP NULL,expires_at TIMESTAMP NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,
 UNIQUE KEY personal_access_tokens_token_unique(token),KEY personal_access_tokens_tokenable_type_tokenable_id_index(tokenable_type,tokenable_id),
 KEY personal_access_tokens_expires_at_index(expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE makers (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NOT NULL,reviewed_by BIGINT UNSIGNED NULL,
 title VARCHAR(255) NOT NULL,description TEXT NOT NULL,status VARCHAR(20) NOT NULL DEFAULT 'draft',review_comment TEXT NULL,
 submitted_at DATETIME NULL,reviewed_at DATETIME NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,
 CONSTRAINT makers_user_id_foreign FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE RESTRICT,
 CONSTRAINT makers_reviewed_by_foreign FOREIGN KEY(reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
 KEY makers_status_index(status),KEY makers_title_index(title),KEY makers_user_id_status_index(user_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE assets (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,maker_id BIGINT UNSIGNED NOT NULL,name VARCHAR(255) NOT NULL,category VARCHAR(30) NOT NULL,
 file_path VARCHAR(255) NOT NULL,layer_order INT UNSIGNED NOT NULL DEFAULT 0,is_active BOOLEAN NOT NULL DEFAULT TRUE,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,
 CONSTRAINT assets_maker_id_foreign FOREIGN KEY(maker_id) REFERENCES makers(id) ON DELETE CASCADE,
 KEY assets_category_index(category),KEY assets_maker_id_layer_order_index(maker_id,layer_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE characters (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NOT NULL,maker_id BIGINT UNSIGNED NOT NULL,name VARCHAR(255) NOT NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,
 CONSTRAINT characters_user_id_foreign FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE RESTRICT,
 CONSTRAINT characters_maker_id_foreign FOREIGN KEY(maker_id) REFERENCES makers(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE asset_character (
 asset_id BIGINT UNSIGNED NOT NULL,character_id BIGINT UNSIGNED NOT NULL,layer_order INT UNSIGNED NOT NULL DEFAULT 0,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,
 PRIMARY KEY(asset_id,character_id),
 CONSTRAINT asset_character_asset_id_foreign FOREIGN KEY(asset_id) REFERENCES assets(id) ON DELETE CASCADE,
 CONSTRAINT asset_character_character_id_foreign FOREIGN KEY(character_id) REFERENCES characters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE posts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NOT NULL,character_id BIGINT UNSIGNED NULL,title VARCHAR(255) NOT NULL,image_path VARCHAR(255) NOT NULL,
 is_visible BOOLEAN NOT NULL DEFAULT TRUE,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,
 CONSTRAINT posts_user_id_foreign FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE RESTRICT,
 CONSTRAINT posts_character_id_foreign FOREIGN KEY(character_id) REFERENCES characters(id) ON DELETE SET NULL,
 KEY posts_is_visible_created_at_index(is_visible,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE comments (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NOT NULL,post_id BIGINT UNSIGNED NOT NULL,body TEXT NOT NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,
 CONSTRAINT comments_user_id_foreign FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT comments_post_id_foreign FOREIGN KEY(post_id) REFERENCES posts(id) ON DELETE CASCADE,
 KEY comments_post_id_created_at_index(post_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE likes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NOT NULL,post_id BIGINT UNSIGNED NOT NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,
 CONSTRAINT likes_user_id_foreign FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT likes_post_id_foreign FOREIGN KEY(post_id) REFERENCES posts(id) ON DELETE CASCADE,
 UNIQUE KEY likes_user_id_post_id_unique(user_id,post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
