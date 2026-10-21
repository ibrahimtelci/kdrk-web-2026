-- 001 — İlk şema (CLAUDE.md §14)
-- Uygulanmış migration düzenlenmez; değişiklik için yeni dosya yazılır.
-- MariaDB 10.4 (yerel) ve 10.11 (sunucu) uyumlu.

CREATE TABLE users (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120) NOT NULL,
  email         VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','editor','viewer') NOT NULL DEFAULT 'viewer',
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE login_attempts (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email        VARCHAR(190) NOT NULL,
  ip           VARCHAR(45) NOT NULL,
  attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_login_attempts (email, attempted_at),
  KEY ix_login_attempts_ip (ip, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE settings (
  `key`      VARCHAR(64) NOT NULL PRIMARY KEY,
  `value`    TEXT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Faaliyetler: tek veri kaynağı (CLAUDE.md §9). Anasayfa, liste, disiplin ve detay buradan okur.
CREATE TABLE activities (
  id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug             VARCHAR(160) NOT NULL,
  title            VARCHAR(160) NOT NULL,
  category         ENUM('gunubirlik','dagcilik','spor-tirmanis','dag-kayagi','egitim','sosyal','arama-kurtarma') NOT NULL,
  starts_on        DATE NOT NULL,
  ends_on          DATE NULL,
  location         VARCHAR(160) NOT NULL DEFAULT '',
  summary          VARCHAR(300) NOT NULL DEFAULT '',        -- kart metni
  body             TEXT NULL,                               -- "Faaliyet Hakkında" (izinli HTML)
  difficulty       TINYINT UNSIGNED NULL,                   -- 1 kolay · 2 orta · 3 zor · 4 çok zor; eğitimde NULL
  is_training      TINYINT(1) NOT NULL DEFAULT 0,
  duration_text    VARCHAR(60) NULL,                        -- "3 gece · 4 gün"
  summit_m         SMALLINT UNSIGNED NULL,
  distance_km      DECIMAL(5,1) NULL,
  elevation_gain_m SMALLINT UNSIGNED NULL,
  quota            SMALLINT UNSIGNED NOT NULL DEFAULT 0,    -- 0 = kontenjan yok
  fee              DECIMAL(10,2) NULL,
  fee_note         VARCHAR(255) NULL,
  meeting_text     VARCHAR(255) NULL,
  return_text      VARCHAR(255) NULL,
  included_text    VARCHAR(255) NULL,
  personal_text    VARCHAR(255) NULL,
  requirement_text TEXT NULL,
  weather_text     VARCHAR(255) NULL,
  program          LONGTEXT NULL CHECK (program IS NULL OR JSON_VALID(program)),     -- [{title, date, items:[{time, text}]}]
  equipment        LONGTEXT NULL CHECK (equipment IS NULL OR JSON_VALID(equipment)), -- ["…", "…"]
  cover_image      VARCHAR(255) NULL,
  leader_name      VARCHAR(120) NULL,
  leader_title     VARCHAR(160) NULL,
  -- Durum otomatik hesaplanır; elle yalnızca bu üçü girilir (CLAUDE.md §14)
  manual_status    ENUM('closed','postponed','cancelled') NULL,
  report_summary   TEXT NULL,
  report_participants SMALLINT UNSIGNED NULL,
  is_published     TINYINT(1) NOT NULL DEFAULT 1,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_activities_slug (slug),
  KEY ix_activities_date (is_published, starts_on),
  KEY ix_activities_category (category, starts_on),
  CONSTRAINT ck_activities_difficulty CHECK (difficulty IS NULL OR difficulty BETWEEN 1 AND 4)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Başvurular. TC kimlik ve sağlık notu şifreli (CLAUDE.md §15).
CREATE TABLE applications (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  activity_id     BIGINT UNSIGNED NOT NULL,
  name            VARCHAR(120) NOT NULL,
  phone           VARCHAR(30) NOT NULL,
  email           VARCHAR(190) NOT NULL,
  tc_enc          VARBINARY(255) NULL,
  emer_name       VARCHAR(120) NULL,
  emer_phone      VARCHAR(30) NULL,
  health_enc      VARBINARY(4096) NULL,
  transport       ENUM('club','own') NULL,
  pickup          VARCHAR(120) NULL,
  status          ENUM('pending','approved','rejected','waitlist') NOT NULL DEFAULT 'pending',
  kvkk_consent_at DATETIME NOT NULL,
  mail_status     ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  purged_at       DATETIME NULL,  -- KVKK saklama süresi dolunca hassas alanlar silindiğinde
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY ix_applications_activity (activity_id, status),
  CONSTRAINT fk_applications_activity FOREIGN KEY (activity_id) REFERENCES activities (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE gallery_albums (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug        VARCHAR(160) NOT NULL,
  title       VARCHAR(160) NOT NULL,
  activity_id BIGINT UNSIGNED NULL,
  category    VARCHAR(40) NULL,
  taken_on    DATE NULL,
  description TEXT NULL,
  cover_photo_id BIGINT UNSIGNED NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_gallery_albums_slug (slug),
  KEY ix_gallery_albums_date (is_published, taken_on),
  CONSTRAINT fk_gallery_albums_activity FOREIGN KEY (activity_id) REFERENCES activities (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE gallery_photos (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  album_id   BIGINT UNSIGNED NOT NULL,
  path       VARCHAR(255) NOT NULL,   -- uploads/… temel ad; türevler -400/-900/-1600/-2400.webp
  width      SMALLINT UNSIGNED NULL,
  height     SMALLINT UNSIGNED NULL,
  caption    VARCHAR(255) NULL,
  sort       INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_gallery_photos_album (album_id, sort),
  CONSTRAINT fk_gallery_photos_album FOREIGN KEY (album_id) REFERENCES gallery_albums (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE news (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug         VARCHAR(160) NOT NULL,
  title        VARCHAR(200) NOT NULL,
  category     ENUM('duyuru','haber','rapor') NOT NULL DEFAULT 'haber',
  excerpt      VARCHAR(400) NULL,
  body         TEXT NULL,
  cover_image  VARCHAR(255) NULL,
  activity_id  BIGINT UNSIGNED NULL,
  link_url     VARCHAR(255) NULL,     -- haber başka bir sayfaya yönlendiriyorsa
  read_minutes TINYINT UNSIGNED NULL,
  is_featured  TINYINT(1) NOT NULL DEFAULT 0,
  status       ENUM('draft','published') NOT NULL DEFAULT 'draft',
  published_on DATE NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_news_slug (slug),
  KEY ix_news_published (status, published_on),
  CONSTRAINT fk_news_activity FOREIGN KEY (activity_id) REFERENCES activities (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Anasayfa slider. En fazla 3 aktif slayt; ilk slayt kulübü tanıtır (CLAUDE.md §4).
CREATE TABLE banners (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  eyebrow      VARCHAR(80) NULL,
  title        VARCHAR(160) NOT NULL,
  text         VARCHAR(400) NULL,
  image        VARCHAR(255) NOT NULL,
  cta_label    VARCHAR(60) NULL,
  cta_url      VARCHAR(255) NULL,
  cta2_label   VARCHAR(60) NULL,
  cta2_url     VARCHAR(255) NULL,
  stamp_1      VARCHAR(60) NULL,
  stamp_2      VARCHAR(60) NULL,
  sort         INT NOT NULL DEFAULT 0,
  is_active    TINYINT(1) NOT NULL DEFAULT 1,
  show_from    DATE NULL,
  show_until   DATE NULL,             -- tarihi geçebilecek duyuru otomatik kalkar
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY ix_banners_active (is_active, sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE messages (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(120) NOT NULL,
  email       VARCHAR(190) NOT NULL,
  phone       VARCHAR(30) NULL,
  topic       VARCHAR(40) NULL,
  subject     VARCHAR(200) NULL,
  body        TEXT NOT NULL,
  is_read     TINYINT(1) NOT NULL DEFAULT 0,
  mail_status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_messages_read (is_read, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE audit_log (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    BIGINT UNSIGNED NULL,
  action     VARCHAR(40) NOT NULL,
  entity     VARCHAR(40) NOT NULL,
  entity_id  BIGINT UNSIGNED NULL,
  details    LONGTEXT NULL CHECK (details IS NULL OR JSON_VALID(details)),
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_audit_entity (entity, entity_id),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

INSERT INTO settings (`key`, `value`) VALUES
  ('stat_kurulus', '1998'),
  ('stat_zirve', '500'),
  ('stat_yuruyus', '1000'),
  ('stat_arama_kurtarma', '25'),
  ('maintenance', '0'),
  ('maintenance_title', 'Kısa bir bakım yapıyoruz.'),
  ('maintenance_message', 'Site kısa süre içinde yeniden açılacak.'),
  ('kvkk_retention_days', '365'),
  ('contact_email', 'info@kdrk.org.tr'),
  ('contact_phone', '+90 (464) 651 00 00'),
  ('contact_address', 'Yeniköy Mah. Cumhuriyet Cad. Rize İş Merkezi No:184/410'),
  ('social_instagram', ''),
  ('social_whatsapp', ''),
  ('social_youtube', ''),
  ('social_strava', '');
