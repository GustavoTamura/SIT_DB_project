// Payment page functionality

// Helper: show feedback message on the page
function showPaymentMessage(text, type = 'info') {
    const box = document.getElementById('payment-message');
    if (!box) return;

    box.textContent = text;
    box.style.display = 'block';

    // You can style these classes in styles.css if you want
    box.className = 'info-message ' + type; // e.g. "info-message error" or "info-message success"
}

// Get booking_id from query string (payment.html?booking_id=11)
const params = new URLSearchParams(window.location.search);
const bookingId = params.get('booking_id');

document.addEventListener('DOMContentLoaded', () => {
    // Show booking id on the page if available
    const bookingLabel = document.getElementById('booking-id-label');
    if (bookingLabel) {
        if (bookingId) {
            bookingLabel.textContent = ' #' + bookingId;
        } else {
            bookingLabel.textContent = '';
        }
    }

    const form = document.getElementById('payment-form');
    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const cardName   = document.getElementById('card-name').value.trim();
        const cardNumber = document.getElementById('card-number').value.trim();
        const expiryDate = document.getElementById('expiry-date').value;
        const cvv        = document.getElementById('cvv').value.trim();

        if (!cardName || !cardNumber || !expiryDate || !cvv) {
            showPaymentMessage('All fields are required.', 'error');
            return;
        }

        try {
            const response = await fetch('payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    bookingId: bookingId,
                    cardName: cardName,
                    cardNumber: cardNumber,
                    expiryDate: expiryDate,
                    cvv: cvv
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                showPaymentMessage(data.message || 'Payment failed.', 'error');
                return;
            }

            showPaymentMessage('Payment processed successfully!', 'success');

            // Redirect to home or a dedicated success page
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1500);

        } catch (error) {
            console.error('Error sending payment:', error);
            showPaymentMessage('Server connection error.', 'error');
        }
    });
});
