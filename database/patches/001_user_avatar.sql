-- Idempotent-ish: fails if column exists (ignored by migrate when duplicate column).
ALTER TABLE users ADD COLUMN avatar TEXT;
