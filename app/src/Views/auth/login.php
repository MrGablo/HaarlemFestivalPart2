<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-6 col-lg-5">

        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h1 class="h4 mb-3">Sign in</h1>

            <?php if (!empty($_GET['registered'])): ?>
              <div class="alert alert-success">Account created. You can log in now.</div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="/login" novalidate>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input name="email" type="email" class="form-control" autocomplete="email" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Password</label>
                <input name="password" type="password" class="form-control" autocomplete="current-password" required>
              </div>

              <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>

            <div class="text-center mt-3">
              <small class="text-muted">No account?</small>
              <a href="/register" class="ms-1">Register</a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</body>
</html>
