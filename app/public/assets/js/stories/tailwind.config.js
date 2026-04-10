window.tailwind = window.tailwind || {};
window.tailwind.config = {
    theme: {
        extend: {
            colors: {
                stories: {
                    bg: '#000000',
                    panel: '#000000',
                    border: 'rgba(255, 255, 255, 0.2)',
                    text: '#ffffff',
                    accent: '#ff3d2e',
                    accentDark: '#d92b1e'
                }
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            }
        }
    }
};