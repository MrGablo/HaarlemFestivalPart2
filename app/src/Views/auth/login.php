<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
  <main class="min-h-screen flex items-center justify-center p-4">
    <section class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl ring-1 ring-slate-200">
      <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Sign in</h1>
      <p class="mt-1 text-sm text-slate-500">Welcome back</p>

      <?php if (!empty($flashSuccess)): ?>
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
          <?= htmlspecialchars($flashSuccess) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($errors['general'])): ?>
        <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
          <?= htmlspecialchars($errors['general']) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="/login" class="mt-6 space-y-4">
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Username</label>
          <input name="userName" type="text"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
            autocomplete="username" required value="<?= htmlspecialchars($old['userName'] ?? '') ?>">
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Password</label>
          <input name="password" type="password"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
            autocomplete="current-password" required>
        </div>

        <button type="submit"
          class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
          Login
        </button>
      </form>

      <div class="mt-5 text-center text-sm text-slate-600">
        <span>No account?</span>
        <a href="/register" class="ml-1 font-medium text-blue-600 hover:text-blue-700">Register</a>
      </div>
    </section>
  </main>
</body>

</html>