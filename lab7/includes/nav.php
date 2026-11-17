<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="../index.php">Peer Tutoring Hub</a>
    <div class="navbar-nav">
      <?php if (!empty($_SESSION['user_id'])): ?>
        <a class="nav-link" href="requests.php">Requests</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Student'): ?><a class="nav-link" href="request-form.php">Request a Session</a><?php endif; ?>
        <a class="nav-link" href="profile.php">My Profile</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Administrator'): ?><a class="nav-link" href="manage-users.php">Manage Users</a><?php endif; ?>
        <form method="post" action="logout.php" class="d-inline"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"><button class="btn btn-link nav-link">Log Out</button></form>
      <?php else: ?>
        <a class="nav-link" href="login.php">Log In</a><a class="nav-link" href="register.php">Create Account</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
