const searchInput = document.querySelector('input[name="search"]');
const tableBody = document.querySelector('.table tbody');
const emptyStateHtml = `
    <tr>
        <td colspan="7">
            <div class="empty-state">
                <span class="material-symbols-outlined empty-icon">search_off</span>
                <p class="empty-text">Nie znaleziono ogłoszeń.</p>
            </div>
        </td>
    </tr>
`;

if (searchInput) {
    searchInput.addEventListener('keyup', function (event) {
        if (event.key === "Enter") {
            event.preventDefault();
        }

        const searchValue = this.value;

        fetch("/search", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ search: searchValue })
        })
        .then(response => response.json())
        .then(listings => {
            tableBody.innerHTML = "";

            if (listings.length === 0) {
                tableBody.innerHTML = emptyStateHtml;
                document.querySelector('.content-count').innerText = 'Znaleziono: 0';
            } else {
                listings.forEach(listing => createListingRow(listing));
                document.querySelector('.content-count').innerText = 'Znaleziono: ' + listings.length;
            }
        })
        .catch(error => console.error('Error:', error));
    });
}

function createListingRow(listing) {
    const template = document.querySelector("#listing-row-template");
    const clone = template.content.cloneNode(true);

    const imgContainer = clone.querySelector('.item-image');
    if (listing.image) {
        imgContainer.style.backgroundImage = `url('/public/uploads/${listing.image}')`;
    } else {
        imgContainer.innerHTML = '<span class="material-symbols-outlined" style="color: #64748b;">inventory_2</span>';
    }

    clone.querySelector('.item-name').textContent = listing.item_name || listing.title;
    clone.querySelector('.item-level').textContent = listing.level;
    clone.querySelector('.item-rarity').textContent = listing.rarity_name || listing.rarity_id || 'Item'; 
    clone.querySelector('.price-text').textContent = listing.price + ' ZŁ';
    clone.querySelector('.item-email').textContent = listing.email || 'Kontakt';
    clone.querySelector('.item-server').textContent = listing.server_name || listing.server_id || 'Serwer';

    tableBody.appendChild(clone);
}