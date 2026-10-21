-- 009 - İletişim kişilerini kullanıcılarla birleştir; eski tablolar korunur.
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS phone VARCHAR(40) NULL,
  ADD COLUMN IF NOT EXISTS legacy_contact_id BIGINT UNSIGNED NULL,
  MODIFY COLUMN email VARCHAR(190) NULL,
  MODIFY COLUMN role ENUM('admin','editor','viewer','guide') NOT NULL DEFAULT 'viewer';
CREATE UNIQUE INDEX IF NOT EXISTS uq_users_legacy_contact ON users (legacy_contact_id);
INSERT INTO users (name, email, password_hash, phone, role, is_active, legacy_contact_id)
SELECT g.name, NULL, '', g.phone, 'guide', 0, g.id FROM guide_contacts g
WHERE NOT EXISTS (SELECT 1 FROM users u WHERE u.legacy_contact_id = g.id);
ALTER TABLE activities
  ADD COLUMN IF NOT EXISTS guide_primary_user_id BIGINT UNSIGNED NULL,
  ADD COLUMN IF NOT EXISTS guide_secondary_user_id BIGINT UNSIGNED NULL;
UPDATE activities a JOIN users u ON u.legacy_contact_id = a.guide_primary_id
SET a.guide_primary_user_id = u.id WHERE a.guide_primary_user_id IS NULL;
UPDATE activities a JOIN users u ON u.legacy_contact_id = a.guide_secondary_id
SET a.guide_secondary_user_id = u.id WHERE a.guide_secondary_user_id IS NULL;
ALTER TABLE activities
  ADD CONSTRAINT fk_activity_primary_user FOREIGN KEY (guide_primary_user_id) REFERENCES users(id);
ALTER TABLE activities
  ADD CONSTRAINT fk_activity_secondary_user FOREIGN KEY (guide_secondary_user_id) REFERENCES users(id);
