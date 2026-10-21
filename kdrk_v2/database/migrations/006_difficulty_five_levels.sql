-- Zorluk: 1–10 puan (panelde kaydırma çubuğu) → 5 kademe, her yerde aynı (CLAUDE.md §1, §14).
--   1 Kolay (1–2) · 2 Kolay-Orta (3–4) · 3 Orta (5–6) · 4 Orta-Zor (7–8) · 5 Zor (9–10)
-- difficulty artık 4 bant değil 5 kademedir ve puandan türetilir: CEIL(puan / 2).
ALTER TABLE activities DROP CONSTRAINT ck_activities_difficulty;
ALTER TABLE activities ADD CONSTRAINT ck_activities_difficulty CHECK (difficulty IS NULL OR difficulty BETWEEN 1 AND 5);
UPDATE activities SET difficulty = CEIL(difficulty_score / 2) WHERE difficulty_score IS NOT NULL;
UPDATE activities SET difficulty = NULL WHERE difficulty_score IS NULL;
