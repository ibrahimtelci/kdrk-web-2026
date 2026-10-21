-- Faaliyet slaytı: işaretlenen faaliyet Anasayfa slaytında görünür; tarihi geçince kendiliğinden kalkar.
ALTER TABLE activities
  ADD COLUMN in_slider TINYINT(1) NOT NULL DEFAULT 0 AFTER is_published,
  ADD KEY ix_activities_slider (in_slider, starts_on);
