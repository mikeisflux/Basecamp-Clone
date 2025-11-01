<?php
/**
 * Database schema manager.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/database
 */

class PFOB_Schema {

    /**
     * Create all database tables.
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // Projects table
        $sql = "CREATE TABLE {$prefix}pfob_projects (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            slug VARCHAR(255) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            start_date DATE,
            end_date DATE,
            company_id BIGINT UNSIGNED,
            creator_id BIGINT UNSIGNED NOT NULL,
            settings JSON,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY company_id (company_id),
            KEY creator_id (creator_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta( $sql );

        // Project members table
        $sql = "CREATE TABLE {$prefix}pfob_project_members (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            project_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            role VARCHAR(50) NOT NULL DEFAULT 'member',
            access_level VARCHAR(20) NOT NULL DEFAULT 'on_project',
            permissions JSON,
            joined_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY project_user (project_id, user_id),
            KEY user_id (user_id),
            KEY role (role),
            KEY access_level (access_level)
        ) $charset_collate;";
        dbDelta( $sql );

        // Project tools table
        $sql = "CREATE TABLE {$prefix}pfob_project_tools (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            project_id BIGINT UNSIGNED NOT NULL,
            tool_type VARCHAR(50) NOT NULL,
            is_enabled TINYINT(1) NOT NULL DEFAULT 1,
            settings JSON,
            position INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY project_tool (project_id, tool_type)
        ) $charset_collate;";
        dbDelta( $sql );

        // Messages table
        $sql = "CREATE TABLE {$prefix}pfob_messages (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            project_id BIGINT UNSIGNED NOT NULL,
            author_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(500) NOT NULL,
            content LONGTEXT NOT NULL,
            category VARCHAR(100),
            is_pinned TINYINT(1) NOT NULL DEFAULT 0,
            is_archived TINYINT(1) NOT NULL DEFAULT 0,
            attachments JSON,
            metadata JSON,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id),
            KEY author_id (author_id),
            KEY category (category),
            KEY is_pinned (is_pinned),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta( $sql );

        // Todo lists table
        $sql = "CREATE TABLE {$prefix}pfob_todo_lists (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            project_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            position INT NOT NULL DEFAULT 0,
            is_archived TINYINT(1) NOT NULL DEFAULT 0,
            created_by BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id),
            KEY position (position),
            KEY created_by (created_by)
        ) $charset_collate;";
        dbDelta( $sql );

        // Todo items table
        $sql = "CREATE TABLE {$prefix}pfob_todo_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            list_id BIGINT UNSIGNED NOT NULL,
            parent_id BIGINT UNSIGNED,
            content TEXT NOT NULL,
            description TEXT,
            assignee_id BIGINT UNSIGNED,
            due_date DATE,
            is_completed TINYINT(1) NOT NULL DEFAULT 0,
            completed_at DATETIME,
            completed_by BIGINT UNSIGNED,
            position INT NOT NULL DEFAULT 0,
            priority VARCHAR(20) DEFAULT 'normal',
            attachments JSON,
            metadata JSON,
            created_by BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY list_id (list_id),
            KEY parent_id (parent_id),
            KEY assignee_id (assignee_id),
            KEY due_date (due_date),
            KEY is_completed (is_completed),
            KEY position (position),
            KEY priority (priority)
        ) $charset_collate;";
        dbDelta( $sql );

        // Documents table
        $sql = "CREATE TABLE {$prefix}pfob_documents (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            project_id BIGINT UNSIGNED NOT NULL,
            parent_id BIGINT UNSIGNED,
            type VARCHAR(20) NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            file_path VARCHAR(500),
            file_size BIGINT UNSIGNED,
            mime_type VARCHAR(100),
            external_url VARCHAR(500),
            external_service VARCHAR(50),
            version INT NOT NULL DEFAULT 1,
            is_folder TINYINT(1) NOT NULL DEFAULT 0,
            position INT NOT NULL DEFAULT 0,
            metadata JSON,
            uploaded_by BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id),
            KEY parent_id (parent_id),
            KEY type (type),
            KEY is_folder (is_folder),
            KEY uploaded_by (uploaded_by)
        ) $charset_collate;";
        dbDelta( $sql );

        // Chat messages table
        $sql = "CREATE TABLE {$prefix}pfob_chat_messages (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            project_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            parent_id BIGINT UNSIGNED,
            message TEXT NOT NULL,
            attachments JSON,
            metadata JSON,
            is_deleted TINYINT(1) NOT NULL DEFAULT 0,
            deleted_at DATETIME,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id),
            KEY user_id (user_id),
            KEY parent_id (parent_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta( $sql );

        // Events table
        $sql = "CREATE TABLE {$prefix}pfob_events (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            project_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            location VARCHAR(255),
            start_datetime DATETIME NOT NULL,
            end_datetime DATETIME NOT NULL,
            all_day TINYINT(1) NOT NULL DEFAULT 0,
            recurrence_rule TEXT,
            color VARCHAR(7),
            metadata JSON,
            created_by BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id),
            KEY start_datetime (start_datetime),
            KEY end_datetime (end_datetime),
            KEY created_by (created_by)
        ) $charset_collate;";
        dbDelta( $sql );

        // Cards table
        $sql = "CREATE TABLE {$prefix}pfob_cards (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            project_id BIGINT UNSIGNED NOT NULL,
            column_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            assignee_id BIGINT UNSIGNED,
            due_date DATE,
            position INT NOT NULL DEFAULT 0,
            color VARCHAR(7),
            priority VARCHAR(20) DEFAULT 'normal',
            tags JSON,
            attachments JSON,
            metadata JSON,
            is_archived TINYINT(1) NOT NULL DEFAULT 0,
            created_by BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id),
            KEY column_id (column_id),
            KEY assignee_id (assignee_id),
            KEY position (position),
            KEY due_date (due_date)
        ) $charset_collate;";
        dbDelta( $sql );

        // Card columns table
        $sql = "CREATE TABLE {$prefix}pfob_card_columns (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            project_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(100) NOT NULL,
            position INT NOT NULL DEFAULT 0,
            color VARCHAR(7),
            limit_cards INT,
            is_archived TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id),
            KEY position (position)
        ) $charset_collate;";
        dbDelta( $sql );

        // Activities table
        $sql = "CREATE TABLE {$prefix}pfob_activities (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            project_id BIGINT UNSIGNED,
            user_id BIGINT UNSIGNED NOT NULL,
            action_type VARCHAR(50) NOT NULL,
            subject_type VARCHAR(50) NOT NULL,
            subject_id BIGINT UNSIGNED NOT NULL,
            description TEXT,
            changes JSON,
            metadata JSON,
            is_public TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id),
            KEY user_id (user_id),
            KEY action_type (action_type),
            KEY subject_type_id (subject_type, subject_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta( $sql );

        // Notifications table
        $sql = "CREATE TABLE {$prefix}pfob_notifications (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            project_id BIGINT UNSIGNED,
            type VARCHAR(50) NOT NULL,
            subject_type VARCHAR(50),
            subject_id BIGINT UNSIGNED,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            action_url VARCHAR(500),
            icon VARCHAR(50),
            metadata JSON,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            read_at DATETIME,
            is_emailed TINYINT(1) NOT NULL DEFAULT 0,
            emailed_at DATETIME,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY project_id (project_id),
            KEY type (type),
            KEY is_read (is_read),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta( $sql );

        // Comments table
        $sql = "CREATE TABLE {$prefix}pfob_comments (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            commentable_type VARCHAR(50) NOT NULL,
            commentable_id BIGINT UNSIGNED NOT NULL,
            parent_id BIGINT UNSIGNED,
            user_id BIGINT UNSIGNED NOT NULL,
            content TEXT NOT NULL,
            attachments JSON,
            metadata JSON,
            is_deleted TINYINT(1) NOT NULL DEFAULT 0,
            deleted_at DATETIME,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY commentable (commentable_type, commentable_id),
            KEY parent_id (parent_id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta( $sql );

        // Companies table
        $sql = "CREATE TABLE {$prefix}pfob_companies (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            description TEXT,
            website VARCHAR(255),
            logo_url VARCHAR(500),
            settings JSON,
            created_by BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY created_by (created_by)
        ) $charset_collate;";
        dbDelta( $sql );

        // Invitations table
        $sql = "CREATE TABLE {$prefix}pfob_invitations (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            email VARCHAR(255) NOT NULL,
            token VARCHAR(64) NOT NULL,
            type VARCHAR(20) NOT NULL,
            inviter_id BIGINT UNSIGNED NOT NULL,
            project_id BIGINT UNSIGNED,
            company_id BIGINT UNSIGNED,
            role VARCHAR(50),
            message TEXT,
            metadata JSON,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            accepted_at DATETIME,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY token (token),
            KEY email (email),
            KEY status (status),
            KEY expires_at (expires_at)
        ) $charset_collate;";
        dbDelta( $sql );

        // Subscriptions table (SaaS billing)
        $sql = "CREATE TABLE {$prefix}pfob_subscriptions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            plan_id VARCHAR(50) NOT NULL,
            paypal_subscription_id VARCHAR(100),
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            trial_ends_at DATETIME,
            current_period_start DATETIME,
            current_period_end DATETIME,
            cancel_at DATETIME,
            canceled_at DATETIME,
            ended_at DATETIME,
            metadata JSON,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY user_id (user_id),
            KEY plan_id (plan_id),
            KEY status (status),
            KEY paypal_subscription_id (paypal_subscription_id),
            KEY current_period_end (current_period_end)
        ) $charset_collate;";
        dbDelta( $sql );

        // Billing history table
        $sql = "CREATE TABLE {$prefix}pfob_billing_history (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            subscription_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            transaction_id VARCHAR(100),
            transaction_type VARCHAR(50) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            currency VARCHAR(3) NOT NULL DEFAULT 'USD',
            status VARCHAR(20) NOT NULL,
            payment_method VARCHAR(50),
            billing_reason VARCHAR(100),
            invoice_url VARCHAR(500),
            receipt_url VARCHAR(500),
            metadata JSON,
            transaction_date DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY subscription_id (subscription_id),
            KEY user_id (user_id),
            KEY transaction_id (transaction_id),
            KEY status (status),
            KEY transaction_date (transaction_date)
        ) $charset_collate;";
        dbDelta( $sql );

        // Usage tracking table
        $sql = "CREATE TABLE {$prefix}pfob_usage_tracking (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            metric_type VARCHAR(50) NOT NULL,
            metric_value BIGINT UNSIGNED NOT NULL DEFAULT 0,
            period_start DATETIME NOT NULL,
            period_end DATETIME NOT NULL,
            metadata JSON,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY metric_type (metric_type),
            KEY period_start (period_start),
            KEY period_end (period_end),
            UNIQUE KEY user_metric_period (user_id, metric_type, period_start)
        ) $charset_collate;";
        dbDelta( $sql );

        // Store database version
        update_option( 'pfob_db_version', PFOB_VERSION );
        update_option( 'pfob_db_version', PFOB_VERSION );
    }

    /**
     * Drop all database tables.
     */
    public static function drop_tables() {
        global $wpdb;
        $prefix = $wpdb->prefix;

        $tables = array(
            'pfob_usage_tracking',
            'pfob_billing_history',
            'pfob_subscriptions',
            'pfob_invitations',
            'pfob_companies',
            'pfob_comments',
            'pfob_notifications',
            'pfob_activities',
            'pfob_card_columns',
            'pfob_cards',
            'pfob_events',
            'pfob_chat_messages',
            'pfob_documents',
            'pfob_todo_items',
            'pfob_todo_lists',
            'pfob_messages',
            'pfob_project_tools',
            'pfob_project_members',
            'pfob_projects',
        );

        foreach ( $tables as $table ) {
            $wpdb->query( "DROP TABLE IF EXISTS {$prefix}{$table}" );
        }

        delete_option( 'pfob_db_version' );
        delete_option( 'pfob_db_version' );
    }
}
