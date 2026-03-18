/**
 * Shared Tailwind theme for the Dance section (Tailwind CDN).
 * Must load AFTER https://cdn.tailwindcss.com (otherwise `tailwind` is undefined).
 */
(function () {
  if (typeof tailwind === 'undefined') return;
  var ctaGlow = '#410000';
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          'dance-bg': '#191717',
          'dance-surface': '#0d0d0d',
          'dance-text': '#F9F9F9',
          'dance-muted': 'rgba(249,249,249,0.85)',
          'dance-text-subtle': 'rgba(249,249,249,0.8)',
          'dance-text-strong': 'rgba(255,255,255,0.95)',
          'dance-on-dark': '#ffffff',
          'dance-accent': '#E60000',
          'dance-accent-soft': 'rgba(230,0,0,0.45)',
          'dance-accent-hover': 'rgba(230,0,0,0.65)',
          'dance-row-glass': 'rgba(255,255,255,0.17)',
          'dance-cta-glow': ctaGlow,
          'dance-strip-bg': '#000000',
          'dance-toast-bg': '#18181b',
          'dance-toast-border': 'rgba(255,255,255,0.15)',
          'dance-toast-subtle': '#d4d4d8',
          'dance-overlay-from': 'rgba(0,0,0,0.8)',
          'dance-overlay-via': 'rgba(0,0,0,0.4)',
          'dance-hero-cta-bg': 'rgba(255,255,255,0.2)',
        },
        maxWidth: {
          'dance-container': '1200px',
          'dance-timetable': '1175px',
          'dance-hero-sub': '615px',
          'dance-lineup-name': '14rem',
          'dance-intro-img': '28rem',
        },
        minHeight: {
          'dance-strip': '67px',
          'dance-hero': '90vh',
          'dance-row': '3.5rem',
        },
        width: {
          'dance-slot-price': '6rem',
          'dance-slot-time': '9rem',
          'dance-slot-venue': '9rem',
          'dance-btn': '6rem',
          'dance-photo-sm': '7rem',
          'dance-photo-lg': '9rem',
        },
        height: {
          'dance-btn': '2.75rem',
          'dance-icon': '1rem',
          'dance-photo-sm': '7rem',
          'dance-photo-lg': '9rem',
        },
        fontSize: {
          'dance-hero-title': ['clamp(3rem,10vw,6rem)', { lineHeight: '1.05', fontWeight: '700' }],
          'dance-strip-text': ['clamp(1.25rem,4vw,2.4375rem)', { lineHeight: '1.2' }],
          'dance-tag': ['0.625rem', { lineHeight: '1.25' }],
        },
        zIndex: {
          'dance-toast': '1200',
        },
        boxShadow: {
          'dance-cta': '0 0 2.6px 0 ' + ctaGlow,
        },
        dropShadow: {
          'dance-hero': '0 4px 4px rgba(0,0,0,0.32)',
        },
      },
    },
  };
})();
