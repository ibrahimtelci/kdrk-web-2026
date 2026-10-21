-- Duyuru & Haber slaytı: işaretlenen haber Anasayfa slaytında görünür.
-- slider_until doluysa o günden sonra slayttan kendiliğinden kalkar (haber yayında kalır).
ALTER TABLE news
  ADD COLUMN in_slider TINYINT(1) NOT NULL DEFAULT 0 AFTER is_featured,
  ADD COLUMN slider_until DATE NULL AFTER in_slider,
  ADD KEY ix_news_slider (in_slider, status, published_on);
