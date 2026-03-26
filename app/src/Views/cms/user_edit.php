<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit User</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-5xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit User</h1>
                    <p class="mt-1 text-sm text-slate-600">Update user account information.</p>
                </div>
                <a href="/cms/users" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Back to Users</a>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <form method="POST" action="/cms/users/<?= (int)$user->id ?>" class="mt-8 space-y-6">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">First Name</label>
                        <input type="text" name="first_name" required
                            value="<?= htmlspecialchars((string)($user->firstName ?? '')) ?>"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Last Name</label>
                        <input type="text" name="last_name" required
                            value="<?= htmlspecialchars((string)($user->lastName ?? '')) ?>"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Username</label>
                        <input type="text" disabled
                            value="<?= htmlspecialchars((string)($user->userName ?? '')) ?>"
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-500 shadow-sm">
                        <p class="mt-1 text-xs text-slate-500">Username cannot be changed.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Email</label>
                        <input type="email" name="email" required
                            value="<?= htmlspecialchars((string)($user->email ?? '')) ?>"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Phone Number (Optional)</label>
                        <input type="text" name="phone_number"
                            value="<?= htmlspecialchars((string)($user->phoneNumber ?? '')) ?>"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Role</label>
                        <select name="role" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= htmlspecialchars($role) ?>"
                                    <?= ((string)($user->role->value ?? '') === $role) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucfirst($role)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">New Password (Optional)</label>
                        <input type="password" name="password" minlength="8"
                            placeholder="Leave blank to keep current password"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
                    <a href="/cms/users" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Cancel</a>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        Update User
                    </button>
                </div>
            </form>
        </section>
    </main>
</body>

</html>