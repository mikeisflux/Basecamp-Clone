<?php
/**
 * Document model class.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/models
 */

class PFOB_Document {

    public static function create( $data ) {
        $data['uploaded_by'] = get_current_user_id();

        if ( isset( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
            $data['metadata'] = wp_json_encode( $data['metadata'] );
        }

        return PFOB_Database::insert( 'documents', $data );
    }

    public static function get( $document_id ) {
        return PFOB_Database::get_row( 'documents', array( 'id' => $document_id ) );
    }

    public static function get_project_documents( $project_id, $parent_id = null ) {
        $where = array( 'project_id' => $project_id );

        if ( $parent_id === null ) {
            // Get root level documents only (handled in query)
            return PFOB_Database::get_results(
                'documents',
                $where,
                array( 'is_folder' => 'DESC', 'name' => 'ASC' )
            );
        } else {
            $where['parent_id'] = $parent_id;
            return PFOB_Database::get_results(
                'documents',
                $where,
                array( 'is_folder' => 'DESC', 'name' => 'ASC' )
            );
        }
    }

    public static function update( $document_id, $data ) {
        if ( isset( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
            $data['metadata'] = wp_json_encode( $data['metadata'] );
        }
        return PFOB_Database::update( 'documents', $data, array( 'id' => $document_id ) );
    }

    public static function delete( $document_id ) {
        // Delete file from filesystem if it exists
        $document = self::get( $document_id );
        if ( $document && ! empty( $document->file_path ) ) {
            $file_path = WP_CONTENT_DIR . $document->file_path;
            if ( file_exists( $file_path ) ) {
                unlink( $file_path );
            }
        }

        return PFOB_Database::delete( 'documents', array( 'id' => $document_id ) );
    }

    public static function create_folder( $project_id, $name, $parent_id = null ) {
        return self::create( array(
            'project_id' => $project_id,
            'parent_id'  => $parent_id,
            'type'       => 'folder',
            'name'       => $name,
            'is_folder'  => 1,
        ) );
    }

    public static function get_folder_contents( $folder_id ) {
        return self::get_project_documents( null, $folder_id );
    }
}
