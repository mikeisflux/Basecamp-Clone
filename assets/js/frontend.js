/**
 * ProjectFOB - Frontend JavaScript
 */

class BasecampWP {
    constructor() {
        if (typeof pfobData !== 'undefined') {
            this.restUrl = pfobData.restUrl;
            this.nonce = pfobData.nonce;
            this.currentUser = pfobData.currentUser;
            this.projectId = pfobData.projectId || null;

            this.init();
        }
    }

    init() {
        // Attach event listeners
        this.attachEventListeners();

        // Initialize notifications
        this.initNotifications();

        // Auto-refresh for chat if on chat page
        if (window.location.pathname.includes('/chat')) {
            this.initChatPolling();
        }
    }

    attachEventListeners() {
        // Create project button
        const createProjectBtn = document.getElementById('create-project-btn');
        if (createProjectBtn) {
            createProjectBtn.addEventListener('click', () => this.showCreateProjectModal());
        }

        // New message button
        const newMessageBtn = document.getElementById('new-message-btn');
        if (newMessageBtn) {
            newMessageBtn.addEventListener('click', () => this.showCreateMessageModal());
        }

        // Notifications button
        const notificationsBtn = document.getElementById('notifications-btn');
        if (notificationsBtn) {
            notificationsBtn.addEventListener('click', () => this.toggleNotificationPanel());
        }

        // Mark all as read
        const markAllReadBtn = document.getElementById('mark-all-read');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', () => this.markAllNotificationsRead());
        }
    }

    // API Request Helper
    async apiRequest(endpoint, method = 'GET', data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': this.nonce
            }
        };

        if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(this.restUrl + endpoint, options);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'API request failed');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            this.showToast('Error: ' + error.message, 'error');
            throw error;
        }
    }

    // Create Project Modal
    showCreateProjectModal() {
        const modal = document.createElement('div');
        modal.className = 'pfob-modal';
        modal.innerHTML = `
            <div class="pfob-modal-content">
                <div class="pfob-modal-header">
                    <h2>Make a new project</h2>
                    <button class="pfob-modal-close">&times;</button>
                </div>
                <div class="pfob-modal-body">
                    <form id="create-project-form">
                        <div class="pfob-form-group">
                            <label for="project-name">Name this project *</label>
                            <input type="text" id="project-name" name="name" required
                                   placeholder="e.g. Office Renovation">
                        </div>
                        <div class="pfob-form-group">
                            <label for="project-description">Add a description (optional)</label>
                            <textarea id="project-description" name="description" rows="3"
                                      placeholder="e.g. Plans and scheduling for expanding the office"></textarea>
                        </div>
                        <div class="pfob-form-actions">
                            <button type="submit" class="pfob-btn pfob-btn-primary">
                                Create Project
                            </button>
                            <button type="button" class="pfob-btn pfob-btn-secondary pfob-modal-close">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Close modal handlers
        modal.querySelectorAll('.pfob-modal-close').forEach(btn => {
            btn.addEventListener('click', () => modal.remove());
        });

        // Click outside to close
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });

        // Form submission
        document.getElementById('create-project-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.createProject(new FormData(e.target));
        });

        // Focus on name input
        document.getElementById('project-name').focus();
    }

    async createProject(formData) {
        const data = {
            name: formData.get('name'),
            description: formData.get('description')
        };

        try {
            const result = await this.apiRequest('/projects', 'POST', data);

            if (result.success) {
                this.showToast('Project created successfully!', 'success');

                // Remove modal
                document.querySelector('.pfob-modal')?.remove();

                // Redirect to project page
                window.location.href = `/basecamp/projects/${result.data.slug}/`;
            }
        } catch (error) {
            // Error already handled in apiRequest
        }
    }

    // Create Message Modal
    showCreateMessageModal() {
        const modal = document.createElement('div');
        modal.className = 'pfob-modal';
        modal.innerHTML = `
            <div class="pfob-modal-content">
                <div class="pfob-modal-header">
                    <h2>New Message</h2>
                    <button class="pfob-modal-close">&times;</button>
                </div>
                <div class="pfob-modal-body">
                    <form id="create-message-form">
                        <div class="pfob-form-group">
                            <label for="message-title">Title *</label>
                            <input type="text" id="message-title" name="title" required
                                   placeholder="What's this message about?">
                        </div>
                        <div class="pfob-form-group">
                            <label for="message-content">Message *</label>
                            <textarea id="message-content" name="content" rows="8" required
                                      placeholder="Write your message..."></textarea>
                        </div>
                        <div class="pfob-form-actions">
                            <button type="submit" class="pfob-btn pfob-btn-primary">
                                Post Message
                            </button>
                            <button type="button" class="pfob-btn pfob-btn-secondary pfob-modal-close">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Close modal handlers
        modal.querySelectorAll('.pfob-modal-close').forEach(btn => {
            btn.addEventListener('click', () => modal.remove());
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });

        // Form submission
        document.getElementById('create-message-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.createMessage(new FormData(e.target));
        });

        document.getElementById('message-title').focus();
    }

    async createMessage(formData) {
        const data = {
            title: formData.get('title'),
            content: formData.get('content')
        };

        try {
            const result = await this.apiRequest(`/projects/${this.projectId}/messages`, 'POST', data);

            if (result.success) {
                this.showToast('Message posted successfully!', 'success');
                document.querySelector('.pfob-modal')?.remove();

                // Reload page to show new message
                window.location.reload();
            }
        } catch (error) {
            // Error already handled
        }
    }

    // Notifications
    initNotifications() {
        this.loadNotifications();

        // Poll for new notifications every 30 seconds
        setInterval(() => this.loadNotifications(), 30000);
    }

    async loadNotifications() {
        try {
            const result = await this.apiRequest('/notifications?unread_only=true');

            if (result.success && result.data) {
                const count = result.data.length;
                const badge = document.getElementById('notification-badge');

                if (count > 0) {
                    badge.textContent = count > 9 ? '9+' : count;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }

                this.renderNotifications(result.data);
            }
        } catch (error) {
            // Silently fail for notifications
            console.error('Failed to load notifications:', error);
        }
    }

    renderNotifications(notifications) {
        const list = document.getElementById('notification-list');
        if (!list) return;

        if (notifications.length === 0) {
            list.innerHTML = '<p style="padding: 20px; text-align: center; color: #757575;">No new notifications</p>';
            return;
        }

        list.innerHTML = notifications.map(notif => `
            <div class="pfob-notification-item" data-id="${notif.id}">
                <h4>${this.escapeHtml(notif.title)}</h4>
                <p>${this.escapeHtml(notif.message)}</p>
                <span class="pfob-notification-time">${this.formatDate(notif.created_at)}</span>
            </div>
        `).join('');
    }

    toggleNotificationPanel() {
        const panel = document.getElementById('notification-panel');
        if (panel.style.display === 'none' || !panel.style.display) {
            panel.style.display = 'block';
        } else {
            panel.style.display = 'none';
        }
    }

    async markAllNotificationsRead() {
        try {
            await this.apiRequest('/notifications/mark-all-read', 'POST');
            this.loadNotifications();
            this.showToast('All notifications marked as read', 'success');
        } catch (error) {
            // Error already handled
        }
    }

    // Chat Polling
    initChatPolling() {
        let lastMessageId = 0;

        setInterval(async () => {
            try {
                const result = await this.apiRequest(`/projects/${this.projectId}/chat/poll?since_id=${lastMessageId}`);

                if (result.success && result.data && result.data.length > 0) {
                    result.data.forEach(message => {
                        this.appendChatMessage(message);
                        lastMessageId = Math.max(lastMessageId, message.id);
                    });
                }
            } catch (error) {
                console.error('Chat polling error:', error);
            }
        }, 3000); // Poll every 3 seconds
    }

    appendChatMessage(message) {
        const chatContainer = document.getElementById('chat-messages');
        if (!chatContainer) return;

        const messageEl = document.createElement('div');
        messageEl.className = 'pfob-chat-message';
        messageEl.innerHTML = `
            <div class="pfob-chat-avatar">
                <img src="${message.user_avatar}" alt="${message.user_name}">
            </div>
            <div class="pfob-chat-content">
                <strong>${this.escapeHtml(message.user_name)}</strong>
                <span class="pfob-chat-time">${this.formatDate(message.created_at)}</span>
                <p>${this.escapeHtml(message.message)}</p>
            </div>
        `;

        chatContainer.appendChild(messageEl);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Toast Notifications
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `pfob-toast pfob-toast-${type}`;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: ${type === 'success' ? '#2d9061' : '#d93a3a'};
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 3000;
            font-weight: 600;
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Utility Functions
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));

        if (days === 0) return 'today';
        if (days === 1) return 'yesterday';
        if (days < 7) return `${days} days ago`;

        return date.toLocaleDateString();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.pfob = new BasecampWP();
});
