-- Galeri: faaliyete bağlı olmayan (eski) albümler için yer bilgisi, fotoğraflarda çeken kişi.
ALTER TABLE gallery_albums
  ADD COLUMN location VARCHAR(160) NULL AFTER category;

ALTER TABLE gallery_photos
  ADD COLUMN credit VARCHAR(120) NULL AFTER caption;

ALTER TABLE gallery_albums
  ADD KEY ix_gallery_albums_activity (activity_id, is_published);
