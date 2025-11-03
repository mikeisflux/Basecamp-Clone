<?php
/**
 * File service class.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/services
 */

class PFOB_File_Service {

    /**
     * Upload directory relative to wp-content.
     */
    const UPLOAD_DIR = '/uploads/pfob/';

    public static function upload( $file, $project_id ) {
        // Validate file
        $validation = self::validate_file( $file );
        if ( is_wp_error( $validation ) ) {
            return $validation;
        }

        // Create upload directory if it doesn't exist
        $upload_path = WP_CONTENT_DIR . self::UPLOAD_DIR . $project_id . '/';
        if ( ! file_exists( $upload_path ) ) {
            wp_mkdir_p( $upload_path );
        }

        // Generate unique filename
        $filename = self::generate_unique_filename( $file['name'], $upload_path );
        $file_path = $upload_path . $filename;

        // Move uploaded file
        if ( ! move_uploaded_file( $file['tmp_name'], $file_path ) ) {
            return new WP_Error( 'upload_failed', __( 'Failed to upload file.', 'projectfob' ) );
        }

        // Return file info
        return array(
            'filename'  => $filename,
            'file_path' => self::UPLOAD_DIR . $project_id . '/' . $filename,
            'file_size' => filesize( $file_path ),
            'mime_type' => $file['type'],
            'url'       => content_url( self::UPLOAD_DIR . $project_id . '/' . $filename ),
        );
    }

    public static function validate_file( $file ) {
        // Check for upload errors
        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            return new WP_Error( 'upload_error', __( 'File upload error.', 'projectfob' ) );
        }

        // Check file size
        $max_size = get_option( 'pfob_max_file_size', 10485760 ); // 10MB default
        if ( $file['size'] > $max_size ) {
            return new WP_Error(
                'file_too_large',
                sprintf( __( 'File size exceeds maximum allowed size of %s.', 'projectfob' ), size_format( $max_size ) )
            );
        }

        // Check file type
        $allowed_types = explode( ',', get_option( 'pfob_allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,zip' ) );
        $file_ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

        if ( ! in_array( $file_ext, $allowed_types ) ) {
            return new WP_Error(
                'invalid_file_type',
                sprintf( __( 'File type .%s is not allowed.', 'projectfob' ), $file_ext )
            );
        }

        return true;
    }

    private static function generate_unique_filename( $filename, $upload_path ) {
        $file_info = pathinfo( $filename );
        $base_name = sanitize_file_name( $file_info['filename'] );
        $extension = $file_info['extension'];

        $unique_filename = $base_name . '.' . $extension;
        $counter = 1;

        while ( file_exists( $upload_path . $unique_filename ) ) {
            $unique_filename = $base_name . '-' . $counter . '.' . $extension;
            $counter++;
        }

        return $unique_filename;
    }

    public static function delete( $file_path ) {
        $full_path = WP_CONTENT_DIR . $file_path;
        if ( file_exists( $full_path ) ) {
            return unlink( $full_path );
        }
        return false;
    }

    public static function get_file_icon( $mime_type ) {
        // Handle null/empty mime type
        if ( empty( $mime_type ) ) {
            return self::get_generic_icon();
        }

        // Image files
        if ( strpos( $mime_type, 'image' ) !== false ) {
            return '<svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#10B981"/><path d="M16 20L20 24L28 16L36 24V34H12V24L16 20Z" fill="white"/><circle cx="20" cy="18" r="3" fill="white"/></svg>';
        }

        // PDF files
        if ( strpos( $mime_type, 'pdf' ) !== false ) {
            return '<svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#DC2626"/><text x="24" y="30" font-family="sans-serif" font-size="14" font-weight="bold" fill="white" text-anchor="middle">PDF</text></svg>';
        }

        // Word documents
        if ( strpos( $mime_type, 'word' ) !== false || strpos( $mime_type, 'document' ) !== false || strpos( $mime_type, 'msword' ) !== false ) {
            return '<svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#2B5797"/><text x="24" y="30" font-family="sans-serif" font-size="14" font-weight="bold" fill="white" text-anchor="middle">DOC</text></svg>';
        }

        // Excel spreadsheets
        if ( strpos( $mime_type, 'excel' ) !== false || strpos( $mime_type, 'spreadsheet' ) !== false ) {
            return '<svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#16A34A"/><text x="24" y="30" font-family="sans-serif" font-size="14" font-weight="bold" fill="white" text-anchor="middle">XLS</text></svg>';
        }

        // PowerPoint presentations
        if ( strpos( $mime_type, 'powerpoint' ) !== false || strpos( $mime_type, 'presentation' ) !== false ) {
            return '<svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#EA580C"/><text x="24" y="30" font-family="sans-serif" font-size="14" font-weight="bold" fill="white" text-anchor="middle">PPT</text></svg>';
        }

        // Text files
        if ( strpos( $mime_type, 'text' ) !== false ) {
            return '<svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#6B7280"/><text x="24" y="30" font-family="sans-serif" font-size="14" font-weight="bold" fill="white" text-anchor="middle">TXT</text></svg>';
        }

        // Zip/Archive files
        if ( strpos( $mime_type, 'zip' ) !== false || strpos( $mime_type, 'compressed' ) !== false || strpos( $mime_type, 'archive' ) !== false ) {
            return '<svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#F59E0B"/><text x="24" y="30" font-family="sans-serif" font-size="14" font-weight="bold" fill="white" text-anchor="middle">ZIP</text></svg>';
        }

        // Video files
        if ( strpos( $mime_type, 'video' ) !== false ) {
            return '<svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#7C3AED"/><polygon points="20,16 20,32 32,24" fill="white"/></svg>';
        }

        // Audio files
        if ( strpos( $mime_type, 'audio' ) !== false ) {
            return '<svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#EC4899"/><path d="M20 14H22V28C22 30 20 32 18 32C16 32 14 30 14 28C14 26 16 24 18 24C19 24 20 24.5 20 25V14Z M26 12L32 14V24C32 26 30 28 28 28C26 28 24 26 24 24C24 22 26 20 28 20C29 20 30 20.5 30 21V16L26 15V12Z" fill="white"/></svg>';
        }

        return self::get_generic_icon();
    }

    private static function get_generic_icon() {
        return '<svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#9CA3AF"/><path d="M18 12H26L32 18V36H18V12Z M26 12V18H32" stroke="white" stroke-width="2" fill="none"/></svg>';
    }
}
