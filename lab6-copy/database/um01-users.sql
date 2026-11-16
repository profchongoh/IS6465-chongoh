-- UM-01: additive schema; existing tables remain unchanged.
CREATE TABLE IF NOT EXISTS users (
 user_id INTEGER PRIMARY KEY,
 full_name TEXT NOT NULL,
 email TEXT NOT NULL COLLATE NOCASE UNIQUE,
 password_hash TEXT NOT NULL,
 role TEXT NOT NULL CHECK(role IN ('Student','Tutor','Administrator')),
 status TEXT NOT NULL DEFAULT 'Active' CHECK(status IN ('Active','Inactive')),
 created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
