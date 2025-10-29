<?php
/**
 * Cloudflare R2 Storage Service
 *
 * Handles file uploads and downloads using Cloudflare R2 S3-compatible API
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/services
 */

class PFOB_R2_Storage_Service {

    /**
     * Get R2 credentials from WordPress options
     *
     * @return array|false Array with access_key_id, secret_access_key, or false if not set
     */
    private static function get_credentials() {
        $access_key_id = get_option( 'pfob_r2_access_key_id' );
        $secret_access_key = get_option( 'pfob_r2_secret_access_key' );

        if ( empty( $access_key_id ) || empty( $secret_access_key ) ) {
            return false;
        }

        return array(
            'access_key_id' => $access_key_id,
            'secret_access_key' => $secret_access_key,
        );
    }

    /**
     * Generate AWS Signature Version 4
     *
     * @param string $method HTTP method
     * @param string $url Full URL
     * @param array  $headers Request headers
     * @param string $payload Request body
     * @return string Authorization header value
     */
    private static function generate_signature( $method, $url, $headers, $payload = '' ) {
        $credentials = self::get_credentials();
        if ( ! $credentials ) {
            return '';
        }

        $parsed_url = wp_parse_url( $url );
        $host = $parsed_url['host'];
        $path = $parsed_url['path'] ?? '/';
        $query = $parsed_url['query'] ?? '';

        $timestamp = gmdate( 'Ymd\THis\Z' );
        $date = gmdate( 'Ymd' );

        // Task 1: Create canonical request
        $canonical_headers = "host:{$host}\n";
        $canonical_headers .= "x-amz-content-sha256:" . hash( 'sha256', $payload ) . "\n";
        $canonical_headers .= "x-amz-date:{$timestamp}\n";

        $signed_headers = 'host;x-amz-content-sha256;x-amz-date';

        $canonical_request = implode( "\n", array(
            $method,
            $path,
            $query,
            $canonical_headers,
            $signed_headers,
            hash( 'sha256', $payload ),
        ) );

        // Task 2: Create string to sign
        $credential_scope = "{$date}/auto/s3/aws4_request";
        $string_to_sign = implode( "\n", array(
            'AWS4-HMAC-SHA256',
            $timestamp,
            $credential_scope,
            hash( 'sha256', $canonical_request ),
        ) );

        // Task 3: Calculate signature
        $k_date = hash_hmac( 'sha256', $date, 'AWS4' . $credentials['secret_access_key'], true );
        $k_region = hash_hmac( 'sha256', 'auto', $k_date, true );
        $k_service = hash_hmac( 'sha256', 's3', $k_region, true );
        $k_signing = hash_hmac( 'sha256', 'aws4_request', $k_service, true );
        $signature = hash_hmac( 'sha256', $string_to_sign, $k_signing );

        // Task 4: Add signing information to request
        $authorization = "AWS4-HMAC-SHA256 Credential={$credentials['access_key_id']}/{$credential_scope}, SignedHeaders={$signed_headers}, Signature={$signature}";

        return array(
            'Authorization' => $authorization,
            'x-amz-date' => $timestamp,
            'x-amz-content-sha256' => hash( 'sha256', $payload ),
        );
    }

    /**
     * Make authenticated S3 API request to R2
     *
     * @param string $method HTTP method
     * @param string $path Object path (e.g., '/file.jpg')
     * @param string $body Request body
     * @param array  $extra_headers Additional headers
     * @return array|WP_Error Response or WP_Error on failure
     */
    private static function api_request( $method, $path, $body = '', $extra_headers = array() ) {
        if ( ! self::get_credentials() ) {
            return new WP_Error( 'no_credentials', 'R2 credentials not configured' );
        }

        $url = PFOB_R2_ENDPOINT . '/' . PFOB_R2_BUCKET . $path;

        // Generate signature
        $auth_headers = self::generate_signature( $method, $url, array(), $body );

        $headers = array_merge(
            array(
                'Host' => wp_parse_url( PFOB_R2_ENDPOINT, PHP_URL_HOST ),
            ),
            $auth_headers,
            $extra_headers
        );

        $args = array(
            'method' => $method,
            'headers' => $headers,
            'timeout' => 60,
        );

        if ( ! empty( $body ) ) {
            $args['body'] = $body;
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );

        if ( $status_code >= 400 ) {
            $error_body = wp_remote_retrieve_body( $response );
            return new WP_Error( 'r2_api_error', 'R2 API error: ' . $error_body, array( 'status' => $status_code ) );
        }

        return array(
            'status' => $status_code,
            'body' => wp_remote_retrieve_body( $response ),
            'headers' => wp_remote_retrieve_headers( $response ),
        );
    }

