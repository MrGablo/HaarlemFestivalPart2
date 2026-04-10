                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-6">
                    <a href="<?= htmlspecialchars($backRoute ?? '/cms/events') ?>"
                        class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        Cancel
                    </a>
                    <button type="submit"
                        class="rounded-xl <?= htmlspecialchars($submitClass ?? 'bg-blue-600 hover:bg-blue-700') ?> px-4 py-2 text-sm font-semibold text-white">
                        <?= htmlspecialchars($submitLabel ?? 'Save changes') ?>
                    </button>
                </div>
                </form>
                </section>
                </main>
                </body>

                </html>