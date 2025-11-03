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
        $icons = array(
            'image' => '🖼️',
            'pdf'   => '📄',
            'word'  => '📝',
            'excel' => '📊',
            'zip'   => '🗜️',
            'video' => '🎥',
            'audio' => '🎵',
        );

        // Handle null/empty mime type
        if ( empty( $mime_type ) ) {
            return '📎';
        }

        if ( strpos( $mime_type, 'image' ) !== false ) {
            return $icons['image'];
        } elseif ( strpos( $mime_type, 'pdf' ) !== false ) {
            return $icons['pdf'];
        } elseif ( strpos( $mime_type, 'word' ) !== false || strpos( $mime_type, 'document' ) !== false ) {
            return $icons['word'];
        } elseif ( strpos( $mime_type, 'excel' ) !== false || strpos( $mime_type, 'spreadsheet' ) !== false ) {
            return $icons['excel'];
        } elseif ( strpos( $mime_type, 'zip' ) !== false || strpos( $mime_type, 'compressed' ) !== false ) {
            return $icons['zip'];
        } elseif ( strpos( $mime_type, 'video' ) !== false ) {
            return $icons['video'];
        } elseif ( strpos( $mime_type, 'audio' ) !== false ) {
            return $icons['audio'];
        }

        return '📎';
    }
}
