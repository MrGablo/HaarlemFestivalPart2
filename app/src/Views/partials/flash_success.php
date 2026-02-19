<?php
// Expects $flashSuccess to be set (string|null)
$flashSuccess = $flashSuccess ?? null;
if (!empty($flashSuccess)): ?>
  <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
    <?= htmlspecialchars($flashSuccess) ?>
  </div>
<?php endif; ?>
