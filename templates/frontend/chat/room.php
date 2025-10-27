<?php
/**
 * Chat room - Real-time messaging
 */

global $bcwp_project;

BCWP_Template::header( $bcwp_project->name . ' - Chat' );

$messages = BCWP_Chat::get_project_messages( $bcwp_project->id, 50 );
?>

<div class="bcwp-container">
    <?php BCWP_Template::navigation(); ?>

    <main class="bcwp-main bcwp-chat">

        <div class="bcwp-page-header">
            <h1>Chat</h1>
            <p class="bcwp-subtitle">Casual, real-time team conversation</p>
        </div>

        <div class="bcwp-chat-container">
            <div class="bcwp-chat-messages" id="chat-messages">
                <?php foreach ( $messages as $msg ) : ?>
                    <div class="bcwp-chat-message" data-id="<?php echo $msg->id; ?>">
                        <div class="bcwp-chat-avatar">
                            <?php echo BCWP_Template::user_avatar( $msg->user_id, 40 ); ?>
                        </div>
                        <div class="bcwp-chat-content">
                            <div class="bcwp-chat-header">
                                <strong><?php echo esc_html( BCWP_Auth_Service::get_user_display_name( $msg->user_id ) ); ?></strong>
                                <span class="bcwp-chat-time"><?php echo BCWP_Template::format_date( $msg->created_at ); ?></span>
                            </div>
                            <p><?php echo esc_html( $msg->message ); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="bcwp-chat-input-container">
                <form id="chat-form">
                    <textarea id="chat-input"
                              placeholder="Type your message..."
                              rows="1"></textarea>
                    <button type="submit" class="bcwp-btn bcwp-btn-primary">Send</button>
                </form>
            </div>
        </div>

    </main>
</div>

<script src="<?php echo BCWP_PLUGIN_URL; ?>assets/js/websocket-client.js"></script>
<script>
const bcwpData = {
    restUrl: '<?php echo rest_url( 'bcwp/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
    projectId: <?php echo $bcwp_project->id; ?>,
    currentUser: <?php echo json_encode( array(
        'id' => get_current_user_id(),
        'name' => wp_get_current_user()->display_name,
        'avatar' => get_avatar_url( get_current_user_id() ),
    ) ); ?>,
    websocket: <?php echo json_encode( BCWP_WebSocket_Service::get_frontend_config( get_current_user_id() ) ); ?>
};

let lastMessageId = <?php echo empty( $messages ) ? 0 : $messages[count($messages)-1]->id; ?>;
let ws = null;
let usingWebSocket = false;
let pollingInterval = null;

// Initialize WebSocket or fall back to polling
if (bcwpData.websocket.enabled) {
    ws = new BasecampWebSocket(bcwpData.websocket);

    ws.on('connected', () => {
        console.log('✅ Using WebSocket for real-time chat');
        usingWebSocket = true;
        ws.joinProject(bcwpData.projectId);

        // Stop polling if it was running
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    });

    ws.on('chat-message', (data) => {
        // Only show messages from other users (our own are shown immediately)
        if (data.userId !== bcwpData.currentUser.id) {
            appendMessage({
                id: data.id,
                message: data.content,
                user_name: data.userName,
                user_avatar: data.avatar,
                created_at: data.createdAt
            });
            lastMessageId = Math.max(lastMessageId, data.id);
        }
    });

    ws.on('fallback-to-polling', () => {
        console.log('⚠️ WebSocket failed, falling back to polling');
        usingWebSocket = false;
        startPolling();
    });

    ws.on('disconnected', () => {
        console.log('⚠️ WebSocket disconnected, using polling');
        usingWebSocket = false;
        startPolling();
    });
} else {
    console.log('📡 Using AJAX polling for chat');
    startPolling();
}

function startPolling() {
    if (pollingInterval) return;
    pollingInterval = setInterval(loadNewMessages, 3000);
}

document.getElementById('chat-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = document.getElementById('chat-input');
    const message = input.value.trim();

    if (!message) return;

    try {
        const response = await fetch(`${bcwpData.restUrl}/projects/${bcwpData.projectId}/chat`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': bcwpData.nonce
            },
            body: JSON.stringify({ message })
        });

        const result = await response.json();

        if (response.ok && result.success) {
            input.value = '';
            input.style.height = 'auto';

            // Show our own message immediately
            appendMessage({
                id: result.data.id,
                message: result.data.message,
                user_name: bcwpData.currentUser.name,
                user_avatar: bcwpData.currentUser.avatar,
                created_at: result.data.created_at
            });

            lastMessageId = Math.max(lastMessageId, result.data.id);

            // If using WebSocket, message will be broadcast by the server
            // If using polling, it will be picked up on next poll
        }
    } catch (error) {
        console.error('Failed to send message:', error);
    }
});

document.getElementById('chat-input').addEventListener('input', (e) => {
    e.target.style.height = 'auto';
    e.target.style.height = (e.target.scrollHeight) + 'px';
});

async function loadNewMessages() {
    if (usingWebSocket) return; // Skip polling if WebSocket is active

    try {
        const response = await fetch(
            `${bcwpData.restUrl}/projects/${bcwpData.projectId}/chat/poll?since_id=${lastMessageId}`,
            { headers: { 'X-WP-Nonce': bcwpData.nonce } }
        );

        const result = await response.json();

        if (result.success && result.data && result.data.length > 0) {
            result.data.forEach(msg => {
                appendMessage(msg);
                lastMessageId = Math.max(lastMessageId, msg.id);
            });
        }
    } catch (error) {
        console.error('Polling error:', error);
    }
}

function appendMessage(msg) {
    const container = document.getElementById('chat-messages');

    // Check if message already exists
    if (container.querySelector(`[data-id="${msg.id}"]`)) {
        return;
    }

    const messageEl = document.createElement('div');
    messageEl.className = 'bcwp-chat-message';
    messageEl.dataset.id = msg.id;
    messageEl.innerHTML = `
        <div class="bcwp-chat-avatar">
            <img src="${msg.user_avatar || bcwpData.currentUser.avatar}" alt="" class="bcwp-avatar">
        </div>
        <div class="bcwp-chat-content">
            <div class="bcwp-chat-header">
                <strong>${escapeHtml(msg.user_name || bcwpData.currentUser.name)}</strong>
                <span class="bcwp-chat-time">just now</span>
            </div>
            <p>${escapeHtml(msg.message)}</p>
        </div>
    `;

    container.appendChild(messageEl);
    container.scrollTop = container.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('chat-messages').scrollTop =
    document.getElementById('chat-messages').scrollHeight;

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (ws) {
        ws.leaveProject(bcwpData.projectId);
        ws.disconnect();
    }
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});
</script>
<script src="<?php echo BCWP_PLUGIN_URL; ?>assets/js/frontend.js"></script>

</body>
</html>
