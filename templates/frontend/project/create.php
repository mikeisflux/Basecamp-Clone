<?php
/**
 * Project Creation Wizard
 */

$user_id = get_current_user_id();
$users = get_users( array( 'orderby' => 'display_name' ) );

BCWP_Template::header( 'Create New Project' );
?>

<div class="bcwp-container">
    <main class="bcwp-main bcwp-create-project">

        <div class="bcwp-wizard-container">
            <div class="bcwp-wizard-header">
                <h1>Create a new project</h1>
                <p class="bcwp-subtitle">Set up your project in a few simple steps</p>
            </div>

            <div class="bcwp-wizard-steps">
                <div class="bcwp-wizard-step active" data-step="1">
                    <div class="bcwp-step-number">1</div>
                    <div class="bcwp-step-label">Basic Info</div>
                </div>
                <div class="bcwp-wizard-step" data-step="2">
                    <div class="bcwp-step-number">2</div>
                    <div class="bcwp-step-label">Choose Tools</div>
                </div>
                <div class="bcwp-wizard-step" data-step="3">
                    <div class="bcwp-step-number">3</div>
                    <div class="bcwp-step-label">Add Team</div>
                </div>
                <div class="bcwp-wizard-step" data-step="4">
                    <div class="bcwp-step-number">4</div>
                    <div class="bcwp-step-label">Review</div>
                </div>
            </div>

            <form id="project-wizard-form">

                <!-- Step 1: Basic Info -->
                <div class="bcwp-wizard-content active" data-step="1">
                    <h2>What's this project about?</h2>

                    <div class="bcwp-form-group">
                        <label for="project-name">Project name *</label>
                        <input type="text"
                               id="project-name"
                               name="name"
                               required
                               placeholder="e.g. Website Redesign"
                               autofocus>
                        <small>Give your project a clear, memorable name</small>
                    </div>

                    <div class="bcwp-form-group">
                        <label for="project-description">Description (optional)</label>
                        <textarea id="project-description"
                                  name="description"
                                  rows="4"
                                  placeholder="e.g. Complete redesign of company website with new branding"></textarea>
                        <small>What's the purpose or goal of this project?</small>
                    </div>

                    <div class="bcwp-wizard-actions">
                        <a href="<?php echo home_url( '/basecamp/dashboard' ); ?>" class="bcwp-btn bcwp-btn-secondary">
                            Cancel
                        </a>
                        <button type="button" class="bcwp-btn bcwp-btn-primary" data-next-step>
                            Next: Choose Tools
                        </button>
                    </div>
                </div>

                <!-- Step 2: Choose Tools -->
                <div class="bcwp-wizard-content" data-step="2">
                    <h2>Which tools do you want to use?</h2>
                    <p class="bcwp-step-description">Select the Basecamp tools you need for this project. You can always add or remove tools later.</p>

                    <div class="bcwp-tools-grid">
                        <label class="bcwp-tool-card">
                            <input type="checkbox" name="tools[]" value="messages" checked>
                            <div class="bcwp-tool-icon">💬</div>
                            <div class="bcwp-tool-name">Message Board</div>
                            <div class="bcwp-tool-description">Post announcements, pitch ideas, progress updates, etc.</div>
                        </label>

                        <label class="bcwp-tool-card">
                            <input type="checkbox" name="tools[]" value="todos" checked>
                            <div class="bcwp-tool-icon">✅</div>
                            <div class="bcwp-tool-name">To-dos</div>
                            <div class="bcwp-tool-description">Make lists of work, assign tasks, and track progress</div>
                        </label>

                        <label class="bcwp-tool-card">
                            <input type="checkbox" name="tools[]" value="documents" checked>
                            <div class="bcwp-tool-icon">📄</div>
                            <div class="bcwp-tool-name">Documents & Files</div>
                            <div class="bcwp-tool-description">Share docs, files, images, and other assets</div>
                        </label>

                        <label class="bcwp-tool-card">
                            <input type="checkbox" name="tools[]" value="chat" checked>
                            <div class="bcwp-tool-icon">💬</div>
                            <div class="bcwp-tool-name">Campfire</div>
                            <div class="bcwp-tool-description">Group chat for quick, casual conversations</div>
                        </label>

                        <label class="bcwp-tool-card">
                            <input type="checkbox" name="tools[]" value="schedule" checked>
                            <div class="bcwp-tool-icon">📅</div>
                            <div class="bcwp-tool-name">Schedule</div>
                            <div class="bcwp-tool-description">Set important dates and track events</div>
                        </label>

                        <label class="bcwp-tool-card">
                            <input type="checkbox" name="tools[]" value="cards" checked>
                            <div class="bcwp-tool-icon">🎴</div>
                            <div class="bcwp-tool-name">Card Table</div>
                            <div class="bcwp-tool-description">Visual workflow management with kanban boards</div>
                        </label>
                    </div>

                    <div class="bcwp-wizard-actions">
                        <button type="button" class="bcwp-btn bcwp-btn-secondary" data-prev-step>
                            Back
                        </button>
                        <button type="button" class="bcwp-btn bcwp-btn-primary" data-next-step>
                            Next: Add Team
                        </button>
                    </div>
                </div>

                <!-- Step 3: Add Team -->
                <div class="bcwp-wizard-content" data-step="3">
                    <h2>Who should be on this project?</h2>
                    <p class="bcwp-step-description">Add people to your project team. You can add more later.</p>

                    <div class="bcwp-team-list">
                        <div class="bcwp-team-member">
                            <?php echo get_avatar( $user_id, 40 ); ?>
                            <div>
                                <strong><?php echo wp_get_current_user()->display_name; ?></strong> (You)
                                <span class="bcwp-member-role">Admin</span>
                            </div>
                        </div>

                        <?php foreach ( $users as $user ) : ?>
                            <?php if ( $user->ID == $user_id ) continue; ?>
                            <label class="bcwp-team-member bcwp-team-member-selectable">
                                <input type="checkbox" name="members[]" value="<?php echo $user->ID; ?>">
                                <?php echo get_avatar( $user->ID, 40 ); ?>
                                <div>
                                    <strong><?php echo esc_html( $user->display_name ); ?></strong>
                                    <span class="bcwp-member-email"><?php echo esc_html( $user->user_email ); ?></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="bcwp-wizard-actions">
                        <button type="button" class="bcwp-btn bcwp-btn-secondary" data-prev-step>
                            Back
                        </button>
                        <button type="button" class="bcwp-btn bcwp-btn-primary" data-next-step>
                            Next: Review
                        </button>
                    </div>
                </div>

                <!-- Step 4: Review -->
                <div class="bcwp-wizard-content" data-step="4">
                    <h2>Review your project</h2>
                    <p class="bcwp-step-description">Everything look good? Create your project to get started!</p>

                    <div class="bcwp-review-section">
                        <h3>Project Details</h3>
                        <div class="bcwp-review-item">
                            <strong>Name:</strong>
                            <span id="review-name">-</span>
                        </div>
                        <div class="bcwp-review-item">
                            <strong>Description:</strong>
                            <span id="review-description">-</span>
                        </div>
                    </div>

                    <div class="bcwp-review-section">
                        <h3>Tools Enabled</h3>
                        <div id="review-tools" class="bcwp-review-tools"></div>
                    </div>

                    <div class="bcwp-review-section">
                        <h3>Team Members</h3>
                        <div id="review-team" class="bcwp-review-team"></div>
                    </div>

                    <div class="bcwp-wizard-actions">
                        <button type="button" class="bcwp-btn bcwp-btn-secondary" data-prev-step>
                            Back
                        </button>
                        <button type="submit" class="bcwp-btn bcwp-btn-primary">
                            Create Project
                        </button>
                    </div>
                </div>

            </form>
        </div>

    </main>
