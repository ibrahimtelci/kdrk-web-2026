CREATE TABLE IF NOT EXISTS guide_contacts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY guide_contacts_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
ALTER TABLE activities
  ADD COLUMN IF NOT EXISTS guide_primary_id BIGINT UNSIGNED NULL,
  ADD COLUMN IF NOT EXISTS guide_secondary_id BIGINT UNSIGNED NULL;
ALTER TABLE activities
  ADD CONSTRAINT fk_activity_guide_primary FOREIGN KEY (guide_primary_id) REFERENCES guide_contacts(id),
  ADD CONSTRAINT fk_activity_guide_secondary FOREIGN KEY (guide_secondary_id) REFERENCES guide_contacts(id);
