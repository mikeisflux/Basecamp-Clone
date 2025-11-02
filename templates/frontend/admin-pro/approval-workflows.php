<?php
/**
 * Approval Workflows - Admin Pro Pack
 *
 * Create and manage approval processes for documents, timesheets,
 * project milestones, and other content requiring review.
 */
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

PFOB_Template::header('Approval Workflows');
?>

<div class="workflows-wrapper">
    <a href="<?php echo home_url('/projectfob/adminland'); ?>" class="back-link">← Back to Adminland</a>

    <h1 class="page-title">✅ Approval Workflows</h1>
    <p class="page-subtitle">Require approvals for documents, timesheets, expenses, or project milestones.</p>

    <!-- Create New Workflow -->
    <div class="workflow-section">
        <h2 class="section-title">Create New Workflow</h2>

        <div class="create-workflow-form">
            <div class="form-row">
                <label class="form-label">Workflow Name</label>
                <input type="text" id="workflow-name" class="form-input" placeholder="e.g., Document Approval">
            </div>

            <div class="form-row">
                <label class="form-label">Trigger Event</label>
                <select id="workflow-trigger" class="form-select">
                    <option value="">Select trigger...</option>
                    <option value="document_upload">Document Uploaded</option>
                    <option value="timesheet_submit">Timesheet Submitted</option>
                    <option value="expense_submit">Expense Report Submitted</option>
                    <option value="milestone_complete">Project Milestone Completed</option>
                    <option value="user_invite">New User Invited</option>
                </select>
            </div>

            <div class="form-row">
                <label class="form-label">Description</label>
                <textarea id="workflow-description" class="form-textarea" rows="2" placeholder="Describe when this workflow should run..."></textarea>
            </div>

            <div class="approval-steps-section">
                <label class="form-label">Approval Steps</label>
                <div id="approval-steps">
                    <div class="approval-step">
                        <div class="step-header">
                            <span class="step-number">Step 1</span>
                            <button class="remove-step-btn" onclick="removeStep(this)" style="display:none">✕</button>
                        </div>
                        <div class="step-fields">
                            <div class="field-group">
                                <label class="field-label">Approver Role</label>
                                <select class="approver-select">
                                    <option value="">Select role...</option>
                                    <option value="admin">Administrator</option>
                                    <option value="manager">Project Manager</option>
                                    <option value="team_lead">Team Lead</option>
                                    <option value="specific_user">Specific User...</option>
                                </select>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Response Time (hours)</label>
                                <input type="number" class="response-time-input" value="24" min="1">
                            </div>
                        </div>
                    </div>
                </div>
                <button class="action-button secondary-button" onclick="addApprovalStep()">+ Add Step</button>
            </div>

            <div class="form-row">
                <label class="form-label">
                    <input type="checkbox" id="workflow-notify" checked>
                    Send email notifications to approvers
                </label>
            </div>

            <div class="form-actions">
                <button class="action-button primary-button" onclick="createWorkflow()">Create Workflow</button>
                <button class="action-button secondary-button" onclick="resetForm()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Existing Workflows -->
    <div class="workflow-section">
        <h2 class="section-title">Active Workflows</h2>

        <div id="workflows-list" class="workflows-list">
            <div class="loading-message">Loading workflows...</div>
        </div>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.workflows-wrapper {
    max-width: 1000px;
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

.workflow-section {
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

/* Form Styles */
.create-workflow-form {
    max-width: 700px;
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

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    font-family: inherit;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.form-textarea {
    resize: vertical;
}

/* Approval Steps */
.approval-steps-section {
    margin-bottom: 20px;
}

.approval-step {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 16px;
    margin-bottom: 12px;
}

.step-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.step-number {
    font-weight: 600;
    color: #0066cc;
    font-size: 14px;
}

.remove-step-btn {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 4px 8px;
    cursor: pointer;
    font-size: 14px;
}

.remove-step-btn:hover {
    background: #c82333;
}

.step-fields {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 12px;
}

.field-group {
    display: flex;
    flex-direction: column;
}

.field-label {
    font-size: 13px;
    font-weight: 500;
    color: #555;
    margin-bottom: 6px;
}

.approver-select,
.response-time-input {
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.approver-select:focus,
.response-time-input:focus {
    outline: none;
    border-color: #0066cc;
}

/* Action Buttons */
.action-button {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    border: none;
    margin-right: 8px;
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

.form-actions {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #e0e0e0;
}

/* Workflows List */
.workflows-list {
    min-height: 200px;
}

.workflow-card {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 20px;
    margin-bottom: 16px;
}

.workflow-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.workflow-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.workflow-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.workflow-trigger {
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
}

.workflow-description {
    font-size: 14px;
    color: #666;
    margin-bottom: 12px;
}

.workflow-steps {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    padding: 12px;
    margin-bottom: 12px;
}

.workflow-step-item {
    font-size: 14px;
    color: #333;
    margin-bottom: 8px;
    padding-left: 20px;
    position: relative;
}

.workflow-step-item:before {
    content: "→";
    position: absolute;
    left: 0;
    color: #0066cc;
}

.workflow-step-item:last-child {
    margin-bottom: 0;
}

.workflow-actions {
    padding-top: 12px;
    border-top: 1px solid #e0e0e0;
}

.workflow-actions .action-button {
    padding: 6px 12px;
    font-size: 14px;
    margin-bottom: 8px;
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
    .workflows-wrapper {
        padding: 12px;
    }

    .workflow-section {
        padding: 20px;
    }

    .page-title {
        font-size: 24px;
    }

    .step-fields {
        grid-template-columns: 1fr;
    }

    .workflow-header {
        flex-direction: column;
    }

    .workflow-status {
        margin-top: 8px;
    }
}
</style>

<script>
let stepCounter = 1;

document.addEventListener('DOMContentLoaded', function() {
    loadWorkflows();

    window.createWorkflow = createWorkflow;
    window.addApprovalStep = addApprovalStep;
    window.removeStep = removeStep;
    window.resetForm = resetForm;
    window.editWorkflow = editWorkflow;
    window.toggleWorkflow = toggleWorkflow;
    window.deleteWorkflow = deleteWorkflow;
});

function addApprovalStep() {
    stepCounter++;
    const stepsContainer = document.getElementById('approval-steps');

    const stepDiv = document.createElement('div');
    stepDiv.className = 'approval-step';
    stepDiv.innerHTML = `
        <div class="step-header">
            <span class="step-number">Step ${stepCounter}</span>
            <button class="remove-step-btn" onclick="removeStep(this)">✕</button>
        </div>
        <div class="step-fields">
            <div class="field-group">
                <label class="field-label">Approver Role</label>
                <select class="approver-select">
                    <option value="">Select role...</option>
                    <option value="admin">Administrator</option>
                    <option value="manager">Project Manager</option>
                    <option value="team_lead">Team Lead</option>
                    <option value="specific_user">Specific User...</option>
                </select>
            </div>
            <div class="field-group">
                <label class="field-label">Response Time (hours)</label>
                <input type="number" class="response-time-input" value="24" min="1">
            </div>
        </div>
    `;

    stepsContainer.appendChild(stepDiv);

    // Show remove buttons if more than 1 step
    updateRemoveButtons();
}

function removeStep(button) {
    const step = button.closest('.approval-step');
    step.remove();

    // Renumber steps
    const steps = document.querySelectorAll('.approval-step');
    steps.forEach((step, index) => {
        step.querySelector('.step-number').textContent = `Step ${index + 1}`;
    });

    stepCounter = steps.length;
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const steps = document.querySelectorAll('.approval-step');
    const removeButtons = document.querySelectorAll('.remove-step-btn');

    removeButtons.forEach(btn => {
        btn.style.display = steps.length > 1 ? 'block' : 'none';
    });
}

async function createWorkflow() {
    const name = document.getElementById('workflow-name').value.trim();
    const trigger = document.getElementById('workflow-trigger').value;
    const description = document.getElementById('workflow-description').value.trim();
    const notify = document.getElementById('workflow-notify').checked;

    if (!name) {
        alert('Please enter a workflow name');
        return;
    }

    if (!trigger) {
        alert('Please select a trigger event');
        return;
    }

    // Collect approval steps
    const steps = [];
    const stepElements = document.querySelectorAll('.approval-step');
    stepElements.forEach((stepEl, index) => {
        const approver = stepEl.querySelector('.approver-select').value;
        const responseTime = stepEl.querySelector('.response-time-input').value;

        if (approver) {
            steps.push({
                order: index + 1,
                approver_role: approver,
                response_time_hours: parseInt(responseTime)
            });
        }
    });

    if (steps.length === 0) {
        alert('Please configure at least one approval step');
        return;
    }

    try {
        const response = await fetch(`${pfobData.restUrl}/admin-pro/workflows`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify({
                name: name,
                trigger: trigger,
                description: description,
                steps: steps,
                notify: notify
            })
        });

        const data = await response.json();

        if (data.success) {
            alert('Workflow created successfully!');
            resetForm();
            loadWorkflows();
        } else {
            alert('Failed to create workflow: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Workflow created!\n\n(API not yet implemented - this is a demo. Your workflow would be saved when the backend is ready.)');
        resetForm();
        loadWorkflows();
    }
}

function resetForm() {
    document.getElementById('workflow-name').value = '';
    document.getElementById('workflow-trigger').value = '';
    document.getElementById('workflow-description').value = '';
    document.getElementById('workflow-notify').checked = true;

    // Reset to one step
    const stepsContainer = document.getElementById('approval-steps');
    stepsContainer.innerHTML = `
        <div class="approval-step">
            <div class="step-header">
                <span class="step-number">Step 1</span>
                <button class="remove-step-btn" onclick="removeStep(this)" style="display:none">✕</button>
            </div>
            <div class="step-fields">
                <div class="field-group">
                    <label class="field-label">Approver Role</label>
                    <select class="approver-select">
                        <option value="">Select role...</option>
                        <option value="admin">Administrator</option>
                        <option value="manager">Project Manager</option>
                        <option value="team_lead">Team Lead</option>
                        <option value="specific_user">Specific User...</option>
                    </select>
                </div>
                <div class="field-group">
                    <label class="field-label">Response Time (hours)</label>
                    <input type="number" class="response-time-input" value="24" min="1">
                </div>
            </div>
        </div>
    `;
    stepCounter = 1;
}

async function loadWorkflows() {
    try {
        const response = await fetch(`${pfobData.restUrl}/admin-pro/workflows`, {
            headers: {
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success) {
            renderWorkflows(data.workflows || []);
        } else {
            document.getElementById('workflows-list').innerHTML = '<div class="empty-message">Failed to load workflows</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        // Show placeholder workflows
        renderPlaceholderWorkflows();
    }
}

function renderWorkflows(workflows) {
    if (workflows.length === 0) {
        document.getElementById('workflows-list').innerHTML = '<div class="empty-message">No workflows yet. Create your first workflow above!</div>';
        return;
    }

    const html = workflows.map(workflow => `
        <div class="workflow-card">
            <div class="workflow-header">
                <div>
                    <div class="workflow-title">${workflow.name}</div>
                    <div class="workflow-trigger">Trigger: ${workflow.trigger_label}</div>
                </div>
                <span class="workflow-status status-${workflow.active ? 'active' : 'inactive'}">
                    ${workflow.active ? 'Active' : 'Inactive'}
                </span>
            </div>

            ${workflow.description ? `<div class="workflow-description">${workflow.description}</div>` : ''}

            <div class="workflow-steps">
                <strong style="font-size: 14px; color: #333; display: block; margin-bottom: 8px;">Approval Steps:</strong>
                ${workflow.steps.map(step => `
                    <div class="workflow-step-item">
                        ${step.approver_label} (${step.response_time_hours}h response time)
                    </div>
                `).join('')}
            </div>

            <div class="workflow-actions">
                <button class="action-button ${workflow.active ? 'secondary-button' : 'primary-button'}" onclick="toggleWorkflow(${workflow.id}, ${workflow.active})">
                    ${workflow.active ? 'Disable' : 'Enable'}
                </button>
                <button class="action-button secondary-button" onclick="editWorkflow(${workflow.id})">Edit</button>
                <button class="action-button secondary-button" style="background:#dc3545;color:white;border:none" onclick="deleteWorkflow(${workflow.id})">Delete</button>
            </div>
        </div>
    `).join('');

    document.getElementById('workflows-list').innerHTML = html;
}

function renderPlaceholderWorkflows() {
    const placeholderWorkflows = [
        {
            id: 1,
            name: 'Document Approval',
            trigger_label: 'Document Uploaded',
            description: 'Require manager approval for all document uploads',
            active: true,
            steps: [
                { approver_label: 'Project Manager', response_time_hours: 24 },
                { approver_label: 'Administrator', response_time_hours: 48 }
            ]
        },
        {
            id: 2,
            name: 'Timesheet Review',
            trigger_label: 'Timesheet Submitted',
            description: 'Weekly timesheet approval by team lead',
            active: true,
            steps: [
                { approver_label: 'Team Lead', response_time_hours: 48 }
            ]
        },
        {
            id: 3,
            name: 'Expense Approval',
            trigger_label: 'Expense Report Submitted',
            description: '',
            active: false,
            steps: [
                { approver_label: 'Project Manager', response_time_hours: 24 },
                { approver_label: 'Administrator', response_time_hours: 48 }
            ]
        }
    ];

    renderWorkflows(placeholderWorkflows);
}

function editWorkflow(workflowId) {
    alert('Edit workflow functionality coming soon. This will allow you to modify workflow settings and approval steps.');
}

function toggleWorkflow(workflowId, currentState) {
    const action = currentState ? 'disable' : 'enable';
    if (confirm(`Are you sure you want to ${action} this workflow?`)) {
        alert(`Workflow ${action}d! (API not yet implemented - this would ${action} the workflow in the database.)`);
        loadWorkflows();
    }
}

function deleteWorkflow(workflowId) {
    if (confirm('Are you sure you want to delete this workflow? This action cannot be undone.')) {
        alert('Workflow deleted! (API not yet implemented - this would remove the workflow from the database.)');
        loadWorkflows();
    }
}
</script>

<?php
PFOB_Template::footer();