    /**
     * Upload a file to R2
     *
     * @param string $file_path Local file path
     * @param string $object_key Object key in R2 (e.g., 'uploads/2025/01/file.jpg')
     * @param string $content_type MIME type
     * @return array|WP_Error Array with URL on success, WP_Error on failure
     */
    public static function upload_file( $file_path, $object_key, $content_type = 'application/octet-stream' ) {
        if ( ! file_exists( $file_path ) ) {
            return new WP_Error( 'file_not_found', 'File not found: ' . $file_path );
        }

        $file_content = file_get_contents( $file_path );
        if ( $file_content === false ) {
            return new WP_Error( 'file_read_error', 'Failed to read file' );
        }

        // Sanitize object key (remove leading slash)
        $object_key = ltrim( $object_key, '/' );

        $response = self::api_request(
            'PUT',
            '/' . $object_key,
            $file_content,
            array(
                'Content-Type' => $content_type,
                'Content-Length' => strlen( $file_content ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return array(
            'url' => PFOB_R2_ENDPOINT . '/' . PFOB_R2_BUCKET . '/' . $object_key,
            'object_key' => $object_key,
            'size' => filesize( $file_path ),
            'content_type' => $content_type,
        );
    }

    /**
     * Upload file from $_FILES array
     *
     * @param array  $file_array $_FILES array element
     * @param int    $user_id User ID (for organizing files)
     * @param int    $project_id Project ID (for organizing files)
     * @return array|WP_Error Upload result or WP_Error on failure
     */
    public static function upload_from_files_array( $file_array, $user_id, $project_id = 0 ) {
        if ( ! isset( $file_array['tmp_name'] ) || ! is_uploaded_file( $file_array['tmp_name'] ) ) {
            return new WP_Error( 'invalid_upload', 'Invalid file upload' );
        }

        if ( $file_array['error'] !== UPLOAD_ERR_OK ) {
            return new WP_Error( 'upload_error', 'File upload error: ' . $file_array['error'] );
        }

        // Generate unique object key
        $file_name = sanitize_file_name( $file_array['name'] );
        $file_ext = pathinfo( $file_name, PATHINFO_EXTENSION );
        $file_base = pathinfo( $file_name, PATHINFO_FILENAME );

        // Create organized path: uploads/user_{id}/project_{id}/year/month/filename
        $date_path = date( 'Y/m' );
        $object_key = sprintf(
            'uploads/user_%d/project_%d/%s/%s',
            $user_id,
            $project_id,
            $date_path,
            $file_name
        );

        // Ensure unique filename
        $counter = 1;
        while ( self::object_exists( $object_key ) ) {
            $object_key = sprintf(
                'uploads/user_%d/project_%d/%s/%s-%d.%s',
                $user_id,
                $project_id,
                $date_path,
                $file_base,
                $counter,
                $file_ext
            );
            $counter++;
        }

        $content_type = $file_array['type'] ?: 'application/octet-stream';

        return self::upload_file( $file_array['tmp_name'], $object_key, $content_type );
    }

    /**
     * Delete a file from R2
     *
     * @param string $object_key Object key in R2
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public static function delete_file( $object_key ) {
        $object_key = ltrim( $object_key, '/' );

        $response = self::api_request( 'DELETE', '/' . $object_key );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return true;
    }

    /**
     * Check if an object exists in R2
     *
     * @param string $object_key Object key
     * @return bool True if exists, false otherwise
     */
    public static function object_exists( $object_key ) {
        $object_key = ltrim( $object_key, '/' );

        $response = self::api_request( 'HEAD', '/' . $object_key );

        return ! is_wp_error( $response ) && $response['status'] === 200;
    }

    /**
     * Generate a signed URL for private file access
     *
     * @param string $object_key Object key
     * @param int    $expires_in Expiration time in seconds (default: 1 hour)
     * @return string|WP_Error Signed URL or WP_Error on failure
     */
    public static function generate_signed_url( $object_key, $expires_in = 3600 ) {
        $credentials = self::get_credentials();
        if ( ! $credentials ) {
            return new WP_Error( 'no_credentials', 'R2 credentials not configured' );
        }

        $object_key = ltrim( $object_key, '/' );
        $timestamp = time();
        $expiration = $timestamp + $expires_in;
        $date = gmdate( 'Ymd', $timestamp );
        $datetime = gmdate( 'Ymd\THis\Z', $timestamp );

        // Build canonical request
        $canonical_uri = '/' . PFOB_R2_BUCKET . '/' . $object_key;
        $credential = $credentials['access_key_id'] . '/' . $date . '/auto/s3/aws4_request';

        $canonical_querystring = http_build_query( array(
            'X-Amz-Algorithm' => 'AWS4-HMAC-SHA256',
            'X-Amz-Credential' => $credential,
            'X-Amz-Date' => $datetime,
            'X-Amz-Expires' => $expires_in,
            'X-Amz-SignedHeaders' => 'host',
        ) );

        $canonical_headers = 'host:' . wp_parse_url( PFOB_R2_ENDPOINT, PHP_URL_HOST );

        $canonical_request = "GET\n{$canonical_uri}\n{$canonical_querystring}\n{$canonical_headers}\n\nhost\nUNSIGNED-PAYLOAD";

        // String to sign
        $string_to_sign = "AWS4-HMAC-SHA256\n{$datetime}\n{$date}/auto/s3/aws4_request\n" . hash( 'sha256', $canonical_request );

        // Calculate signature
        $k_date = hash_hmac( 'sha256', $date, 'AWS4' . $credentials['secret_access_key'], true );
        $k_region = hash_hmac( 'sha256', 'auto', $k_date, true );
        $k_service = hash_hmac( 'sha256', 's3', $k_region, true );
        $k_signing = hash_hmac( 'sha256', 'aws4_request', $k_service, true );
        $signature = hash_hmac( 'sha256', $string_to_sign, $k_signing );

        return PFOB_R2_ENDPOINT . $canonical_uri . '?' . $canonical_querystring . '&X-Amz-Signature=' . $signature;
    }

    /**
     * Get file metadata
     *
     * @param string $object_key Object key
     * @return array|WP_Error Metadata array or WP_Error on failure
     */
    public static function get_file_metadata( $object_key ) {
        $object_key = ltrim( $object_key, '/' );

        $response = self::api_request( 'HEAD', '/' . $object_key );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $headers = $response['headers'];

        return array(
            'content_type' => $headers['content-type'] ?? 'application/octet-stream',
            'content_length' => $headers['content-length'] ?? 0,
            'last_modified' => $headers['last-modified'] ?? '',
            'etag' => trim( $headers['etag'] ?? '', '"' ),
        );
    }

    /**
     * List objects with a prefix
     *
     * @param string $prefix Object key prefix
     * @param int    $max_keys Maximum number of keys to return
     * @return array|WP_Error Array of objects or WP_Error on failure
     */
    public static function list_objects( $prefix = '', $max_keys = 1000 ) {
        $query_params = array(
            'list-type' => 2,
            'max-keys' => $max_keys,
        );

        if ( ! empty( $prefix ) ) {
            $query_params['prefix'] = ltrim( $prefix, '/' );
        }

        $path = '/?' . http_build_query( $query_params );

        $response = self::api_request( 'GET', $path );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // Parse XML response
        $xml = simplexml_load_string( $response['body'] );
        if ( $xml === false ) {
            return new WP_Error( 'parse_error', 'Failed to parse R2 response' );
        }

        $objects = array();
        if ( isset( $xml->Contents ) ) {
            foreach ( $xml->Contents as $item ) {
                $objects[] = array(
                    'key' => (string) $item->Key,
                    'size' => (int) $item->Size,
                    'last_modified' => (string) $item->LastModified,
                    'etag' => trim( (string) $item->ETag, '"' ),
                );
            }
        }

        return $objects;
    }

    /**
     * Save R2 credentials
     *
     * @param string $access_key_id R2 Access Key ID
     * @param string $secret_access_key R2 Secret Access Key
     * @return bool Success
     */
    public static function save_credentials( $access_key_id, $secret_access_key ) {
        update_option( 'pfob_r2_access_key_id', sanitize_text_field( $access_key_id ) );
        update_option( 'pfob_r2_secret_access_key', sanitize_text_field( $secret_access_key ) );

        return true;
    }

    /**
     * Check if R2 is configured
     *
     * @return bool True if configured, false otherwise
     */
    public static function is_configured() {
        return (bool) self::get_credentials();
    }

    /**
     * Get storage usage for a user
     *
     * @param int $user_id User ID
     * @return int Total storage used in bytes
     */
    public static function get_user_storage_usage( $user_id ) {
        $prefix = "uploads/user_{$user_id}/";
        $objects = self::list_objects( $prefix );

        if ( is_wp_error( $objects ) ) {
            return 0;
        }

        $total_size = 0;
        foreach ( $objects as $object ) {
            $total_size += $object['size'];
        }

        return $total_size;
    }

    /**
     * Extract object key from full R2 URL
     *
     * @param string $url Full R2 URL
     * @return string Object key
     */
    public static function extract_object_key_from_url( $url ) {
        $bucket_path = '/' . PFOB_R2_BUCKET . '/';
        $pos = strpos( $url, $bucket_path );

        if ( $pos === false ) {
            return '';
        }

        return substr( $url, $pos + strlen( $bucket_path ) );
    }
}
