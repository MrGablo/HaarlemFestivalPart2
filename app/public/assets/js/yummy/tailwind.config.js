window.tailwind = window.tailwind || {};
window.tailwind.config = {
    corePlugins: {
        preflight: false
    },
    theme: {
        extend: {
            colors: {
                yummy: {
                    primary: '#FDF3D8',
                    secondary: '#F8E1AC'
                }
            },
            fontFamily: {
                display: ['Cormorant Garamond', 'Times New Roman', 'serif'],
                body: ['Manrope', 'Segoe UI', 'sans-serif']
            }
        }
    }
};