document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.tab');
    const rows = document.querySelectorAll('#productTable tr');
    const searchBar = document.getElementById('searchBar');

    // Gestion des onglets
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const category = tab.getAttribute('data-category');
            rows.forEach(row => {
                row.style.display = row.getAttribute('data-category') === category ? '' : 'none';
            });
        });
    });

    // Recherche
    searchBar.addEventListener('input', () => {
        const searchText = searchBar.value.toLowerCase();
        rows.forEach(row => {
            const productName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            row.style.display = productName.includes(searchText) ? '' : 'none';
        });
    });
});
