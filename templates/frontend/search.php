<?php
/**
 * Universal Search
 */

PFOB_Template::header( 'Search' );
?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-search">

        <div class="pfob-page-header">
            <h1>Search</h1>
            <p class="pfob-subtitle">Search across all your projects and content</p>
        </div>

        <div class="pfob-search-container">
            <div class="pfob-search-input-wrapper">
                <span class="pfob-search-icon">🔍</span>
                <input type="text"
                       id="search-input"
                       class="pfob-search-input"
                       placeholder="Search for projects, messages, todos, files..."
                       autocomplete="off"
                       autofocus>
                <button id="clear-search" class="pfob-search-clear" style="display: none;">×</button>
            </div>

            <div class="pfob-search-filters">
                <label>
                    <input type="checkbox" name="type" value="projects" checked> Projects
                </label>
                <label>
                    <input type="checkbox" name="type" value="messages" checked> Messages
                </label>
                <label>
                    <input type="checkbox" name="type" value="todos" checked> To-dos
                </label>
                <label>
                    <input type="checkbox" name="type" value="documents" checked> Documents
                </label>
                <label>
                    <input type="checkbox" name="type" value="events" checked> Events
                </label>
                <label>
                    <input type="checkbox" name="type" value="cards" checked> Cards
                </label>
            </div>

            <div id="search-status" class="pfob-search-status"></div>

            <div id="search-results" class="pfob-search-results"></div>

            <div class="pfob-search-empty" id="search-empty">
                <p>Start typing to search across all your content...</p>
            </div>
        </div>

    </main>
</div>

<script>
const pfobData = {
    restUrl: '<?php echo rest_url( 'pfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
};

let searchTimeout = null;
let currentQuery = '';

const searchInput = document.getElementById('search-input');
const clearButton = document.getElementById('clear-search');
const searchStatus = document.getElementById('search-status');
const searchResults = document.getElementById('search-results');
const searchEmpty = document.getElementById('search-empty');
const typeFilters = document.querySelectorAll('input[name="type"]');

searchInput.addEventListener('input', (e) => {
    const query = e.target.value.trim();

    clearButton.style.display = query ? 'block' : 'none';

    if (query.length < 2) {
        searchResults.innerHTML = '';
        searchStatus.textContent = '';
        searchEmpty.style.display = 'block';
        return;
    }

    searchEmpty.style.display = 'none';
    searchStatus.textContent = 'Searching...';

    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => performSearch(query), 300);
});

clearButton.addEventListener('click', () => {
    searchInput.value = '';
    clearButton.style.display = 'none';
    searchResults.innerHTML = '';
    searchStatus.textContent = '';
    searchEmpty.style.display = 'block';
    searchInput.focus();
});

typeFilters.forEach(filter => {
    filter.addEventListener('change', () => {
        if (currentQuery) {
            performSearch(currentQuery);
        }
    });
});

async function performSearch(query) {
    currentQuery = query;

    const selectedTypes = Array.from(typeFilters)
        .filter(f => f.checked)
        .map(f => f.value)
        .join(',');

    try {
        const response = await fetch(
            `${pfobData.restUrl}/search?q=${encodeURIComponent(query)}&types=${selectedTypes}`,
            { headers: { 'X-WP-Nonce': pfobData.nonce } }
        );

        const result = await response.json();

        if (result.success) {
            displayResults(result.data, result.total);
            searchStatus.textContent = result.total > 0
                ? `Found ${result.total} result${result.total !== 1 ? 's' : ''}`
                : '';

            if (result.total === 0) {
                searchResults.innerHTML = '<div class="pfob-no-results"><p>No results found for "' + escapeHtml(query) + '"</p></div>';
            }
        }
    } catch (error) {
        console.error('Search error:', error);
        searchStatus.textContent = 'Search failed. Please try again.';
    }
}

function displayResults(data, total) {
    if (total === 0) {
        return;
    }

    let html = '';

    const typeOrder = ['projects', 'messages', 'todos', 'documents', 'events', 'cards'];
    const typeLabels = {
        projects: 'Projects',
        messages: 'Messages',
        todos: 'To-dos',
        documents: 'Documents',
        events: 'Events',
        cards: 'Cards'
    };

    typeOrder.forEach(type => {
        if (data[type] && data[type].length > 0) {
            html += `<div class="pfob-search-group">
                <h3 class="pfob-search-group-title">${typeLabels[type]} (${data[type].length})</h3>
                <div class="pfob-search-items">`;

            data[type].forEach(item => {
                html += `
                    <a href="${item.url}" class="pfob-search-item">
                        <div class="pfob-search-item-icon">${item.icon}</div>
                        <div class="pfob-search-item-content">
                            <div class="pfob-search-item-title">${escapeHtml(item.title)}</div>
                            ${item.excerpt ? `<div class="pfob-search-item-excerpt">${escapeHtml(item.excerpt)}</div>` : ''}
                            ${item.project ? `<div class="pfob-search-item-meta">${escapeHtml(item.project)}</div>` : ''}
                            ${item.date ? `<div class="pfob-search-item-meta">${escapeHtml(item.date)}</div>` : ''}
                            ${item.filesize ? `<div class="pfob-search-item-meta">${escapeHtml(item.filesize)}</div>` : ''}
                        </div>
                    </a>
                `;
            });

            html += '</div></div>';
        }
    });

    searchResults.innerHTML = html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Keyboard navigation
searchInput.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        searchInput.value = '';
        clearButton.style.display = 'none';
        searchResults.innerHTML = '';
        searchStatus.textContent = '';
        searchEmpty.style.display = 'block';
    }
});
</script>
<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js"></script>

</body>
</html>
