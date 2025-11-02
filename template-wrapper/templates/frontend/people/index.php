<?php
/**
 * People Management Page
 *
 * Lists all users in the account with their roles and access.
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id = get_current_user_id();

PFOB_Template::header( 'People' );
?>

<div class="pfob-container pfob-people-page">
    <div class="pfob-page-header">
        <h1>People</h1>
        <a href="<?php echo home_url( '/projectfob/people/invite' ); ?>" class="pfob-btn pfob-btn-primary">
            + Invite People
        </a>
    </div>

    <div class="pfob-people-filters">
        <button class="pfob-filter-btn active" data-filter="all">All</button>
        <button class="pfob-filter-btn" data-filter="team_member">Team Members</button>
        <button class="pfob-filter-btn" data-filter="contractor">Contractors</button>
        <button class="pfob-filter-btn" data-filter="client">Clients</button>
    </div>

    <div id="people-list" class="pfob-people-list">
        <div class="pfob-loading">Loading people...</div>
    </div>
</div>

<style>
.pfob-people-page {
    max-width: 1200px;
    margin: 0 auto;
}

.pfob-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.pfob-people-filters {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
}

.pfob-filter-btn {
    padding: 8px 16px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.pfob-filter-btn.active {
    background: #0066cc;
    color: white;
    border-color: #0066cc;
}

.pfob-people-list {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.pfob-person-card {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
}

.pfob-person-card:hover {
    background: #f8f9fa;
}

.pfob-person-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #6b46c1;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
    margin-right: 16px;
    flex-shrink: 0;
}

.pfob-person-info {
    flex: 1;
}

.pfob-person-name {
    font-weight: 500;
    font-size: 16px;
    margin-bottom: 4px;
}

.pfob-person-meta {
    font-size: 14px;
    color: #666;
}

.pfob-person-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    margin-left: 8px;
}

.pfob-person-badge.team_member {
    background: #e8f5e9;
    color: #2e7d32;
}

.pfob-person-badge.contractor {
    background: #fff3e0;
    color: #ef6c00;
}

.pfob-person-badge.client {
    background: #e3f2fd;
    color: #1976d2;
}

.pfob-person-actions {
    display: flex;
    gap: 8px;
}

.pfob-person-actions button {
    padding: 6px 12px;
    font-size: 14px;
}

.pfob-loading {
    text-align: center;
    padding: 40px;
    color: #666;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let allPeople = [];
    let currentFilter = 'all';

    loadPeople();

    // Filter buttons
    document.querySelectorAll('.pfob-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.pfob-filter-btn').forEach(b => b.classList.remove('active'));
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
                document.getElementById('people-list').innerHTML = '<div class="pfob-loading">Failed to load people</div>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('people-list').innerHTML = '<div class="pfob-loading">Error loading people</div>';
        }
    }

    function renderPeople() {
        const filteredPeople = currentFilter === 'all'
            ? allPeople
            : allPeople.filter(p => p.user_type === currentFilter);

        if (filteredPeople.length === 0) {
            document.getElementById('people-list').innerHTML = '<div class="pfob-loading">No people found</div>';
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
                <div class="pfob-person-card" data-user-id="${person.id}">
                    <div class="pfob-person-avatar">${initials}</div>
                    <div class="pfob-person-info">
                        <div class="pfob-person-name">${person.name}</div>
                        <div class="pfob-person-meta">
                            ${person.email}
                            ${person.job_title ? ` • ${person.job_title}` : ''}
                            ${person.organization ? ` • ${person.organization}` : ''}
                            <span class="pfob-person-badge ${person.user_type}">${typeName}</span>
                        </div>
                    </div>
                    <div class="pfob-person-actions">
                        <a href="${pfobData.homeUrl}/projectfob/people/${person.id}/projects" class="pfob-btn pfob-btn-secondary">
                            Manage Projects
                        </a>
                        <button class="pfob-btn pfob-btn-secondary" onclick="editPerson(${person.id})">Edit</button>
                        <button class="pfob-btn pfob-btn-danger" onclick="removePerson(${person.id})">Remove</button>
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
