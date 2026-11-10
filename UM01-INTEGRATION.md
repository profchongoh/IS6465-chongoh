# UM-01 Integration and Traceability

## Baseline
UM-01 and WF-01 in User Management Design Package v1.0. The supplied Skills Pack requires separation of presentation, service, repository and PDO connection, isolated tests, and forward/backward traceability.

## Schema decision
Add `users` with `user_id`, `full_name`, `email`, `password_hash`, `role`, `status`, `created_at`. Do not change existing `students` or `tutoring_requests` tables. Linking accounts to tutoring requests is deferred pending design approval. Password minimum of 12 characters is provisional; exact policy is unresolved in design.

## Install from project root
```sh
sqlite3 database/tutoring.db < database/um01-users.sql
php -S 0.0.0.0:8000 php/router.php
```
Open `/php/register.php` in forwarded port. To test:
```sh
php tests/um01-tests.php
php tests/integration-tests.php
```
Tests use in-memory SQLite; they do not touch `tutoring.db`. Run existing validation tests separately as documented in README.

## Forward traceability
| Requirement | Code | Test | Status |
|---|---|---|---|
| UM01-R01 form | php/register.php | Manual browser check needed | Implemented, unverified |
| UM01-R02 Student/Tutor | php/register.php; services/userService.php | tests/um01-tests.php | Implemented, unverified |
| UM01-R03 required fields/email | services/userService.php | tests/um01-tests.php | Implemented, unverified |
| UM01-R04 matching passwords | services/userService.php | tests/um01-tests.php | Implemented, unverified |
| UM01-R05 unique email | repositories/userPDO.php; database/um01-users.sql | tests/um01-tests.php | Implemented, unverified |
| UM01-R06 no public Admin | services/userService.php | tests/um01-tests.php | Implemented, unverified |
| UM01-R07 hash password | services/userService.php | tests/um01-tests.php | Implemented, unverified |
| UM01-R08 Active account | repositories/userPDO.php | tests/um01-tests.php | Implemented, unverified |
| UM01-R09 confirmation | php/register.php | Manual browser check needed | Implemented, unverified |
| UM01-R10 no invalid writes | services/userService.php | tests/um01-tests.php | Implemented, unverified |

## Backward traceability and limitations
The new `users` table, form, service and repository map to UM-01. The CSRF token is a protective control for the public registration form. No login, sessions for authentication, profile editing, or linkage to tutoring requests are introduced. A session is used only to hold a CSRF token. Existing tutoring request screens remain unchanged except navigation. This classroom project is not ready for public deployment; registration abuse prevention, rate limiting, email verification, secure production session configuration and deployment access controls remain outstanding.

## Verification evidence
PHP CLI is unavailable in the authoring environment, so tests were generated but not executed. No PASS results are claimed. Run both test suites in Codespaces and investigate failures before considering UM-01 verified.
