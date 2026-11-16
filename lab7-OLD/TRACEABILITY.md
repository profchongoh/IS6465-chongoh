# User-management traceability

| Requirement | Code and schema evidence | Test evidence | Status |
|---|---|---|---|
| UM-01 Create account | `register.php`, `process-registration.php`, `users`, `user_roles`, `students.user_id` | UM-01 / SEC-01 | Implemented, unverified here |
| UM-02 View own account | `profile.php` selects the session user ID | UM-02 | Implemented, unverified here |
| UM-03 Search accounts | `manage-users.php`, `searchUsers()`, admin role guard | UM-03 and manual role check | Implemented, unverified here |
| UM-04 Update own account | `process-profile.php`, `updateOwnProfile()` | UM-04 | Implemented, unverified here |
| UM-05 Change password | `process-password.php`, `password_verify`, `password_hash` | UM-05 | Implemented, unverified here |
| UM-06 Update role | `process-user-admin.php`, `replaceUserRole()` | UM-06 | Implemented, unverified here |
| UM-07 Account status | active account query at login | UM-07 | Implemented, unverified here |
| UM-08 Delete account | `deleteAllowed()` blocks related requests and final active admin | UM-08 | Implemented, unverified here |
| UM-09 Login | secure session, `session_regenerate_id(true)` | UM-09 plus manual session check | Implemented, unverified here |
| UM-10 Logout | POST-only logout, CSRF, cookie expiry, session destruction | Manual browser check | Implemented, manual verification needed |

## Manual security checks

1. Start locally with `APP_ENV=local php -S 0.0.0.0:8000 php/router.php`; use the default environment for an HTTPS Codespaces forwarded URL.
2. Confirm anonymous direct URLs redirect to login before protected output.
3. Register two students, create a request as one, then alter the request ID as the other. Confirm 404 and no update or cancellation.
4. Remove or change a hidden CSRF token from each state-changing form. Confirm 403 and no database write.
5. Confirm a student gets 403 on administrator endpoints; log out and confirm dashboard access redirects to login.

The assembly runtime has no PHP executable, so PHP syntax and CLI tests were not executed here. The status labels intentionally separate implemented controls from executed evidence.
