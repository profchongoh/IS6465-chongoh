CREATE TABLE IF NOT EXISTS `request` (
    `requestid` INTEGER PRIMARY KEY AUTOINCREMENT,
    `course` TEXT NOT NULL,
    `topic` TEXT NOT NULL CHECK (length(`topic`) <= 120),
    `preferreddate` TEXT NOT NULL,
    `preferredtime` TEXT NOT NULL
);
