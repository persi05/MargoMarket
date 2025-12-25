document.addEventListener('DOMContentLoaded', function() {
    const menuBtn = document.getElementById('mobile-menu-toggle');
    const mobileNav = document.getElementById('mobile-nav-panel');
    
    if (menuBtn && mobileNav) {
        menuBtn.addEventListener('click', function() {
            mobileNav.classList.toggle('open');
            const icon = this.querySelector('.material-symbols-outlined');
            if (icon) {
                icon.textContent = mobileNav.classList.contains('open') ? 'close' : 'menu';
            }
        });
    }
    
    const deleteForms = document.querySelectorAll('form[data-confirm]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!confirm(form.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });
});