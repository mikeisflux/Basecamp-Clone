<?php
/**
 * API Access Management
 *
 * Required: Enterprise plan only
 */

// Check if user has API Access feature in their plan
$user_id = get_current_user_id();
$subscription = PFOB_Subscription::get_by_user_id( $user_id );

if ( ! $subscription ) {
    wp_die( __( 'Access denied. You must have an active subscription to access this page.', 'projectfob' ) );
}

$plans_config = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
$plan = isset( $plans_config[ $subscription->plan_id ] ) ? $plans_config[ $subscription->plan_id ] : null;

// Check if plan includes API Access feature
if ( ! $plan || empty( $plan['features']['api_access'] ) ) {
    wp_die( __( 'Access denied. API Access is only available on Enterprise plans. <a href="' . home_url( '/projectfob/adminland/billing' ) . '">Upgrade your plan</a>', 'projectfob' ) );
}

PFOB_Template::header( 'API Access' );
?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-api-access">

        <header class="pfob-page-header">
            <div>
                <h1>🔌 API Access</h1>
                <p class="pfob-subtitle">Integrate ProjectFOB with your custom applications and workflows</p>
            </div>
            <div>
                <a href="#documentation" class="pfob-btn pfob-btn-secondary">📚 View Documentation</a>
                <button id="create-key-btn" class="pfob-btn pfob-btn-primary">+ Create API Key</button>
            </div>
        </header>

        <!-- API Overview -->
        <div class="api-overview">
            <div class="overview-card">
                <div class="overview-icon">🔑</div>
                <div class="overview-content">
                    <div class="overview-value" id="total-keys">0</div>
                    <div class="overview-label">Active API Keys</div>
                </div>
            </div>
            <div class="overview-card">
                <div class="overview-icon">📊</div>
                <div class="overview-content">
                    <div class="overview-value" id="total-requests">0</div>
                    <div class="overview-label">Requests (Last 30 Days)</div>
                </div>
            </div>
            <div class="overview-card">
                <div class="overview-icon">⚡</div>
                <div class="overview-content">
                    <div class="overview-value" id="rate-limit">10,000/hour</div>
                    <div class="overview-label">Rate Limit</div>
                </div>
            </div>
        </div>

        <!-- API Keys List -->
        <div class="pfob-section">
            <h2>API Keys</h2>
            <div id="api-keys-container">
                <div class="loading-message">Loading API keys...</div>
            </div>
        </div>

        <!-- API Documentation -->
        <div class="pfob-section" id="documentation">
            <h2>📖 API Documentation</h2>

            <div class="docs-tabs">
                <button class="docs-tab active" data-tab="quickstart">Quick Start</button>
                <button class="docs-tab" data-tab="authentication">Authentication</button>
                <button class="docs-tab" data-tab="endpoints">Endpoints</button>
                <button class="docs-tab" data-tab="examples">Examples</button>
            </div>

            <div class="docs-content">
                <div id="tab-quickstart" class="docs-tab-content active">
                    <h3>Quick Start Guide</h3>
                    <p>Get started with the ProjectFOB API in minutes:</p>

                    <ol class="docs-steps">
                        <li>
                            <strong>Create an API Key</strong>
                            <p>Click the "Create API Key" button above to generate your first API key.</p>
                        </li>
                        <li>
                            <strong>Make Your First Request</strong>
                            <pre class="code-block">curl -H "Authorization: Bearer YOUR_API_KEY" \
     <?php echo rest_url( 'projectfob/v1/projects' ); ?></pre>
                        </li>
                        <li>
                            <strong>Handle the Response</strong>
                            <p>All API responses are in JSON format with a consistent structure.</p>
                        </li>
                    </ol>
                </div>

                <div id="tab-authentication" class="docs-tab-content">
                    <h3>Authentication</h3>
                    <p>All API requests must include your API key in the Authorization header:</p>
                    <pre class="code-block">Authorization: Bearer YOUR_API_KEY</pre>

                    <h4>Security Best Practices</h4>
                    <ul>
                        <li>Never expose your API keys in client-side code</li>
                        <li>Rotate keys regularly</li>
                        <li>Use different keys for different applications</li>
                        <li>Revoke compromised keys immediately</li>
                    </ul>
                </div>

                <div id="tab-endpoints" class="docs-tab-content">
                    <h3>Available Endpoints</h3>

                    <div class="endpoint-group">
                        <h4>Projects</h4>
                        <div class="endpoint">
                            <span class="http-method get">GET</span>
                            <code>/projectfob/v1/projects</code>
                            <p>List all projects</p>
                        </div>
                        <div class="endpoint">
                            <span class="http-method post">POST</span>
                            <code>/projectfob/v1/projects</code>
                            <p>Create a new project</p>
                        </div>
                        <div class="endpoint">
                            <span class="http-method get">GET</span>
                            <code>/projectfob/v1/projects/{id}</code>
                            <p>Get project details</p>
                        </div>
                    </div>

                    <div class="endpoint-group">
                        <h4>Messages</h4>
                        <div class="endpoint">
                            <span class="http-method get">GET</span>
                            <code>/projectfob/v1/messages</code>
                            <p>List messages</p>
                        </div>
                        <div class="endpoint">
                            <span class="http-method post">POST</span>
                            <code>/projectfob/v1/messages</code>
                            <p>Create a message</p>
                        </div>
                    </div>

                    <div class="endpoint-group">
                        <h4>To-dos</h4>
                        <div class="endpoint">
                            <span class="http-method get">GET</span>
                            <code>/projectfob/v1/todos</code>
                            <p>List todos</p>
                        </div>
                        <div class="endpoint">
                            <span class="http-method post">POST</span>
                            <code>/projectfob/v1/todos</code>
                            <p>Create a todo</p>
                        </div>
                    </div>
                </div>

                <div id="tab-examples" class="docs-tab-content">
                    <h3>Code Examples</h3>

                    <h4>JavaScript / Node.js</h4>
                    <pre class="code-block">const response = await fetch('<?php echo rest_url( 'projectfob/v1/projects' ); ?>', {
  headers: {
    'Authorization': 'Bearer YOUR_API_KEY',
    'Content-Type': 'application/json'
  }
});

