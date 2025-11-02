<?php
/**
 * People Management Page
 *
 * Lists all users in the account with their roles and access.
 * COMPLETE REWRITE - NO FLEX, NO GRID, NO COLUMNS
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id = get_current_user_id();

PFOB_Template::header( 'People' );
?>

<div class="people-page-wrapper">
    <div class="people-page-header">
        <h1 class="people-page-title">People</h1>
        <a href="<?php echo home_url( '/projectfob/people/invite' ); ?>" class="invite-button">
            + Invite People
        </a>
    </div>

    <div class="people-filters">
        <button class="filter-button active" data-filter="all">All</button>
        <button class="filter-button" data-filter="team_member">Team Members</button>
        <button class="filter-button" data-filter="contractor">Contractors</button>
        <button class="filter-button" data-filter="client">Clients</button>
    </div>

    <div id="people-list" class="people-list">
        <div class="loading-message">Loading people...</div>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.people-page-wrapper {
    max-width: 1000px;
    margin: 40px auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.people-page-header {
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #e0e0e0;
}

.people-page-title {
    font-size: 32px;
    margin: 0 0 12px 0;
    color: #333;
}

.invite-button {
    display: inline-block;
    padding: 10px 20px;
    background: #0066cc;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500;
    font-size: 15px;
}

.invite-button:hover {
    background: #0052a3;
}

.people-filters {
    margin-bottom: 24px;
}

.filter-button {
    padding: 8px 16px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    margin-right: 8px;
    margin-bottom: 8px;
    transition: all 0.2s;
}

.filter-button:hover {
    border-color: #0066cc;
}

.filter-button.active {
    background: #0066cc;
    color: white;
    border-color: #0066cc;
}

.people-list {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.person-card {
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
}

.person-card:last-child {
    border-bottom: none;
}

.person-card:hover {
    background: #f8f9fa;
}

.person-header {
    margin-bottom: 12px;
}

.person-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #6b46c1;
    color: white;
    text-align: center;
    line-height: 48px;
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 12px;
    display: inline-block;
}

.person-name {
    font-weight: 600;
    font-size: 18px;
    color: #333;
    margin-bottom: 8px;
}

.person-meta {
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
    line-height: 1.6;
}

.person-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    margin-top: 4px;
}

.person-badge.team_member {
    background: #e8f5e9;
    color: #2e7d32;
}

.person-badge.contractor {
    background: #fff3e0;
    color: #ef6c00;
}

.person-badge.client {
    background: #e3f2fd;
    color: #1976d2;
}

.person-actions {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #f5f5f5;
}

.person-action-button {
    display: inline-block;
    padding: 8px 16px;
    margin-right: 8px;
    margin-bottom: 8px;
    border-radius: 6px;
    font-size: 14px;
    text-decoration: none;
    cursor: pointer;
    border: none;
    font-weight: 500;
    transition: all 0.2s;
}

.action-primary {
    background: #0066cc;
    color: white;
}

.action-primary:hover {
    background: #0052a3;
}

.action-secondary {
    background: #e5e7eb;
    color: #333;
    border: 1px solid #d1d5db;
}

.action-secondary:hover {
    background: #d1d5db;
}

.action-danger {
    background: #dc3545;
    color: white;
}

.action-danger:hover {
    background: #c82333;
}

.loading-message {
    text-align: center;
    padding: 40px;
    color: #666;
    font-size: 15px;
}

@media (max-width: 768px) {
    .people-page-wrapper {
        padding: 12px;
    }

    .people-page-title {
        font-size: 24px;
    }

    .person-card {
        padding: 16px;
    }

    .filter-button {
        font-size: 13px;
        padding: 6px 12px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let allPeople = [];
    let currentFilter = 'all';

    loadPeople();

    // Filter buttons
    document.querySelectorAll('.filter-button').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            renderPeople();
        });
    });

    async function loadPeople() {
        try {
            const response = await fetch(`${pfobData.restUrl}/account/users`, {
                headers: {
                    'X-WP-Nonce': pfobData.nonce
                }
            });

            const data = await response.json();

            if (data.success) {
                allPeople = data.users;
                renderPeople();
            } else {
                document.getElementById('people-list').innerHTML = '<div class="loading-message">Failed to load people</div>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('people-list').innerHTML = '<div class="loading-message">Error loading people</div>';
        }
    }

    function renderPeople() {
        const filteredPeople = currentFilter === 'all'
            ? allPeople
            : allPeople.filter(p => p.user_type === currentFilter);

        if (filteredPeople.length === 0) {
            document.getElementById('people-list').innerHTML = '<div class="loading-message">No people found</div>';
            return;
        }

        const html = filteredPeople.map(person => {
            const initials = getInitials(person.name);
            const typeName = {
                'team_member': 'Team Member',
                'contractor': 'Contractor',
                'client': 'Client'
            }[person.user_type] || person.user_type;

            return `
                <div class="person-card" data-user-id="${person.id}">
                    <div class="person-header">
                        <div class="person-avatar">${initials}</div>
                    </div>
                    <div class="person-name">${person.name}</div>
                    <div class="person-meta">
                        ${person.email}<br>
                        ${person.job_title ? person.job_title : ''}
                        ${person.organization ? ' • ' + person.organization : ''}
                    </div>
                    <span class="person-badge ${person.user_type}">${typeName}</span>
                    <div class="person-actions">
                        <a href="${pfobData.homeUrl}/projectfob/people/${person.id}/projects" class="person-action-button action-primary">
                            Manage Projects
                        </a>
                        <button class="person-action-button action-secondary" onclick="editPerson(${person.id})">Edit</button>
                        <button class="person-action-button action-danger" onclick="removePerson(${person.id})">Remove</button>
                    </div>
                </div>
            `;
        }).join('');

        document.getElementById('people-list').innerHTML = html;
    }

    function getInitials(name) {
        const parts = name.trim().split(' ');
        if (parts.length === 1) {
            return parts[0].substring(0, 2).toUpperCase();
        }
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    // Make functions global
    window.editPerson = editPerson;
    window.removePerson = removePerson;

    function editPerson(userId) {
        // TODO: Show edit modal
        alert('Edit person functionality coming soon');
    }

    function removePerson(userId) {
        if (!confirm('Are you sure you want to remove this person from your account?')) {
            return;
        }

        // TODO: Implement remove API call
        alert('Remove person functionality coming soon');
    }
});
</script>

<?php
PFOB_Template::footer();
