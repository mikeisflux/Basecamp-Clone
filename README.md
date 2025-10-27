# Basecamp WP Pro

A complete Basecamp clone built as a single WordPress plugin with no third-party dependencies.

## Description

Basecamp WP Pro transforms WordPress into a fully-featured project management system inspired by Basecamp. It provides a custom interface with all the tools teams need to collaborate, communicate, and manage projects effectively.

## Features

### Core Features
- **Projects** - Create and manage unlimited projects
- **Message Board** - Post announcements, discussions, and feedback
- **To-dos** - Task lists with assignments and due dates
- **Docs & Files** - Document storage with folder organization
- **Chat** - Real-time messaging for each project
- **Schedule** - Calendar with events and milestones
- **Card Table** - Kanban-style workflow boards

### Additional Features
- **Dashboard** - Overview of all your projects
- **My Stuff** - Personal workspace with assignments and bookmarks
- **Activity Feed** - Track all project activity
- **Notifications** - Real-time notifications and email alerts
- **Search** - Universal search across all content
- **User Management** - Role-based permissions and access control

## Installation

1. Upload the `basecamp-wp-pro` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to the Basecamp menu in the WordPress admin
4. Configure your settings (company name, colors, etc.)
5. Visit `yoursite.com/basecamp/` to start using the system

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Modern web browser (Chrome, Firefox, Safari, Edge)

## Configuration

### Admin Settings

Go to **WordPress Admin > Basecamp** to configure:

- **Company Name** - Your organization name
- **Primary Color** - Brand color for buttons and accents
- **Email Notifications** - Enable/disable email notifications
- **File Upload Settings** - Maximum file size and allowed file types

### Custom URLs

The plugin creates custom URLs that work independently of your WordPress theme:

- `/basecamp/` - Dashboard
- `/basecamp/projects/` - Projects list
- `/basecamp/projects/{slug}/` - Individual project
- `/basecamp/my-stuff/` - Personal workspace
- `/basecamp/activity/` - Activity feed

## User Roles & Permissions

### Administrator
- Full access to all features
- Can manage plugin settings
- Can create and delete projects
- Can add/remove team members

### Regular Users
- Can create projects
- Can be added to projects
- Can post messages, create todos, upload files
- Can only access projects they're members of

## Database Tables

The plugin creates the following database tables:

- `wp_bcwp_projects` - Project information
- `wp_bcwp_project_members` - Project team members
- `wp_bcwp_project_tools` - Enabled tools per project
- `wp_bcwp_messages` - Message board posts
- `wp_bcwp_todo_lists` - Todo lists
- `wp_bcwp_todo_items` - Individual todo items
- `wp_bcwp_documents` - Files and folders
- `wp_bcwp_chat_messages` - Chat messages
- `wp_bcwp_events` - Calendar events
- `wp_bcwp_cards` - Kanban cards
- `wp_bcwp_card_columns` - Kanban columns
- `wp_bcwp_activities` - Activity log
- `wp_bcwp_notifications` - User notifications
- `wp_bcwp_comments` - Comments on various items
- `wp_bcwp_companies` - Company/organization data
- `wp_bcwp_invitations` - Project invitations

## REST API

The plugin exposes a REST API at `/wp-json/bcwp/v1/` with the following endpoints:

### Projects
- `GET /projects` - List all projects
- `POST /projects` - Create a project
- `GET /projects/{id}` - Get project details
- `PUT /projects/{id}` - Update project
- `DELETE /projects/{id}` - Delete project
- `GET /projects/{id}/members` - Get project members
- `POST /projects/{id}/members` - Add member

### Messages
- `GET /projects/{id}/messages` - List messages
- `POST /projects/{id}/messages` - Create message
- `GET /messages/{id}` - Get message
- `PUT /messages/{id}` - Update message
- `DELETE /messages/{id}` - Delete message

### Todos
- `GET /projects/{id}/todo-lists` - List todo lists
- `POST /projects/{id}/todo-lists` - Create list
- `GET /todo-lists/{id}/items` - List items
- `POST /todo-lists/{id}/items` - Create item
- `PUT /todo-items/{id}` - Update item
- `POST /todo-items/{id}/complete` - Complete item

### Chat
- `GET /projects/{id}/chat` - Get messages
- `POST /projects/{id}/chat` - Send message
- `GET /projects/{id}/chat/poll` - Poll for new messages

## File Uploads

Files are uploaded to `/wp-content/uploads/bcwp/{project_id}/`

Supported file types (configurable):
- Images: jpg, jpeg, png, gif
- Documents: pdf, doc, docx, xls, xlsx, ppt, pptx
- Archives: zip

Maximum file size is configurable (default: 10MB)

## Customization

### CSS Customization

The plugin uses CSS variables for easy customization. Override these in your theme:

```css
:root {
    --bcwp-primary-color: #2d9061;
    --bcwp-background-color: #f7f6f3;
    --bcwp-text-color: #1d1d1d;
}
```

### Hooks & Filters

Developers can extend the plugin using WordPress hooks:

**Actions:**
- `bcwp_project_created` - Fired when a project is created
- `bcwp_message_posted` - Fired when a message is posted
- `bcwp_todo_completed` - Fired when a todo is completed

**Filters:**
- `bcwp_project_data` - Modify project data before saving
- `bcwp_notification_message` - Customize notification messages
- `bcwp_allowed_file_types` - Modify allowed file types

## Performance

- Uses WordPress transients for caching
- Optimized database queries with proper indexing
- Lazy loading for large datasets
- AJAX polling for real-time updates (3-30 second intervals)

## Security

- WordPress nonce verification on all requests
- REST API authentication required
- SQL injection prevention via prepared statements
- XSS protection via proper escaping
- CSRF protection on all forms
- Role-based access control

## Uninstallation

When you uninstall the plugin:

1. All database tables are dropped
2. All plugin options are deleted
3. All uploaded files are removed
4. No data remains in your WordPress installation

**Warning:** This action is permanent and cannot be undone!

## Support

For issues, questions, or feature requests:
- GitHub: https://github.com/mikeisflux/Basecamp-Clone
- Documentation: See docs/ folder

## Changelog

### Version 1.0.0
- Initial release
- Complete project management system
- All core features implemented
- REST API endpoints
- Real-time chat
- Notifications system
- Search functionality

## Credits

Inspired by Basecamp by 37signals

## License

GPL-2.0+

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

## Author

Mike
- GitHub: https://github.com/mikeisflux