const data = await response.json();
console.log(data);</pre>

                    <h4>Python</h4>
                    <pre class="code-block">import requests

headers = {
    'Authorization': 'Bearer YOUR_API_KEY',
    'Content-Type': 'application/json'
}

response = requests.get(
    '<?php echo rest_url( 'projectfob/v1/projects' ); ?>',
    headers=headers
)

print(response.json())</pre>

                    <h4>PHP</h4>
                    <pre class="code-block">$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, '<?php echo rest_url( 'projectfob/v1/projects' ); ?>');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer YOUR_API_KEY',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$data = json_decode($response);
curl_close($ch);

print_r($data);</pre>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- Create API Key Modal -->
<div id="create-key-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create API Key</h2>
            <button class="modal-close" onclick="closeCreateKeyModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-field">
                <label for="key-name">Key Name</label>
                <input type="text" id="key-name" class="pfob-input" placeholder="e.g., Production App, Development">
                <span class="field-hint">Give this key a memorable name to identify its purpose</span>
            </div>
            <div class="form-field">
                <label for="key-description">Description (Optional)</label>
                <textarea id="key-description" class="pfob-textarea" rows="3" placeholder="What will this key be used for?"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeCreateKeyModal()" class="pfob-btn pfob-btn-secondary">Cancel</button>
            <button onclick="createAPIKey()" class="pfob-btn pfob-btn-primary">Create Key</button>
        </div>
    </div>
</div>

<script>
const pfobData = {
    restUrl: '<?php echo rest_url( 'projectfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
};

// Load API keys on page load
loadAPIKeys();
loadUsageStats();

