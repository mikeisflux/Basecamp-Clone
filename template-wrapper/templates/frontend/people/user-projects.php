<?php
/**
 * User Project Access Management
 *
 * Set which projects a user can access and their access level:
 * - On the project: Full access, notifications enabled
 * - Just following: Limited access, notifications only on @mention
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$account_owner_id = get_current_user_id();
$target_user_id = get_query_var( 'pfob_user' );

// Get target user
$target_user = get_userdata( $target_user_id );

if ( ! $target_user ) {
    wp_die( __( 'User not found.', 'projectfob' ), 404 );
}

// Verify this user belongs to the account owner
$user_owner = get_user_meta( $target_user_id, 'pfob_account_owner', true );
if ( $user_owner != $account_owner_id ) {
    wp_die( __( 'You do not have permission to manage this user.', 'projectfob' ), 403 );
}

$user_name = $target_user->display_name;

PFOB_Template::header( "Project Access for {$user_name}" );
?>

<div class="pfob-container pfob-user-projects-page">
    <div class="pfob-page-header">
        <a href="<?php echo home_url( '/projectfob/people' ); ?>" class="pfob-back-link">← Back to People</a>
        <h1>What can <?php echo esc_html( $user_name ); ?> access?</h1>
        <p>First, check off the projects they should be able to access. Then, decide if they should be "On the project" (their avatar will show up at the top) or "Just following" (they won't be notified unless someone specifically @mentions them).</p>
    </div>

    <div class="pfob-bulk-actions">
        <button class="pfob-btn pfob-btn-secondary" id="check-all">Check all</button>
        <button class="pfob-btn pfob-btn-secondary" id="check-none">Check none</button>
        <button class="pfob-btn pfob-btn-secondary" id="all-on-project">All "On the project"</button>
        <button class="pfob-btn pfob-btn-secondary" id="all-following">All "Just following"</button>
    </div>

    <div id="projects-list" class="pfob-projects-list">
        <div class="pfob-loading">Loading projects...</div>
    </div>

    <div class="pfob-next-steps">
        <h3>What happens next?</h3>
        <p>We'll send <strong><?php echo esc_html( $user_name ); ?></strong> a single email listing all the projects you've added them to. They will then be able to see everything in those projects, start posting, and interact with the rest of the team. If they haven't signed into ProjectFOB before, they'll get instructions on how to join.</p>
    </div>

    <div class="pfob-form-actions">
        <button id="save-changes" class="pfob-btn pfob-btn-primary pfob-btn-large">
            Save changes for <?php echo esc_html( $user_name ); ?>
        </button>
        <a href="<?php echo home_url( '/projectfob/people' ); ?>" class="pfob-btn pfob-btn-secondary">Cancel</a>
    </div>
</div>

<style>
.pfob-user-projects-page {
    max-width: 900px;
    margin: 0 auto;
}

.pfob-back-link {
    display: inline-block;
    margin-bottom: 16px;
    color: #0066cc;
    text-decoration: none;
}

.pfob-back-link:hover {
    text-decoration: underline;
}

.pfob-page-header p {
    color: #666;
    line-height: 1.6;
    max-width: 700px;
}

.pfob-bulk-actions {
    display: flex;
    gap: 12px;
    margin: 24px 0;
    flex-wrap: wrap;
}

.pfob-projects-list {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 32px;
}

.pfob-project-item {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #f0f0f0;
    gap: 16px;
}

.pfob-project-item:last-child {
    border-bottom: none;
}

.pfob-project-checkbox {
    flex-shrink: 0;
}

.pfob-project-checkbox input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.pfob-project-info {
    flex: 1;
}

.pfob-project-name {
    font-weight: 500;
    font-size: 16px;
    margin-bottom: 4px;
}

.pfob-project-meta {
    font-size: 14px;
    color: #666;
}

.pfob-access-level {
    flex-shrink: 0;
    min-width: 200px;
}

.pfob-access-level select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
}

.pfob-access-level select:disabled {
    background: #f5f5f5;
    cursor: not-allowed;
}

.pfob-next-steps {
    background: #f8f9fa;
    padding: 24px;
    border-radius: 8px;
    margin-bottom: 24px;
}

.pfob-next-steps h3 {
    margin: 0 0 12px 0;
    font-size: 18px;
}

.pfob-next-steps p {
    margin: 0;
    color: #666;
    line-height: 1.6;
}

.pfob-form-actions {
    display: flex;
    gap: 12px;
}

.pfob-loading {
    text-align: center;
    padding: 40px;
    color: #666;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userId = <?php echo $target_user_id; ?>;
    let projects = [];

    loadProjects();

    // Bulk action buttons
    document.getElementById('check-all').addEventListener('click', () => {
        document.querySelectorAll('.pfob-project-checkbox input').forEach(cb => {
            cb.checked = true;
            cb.dispatchEvent(new Event('change'));
        });
    });

    document.getElementById('check-none').addEventListener('click', () => {
        document.querySelectorAll('.pfob-project-checkbox input').forEach(cb => {
            cb.checked = false;
            cb.dispatchEvent(new Event('change'));
        });
    });

    document.getElementById('all-on-project').addEventListener('click', () => {
        document.querySelectorAll('.pfob-access-level select').forEach(select => {
            if (!select.disabled) {
                select.value = 'on_project';
            }
        });
    });

    document.getElementById('all-following').addEventListener('click', () => {
        document.querySelectorAll('.pfob-access-level select').forEach(select => {
            if (!select.disabled) {
                select.value = 'just_following';
            }
        });
    });

    // Save changes
    document.getElementById('save-changes').addEventListener('click', saveChanges);

    async function loadProjects() {
        try {
            const response = await fetch(`${pfobData.restUrl}/projects?per_page=100`, {
                headers: {
                    'X-WP-Nonce': pfobData.nonce
                }
            });

            const data = await response.json();

            if (data.success) {
                projects = data.projects;

                // Load user's current project access
                const accessResponse = await fetch(`${pfobData.restUrl}/users/${userId}/projects`, {
                    headers: {
                        'X-WP-Nonce': pfobData.nonce
                    }
                });

                const accessData = await accessResponse.json();
                const userProjects = accessData.success ? accessData.projects : [];

                renderProjects(userProjects);
            } else {
                document.getElementById('projects-list').innerHTML = '<div class="pfob-loading">Failed to load projects</div>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('projects-list').innerHTML = '<div class="pfob-loading">Error loading projects</div>';
        }
    }

    function renderProjects(userProjects) {
        if (projects.length === 0) {
            document.getElementById('projects-list').innerHTML = '<div class="pfob-loading">No projects found</div>';
            return;
        }

        const html = projects.map(project => {
            const userProject = userProjects.find(up => up.project_id === project.id);
            const isChecked = !!userProject;
            const accessLevel = userProject ? userProject.access_level : 'on_project';

            return `
                <div class="pfob-project-item">
                    <div class="pfob-project-checkbox">
                        <input type="checkbox"
                               data-project-id="${project.id}"
                               ${isChecked ? 'checked' : ''}>
                    </div>
                    <div class="pfob-project-info">
                        <div class="pfob-project-name">${project.name}</div>
                        <div class="pfob-project-meta">${project.member_count || 0} people</div>
                    </div>
                    <div class="pfob-access-level">
                        <select data-project-id="${project.id}" ${!isChecked ? 'disabled' : ''}>
                            <option value="on_project" ${accessLevel === 'on_project' ? 'selected' : ''}>
                                On the project
                            </option>
                            <option value="just_following" ${accessLevel === 'just_following' ? 'selected' : ''}>
                                Just following
                            </option>
                        </select>
                    </div>
                </div>
            `;
        }).join('');

        document.getElementById('projects-list').innerHTML = html;

        // Add event listeners to checkboxes
        document.querySelectorAll('.pfob-project-checkbox input').forEach(cb => {
            cb.addEventListener('change', function() {
                const select = document.querySelector(`select[data-project-id="${this.dataset.projectId}"]`);
                select.disabled = !this.checked;
            });
        });
    }

    async function saveChanges() {
        const btn = document.getElementById('save-changes');
        btn.disabled = true;
        btn.textContent = 'Saving...';

        const projectAccess = [];
        document.querySelectorAll('.pfob-project-checkbox input:checked').forEach(cb => {
            const projectId = cb.dataset.projectId;
            const accessLevel = document.querySelector(`select[data-project-id="${projectId}"]`).value;
            projectAccess.push({
                project_id: parseInt(projectId),
                access_level: accessLevel
            });
        });

        try {
            const response = await fetch(`${pfobData.restUrl}/users/${userId}/projects`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': pfobData.nonce
                },
                body: JSON.stringify({ projects: projectAccess })
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                window.location.href = '<?php echo home_url( "/projectfob/people" ); ?>';
            } else {
                alert(data.message || 'Failed to save changes');
                btn.disabled = false;
                btn.textContent = 'Save changes for <?php echo esc_js( $user_name ); ?>';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
            btn.disabled = false;
            btn.textContent = 'Save changes for <?php echo esc_js( $user_name ); ?>';
        }
    }
});
</script>

<?php
PFOB_Template::footer();
