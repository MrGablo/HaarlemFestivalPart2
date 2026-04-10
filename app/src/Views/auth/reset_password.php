<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
  <?php include __DIR__ . '/../partials/header.php'; ?>
  <main class="min-h-screen flex items-center justify-center p-4">
    <section class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl ring-1 ring-slate-200">
      <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Reset password</h1>
      <p class="mt-1 text-sm text-slate-500">Choose a new password for your account.</p>

      <?php require __DIR__ . '/../partials/flash_success.php'; ?>
      <?php require __DIR__ . '/../partials/error_general.php'; ?>

      <form method="POST" action="/reset-password" class="mt-6 space-y-4">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Utils\Csrf::token('auth_csrf_token'), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">New password</label>
          <input name="password" type="password"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
            autocomplete="new-password" required minlength="8">
          <p class="mt-1 text-xs text-slate-500">Minimum 8 characters.</p>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Confirm new password</label>
          <input name="confirmPassword" type="password"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
            autocomplete="new-password" required minlength="8">
        </div>

        <button type="submit"
          class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
          Reset password
        </button>
      </form>

      <div class="mt-5 text-center text-sm text-slate-600">
        <a href="/login" class="font-medium text-blue-600 hover:text-blue-700">Back to login</a>
      </div>
    </section>
  </main>
  <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
