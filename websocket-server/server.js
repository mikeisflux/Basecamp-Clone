/**
 * Basecamp WP WebSocket Server
 *
 * Provides real-time functionality for:
 * - Chat messages
 * - Activity feed updates
 * - Notifications
 * - Live presence
 */

const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors');
const jwt = require('jsonwebtoken');
require('dotenv').config();

const app = express();
const server = http.createServer(app);

// Configure CORS
app.use(cors());
app.use(express.json());

// Socket.IO setup with CORS
const io = socketIo(server, {
    cors: {
        origin: process.env.WORDPRESS_URL || "http://localhost",
        methods: ["GET", "POST"],
        credentials: true
    }
});

// In-memory storage
const connections = new Map(); // socket.id -> { userId, projects, rooms }
const projectRooms = new Map(); // projectId -> Set of socket.ids
const userPresence = new Map(); // userId -> { online, lastSeen, currentProject }

// Middleware to verify JWT token
io.use((socket, next) => {
    const token = socket.handshake.auth.token;

    if (!token) {
        return next(new Error('Authentication token required'));
    }

    try {
        // Verify JWT token with secret for security
        const secret = process.env.JWT_SECRET || process.env.WEBSOCKET_SECRET;

        if (!secret) {
            console.error('CRITICAL: JWT_SECRET or WEBSOCKET_SECRET not configured');
            return next(new Error('Server configuration error'));
        }

        // Use jwt.verify instead of jwt.decode for cryptographic verification
        const decoded = jwt.verify(token, secret, {
            algorithms: ['HS256'],
            maxAge: '24h' // Token expires after 24 hours
        });

        if (!decoded || !decoded.user_id) {
            return next(new Error('Invalid token payload'));
        }

        socket.userId = decoded.user_id;
        socket.userName = decoded.user_name || 'User';
        next();
    } catch (err) {
        console.error('JWT verification failed:', err.message);
        if (err.name === 'TokenExpiredError') {
            next(new Error('Token expired'));
        } else if (err.name === 'JsonWebTokenError') {
            next(new Error('Invalid token'));
        } else {
            next(new Error('Authentication failed'));
        }
    }
});

