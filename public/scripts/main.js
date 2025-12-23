if (typeof tailwind !== 'undefined') {
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    primary: "#d4af37",
                    primaryHover: "#b5952f",
                    bgDark: "#0f172a",
                    cardDark: "#1e293b",
                    inputDark: "#334155",
                },
                fontFamily: {
                    "display": ["Space Grotesk", "sans-serif"]
                }
            },
        },
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('[data-alert]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const deleteForms = document.querySelectorAll('form[data-confirm]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', (e) => {
            const message = form.dataset.confirm || 'Czy na pewno chcesz wykonać tę akcję?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
});

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInputs = document.querySelectorAll('input[data-live-search]');
    
    searchInputs.forEach(input => {
        const debouncedSubmit = debounce(() => {
            input.closest('form')?.submit();
        }, 500);
        
        input.addEventListener('input', debouncedSubmit);
    });
});

function smoothScrollTo(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

const Toast = {
    container: null,
    
    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(this.container);
        }
    },
    
    show(message, type = 'info') {
        this.init();
        
        const colors = {
            success: 'bg-green-500/10 border-green-500/20 text-green-400',
            error: 'bg-red-500/10 border-red-500/20 text-red-400',
            info: 'bg-blue-500/10 border-blue-500/20 text-blue-400',
            warning: 'bg-yellow-500/10 border-yellow-500/20 text-yellow-400'
        };
        
        const icons = {
            success: 'check_circle',
            error: 'error',
            info: 'info',
            warning: 'warning'
        };
        
        const toast = document.createElement('div');
        toast.className = `flex items-start gap-3 p-4 rounded-lg border ${colors[type]} backdrop-blur-sm shadow-lg min-w-[300px] animate-fade-in`;
        toast.innerHTML = `
            <span class="material-symbols-outlined text-[20px] mt-0.5">${icons[type]}</span>
            <span class="flex-1">${message}</span>
            <button onclick="this.parentElement.remove()" class="opacity-50 hover:opacity-100">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        `;
        
        this.container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s ease-out';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
};

window.Toast = Toast;
window.smoothScrollTo = smoothScrollTo;