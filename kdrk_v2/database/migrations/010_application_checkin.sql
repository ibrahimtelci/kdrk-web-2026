-- 010 - Faaliyet günü yoklaması: katılımcı geldi mi, ücretini ödedi mi.
-- attended: NULL = henüz işaretlenmedi, 1 = geldi, 0 = gelmedi. paid: 1 = ödedi, 0 = ödemedi.
-- checked_by: son işaretlemeyi yapan panel kullanıcısı (rehber, editör veya admin).
ALTER TABLE applications
  ADD COLUMN IF NOT EXISTS attended    TINYINT(1)      NULL     AFTER pickup,
  ADD COLUMN IF NOT EXISTS attended_at DATETIME        NULL     AFTER attended,
  ADD COLUMN IF NOT EXISTS paid        TINYINT(1)      NOT NULL DEFAULT 0 AFTER attended_at,
  ADD COLUMN IF NOT EXISTS paid_at     DATETIME        NULL     AFTER paid,
  ADD COLUMN IF NOT EXISTS checked_by  BIGINT UNSIGNED NULL     AFTER paid_at;

ALTER TABLE applications
  ADD CONSTRAINT fk_applications_checked_by FOREIGN KEY IF NOT EXISTS (checked_by) REFERENCES users (id) ON DELETE SET NULL;
