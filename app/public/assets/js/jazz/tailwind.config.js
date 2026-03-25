window.tailwind = window.tailwind || {};
window.tailwind.config = {
	theme: {
		extend: {
			colors: {
				jazz: {
					dark: '#0b0b0b',
					accent: '#f7c600',
					'accent-text': '#111',
					'button-dark': '#2b2b2b',
				},
			},
			maxWidth: {
				'jazz-container': '1200px',
				'jazz-hero': '980px',
				'jazz-hero-content': '900px',
				'jazz-intro': '1000px',
				'jazz-text': '820px',
				'jazz-album-text': '860px',
			},
			width: {
				'jazz-media': '360px',
			},
			gridTemplateColumns: {
				'jazz-events-rail': '26px 1fr',
				'jazz-events-rail-sm': '12px 1fr',
				'jazz-event-row': '360px 1fr 220px',
				'jazz-album': '560px 1fr',
			},
			backgroundImage: {
				'jazz-accent-rail': 'linear-gradient(180deg, #f7c600, rgba(247,198,0,.35))',
			},
		},
	},
};
