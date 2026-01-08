<?php

namespace BookManager\Database;

/**
 * Database Manager Class
 * Handles creation and management of custom database tables
 */
class DatabaseManager
{
    private static $instance = null;
    private $wpdb;
    private $authorsTable;
    private $publishersTable;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->authorsTable = $wpdb->prefix . 'book_authors';
        $this->publishersTable = $wpdb->prefix . 'book_publishers';
    }

    /**
     * Create custom database tables
     */
    public function createTables()
    {
        $charset_collate = $this->wpdb->get_charset_collate();

        // Authors table
        $authors_sql = "CREATE TABLE IF NOT EXISTS {$this->authorsTable} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            bio text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            image_url varchar(500),
            PRIMARY KEY (id),
            KEY name (name)
        ) {$charset_collate};";

        // Publishers table
        $publishers_sql = "CREATE TABLE IF NOT EXISTS {$this->publishersTable} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            address text,
            website varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY name (name)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($authors_sql);
        dbDelta($publishers_sql);
    }

    /**
     * Get all authors
     */
    public function getAuthors()
    {
        return $this->wpdb->get_results(
            "SELECT * FROM {$this->authorsTable} ORDER BY name ASC"
        );
    }

    /**
     * Get author by ID
     */
    public function getAuthor($id)
    {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->authorsTable} WHERE id = %d",
                $id
            )
        );
    }

    /**
     * Insert author
     */
    public function insertAuthor($name, $bio, $image_url = '') {
        global $wpdb;
        return $wpdb->insert(
            $wpdb->prefix . 'book_authors',
            [
                'name' => $name,
                'bio' => $bio,
                'image_url' => $image_url
            ],
            ['%s', '%s', '%s']
        );
    }

    /**
     * Update author
     */
    public function updateAuthor($id, $name, $bio = '', $image_url = '') {
        global $wpdb;
        return $wpdb->update(
            $wpdb->prefix . 'book_authors',
            [
                'name' => $name,
                'bio' => $bio,
                'image_url' => $image_url
            ],
            ['id' => $id],
            ['%s', '%s', '%s'],
            ['%d']
        );
    }

    /**
     * Delete author
     */
    public function deleteAuthor($id)
    {
        return $this->wpdb->delete(
            $this->authorsTable,
            ['id' => $id],
            ['%d']
        );
    }

    /**
     * Get all publishers
     */
    public function getPublishers()
    {
        return $this->wpdb->get_results(
            "SELECT * FROM {$this->publishersTable} ORDER BY name ASC"
        );
    }

    /**
     * Get publisher by ID
     */
    public function getPublisher($id)
    {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->publishersTable} WHERE id = %d",
                $id
            )
        );
    }

    /**
     * Insert publisher
     */
    public function insertPublisher($name, $address = '', $website = '')
    {
        $result = $this->wpdb->insert(
            $this->publishersTable,
            [
                'name' => $name,
                'address' => $address,
                'website' => $website
            ],
            ['%s', '%s', '%s']
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Update publisher
     */
    public function updatePublisher($id, $name, $address = '', $website = '')
    {
        return $this->wpdb->update(
            $this->publishersTable,
            [
                'name' => $name,
                'address' => $address,
                'website' => $website
            ],
            ['id' => $id],
            ['%s', '%s', '%s'],
            ['%d']
        );
    }

    /**
     * Delete publisher
     */
    public function deletePublisher($id)
    {
        return $this->wpdb->delete(
            $this->publishersTable,
            ['id' => $id],
            ['%d']
        );
    }

    /**
     * Get table names
     */
    public function getAuthorsTable()
    {
        return $this->authorsTable;
    }

    public function getPublishersTable()
    {
        return $this->publishersTable;
    }
}