# User Management Traceability

| Requirement | Implementation evidence | Automated evidence | Status |
|---|---|---|---|
| UM-01 Create account | `php/register.php`, `services/userService.php::registerUser`, `repositories/userPDO.php::insertUser` | `user-management-tests.php` registration and duplicate cases | Implemented, unverified locally |
| UM-02 View own account | `php/profile.php` with `requireLogin` | own-account retrieval assertion | Implemented, unverified locally |
| UM-03 Search accounts | `php/manage-users.php`, `listUsers` | filtered-account assertion | Implemented, unverified locally |
| UM-04 Update own account | `profile.php`, `updateOwnAccount` | permitted-update assertion | Implemented, unverified locally |
| UM-05 Change password | `change-password.php`, `changePassword` | new password succeeds and old password fails | Implemented, unverified locally |
| UM-06 Update role | `user-details.php`, `changeUserRole` | role-update assertion | Implemented, unverified locally |
| UM-07 Account status | `user-details.php`, `changeUserStatus` | inactive login and last-admin assertions | Implemented, unverified locally |
| UM-08 Delete account | `user-details.php`, `removeUserAccount` | deletion without requests assertion | Implemented, unverified locally |
| UM-09 Log in | `login.php`, `authenticateUser`, `loginSession` | active credential assertion | Implemented, unverified locally |
| UM-10 Log out | `logout.php`, `logoutSession` | manual browser test required | Implemented, unverified locally |

## Known constraints

The supplied design does not specify tutor assignment or administrator support actions for tutoring requests. Tutors can manage their own account but are denied request pages. The session cookie is marked `Secure` when the request is HTTPS, which supports Codespaces and keeps local HTTP testing possible. Execute the two PHP test commands in Codespaces before marking any requirement Verified.
