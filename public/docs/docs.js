// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const endpoints = document.querySelectorAll('.endpoint');

            endpoints.forEach(endpoint => {
                const text = endpoint.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    endpoint.style.display = 'block';
                } else {
                    endpoint.style.display = 'none';
                }
            });
        });
    }
});

// Copy to clipboard
function copyToClipboard(text) {
    const baseUrl = window.location.origin;
    const fullUrl = baseUrl + text;

    navigator.clipboard.writeText(fullUrl).then(() => {
        const btn = event.target;
        const originalText = btn.textContent;
        btn.textContent = 'Copied!';
        btn.style.background = '#28a745';

        setTimeout(() => {
            btn.textContent = originalText;
            btn.style.background = '#667eea';
        }, 2000);
    }).catch(err => {
        alert('Failed to copy: ' + err);
    });
}
