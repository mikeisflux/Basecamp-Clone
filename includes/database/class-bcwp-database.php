<?php
/**
 * Database helper class.
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes/database
 */

class BCWP_Database {

    /**
     * Get table name with prefix.
     *
     * @param string $table Table name without prefix.
     * @return string Full table name with prefix.
     */
    public static function get_table_name( $table ) {
        global $wpdb;
        return $wpdb->prefix . 'bcwp_' . $table;
    }

    /**
     * Insert record into database.
     *
     * @param string $table Table name.
     * @param array  $data  Data to insert.
     * @return int|false Insert ID on success, false on failure.
     */
    public static function insert( $table, $data ) {
        global $wpdb;

        // Add timestamps
        if ( ! isset( $data['created_at'] ) ) {
            $data['created_at'] = current_time( 'mysql' );
        }
        if ( ! isset( $data['updated_at'] ) ) {
            $data['updated_at'] = current_time( 'mysql' );
        }

        $result = $wpdb->insert(
            self::get_table_name( $table ),
            $data
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update record in database.
     *
     * @param string $table Table name.
     * @param array  $data  Data to update.
     * @param array  $where Where conditions.
     * @return int|false Number of rows updated, false on error.
     */
    public static function update( $table, $data, $where ) {
        global $wpdb;

        // Update timestamp
        $data['updated_at'] = current_time( 'mysql' );

        return $wpdb->update(
            self::get_table_name( $table ),
            $data,
            $where
        );
    }

    /**
     * Delete record from database.
     *
     * @param string $table Table name.
     * @param array  $where Where conditions.
     * @return int|false Number of rows deleted, false on error.
     */
    public static function delete( $table, $where ) {
        global $wpdb;

        return $wpdb->delete(
            self::get_table_name( $table ),
            $where
        );
    }

    /**
     * Get single record from database.
     *
     * @param string $table Table name.
     * @param array  $where Where conditions.
     * @return object|null Record object or null if not found.
     */
    public static function get_row( $table, $where ) {
        global $wpdb;

        $conditions = array();
        $values = array();

        foreach ( $where as $key => $value ) {
            $conditions[] = "$key = %s";
            $values[] = $value;
        }

        $sql = sprintf(
            "SELECT * FROM %s WHERE %s LIMIT 1",
            self::get_table_name( $table ),
            implode( ' AND ', $conditions )
        );

        return $wpdb->get_row( $wpdb->prepare( $sql, $values ) );
    }

    /**
     * Get multiple records from database.
     *
     * @param string $table   Table name.
     * @param array  $where   Where conditions (optional).
     * @param array  $orderby Order by columns (optional).
     * @param int    $limit   Limit number of results (optional).
     * @param int    $offset  Offset for pagination (optional).
     * @return array Array of record objects.
     */
    public static function get_results( $table, $where = array(), $orderby = array(), $limit = null, $offset = 0 ) {
        global $wpdb;

        $sql = "SELECT * FROM " . self::get_table_name( $table );

        // Build WHERE clause
        if ( ! empty( $where ) ) {
            $conditions = array();
            $values = array();

            foreach ( $where as $key => $value ) {
                if ( is_array( $value ) ) {
                    // Handle IN clause
                    $placeholders = implode( ', ', array_fill( 0, count( $value ), '%s' ) );
                    $conditions[] = "$key IN ($placeholders)";
                    $values = array_merge( $values, $value );
                } else {
                    $conditions[] = "$key = %s";
                    $values[] = $value;
                }
            }

            $sql .= " WHERE " . implode( ' AND ', $conditions );
        }

        // Build ORDER BY clause
        if ( ! empty( $orderby ) ) {
            $order_clauses = array();
            foreach ( $orderby as $column => $direction ) {
                $order_clauses[] = "$column $direction";
            }
            $sql .= " ORDER BY " . implode( ', ', $order_clauses );
        }

        // Add LIMIT and OFFSET
        if ( $limit !== null ) {
            $sql .= " LIMIT %d";
            $values[] = $limit;

            if ( $offset > 0 ) {
                $sql .= " OFFSET %d";
                $values[] = $offset;
            }
        }

        if ( ! empty( $values ) ) {
            $sql = $wpdb->prepare( $sql, $values );
        }

        return $wpdb->get_results( $sql );
    }

    /**
     * Count records in database.
     *
     * @param string $table Table name.
     * @param array  $where Where conditions (optional).
     * @return int Record count.
     */
    public static function count( $table, $where = array() ) {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM " . self::get_table_name( $table );

        if ( ! empty( $where ) ) {
            $conditions = array();
            $values = array();

            foreach ( $where as $key => $value ) {
                $conditions[] = "$key = %s";
                $values[] = $value;
            }

            $sql .= " WHERE " . implode( ' AND ', $conditions );
            $sql = $wpdb->prepare( $sql, $values );
        }

        return (int) $wpdb->get_var( $sql );
    }

    /**
     * Check if record exists.
     *
     * @param string $table Table name.
     * @param array  $where Where conditions.
     * @return bool True if exists, false otherwise.
     */
    public static function exists( $table, $where ) {
        return self::count( $table, $where ) > 0;
    }

    /**
     * Execute custom query.
     *
     * @param string $query  SQL query.
     * @param array  $values Values for prepared statement.
     * @return mixed Query result.
     */
    public static function query( $query, $values = array() ) {
        global $wpdb;

        if ( ! empty( $values ) ) {
            $query = $wpdb->prepare( $query, $values );
        }

        return $wpdb->query( $query );
    }
}
