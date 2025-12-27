document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    const tableBody = document.querySelector('.table tbody');
    const paginationContainer = document.querySelector('.pagination');
    let currentSearchTerm = '';

    const emptyStateHtml = `
        <tr>
            <td colspan="6">
                <div class="empty-state">
                    <span class="material-symbols-outlined empty-icon">search_off</span>
                    <p class="empty-text">Nie znaleziono ogłoszeń.</p>
                </div>
            </td>
        </tr>
    `;

    function fetchListings(searchTerm, page = 1) {
        fetch("/search", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                search: searchTerm,
                page: page 
            })
        })
        .then(response => response.json())
        .then(data => {
            tableBody.innerHTML = "";
            const listings = data.listings;
            const pagination = data.pagination;

            if (listings.length === 0) {
                tableBody.innerHTML = emptyStateHtml;
                const countElem = document.querySelector('.content-count');
                if(countElem) countElem.innerText = 'Znaleziono: 0';
            } else {
                listings.forEach(listing => createListingRow(listing));
                const countElem = document.querySelector('.content-count');
                if(countElem) countElem.innerText = `Wyniki: ${listings.length}`;
            }

            renderPagination(pagination.currentPage, pagination.totalPages, searchTerm);
        })
        .catch(error => console.error('Error:', error));
    }

    if (searchInput) {
        searchInput.addEventListener('keyup', function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
            }
            currentSearchTerm = this.value;
            fetchListings(currentSearchTerm, 1);
        });
    }

    function createListingRow(listing) {
        const template = document.querySelector("#listing-row-template");
        if (!template) return;
        
        const clone = template.content.cloneNode(true);

        clone.querySelector('.item-name').textContent = listing.item_name;
        clone.querySelector('.item-level').textContent = listing.level;
        clone.querySelector('.item-rarity').textContent = listing.rarity; 
        
        let priceText = listing.price;
        if(listing.currency === 'pln') priceText += ' PLN';
        else if(listing.currency === 'w grze') priceText += ' złota';
        else priceText += ' ' + (listing.currency || '');
        
        clone.querySelector('.price-text').textContent = priceText;
        clone.querySelector('.item-email').textContent = listing.contact;
        clone.querySelector('.item-server').textContent = listing.server;

        tableBody.appendChild(clone);
    }

    function renderPagination(currentPage, totalPages, searchTerm) {
        if (!paginationContainer) return;
        paginationContainer.innerHTML = '';

        if (totalPages <= 1) return;

        let html = '';

        if (currentPage > 1) {
            html += createPaginationButton(currentPage - 1, 'chevron_left', false, searchTerm);
        }

        const start = Math.max(1, currentPage - 2);
        const end = Math.min(totalPages, currentPage + 2);

        if (start > 1) {
            html += createPaginationButton(1, '1', currentPage === 1, searchTerm);
            if (start > 2) html += '<span class="pagination-dots">...</span>';
        }

        for (let i = start; i <= end; i++) {
            html += createPaginationButton(i, i, currentPage === i, searchTerm);
        }

        if (end < totalPages) {
            if (end < totalPages - 1) html += '<span class="pagination-dots">...</span>';
            html += createPaginationButton(totalPages, totalPages, currentPage === totalPages, searchTerm);
        }

        if (currentPage < totalPages) {
            html += createPaginationButton(currentPage + 1, 'chevron_right', false, searchTerm);
        }

        paginationContainer.innerHTML = html;
        attachPaginationEvents();
    }

    function createPaginationButton(page, content, isActive, searchTerm) {
        const activeClass = isActive ? 'active' : '';
        const isIcon = typeof content === 'string' && (content.includes('chevron'));
        const contentHtml = isIcon ? `<span class="material-symbols-outlined">${content}</span>` : content;
        
        return `<a href="#" class="pagination-btn ${activeClass}" data-page="${page}">${contentHtml}</a>`;
    }

    function attachPaginationEvents() {
        const buttons = paginationContainer.querySelectorAll('.pagination-btn');
        buttons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.dataset.page);
                fetchListings(currentSearchTerm, page);
                
                const contentArea = document.querySelector('.content-area');
                if(contentArea) contentArea.scrollIntoView({ behavior: 'smooth' });
            });
        });
    }
});