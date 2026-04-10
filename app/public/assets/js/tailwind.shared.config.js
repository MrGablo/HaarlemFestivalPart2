(function () {
    var sharedConfig = {
        corePlugins: {
            preflight: false
        },
        theme: {
            extend: {
                colors: {
                    ui: {
                        canvas: '#f8fafc',
                        surface: '#ffffff',
                        surfaceAlt: '#f1f5f9',
                        ink: '#0f172a',
                        muted: '#475569',
                        line: '#cbd5e1',
                        primary: '#2563eb',
                        primaryDark: '#1d4ed8',
                        success: '#059669',
                        danger: '#dc2626',
                        warning: '#d97706'
                    }
                },
                fontFamily: {
                    uiSans: ['Inter', 'system-ui', 'sans-serif'],
                    uiDisplay: ['Poppins', 'Inter', 'system-ui', 'sans-serif']
                },
                borderRadius: {
                    ui: '1rem',
                    uiLg: '1.5rem'
                },
                boxShadow: {
                    uiCard: '0 16px 40px rgba(15, 23, 42, 0.08)',
                    uiSoft: '0 10px 24px rgba(15, 23, 42, 0.06)',
                    uiFocus: '0 0 0 4px rgba(37, 99, 235, 0.18)'
                },
                maxWidth: {
                    uiShell: '1200px'
                }
            }
        },
        plugins: [
            function (_ref) {
                var addComponents = _ref.addComponents;
                var addUtilities = _ref.addUtilities;

                addComponents({
                    '.ui-card': {
                        backgroundColor: '#ffffff',
                        borderRadius: '1rem',
                        border: '1px solid #e2e8f0',
                        boxShadow: '0 16px 40px rgba(15, 23, 42, 0.08)'
                    },
                    '.ui-card-soft': {
                        backgroundColor: '#ffffff',
                        borderRadius: '1rem',
                        border: '1px solid #e2e8f0',
                        boxShadow: '0 10px 24px rgba(15, 23, 42, 0.06)'
                    },
                    '.ui-input': {
                        width: '100%',
                        borderRadius: '0.75rem',
                        border: '1px solid #cbd5e1',
                        backgroundColor: '#ffffff',
                        padding: '0.625rem 0.875rem',
                        color: '#0f172a',
                        transition: 'border-color 0.2s ease, box-shadow 0.2s ease'
                    },
                    '.ui-input:focus': {
                        borderColor: '#2563eb',
                        boxShadow: '0 0 0 4px rgba(37, 99, 235, 0.18)',
                        outline: 'none'
                    },
                    '.ui-button-primary': {
                        display: 'inline-flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        borderRadius: '0.75rem',
                        backgroundColor: '#2563eb',
                        padding: '0.625rem 1rem',
                        fontWeight: '600',
                        color: '#ffffff',
                        transition: 'background-color 0.2s ease, transform 0.2s ease'
                    },
                    '.ui-button-primary:hover': {
                        backgroundColor: '#1d4ed8'
                    },
                    '.ui-button-secondary': {
                        display: 'inline-flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        borderRadius: '0.75rem',
                        border: '1px solid #cbd5e1',
                        backgroundColor: '#ffffff',
                        padding: '0.625rem 1rem',
                        fontWeight: '600',
                        color: '#0f172a',
                        transition: 'background-color 0.2s ease, border-color 0.2s ease'
                    },
                    '.ui-button-secondary:hover': {
                        backgroundColor: '#f8fafc',
                        borderColor: '#94a3b8'
                    },
                    '.ui-badge': {
                        display: 'inline-flex',
                        alignItems: 'center',
                        borderRadius: '9999px',
                        backgroundColor: '#eff6ff',
                        padding: '0.25rem 0.625rem',
                        fontSize: '0.75rem',
                        fontWeight: '700',
                        color: '#1d4ed8'
                    }
                });

                addUtilities({
                    '.hide-scrollbar': {
                        '-ms-overflow-style': 'none',
                        'scrollbar-width': 'none'
                    },
                    '.hide-scrollbar::-webkit-scrollbar': {
                        display: 'none'
                    }
                });
            }
        ]
    };

    function isPlainObject(value) {
        return Object.prototype.toString.call(value) === '[object Object]';
    }

    function mergeConfig(target, source) {
        var output = isPlainObject(target) ? Object.assign({}, target) : {};

        Object.keys(source).forEach(function (key) {
            var sourceValue = source[key];
            var targetValue = output[key];

            if (Array.isArray(sourceValue) && Array.isArray(targetValue)) {
                output[key] = targetValue.concat(sourceValue);
                return;
            }

            if (isPlainObject(sourceValue) && isPlainObject(targetValue)) {
                output[key] = mergeConfig(targetValue, sourceValue);
                return;
            }

            if (Array.isArray(sourceValue)) {
                output[key] = sourceValue.slice();
                return;
            }

            if (isPlainObject(sourceValue)) {
                output[key] = mergeConfig({}, sourceValue);
                return;
            }

            output[key] = sourceValue;
        });

        return output;
    }

    window.tailwind = window.tailwind || {};
    window.tailwind.config = mergeConfig(window.tailwind.config || {}, sharedConfig);

    if (typeof window.tailwind.refresh === 'function') {
        window.tailwind.refresh();
    }
})();