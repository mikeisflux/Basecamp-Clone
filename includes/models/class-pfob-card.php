<?php
/**
 * Card model class (Kanban).
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/models
 */

class PFOB_Card {

    public static function create_column( $data ) {
        return PFOB_Database::insert( 'card_columns', $data );
    }

    public static function get_column( $column_id ) {
        return PFOB_Database::get_row( 'card_columns', array( 'id' => $column_id ) );
    }

    public static function get_project_columns( $project_id ) {
        return PFOB_Database::get_results(
            'card_columns',
            array( 'project_id' => $project_id, 'is_archived' => 0 ),
            array( 'position' => 'ASC' )
        );
    }

    public static function update_column( $column_id, $data ) {
        return PFOB_Database::update( 'card_columns', $data, array( 'id' => $column_id ) );
    }

    public static function delete_column( $column_id ) {
        return PFOB_Database::delete( 'card_columns', array( 'id' => $column_id ) );
    }

    public static function create_card( $data ) {
        $data['created_by'] = get_current_user_id();

        if ( isset( $data['tags'] ) && is_array( $data['tags'] ) ) {
            $data['tags'] = wp_json_encode( $data['tags'] );
        }
        if ( isset( $data['attachments'] ) && is_array( $data['attachments'] ) ) {
            $data['attachments'] = wp_json_encode( $data['attachments'] );
        }
        if ( isset( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
            $data['metadata'] = wp_json_encode( $data['metadata'] );
        }

        return PFOB_Database::insert( 'cards', $data );
    }

    public static function get_card( $card_id ) {
        return PFOB_Database::get_row( 'cards', array( 'id' => $card_id ) );
    }

    public static function get_column_cards( $column_id ) {
        return PFOB_Database::get_results(
            'cards',
            array( 'column_id' => $column_id, 'is_archived' => 0 ),
            array( 'position' => 'ASC' )
        );
    }

    public static function get_project_cards( $project_id ) {
        return PFOB_Database::get_results(
            'cards',
            array( 'project_id' => $project_id, 'is_archived' => 0 ),
            array( 'position' => 'ASC' )
        );
    }

    public static function update_card( $card_id, $data ) {
        if ( isset( $data['tags'] ) && is_array( $data['tags'] ) ) {
            $data['tags'] = wp_json_encode( $data['tags'] );
        }
        if ( isset( $data['attachments'] ) && is_array( $data['attachments'] ) ) {
            $data['attachments'] = wp_json_encode( $data['attachments'] );
        }
        return PFOB_Database::update( 'cards', $data, array( 'id' => $card_id ) );
    }

    public static function delete_card( $card_id ) {
        return PFOB_Database::delete( 'cards', array( 'id' => $card_id ) );
    }

    public static function move_card( $card_id, $column_id, $position ) {
        return self::update_card( $card_id, array(
            'column_id' => $column_id,
            'position'  => $position,
        ) );
    }

    public static function archive_card( $card_id ) {
        return self::update_card( $card_id, array( 'is_archived' => 1 ) );
    }
}