// Connection handler
io.on('connection', (socket) => {
    console.log(`User ${socket.userId} connected (${socket.userName})`);

    // Initialize connection data
    connections.set(socket.id, {
        userId: socket.userId,
        userName: socket.userName,
        projects: new Set(),
        rooms: new Set()
    });

    // Update user presence
    userPresence.set(socket.userId, {
        online: true,
        lastSeen: new Date(),
        socketId: socket.id
    });

    // Notify others in user's projects about online status
    broadcastPresenceUpdate(socket.userId, true);

    // Join project room
    socket.on('join-project', (projectId) => {
        const room = `project-${projectId}`;
        socket.join(room);

        const conn = connections.get(socket.id);
        conn.projects.add(projectId);
        conn.rooms.add(room);

        // Track in project rooms
        if (!projectRooms.has(projectId)) {
            projectRooms.set(projectId, new Set());
        }
        projectRooms.get(projectId).add(socket.id);

        console.log(`User ${socket.userId} joined project ${projectId}`);

        // Send current online users in this project
        const onlineUsers = getOnlineUsersInProject(projectId);
        socket.emit('project-users-online', { projectId, users: onlineUsers });
    });

    // Leave project room
    socket.on('leave-project', (projectId) => {
        const room = `project-${projectId}`;
        socket.leave(room);

        const conn = connections.get(socket.id);
        conn.projects.delete(projectId);
        conn.rooms.delete(room);

        if (projectRooms.has(projectId)) {
            projectRooms.get(projectId).delete(socket.id);
        }

        console.log(`User ${socket.userId} left project ${projectId}`);
    });

    // Chat message
    socket.on('chat-message', (data) => {
        const { projectId, message } = data;
        const room = `project-${projectId}`;

        // Broadcast to all users in the project except sender
        socket.to(room).emit('chat-message', {
            id: message.id,
            content: message.content,
            userId: socket.userId,
            userName: socket.userName,
            avatar: message.avatar,
            createdAt: message.createdAt
        });

        console.log(`Chat message in project ${projectId} from ${socket.userName}`);
    });

    // Activity update
    socket.on('activity-update', (data) => {
        const { projectId, activity } = data;
        const room = `project-${projectId}`;

        // Broadcast to all users in the project
        io.to(room).emit('activity-update', activity);

        console.log(`Activity update in project ${projectId}: ${activity.type}`);
    });

    // Typing indicator
    socket.on('typing-start', (data) => {
        const { projectId } = data;
        const room = `project-${projectId}`;

        socket.to(room).emit('user-typing', {
            userId: socket.userId,
            userName: socket.userName,
            projectId
        });
    });

    socket.on('typing-stop', (data) => {
        const { projectId } = data;
        const room = `project-${projectId}`;

        socket.to(room).emit('user-stopped-typing', {
            userId: socket.userId,
            projectId
        });
    });

    // Notification
    socket.on('send-notification', (data) => {
        const { targetUserId, notification } = data;

        // Find target user's socket
        const targetPresence = userPresence.get(targetUserId);
        if (targetPresence && targetPresence.online) {
            io.to(targetPresence.socketId).emit('notification', notification);
        }
    });

    // Broadcast to all project members
    socket.on('broadcast-to-project', (data) => {
        const { projectId, event, payload } = data;
        const room = `project-${projectId}`;

        io.to(room).emit(event, payload);
    });

    // Handle disconnect
    socket.on('disconnect', () => {
        console.log(`User ${socket.userId} disconnected`);

        // Clean up
        const conn = connections.get(socket.id);
        if (conn) {
            // Remove from project rooms
            conn.projects.forEach(projectId => {
                if (projectRooms.has(projectId)) {
                    projectRooms.get(projectId).delete(socket.id);
                }
            });

            connections.delete(socket.id);
        }

        // Update presence
        userPresence.set(socket.userId, {
            online: false,
            lastSeen: new Date()
        });

        // Notify others
        broadcastPresenceUpdate(socket.userId, false);
    });

    // Ping for keepalive
    socket.on('ping', () => {
        socket.emit('pong');
    });
});

// Helper functions
function getOnlineUsersInProject(projectId) {
    const users = [];
    const sockets = projectRooms.get(projectId);

    if (sockets) {
        sockets.forEach(socketId => {
            const conn = connections.get(socketId);
            if (conn) {
                users.push({
                    userId: conn.userId,
                    userName: conn.userName
                });
            }
        });
    }

    return users;
}

function broadcastPresenceUpdate(userId, online) {
    // Find all projects this user is a member of and broadcast
    connections.forEach((conn, socketId) => {
        if (conn.userId !== userId) {
            io.to(socketId).emit('user-presence-changed', {
                userId,
                online,
                timestamp: new Date()
            });
        }
    });
}

// REST API endpoint for WordPress to trigger events
app.post('/api/trigger', (req, res) => {
    const { projectId, event, payload, secret } = req.body;

    // Verify secret key
    if (secret !== process.env.WEBSOCKET_SECRET) {
        return res.status(403).json({ error: 'Unauthorized' });
    }

    // Broadcast event to project room
    const room = `project-${projectId}`;
    io.to(room).emit(event, payload);

    res.json({ success: true, message: 'Event triggered' });
});

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        connections: connections.size,
        projects: projectRooms.size,
        onlineUsers: Array.from(userPresence.values()).filter(u => u.online).length
    });
});

// Stats endpoint
app.get('/stats', (req, res) => {
    const stats = {
        totalConnections: connections.size,
        totalProjects: projectRooms.size,
        onlineUsers: Array.from(userPresence.values()).filter(u => u.online).length,
        projectBreakdown: {}
    };

    projectRooms.forEach((sockets, projectId) => {
        stats.projectBreakdown[projectId] = sockets.size;
    });

    res.json(stats);
});

// Start server
const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`\n🚀 Basecamp WP WebSocket Server running on port ${PORT}`);
    console.log(`📊 Health check: http://localhost:${PORT}/health`);
    console.log(`📈 Stats: http://localhost:${PORT}/stats\n`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('SIGTERM received, closing server...');
    server.close(() => {
        console.log('Server closed');
        process.exit(0);
    });
});
