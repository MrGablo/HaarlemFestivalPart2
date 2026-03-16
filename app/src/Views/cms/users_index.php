<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CMS Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 antialiased">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="mx-auto max-w-7xl p-4 py-8">
        <section class="rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Users</h1>
                    <p class="mt-1 text-sm text-slate-600">Create, edit, and delete user accounts.</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="/cms/users/create"
                        class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        + Create User
                    </a>

                    <a href="/cms"
                        class="text-sm font-medium text-slate-600 hover:text-slate-900">← Back to CMS</a>
                </div>
            </div>

            <?php require __DIR__ . '/../partials/flash_success.php'; ?>
            <?php require __DIR__ . '/../partials/error_general.php'; ?>

            <!-- Search, Filter, Sort -->
            <form method="GET" action="/cms/users" class="mt-6 space-y-4 rounded-lg bg-slate-50 p-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Search</label>
                        <input
                            type="text"
                            name="search"
                            placeholder="Name, username, or email..."
                            value="<?= htmlspecialchars((string)($search ?? '')) ?>"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                    </div>

                    <!-- Role Filter -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Role</label>
                        <select
                            name="role"
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                            <option value="">All Roles</option>
                            <?php foreach (($roles ?? []) as $role): ?>
                                <option value="<?= htmlspecialchars($role) ?>" <?= ((string)($roleFilter ?? '') === $role) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucfirst($role)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Sort By</label>
                        <div class="mt-1 flex gap-2">
                            <select
                                name="sort"
                                class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <option value="id" <?= ((string)($sortColumn ?? 'name') === 'id') ? 'selected' : '' ?>>ID</option>
                                <option value="name" <?= ((string)($sortColumn ?? 'name') === 'name') ? 'selected' : '' ?>>Name</option>
                                <option value="created_at" <?= ((string)($sortColumn ?? 'name') === 'created_at') ? 'selected' : '' ?>>Registration Date</option>
                                <option value="email" <?= ((string)($sortColumn ?? 'name') === 'email') ? 'selected' : '' ?>>Email</option>
                            </select>

                            <select
                                name="dir"
                                class="w-24 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <option value="ASC" <?= ((string)($sortDirection ?? 'ASC') === 'ASC') ? 'selected' : '' ?>>↑ Asc</option>
                                <option value="DESC" <?= ((string)($sortDirection ?? 'ASC') === 'DESC') ? 'selected' : '' ?>>↓ Desc</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Apply Filters
                    </button>
                    <a href="/cms/users"
                        class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">
                        Clear
                    </a>
                </div>
            </form>

            <!-- Users Table -->
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">ID</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Username</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Email</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Role</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Registration Date</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-slate-600">No users found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= (int)($u->id ?? 0) ?></td>
                                    <td class="px-4 py-3 font-medium text-slate-900">
                                        <?= htmlspecialchars((string)($u->firstName ?? '')) . ' ' . htmlspecialchars((string)($u->lastName ?? '')) ?>
                                    </td>
                                    <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars((string)($u->userName ?? '')) ?></td>
                                    <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars((string)($u->email ?? '')) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                            <?= htmlspecialchars(ucfirst((string)($u->role->value ?? ''))) ?>
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                        <?= htmlspecialchars((string)($u->created_at ?? '')) ?>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="/cms/users/<?= (int)($u->id ?? 0) ?>"
                                                class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                                Edit
                                            </a>

                                            <form method="POST"
                                                action="/cms/users/<?= (int)($u->id ?? 0) ?>/delete"
                                                style="display: inline;"
                                                onsubmit="return confirm('Delete this user? This action cannot be undone.');">
                                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">
                                                <button type="submit"
                                                    class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>

</html>