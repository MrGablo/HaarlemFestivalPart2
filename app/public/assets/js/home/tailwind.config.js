tailwind.config = {
    corePlugins: {
        preflight: false
    },
    theme: {
        extend: {
            backgroundColor: {
                'stat-highlight': 'var(--tw-after-bg-color)',
            }
        }
    },
    plugins: [
        function ({ addUtilities }) {
            addUtilities({
                '.hide-scrollbar': {
                    '-ms-overflow-style': 'none',
                    'scrollbar-width': 'none',
                    '&::-webkit-scrollbar': {
                        display: 'none'
                    }
                }
            })
        }
    ]
}