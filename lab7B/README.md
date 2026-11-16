# Peer Tutoring Hub User Management

This version implements UM-01 through UM-10: registration, self-service profile and password updates, administrator user management, login, logout, sessions, CSRF protection, role checks, and student request ownership checks.

## First-time database setup

For a new database, recreate it from `database/setup-database.sql`. For the supplied Lab 6 database, run `database/upgrade-to-user-management.sql` once. Then create the first administrator from the project root:

`php database/create-admin.php "Admin Name" admin@example.edu "a-password-with-12-or-more-characters"`

The current retention policy is conservative: an administrator can delete an account only when it has no tutoring requests. The linked student record is retained and unlinked. The last active administrator cannot be deactivated, demoted, or deleted.

## Tests

Run the existing request regression test and the new isolated account test from the project root:

`php tests/integration-tests.php`

`php tests/user-management-tests.php`

## Week 7 Peer Tutoring Hub (Lab 4 continuation)

Keep the Lab 4 HTML, CSS, includes, checkbox topics, radio time choices and existing validation tests. Add student name/email and persistent SQLite storage. The approved Week 6 database has only one `topic` column. This teaching version stores a JSON string in that TEXT column with `reason` and `topics` so both Lab 4 values survive without changing the ERD. The `preferred_time` column stores the Lab 4 values Morning, Afternoon or Evening (not a clock time).

This version uses PHP 7.0-compatible syntax. It requires the PDO SQLite and mbstring extensions.

## Run in GitHub Codespaces

1. From this folder: `mkdir -p database && sqlite3 database/tutoring.db < database/setup-database.sql`
2. Run `php -S 0.0.0.0:8000 php/router.php` and open forwarded port 8000.
3. Visit `/` or `/index.php`; it opens the request form at `/php/request-form.php`. Use `/php/requests.php` to filter, view, edit, or cancel requests.
4. Run `php tests/integration-tests.php` in a separate terminal.
5. The Lab 4 validation checks remain in `tests/validation-tests.php`. Run them through the classroom server if desired.

**Classroom only:** no login, per-request authorization, or CSRF tokens yet. Do not deploy publicly. Router blocks direct access to internal folders only when launched as shown; a production server must have a dedicated public document root and full access controls. No real student personal information should be used in this demo.
