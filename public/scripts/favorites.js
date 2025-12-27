document.addEventListener('DOMContentLoaded', function() {
    initializeFavorites();
});

function initializeFavorites() {
    document.addEventListener('click', function(e) {
        const button = e.target.closest('.favorite-btn');
        if (button) {
            e.preventDefault();
            const listingId = parseInt(button.dataset.listingId);
            if (listingId) {
                toggleFavorite(listingId, button);
            }
        }
    });
    
    addAnimationStyles();
}

function toggleFavorite(listingId, btnElement) {
    const isFavorite = btnElement.classList.contains('is-favorite');
    
    btnElement.disabled = true;
    btnElement.style.opacity = '0.5';

    const formData = new URLSearchParams();
    formData.append('listing_id', listingId);

    fetch('/favorite-toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            btnElement.style.transform = 'scale(1.3)';
            setTimeout(() => {
                btnElement.style.transform = 'scale(1)';
            }, 200);

            if (data.isFavorite) {
                btnElement.classList.add('is-favorite');
                btnElement.title = 'Usuń z ulubionych';
            } else {
                btnElement.classList.remove('is-favorite');
                btnElement.title = 'Dodaj do ulubionych';
            }
            
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'Wystąpił błąd', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Wystąpił błąd połączenia.', 'error');
    })
    .finally(() => {
        btnElement.disabled = false;
        btnElement.style.opacity = '1';
    });
}

function showNotification(message, type) {
    const existing = document.querySelector('.favorite-notification');
    if (existing) {
        existing.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = 'favorite-notification';
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 12px 20px;
        background-color: ${type === 'success' ? 'rgba(34, 197, 94, 0.95)' : 'rgba(239, 68, 68, 0.95)'};
        color: white;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 8px;
        animation: slideIn 0.3s ease-out;
    `;
    
    notification.innerHTML = `
        <span class="material-symbols-outlined" style="font-size: 20px;">
            ${type === 'success' ? 'check_circle' : 'error'}
        </span>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

function addAnimationStyles() {
    if (!document.getElementById('favorite-animations')) {
        const style = document.createElement('style');
        style.id = 'favorite-animations';
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            .favorite-notification {
                transition: opacity 0.3s ease-out, transform 0.3s ease-out;
            }
        `;
        document.head.appendChild(style);
    }
}

function filterListings(status) {
    const cards = document.querySelectorAll('.listing-card');
    const buttons = document.querySelectorAll('.filter-btn');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    document.getElementById(`filter-${status}`).classList.add('active');
    
    cards.forEach(card => {
        if (status === 'all') {
            card.style.display = 'flex';
        } else {
            const cardStatus = card.getAttribute('data-status');
            card.style.display = cardStatus === status ? 'flex' : 'none';
        }
    });
}