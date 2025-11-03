<?php
/**
 * User Invitation Page
 *
 * Allows account owners/admins to invite 3 types of users:
 * - Team Member (full access, can create projects)
 * - Contractor (limited access, cannot create projects)
 * - Client (view-only access to assigned projects)
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id = get_current_user_id();
$user = wp_get_current_user();

// Get organization name (from user meta or default)
$organization_name = get_user_meta( $user_id, 'pfob_organization', true ) ?: get_bloginfo( 'name' );

PFOB_Template::header( 'Invite People' );
?>

<div class="pfob-container">
    <div class="pfob-page-header">
        <h1>Invite People to <?php echo esc_html( $organization_name ); ?></h1>
        <p>Choose the type of person you're inviting to determine their access level.</p>
    </div>

    <div class="pfob-invite-container">
        <!-- User Type Selection -->
        <div class="pfob-user-type-selector">
            <label class="pfob-user-type-option">
                <input type="radio" name="user_type" value="team_member" checked>
                <div class="pfob-user-type-card">
                    <h3>👤 Team Member</h3>
                    <p class="pfob-description">People who work at <?php echo esc_html( $organization_name ); ?> are the only people who can create projects, add others to projects, and act as administrators.</p>
                    <ul class="pfob-capabilities">
                        <li>✓ Can create projects</li>
                        <li>✓ Can add others to projects</li>
                        <li>✓ Can be administrators</li>
                        <li>✓ Full-time, part-time, or volunteer</li>
                    </ul>
                </div>
            </label>

            <label class="pfob-user-type-option">
                <input type="radio" name="user_type" value="contractor">
                <div class="pfob-user-type-card">
                    <h3>🤝 Contractor/Partner/Guest</h3>
                    <p class="pfob-description">Outside collaborators who work on specific projects but don't have full organizational access.</p>
                    <ul class="pfob-capabilities">
                        <li>✓ Can collaborate on projects</li>
                        <li>✗ Cannot create projects</li>
                        <li>✗ Cannot invite people</li>
                        <li>✗ Cannot be admins</li>
                    </ul>
                </div>
            </label>

            <label class="pfob-user-type-option">
                <input type="radio" name="user_type" value="client">
                <div class="pfob-user-type-card">
                    <h3>💼 Client</h3>
                    <p class="pfob-description">Clients can access projects created for them. You can hide parts of projects that are still in progress.</p>
                    <ul class="pfob-capabilities">
                        <li>✓ Can access assigned projects</li>
                        <li>✗ Cannot create their own projects</li>
                        <li>✗ Cannot invite or add people</li>
                        <li>✗ Cannot be admins</li>
                    </ul>
                </div>
            </label>
        </div>

        <!-- Invitation Form -->
        <div class="pfob-invite-form-container">
            <form id="pfob-invite-form" class="pfob-form">
                <div class="pfob-form-group">
                    <label for="full_name">Full name *</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>

                <div class="pfob-form-group">
                    <label for="email">Email address *</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="pfob-form-group">
                    <label for="job_title">Job title (optional)</label>
                    <input type="text" id="job_title" name="job_title">
                </div>

                <div class="pfob-form-group" id="organization-group">
                    <label for="organization">Company/organization</label>
                    <input type="text" id="organization" name="organization" value="<?php echo esc_attr( $organization_name ); ?>">
                    <p class="pfob-hint team-member-hint">Pre-filled with your organization name</p>
                    <p class="pfob-hint contractor-hint" style="display: none;">Enter their organization name</p>
                    <p class="pfob-hint client-hint" style="display: none;">Enter their company name</p>
                </div>

                <div class="pfob-form-group">
                    <label for="personal_note">Add a personal note to the invitation email (optional)</label>
                    <textarea id="personal_note" name="personal_note" rows="3" placeholder="Add a personal message..."></textarea>
                </div>

                <div class="pfob-form-actions">
                    <button type="submit" class="pfob-btn pfob-btn-primary">
                        📧 Email invitation now
                    </button>
                    <a href="<?php echo home_url( '/projectfob/people' ); ?>" class="pfob-btn pfob-btn-secondary">Cancel</a>
                </div>
            </form>

            <div class="pfob-bulk-invite-notice">
                <p>💡 Need to add several team members at once? <a href="#" id="show-bulk-invite">Invite them with a link instead.</a></p>
            </div>
        </div>
    </div>
</div>

<style>
.pfob-invite-container {
    max-width: 1200px;
    margin: 0 auto;
}

.pfob-user-type-selector {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 40px;
}

.pfob-user-type-option {
    cursor: pointer;
}

.pfob-user-type-option input[type="radio"] {
    display: none;
}

.pfob-user-type-card {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 24px;
    background: white;
    transition: all 0.2s;
    height: 100%;
}

.pfob-user-type-option input[type="radio"]:checked + .pfob-user-type-card {
    border-color: #0066cc;
    background: #f0f7ff;
}

.pfob-user-type-card h3 {
    margin: 0 0 12px 0;
    font-size: 18px;
}

.pfob-user-type-card .pfob-description {
    color: #666;
    margin-bottom: 16px;
    font-size: 14px;
    line-height: 1.5;
}

.pfob-user-type-card .pfob-capabilities {
    list-style: none;
    padding: 0;
    margin: 0;
}

.pfob-user-type-card .pfob-capabilities li {
    padding: 6px 0;
    font-size: 14px;
    color: #333;
}

.pfob-invite-form-container {
    max-width: 600px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.pfob-form-group {
    margin-bottom: 20px;
}

.pfob-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.pfob-form-group input,
.pfob-form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.pfob-form-group textarea {
    resize: vertical;
}

.pfob-hint {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
}

.pfob-form-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}

.pfob-bulk-invite-notice {
    margin-top: 24px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 4px;
    text-align: center;
}

.pfob-bulk-invite-notice p {
    margin: 0;
    font-size: 14px;
    color: #666;
}

.pfob-bulk-invite-notice a {
    color: #0066cc;
    text-decoration: none;
    font-weight: 500;
}

.pfob-bulk-invite-notice a:hover {
    text-decoration: underline;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userTypeRadios = document.querySelectorAll('input[name="user_type"]');
    const organizationInput = document.getElementById('organization');
    const teamMemberHint = document.querySelector('.team-member-hint');
    const contractorHint = document.querySelector('.contractor-hint');
    const clientHint = document.querySelector('.client-hint');
    const organizationValue = "<?php echo esc_js( $organization_name ); ?>";

    // Update organization field based on user type
    userTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Hide all hints
            teamMemberHint.style.display = 'none';
            contractorHint.style.display = 'none';
            clientHint.style.display = 'none';

            if (this.value === 'team_member') {
                organizationInput.value = organizationValue;
                organizationInput.readOnly = true;
                teamMemberHint.style.display = 'block';
            } else {
                organizationInput.value = '';
                organizationInput.readOnly = false;
                if (this.value === 'contractor') {
                    contractorHint.style.display = 'block';
                } else {
                    clientHint.style.display = 'block';
                }
            }
        });
    });

    // Form submission
    const form = document.getElementById('pfob-invite-form');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const userType = document.querySelector('input[name="user_type"]:checked').value;
        formData.append('user_type', userType);

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending invitation...';

        try {
            const response = await fetch(`${pfobData.restUrl}/invitations/send`, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': pfobData.nonce
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Show success message
                const successHtml = `
                    <div class="pfob-success-message">
                        <h2>✓ Invitation emailed to ${formData.get('full_name')}</h2>
                        <p>${data.message}</p>
                        <div class="pfob-next-steps">
                            <h3>What would you like to do next?</h3>
                            <a href="${data.set_projects_url}" class="pfob-btn pfob-btn-primary">Set up which projects they can see</a>
                            <a href="<?php echo home_url( '/projectfob/people' ); ?>" class="pfob-btn pfob-btn-secondary">Not now, I'll do this later</a>
                        </div>
                    </div>
                `;
                document.querySelector('.pfob-invite-form-container').innerHTML = successHtml;
            } else {
                alert(data.message || 'Failed to send invitation. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = '📧 Email invitation now';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            submitBtn.disabled = false;
            submitBtn.textContent = '📧 Email invitation now';
        }
    });
});
</script>

<?php
PFOB_Template::footer();
