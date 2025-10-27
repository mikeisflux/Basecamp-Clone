/**
 * Basecamp WP WebSocket Client
 *
 * Handles WebSocket connections for real-time features
 */

class BasecampWebSocket {
    constructor(config) {
        this.config = config;
        this.socket = null;
        this.connected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 2000;
        this.listeners = new Map();
        this.currentProject = null;

        if (config.enabled && config.url && config.token) {
            this.connect();
        }
    }

    connect() {
        if (this.socket && this.connected) {
            return;
        }

        console.log('🔌 Connecting to WebSocket server...');

        // Load Socket.IO from CDN if not already loaded
        if (typeof io === 'undefined') {
            this.loadSocketIO().then(() => this.initializeSocket());
        } else {
            this.initializeSocket();
        }
    }

    loadSocketIO() {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdn.socket.io/4.6.1/socket.io.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    initializeSocket() {
        this.socket = io(this.config.url, {
            auth: {
                token: this.config.token
            },
            reconnection: true,
            reconnectionAttempts: this.maxReconnectAttempts,
            reconnectionDelay: this.reconnectDelay
        });

        this.setupEventHandlers();
    }

    setupEventHandlers() {
        this.socket.on('connect', () => {
            console.log('✅ WebSocket connected');
            this.connected = true;
            this.reconnectAttempts = 0;

            // Rejoin current project if any
            if (this.currentProject) {
                this.joinProject(this.currentProject);
            }

            this.trigger('connected');
        });

        this.socket.on('disconnect', (reason) => {
            console.log('❌ WebSocket disconnected:', reason);
            this.connected = false;
            this.trigger('disconnected', { reason });
        });

        this.socket.on('connect_error', (error) => {
            console.error('WebSocket connection error:', error);
            this.reconnectAttempts++;

            if (this.reconnectAttempts >= this.maxReconnectAttempts) {
                console.error('Max reconnection attempts reached. Falling back to polling.');
                this.trigger('fallback-to-polling');
            }
        });

        // Chat messages
        this.socket.on('chat-message', (data) => {
            this.trigger('chat-message', data);
        });

        // Activity updates
        this.socket.on('activity-update', (data) => {
            this.trigger('activity-update', data);
        });

        // Typing indicators
        this.socket.on('user-typing', (data) => {
            this.trigger('user-typing', data);
        });

        this.socket.on('user-stopped-typing', (data) => {
            this.trigger('user-stopped-typing', data);
        });

        // User presence
        this.socket.on('user-presence-changed', (data) => {
            this.trigger('user-presence-changed', data);
        });

        this.socket.on('project-users-online', (data) => {
            this.trigger('project-users-online', data);
        });

        // Notifications
        this.socket.on('notification', (data) => {
            this.trigger('notification', data);
        });

        // Pong response
        this.socket.on('pong', () => {
            // Keepalive response
        });
    }

    joinProject(projectId) {
        if (!this.connected) {
            console.warn('Cannot join project: not connected');
            return;
        }

        this.currentProject = projectId;
        this.socket.emit('join-project', projectId);
        console.log(`📂 Joined project ${projectId}`);
    }

    leaveProject(projectId) {
        if (!this.connected) {
            return;
        }

        this.socket.emit('leave-project', projectId);

        if (this.currentProject === projectId) {
            this.currentProject = null;
        }

        console.log(`📂 Left project ${projectId}`);
    }

    sendChatMessage(projectId, message) {
        if (!this.connected) {
            console.warn('Cannot send message: not connected');
            return false;
        }

        this.socket.emit('chat-message', {
            projectId,
            message
        });

        return true;
    }

    sendActivity(projectId, activity) {
        if (!this.connected) {
            return false;
        }

        this.socket.emit('activity-update', {
            projectId,
            activity
        });

        return true;
    }

    startTyping(projectId) {
        if (!this.connected) {
            return;
        }

        this.socket.emit('typing-start', { projectId });
    }

    stopTyping(projectId) {
        if (!this.connected) {
            return;
        }

        this.socket.emit('typing-stop', { projectId });
    }

    on(event, callback) {
        if (!this.listeners.has(event)) {
            this.listeners.set(event, []);
        }
        this.listeners.get(event).push(callback);
    }

    off(event, callback) {
        if (!this.listeners.has(event)) {
            return;
        }

        const callbacks = this.listeners.get(event);
        const index = callbacks.indexOf(callback);

        if (index > -1) {
            callbacks.splice(index, 1);
        }
    }

    trigger(event, data) {
        if (!this.listeners.has(event)) {
            return;
        }

        this.listeners.get(event).forEach(callback => {
            try {
                callback(data);
            } catch (error) {
                console.error(`Error in ${event} listener:`, error);
            }
        });
    }

    disconnect() {
        if (this.socket) {
            this.socket.disconnect();
            this.socket = null;
            this.connected = false;
        }
    }

    isConnected() {
        return this.connected;
    }

    // Keepalive ping
    startKeepAlive() {
        this.keepAliveInterval = setInterval(() => {
            if (this.connected) {
                this.socket.emit('ping');
            }
        }, 30000); // Ping every 30 seconds
    }

    stopKeepAlive() {
        if (this.keepAliveInterval) {
            clearInterval(this.keepAliveInterval);
            this.keepAliveInterval = null;
        }
    }
}

// Global instance
window.BasecampWebSocket = BasecampWebSocket;
