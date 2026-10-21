-- V2: doğrulanmış faaliyet sonucu ve kalıcı eski adresler. Eski kayıtlar korunur.
ALTER TABLE activities ADD COLUMN completed_on DATE NULL;
CREATE TABLE content_redirects (
  kind VARCHAR(20) NOT NULL,
  content_id BIGINT UNSIGNED NOT NULL,
  slug VARCHAR(160) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (kind, slug),
  KEY ix_redirect_content (kind, content_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
