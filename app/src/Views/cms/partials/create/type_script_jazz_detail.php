<script>
    const artistSelect = document.getElementById('selected_artist_id');
    const artistNameInput = document.getElementById('content_artist_name');
    const breadcrumbCurrentInput = document.getElementById('content_artist_breadcrumb_current');
    const heroTitleInput = document.getElementById('content_artist_hero_title');
    const pageTitleInput = document.getElementById('page_title');

    if (artistSelect && artistNameInput) {
        let pageTitleManuallyEdited = pageTitleInput ? pageTitleInput.value.trim() !== '' : false;

        if (pageTitleInput) {
            pageTitleInput.addEventListener('input', () => {
                pageTitleManuallyEdited = true;
            });
        }

        const syncArtistIntoFields = () => {
            const selectedOption = artistSelect.options[artistSelect.selectedIndex] || null;
            const artistName = selectedOption ? (selectedOption.dataset.artistName || '').trim() : '';
            if (artistName === '') {
                return;
            }

            artistNameInput.value = artistName;
            if (breadcrumbCurrentInput) {
                breadcrumbCurrentInput.value = artistName;
            }
            if (heroTitleInput && heroTitleInput.value.trim() === '') {
                heroTitleInput.value = artistName;
            }
            if (pageTitleInput && !pageTitleManuallyEdited) {
                pageTitleInput.value = artistName;
            }
        };

        artistSelect.addEventListener('change', syncArtistIntoFields);
        syncArtistIntoFields();
    }
</script>