</div>

<script>
const bcwpData = {
    restUrl: '<?php echo rest_url( 'bcwp/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
};

let currentStep = 1;

// Navigation
document.querySelectorAll('[data-next-step]').forEach(btn => {
    btn.addEventListener('click', () => {
        if (validateCurrentStep()) {
            goToStep(currentStep + 1);
        }
    });
});

document.querySelectorAll('[data-prev-step]').forEach(btn => {
    btn.addEventListener('click', () => goToStep(currentStep - 1));
});

function goToStep(step) {
    // Hide current step
    document.querySelector(`.bcwp-wizard-content[data-step="${currentStep}"]`)?.classList.remove('active');
    document.querySelector(`.bcwp-wizard-step[data-step="${currentStep}"]`)?.classList.remove('active');

    // Show new step
    document.querySelector(`.bcwp-wizard-content[data-step="${step}"]`)?.classList.add('active');
    document.querySelector(`.bcwp-wizard-step[data-step="${step}"]`)?.classList.add('active');

    // Mark previous steps as completed
    for (let i = 1; i < step; i++) {
        document.querySelector(`.bcwp-wizard-step[data-step="${i}"]`)?.classList.add('completed');
    }

    currentStep = step;

    // If going to review, populate it
    if (step === 4) {
        populateReview();
    }

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function validateCurrentStep() {
    if (currentStep === 1) {
        const name = document.getElementById('project-name').value.trim();
        if (!name) {
            alert('Please enter a project name');
            return false;
        }
    }
    return true;
}

function populateReview() {
    // Name and description
    const name = document.getElementById('project-name').value || '-';
    const description = document.getElementById('project-description').value || 'No description';
    document.getElementById('review-name').textContent = name;
    document.getElementById('review-description').textContent = description;

    // Tools
    const tools = Array.from(document.querySelectorAll('input[name="tools[]"]:checked'))
        .map(cb => cb.closest('.bcwp-tool-card').querySelector('.bcwp-tool-name').textContent);
    document.getElementById('review-tools').innerHTML = tools.length > 0
        ? tools.map(t => `<span class="bcwp-review-tag">${t}</span>`).join('')
        : '<span class="bcwp-text-light">No tools selected</span>';

    // Team members
    const members = Array.from(document.querySelectorAll('input[name="members[]"]:checked'))
        .map(cb => cb.closest('.bcwp-team-member').querySelector('strong').textContent);
    members.unshift('<?php echo wp_get_current_user()->display_name; ?> (You)');
    document.getElementById('review-team').innerHTML = members.map(m => `<span class="bcwp-review-tag">${m}</span>`).join('');
}

// Form submission
document.getElementById('project-wizard-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = {
        name: formData.get('name'),
        description: formData.get('description'),
        tools: formData.getAll('tools[]'),
        members: formData.getAll('members[]')
    };

    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating...';

    try {
        const response = await fetch(bcwpData.restUrl + '/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': bcwpData.nonce
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            window.location.href = `/basecamp/projects/${result.data.slug}/`;
        } else {
            alert(result.message || 'Failed to create project');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Project';
        }
    } catch (error) {
        console.error('Error creating project:', error);
        alert('Failed to create project. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Project';
    }
});
</script>
<script src="<?php echo BCWP_PLUGIN_URL; ?>assets/js/frontend.js"></script>

</body>
</html>
