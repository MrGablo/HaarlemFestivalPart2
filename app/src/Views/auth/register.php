<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-7 col-lg-6">

        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h1 class="h4 mb-3">Create account</h1>

            <?php if (!empty($errors['general'])): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif; ?>

            <form method="POST" action="/register">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">First name</label>
                  <input
                    name="firstName"
                    class="form-control"
                    required
                    value="<?= htmlspecialchars($old['firstName'] ?? '') ?>"
                  >
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Last name</label>
                  <input
                    name="lastName"
                    class="form-control"
                    required
                    value="<?= htmlspecialchars($old['lastName'] ?? '') ?>"
                  >
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Username</label>
                <input
                  name="userName"
                  class="form-control"
                  required
                  value="<?= htmlspecialchars($old['userName'] ?? '') ?>"
                >
              </div>

              <div class="mb-3">
                <label class="form-label">Email</label>
                <input
                  name="email"
                  type="email"
                  class="form-control"
                  required
                  value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                >
              </div>

              <div class="mb-3">
                <label class="form-label">Phone number (optional)</label>
                <input
                  name="phoneNumber"
                  class="form-control"
                  value="<?= htmlspecialchars($old['phoneNumber'] ?? '') ?>"
                >
              </div>

              <div class="mb-3">
                <label class="form-label">Password</label>
                <input
                  name="password"
                  type="password"
                  class="form-control"
                  autocomplete="new-password"
                  required
                  minlength="8"
                >
                <div class="form-text">Minimum 8 characters.</div>
              </div>

              <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">Register</button>
            </form>

            <div class="text-center mt-3">
              <small class="text-muted">Already have an account?</small>
              <a href="/login" class="ml-1 font-medium text-blue-600 hover:text-blue-700">Login</a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</body>
</html>
