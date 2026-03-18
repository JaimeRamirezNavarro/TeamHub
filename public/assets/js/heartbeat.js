/**
 * Heartbeat
 * Sends a pulse to the server every minute to update last_activity
 */
function sendHeartbeat() {
    fetch('/?api=heartbeat')
        .then(response => {
            if (!response.ok) console.warn('Heartbeat failed');
        })
        .catch(err => console.error('Heartbeat error:', err));
}

// Send immediately on load, then every 60 seconds
document.addEventListener('DOMContentLoaded', () => {
    sendHeartbeat();
    setInterval(sendHeartbeat, 60000);
});