async function loadAPIKeys() {
    const container = document.getElementById('api-keys-container');

    try {
        const response = await fetch(`${pfobData.restUrl}/api-keys`, {
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        const result = await response.json();

        if (result.success && result.data.length > 0) {
            document.getElementById('total-keys').textContent = result.data.length;

            container.innerHTML = result.data.map(key => `
                <div class="api-key-card">
                    <div class="key-header">
                        <div>
                            <h3>${escapeHtml(key.name)}</h3>
                            ${key.description ? `<p class="key-description">${escapeHtml(key.description)}</p>` : ''}
                        </div>
                        <button onclick="revokeKey('${key.id}')" class="btn-revoke">Revoke</button>
                    </div>
                    <div class="key-details">
                        <div class="key-value">
                            <span class="key-label">Key:</span>
                            <code class="key-code">${key.masked_key}</code>
                            ${key.show_full ? `<button onclick="copyKey('${key.key}')" class="btn-copy">📋 Copy</button>` : ''}
                        </div>
                        <div class="key-meta">
                            <span>Created: ${new Date(key.created_at).toLocaleDateString()}</span>
                            <span>Last used: ${key.last_used ? new Date(key.last_used).toLocaleDateString() : 'Never'}</span>
                            <span>Requests: ${key.request_count.toLocaleString()}</span>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">🔑</div>
                    <h3>No API Keys Yet</h3>
                    <p>Create your first API key to start integrating with the ProjectFOB API</p>
                    <button onclick="openCreateKeyModal()" class="pfob-btn pfob-btn-primary">Create API Key</button>
                </div>
            `;
        }
    } catch (error) {
        container.innerHTML = '<div class="error-message">Failed to load API keys</div>';
    }
}

async function loadUsageStats() {
    try {
        const response = await fetch(`${pfobData.restUrl}/api-keys/stats`, {
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        const result = await response.json();

        if (result.success) {
            document.getElementById('total-requests').textContent = result.data.total_requests.toLocaleString();
        }
    } catch (error) {
        console.error('Failed to load stats:', error);
    }
}

function openCreateKeyModal() {
    document.getElementById('create-key-modal').style.display = 'flex';
}

function closeCreateKeyModal() {
    document.getElementById('create-key-modal').style.display = 'none';
    document.getElementById('key-name').value = '';
    document.getElementById('key-description').value = '';
}

async function createAPIKey() {
    const name = document.getElementById('key-name').value.trim();
    const description = document.getElementById('key-description').value.trim();

    if (!name) {
        alert('Please enter a name for the API key');
        return;
    }

    try {
        const response = await fetch(`${pfobData.restUrl}/api-keys`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': pfobData.nonce,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ name, description })
        });

        const result = await response.json();

        if (result.success) {
            alert(`API Key Created!\n\nKey: ${result.data.key}\n\n⚠️ Save this key now - you won't be able to see it again!`);
            closeCreateKeyModal();
            loadAPIKeys();
        } else {
            alert('Error: ' + (result.message || 'Failed to create API key'));
        }
    } catch (error) {
        alert('Error creating API key. Please try again.');
    }
}

async function revokeKey(keyId) {
    if (!confirm('Are you sure you want to revoke this API key? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`${pfobData.restUrl}/api-keys/${keyId}`, {
            method: 'DELETE',
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        const result = await response.json();

        if (result.success) {
            loadAPIKeys();
        } else {
            alert('Error: ' + (result.message || 'Failed to revoke key'));
        }
    } catch (error) {
        alert('Error revoking key. Please try again.');
    }
}

function copyKey(key) {
    navigator.clipboard.writeText(key).then(() => {
        alert('API key copied to clipboard!');
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Documentation tabs
document.querySelectorAll('.docs-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        const tabName = tab.dataset.tab;

        document.querySelectorAll('.docs-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.docs-tab-content').forEach(c => c.classList.remove('active'));

        tab.classList.add('active');
        document.getElementById(`tab-${tabName}`).classList.add('active');
    });
});

document.getElementById('create-key-btn').addEventListener('click', openCreateKeyModal);
</script>

<style>
<?php include PFOB_PLUGIN_DIR . 'assets/css/api-access.css'; ?>
</style>

<?php PFOB_Template::footer(); ?>
