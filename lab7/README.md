# Week 7 Peer Tutoring Hub (Lab 4 continuation)

Keep the Lab 4 HTML, CSS, includes, checkbox topics, radio time choices and existing validation tests. Add student name/email and persistent SQLite storage. The approved Week 6 database has only one `topic` column. This teaching version stores a JSON string in that TEXT column with `reason` and `topics` so both Lab 4 values survive without changing the ERD. The `preferred_time` column stores the Lab 4 values Morning, Afternoon or Evening (not a clock time).

This version now requires PHP 8.0+ with the PDO SQLite and mbstring extensions.

## User management (UM-01 through UM-10)

Registration, login/logout, profile editing, password changes, administrator user search and management, role and status changes, and retention-safe account deletion are included. Password hashes, server-side sessions, CSRF tokens, role checks, and ownership-constrained student request queries are enforced server-side.

For a new database, run `sqlite3 database/tutoring.db < database/setup-database.sql`. To upgrade the supplied Lab 6 database, run `php database/migrate-database.php` once. Create the first administrator only from the terminal: `php database/create-admin.php "Admin Name" admin@example.edu "a 12 character password"`.

## Run in GitHub Codespaces

1. From this folder: `mkdir -p database && sqlite3 database/tutoring.db < database/setup-database.sql`
2. Run `php -S 0.0.0.0:8000 php/router.php` and open forwarded port 8000.
3. Visit `/` or `/index.php`; it opens the login page. Register student and tutor accounts in the browser. The first administrator is created with the CLI command above.
4. Run `php tests/integration-tests.php` and `php tests/user-management-tests.php` in a separate terminal.
5. The Lab 4 validation checks remain in `tests/validation-tests.php`. Run them through the classroom server if desired.

**Classroom only:** use fictional accounts and data. Router blocks direct access to internal folders only when launched as shown; a production server still needs a dedicated public document root, HTTPS, environment-managed configuration, logging, rate limiting, and a password-reset process. See `TRACEABILITY.md` for manual security verification.
