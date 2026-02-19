<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Account</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
  <main class="mx-auto max-w-3xl p-4 py-8">
    <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Manage account</h1>
        <a href="/" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Back to home</a>
      </div>

      <div class="mt-4 flex items-center gap-3">
        <img
          src="<?= htmlspecialchars(($user->profilePicturePath ?? '') !== '' ? $user->profilePicturePath : '/assets/img/default-user.png') ?>" 
          alt="Profile picture"
          class="h-14 w-14 rounded-full border border-slate-200 object-cover">
        <p class="text-sm text-slate-600">Default image is used when no profile picture is set.</p>
      </div>

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

      <form method="POST" action="/account/manage/update" enctype="multipart/form-data" class="mt-6 space-y-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">First name</label>
            <input
              name="firstName"
              type="text"
              required
              value="<?= htmlspecialchars($old['firstName'] ?? $user->firstName) ?>"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Last name</label>
            <input
              name="lastName"
              type="text"
              required
              value="<?= htmlspecialchars($old['lastName'] ?? $user->lastName) ?>"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
          </div>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
          <input
            name="email"
            type="email"
            required
            value="<?= htmlspecialchars($old['email'] ?? $user->email) ?>"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Username</label>
            <input
              type="text"
              disabled
              value="<?= htmlspecialchars($user->userName) ?>"
              class="w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-slate-600">
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">New password (optional)</label>
            <input
              name="password"
              type="password"
              minlength="8"
              autocomplete="new-password"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
          </div>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Profile picture URL (optional)</label>
          <input
            name="profilePicturePath"
            type="text"
            value="<?= htmlspecialchars($old['profilePicturePath'] ?? ($user->profilePicturePath ?? '/assets/img/default-user.png')) ?>"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
          <p class="mt-1 text-xs text-slate-500">Or upload a file below.</p>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Profile picture file (optional)</label>
          <input
            name="profilePicture"
            type="file"
            accept=".jpg,.jpeg,.png,.webp,.gif"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
        </div>

        <button type="submit"
          class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
          Save changes
        </button>
      </form>

      <section class="mt-8 border-t border-slate-200 pt-6">
        <h2 class="text-lg font-semibold text-rose-700">Delete account</h2>
        <p class="mt-1 text-sm text-slate-600">This action is permanent.</p>

        <form method="POST" action="/account/manage/delete" class="mt-4 space-y-3">
          <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="confirmDelete" value="DELETE" class="h-4 w-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
            I understand and confirm account deletion
          </label>

          <button type="submit"
            class="rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-300">
            Delete account
          </button>
        </form>
      </section>
    </section>
  </main>
</body>

</html>
