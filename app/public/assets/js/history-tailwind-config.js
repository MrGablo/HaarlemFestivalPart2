window.tailwind = window.tailwind || {};
window.tailwind.config = {
    corePlugins: {
        preflight: false,
    },
    theme: {
        extend: {
            colors: {
                history: {
                    paper: '#faf7ef',
                    cream: '#fffaf1',
                    ink: '#171717',
                    muted: '#555555',
                    warm: '#8d7e63',
                    sand: '#d7cfbf',
                    gold: '#f3d6a2',
                    olive: '#7e8552',
                    oliveDark: '#697043',
                    charcoal: '#121212',
                },
            },
            fontFamily: {
                historyDisplay: ['"Cormorant Garamond"', 'Georgia', 'serif'],
                historySans: ['"Source Sans 3"', 'Segoe UI', 'Tahoma', 'Geneva', 'Verdana', 'sans-serif'],
            },
            boxShadow: {
                historySoft: '0 18px 60px rgba(0, 0, 0, 0.08)',
                historyCard: '0 18px 60px rgba(0, 0, 0, 0.06)',
                historyMedia: '0 18px 60px rgba(0, 0, 0, 0.10)',
                historyInset: '0 12px 40px rgba(0, 0, 0, 0.05)',
            },
            borderRadius: {
                historyLg: '28px',
                historyXl: '32px',
            },
            maxWidth: {
                history: '1200px',
            },
        },
    },
};