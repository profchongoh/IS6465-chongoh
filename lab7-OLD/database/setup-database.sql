PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    user_id INTEGER PRIMARY KEY,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    active INTEGER NOT NULL DEFAULT 1 CHECK(active IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_roles (
    user_id INTEGER NOT NULL,
    role TEXT NOT NULL CHECK(role IN ('student', 'tutor', 'admin')),
    PRIMARY KEY (user_id, role),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS students (
    student_id INTEGER PRIMARY KEY,
    student_name TEXT NOT NULL,
    student_email TEXT NOT NULL UNIQUE,
    user_id INTEGER UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE
    SET
        NULL
);

CREATE TABLE IF NOT EXISTS tutoring_requests (
    request_id INTEGER PRIMARY KEY,
    student_id INTEGER NOT NULL,
    course TEXT NOT NULL,
    topic TEXT NOT NULL,
    preferred_date TEXT NOT NULL,
    preferred_time TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'Open' CHECK(
        status IN ('Open', 'Cancelled', 'Assigned', 'Completed')
    ),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(student_id) REFERENCES students(student_id)
);

INSERT
    OR IGNORE INTO students (student_name, student_email)
VALUES
    ('Maria Lopez', 'maria.lopez@example.edu');

INSERT
    OR IGNORE INTO tutoring_requests (
        student_id,
        course,
        topic,
        preferred_date,
        preferred_time
    )
VALUES
    (
        1,
        'IS 6465',
        'PHP arrays and forms',
        '2026-11-03',
        '14:00'
    );