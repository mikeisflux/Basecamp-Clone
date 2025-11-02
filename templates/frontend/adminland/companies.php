<?php
/**
 * Companies Management Page
 *
 * Allows administrators to manage client companies and organizations.
 * Companies help organize clients and their associated projects.
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id = get_current_user_id();

PFOB_Template::header( 'Manage Companies' );
?>

<div class="companies-page-wrapper">
    <a href="<?php echo home_url( '/projectfob/adminland' ); ?>" class="back-link">← Back to Adminland</a>

    <h1 class="page-title">Manage Companies</h1>
    <p class="page-subtitle">Organize client companies and track which people belong to each organization.</p>

    <!-- Create New Company -->
    <div class="companies-section">
        <h2 class="section-title">Add New Company</h2>

        <div class="create-company-form">
            <div class="form-row">
                <label class="form-label">Company Name</label>
                <input type="text" id="new-company-name" class="form-input" placeholder="e.g., Acme Corporation">
            </div>

            <div class="form-row">
                <label class="form-label">Industry (Optional)</label>
                <input type="text" id="new-company-industry" class="form-input" placeholder="e.g., Technology, Healthcare, Finance">
            </div>

            <div class="form-row">
                <label class="form-label">Website (Optional)</label>
                <input type="url" id="new-company-website" class="form-input" placeholder="https://example.com">
            </div>

            <div class="form-row">
                <label class="form-label">Notes (Optional)</label>
                <textarea id="new-company-notes" class="form-textarea" rows="3" placeholder="Additional information about this company..."></textarea>
            </div>

            <button class="action-button primary-button" onclick="createCompany()">Add Company</button>
        </div>
    </div>

    <!-- Existing Companies -->
    <div class="companies-section">
        <h2 class="section-title">Your Companies</h2>

        <div id="companies-list" class="companies-list">
            <div class="loading-message">Loading companies...</div>
        </div>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.companies-page-wrapper {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.back-link {
    display: inline-block;
    margin-bottom: 16px;
    color: #0066cc;
    text-decoration: none;
    font-size: 15px;
}

.back-link:hover {
    text-decoration: underline;
}

.page-title {
    font-size: 32px;
    margin: 0 0 8px 0;
    color: #333;
}

.page-subtitle {
    font-size: 16px;
    color: #666;
    margin: 0 0 32px 0;
}

.companies-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 32px;
    margin-bottom: 24px;
}

.section-title {
    font-size: 20px;
    margin: 0 0 20px 0;
    color: #333;
}

.create-company-form {
    max-width: 600px;
}

.form-row {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-size: 15px;
    font-weight: 500;
    color: #333;
    margin-bottom: 8px;
}

.form-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    font-family: inherit;
}

.form-input:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.form-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    font-family: inherit;
    resize: vertical;
}

.form-textarea:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.action-button {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    border: none;
}

.primary-button {
    background: #0066cc;
    color: white;
}

.primary-button:hover {
    background: #0052a3;
}

.secondary-button {
    background: #e5e7eb;
    color: #333;
    border: 1px solid #d1d5db;
}

.secondary-button:hover {
    background: #d1d5db;
}

.danger-button {
    background: #dc3545;
    color: white;
}

.danger-button:hover {
    background: #c82333;
}

.companies-list {
    min-height: 200px;
}

.company-card {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 20px;
    margin-bottom: 16px;
}

.company-header {
    margin-bottom: 12px;
}

.company-name {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.company-industry {
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
}

.company-details {
    margin-bottom: 12px;
}

.company-detail-row {
    font-size: 14px;
    color: #666;
    margin-bottom: 4px;
}

.detail-label {
    font-weight: 500;
    color: #333;
}

.company-meta {
    font-size: 13px;
    color: #666;
    margin-bottom: 12px;
}

.company-people {
    margin-bottom: 12px;
}

.people-label {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    margin-bottom: 8px;
}

.people-list {
    padding-left: 20px;
    margin-bottom: 12px;
}

.people-item {
    font-size: 14px;
    color: #666;
    margin-bottom: 4px;
}

.company-actions {
    padding-top: 12px;
    border-top: 1px solid #e0e0e0;
}

.company-actions .action-button {
    margin-right: 8px;
    margin-bottom: 8px;
    padding: 6px 12px;
    font-size: 14px;
}

.loading-message {
    text-align: center;
    padding: 40px;
    color: #666;
    font-size: 15px;
}

.empty-message {
    text-align: center;
    padding: 40px;
    color: #666;
    font-style: italic;
}

@media (max-width: 768px) {
    .companies-page-wrapper {
        padding: 12px;
    }

    .companies-section {
        padding: 20px;
    }

    .page-title {
        font-size: 24px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadCompanies();

    window.createCompany = createCompany;
    window.editCompany = editCompany;
    window.deleteCompany = deleteCompany;
    window.managePeople = managePeople;
});

async function loadCompanies() {
    try {
        const response = await fetch(`${pfobData.restUrl}/account/companies`, {
            headers: {
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success) {
            renderCompanies(data.companies || []);
        } else {
            document.getElementById('companies-list').innerHTML = '<div class="empty-message">Failed to load companies</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        // For now, show placeholder since API might not be built yet
        renderPlaceholderCompanies();
    }
}

function renderCompanies(companies) {
    if (companies.length === 0) {
        document.getElementById('companies-list').innerHTML = '<div class="empty-message">No companies yet. Add your first company above!</div>';
        return;
    }

    const html = companies.map(company => `
        <div class="company-card">
            <div class="company-header">
                <div class="company-name">${company.name}</div>
                ${company.industry ? `<div class="company-industry">${company.industry}</div>` : ''}
            </div>

            <div class="company-details">
                ${company.website ? `
                    <div class="company-detail-row">
                        <span class="detail-label">Website:</span>
                        <a href="${company.website}" target="_blank" style="color: #0066cc;">${company.website}</a>
                    </div>
                ` : ''}
                ${company.notes ? `
                    <div class="company-detail-row">
                        <span class="detail-label">Notes:</span> ${company.notes}
                    </div>
                ` : ''}
            </div>

            <div class="company-meta">
                Added ${company.created_at} • ${company.people_count || 0} people
            </div>

            ${company.people && company.people.length > 0 ? `
                <div class="company-people">
                    <div class="people-label">People:</div>
                    <div class="people-list">
                        ${company.people.map(p => `<div class="people-item">• ${p.name} (${p.role || 'Member'})</div>`).join('')}
                    </div>
                </div>
            ` : ''}

            <div class="company-actions">
                <button class="action-button primary-button" onclick="managePeople(${company.id})">Manage People</button>
                <button class="action-button secondary-button" onclick="editCompany(${company.id})">Edit</button>
                <button class="action-button danger-button" onclick="deleteCompany(${company.id})">Delete</button>
            </div>
        </div>
    `).join('');

    document.getElementById('companies-list').innerHTML = html;
}

function renderPlaceholderCompanies() {
    // Show example companies since API is not built yet
    const placeholderCompanies = [
        {
            id: 1,
            name: 'Acme Corporation',
            industry: 'Technology',
            website: 'https://acme.example.com',
            notes: 'Primary client for Q1 2025',
            created_at: 'January 10, 2025',
            people_count: 4,
            people: [
                { name: 'John Doe', role: 'Project Manager' },
                { name: 'Jane Smith', role: 'Developer' }
            ]
        },
        {
            id: 2,
            name: 'Global Industries Inc.',
            industry: 'Manufacturing',
            website: '',
            notes: '',
            created_at: 'December 5, 2024',
            people_count: 2,
            people: []
        }
    ];

    renderCompanies(placeholderCompanies);
}

async function createCompany() {
    const name = document.getElementById('new-company-name').value.trim();
    const industry = document.getElementById('new-company-industry').value.trim();
    const website = document.getElementById('new-company-website').value.trim();
    const notes = document.getElementById('new-company-notes').value.trim();

    if (!name) {
        alert('Please enter a company name');
        return;
    }

    try {
        const response = await fetch(`${pfobData.restUrl}/account/companies`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify({ name, industry, website, notes })
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('new-company-name').value = '';
            document.getElementById('new-company-industry').value = '';
            document.getElementById('new-company-website').value = '';
            document.getElementById('new-company-notes').value = '';
            loadCompanies();
            alert('Company added successfully!');
        } else {
            alert('Failed to create company: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Company creation API not yet implemented. This will create a new company when the backend is ready.');
    }
}

function editCompany(companyId) {
    alert('Edit company functionality coming soon. This will allow you to update company details.');
}

function deleteCompany(companyId) {
    if (!confirm('Are you sure you want to delete this company? This will not delete the people associated with it.')) {
        return;
    }

    alert('Delete company functionality coming soon. This will remove the company but keep all associated people in your account.');
}

function managePeople(companyId) {
    alert('Manage people functionality coming soon. This will let you assign or remove people from this company.');
}
</script>

<?php
PFOB_Template::footer();
