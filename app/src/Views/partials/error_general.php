<?php
// Expects $errors array available and possibly $key (default 'general')
$errors = $errors ?? [];
$key = $key ?? 'general';
if (!empty($errors[$key])): ?>
  <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
    <?= htmlspecialchars($errors[$key]) ?>
  </div>
<?php endif; ?>
