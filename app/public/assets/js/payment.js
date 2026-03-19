/**
 * Stripe Checkout redirect handler.
 *
 * Any button with class "stripe-buy-btn" will trigger a Stripe Checkout.
 * Required data attributes on the button:
 *   data-event-id   = the event ID to buy
 *   data-quantity    = number of tickets (optional, defaults to 1)
 *
 * The Stripe publishable key is read from:
 *   <meta name="stripe-key" content="pk_test_...">
 */
(() => {
    // Read the publishable key from a <meta> tag
    const metaTag = document.querySelector('meta[name="stripe-key"]');
    if (!metaTag) return; // no key = payment not enabled on this page

    const stripeKey = metaTag.getAttribute('content');
    if (!stripeKey) return;

    const stripe = Stripe(stripeKey); // Stripe.js must be loaded via <script>

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.stripe-buy-btn');
        if (!btn) return;

        e.preventDefault();

        const eventId  = btn.dataset.eventId;
        const quantity = btn.dataset.quantity || 1;

        if (!eventId) {
            alert('Missing event ID.');
            return;
        }

        // Disable button while loading
        btn.disabled = true;
        const originalText = btn.textContent;
        btn.textContent = 'Loading...';

        let shouldRestore = true;

        try {
            // 1) Ask backend to create a Stripe Checkout Session
            const response = await fetch('/api/payment/create-session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    event_id: parseInt(eventId, 10),
                    quantity: parseInt(quantity, 10)
                })
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                if (response.status === 401) {
                    shouldRestore = false;
                    window.location.href = '/login';
                    return;
                }
                alert(data.message || 'Could not start checkout.');
                return;
            }

            // 2) Redirect to Stripe Checkout
            // Keep button disabled -- redirectToCheckout navigates the page on success.
            shouldRestore = false;

            const result = await stripe.redirectToCheckout({
                sessionId: data.session_id
            });

            // Only reached when the redirect itself fails
            if (result.error) {
                shouldRestore = true;
                alert(result.error.message);
            }
        } catch (err) {
            shouldRestore = true;
            console.error('Payment error:', err);
            alert('Network error. Please try again.');
        } finally {
            if (shouldRestore) {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        }
    });
})();
