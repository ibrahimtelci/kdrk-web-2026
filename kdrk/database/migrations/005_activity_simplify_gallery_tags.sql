-- Faaliyet formu sadeleşti: zorluk 1–10 puan (bant: 1–3 kolay, 4–6 orta, 7–8 zor, 9–10 çok zor), haritadan konum.
-- difficulty (1–4) rozet sınıfı için kalır ve puandan türetilir.
ALTER TABLE activities
  ADD COLUMN difficulty_score TINYINT UNSIGNED NULL AFTER difficulty,
  ADD COLUMN lat DECIMAL(9,6) NULL AFTER location,
  ADD COLUMN lng DECIMAL(9,6) NULL AFTER lat,
  ADD CONSTRAINT ck_activities_difficulty_score CHECK (difficulty_score IS NULL OR difficulty_score BETWEEN 1 AND 10);

UPDATE activities SET difficulty_score = ELT(difficulty, 2, 5, 7, 9) WHERE difficulty IS NOT NULL;

-- Albüm etiketleri: faaliyet adı, yer, kategori, yıl… virgülle ayrılır; panel ve site aramasında kullanılır.
ALTER TABLE gallery_albums
  ADD COLUMN tags VARCHAR(500) NULL AFTER location;

UPDATE gallery_albums g
  JOIN activities a ON a.id = g.activity_id
   SET g.tags = CONCAT_WS(', ', a.title, NULLIF(TRIM(SUBSTRING_INDEX(a.location, '·', 1)), ''), YEAR(a.starts_on))
 WHERE g.tags IS NULL;
