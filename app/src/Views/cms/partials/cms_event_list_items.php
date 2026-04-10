<?php

/**
 * Shared component for CMS event tables.
 * 
 * Expected variables:
 * - $events: array of event objects
 * - $columns: array of shape ['label' => string, 'value' => callable(object): string]
 * - $editRoute: callable(object): string - returns the URL for editing an event
 * - $deleteRoute: callable(object): string - returns the URL for deleting an event
 * - $csrfToken: string - CSRF token for delete form
 * - $emptyMessage: string - message to show when no events are found
 */
?>
<div class="mt-6 overflow-x-auto rounded-xl border border-slate-200 bg-slate-50">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-100">
            <tr>
                <?php foreach ($columns as $col): ?>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700"><?= htmlspecialchars($col['label']) ?></th>
                <?php endforeach; ?>
                <th class="px-4 py-3 text-right font-semibold text-slate-700">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
            <?php if (empty($events)): ?>
                <tr>
                    <td colspan="<?= count($columns) + 1 ?>" class="px-4 py-6 text-center text-slate-500">
                        <?= htmlspecialchars($emptyMessage ?? 'No events found.') ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($events as $e): ?>
                    <tr class="hover:bg-slate-50">
                        <?php foreach ($columns as $col): ?>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                <?= $col['value']($e) ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?= htmlspecialchars($editRoute($e)) ?>"
                                    class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                    Edit
                                </a>
                                <form method="POST" action="<?= htmlspecialchars($deleteRoute($e)) ?>"
                                    onsubmit="return confirm('Are you sure you want to delete this event? This cannot be undone.');"
                                    class="inline-block m-0 p-0">
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