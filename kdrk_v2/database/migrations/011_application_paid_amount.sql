-- 011 - Yoklamada "Ödedi" işaretlenince alınan tutar. Varsayılanı faaliyet ücretidir (activities.fee);
-- rehber sahada değiştirebilir (indirim, kısmi ödeme). Ödemedi'ye çekilince NULL olur.
ALTER TABLE applications
  ADD COLUMN IF NOT EXISTS paid_amount DECIMAL(10,2) NULL AFTER paid;
