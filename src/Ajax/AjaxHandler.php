<?php

namespace BookManager\Ajax;

use BookManager\Database\DatabaseManager;

/**
 * AJAX Handler Class
 * Handles AJAX requests for dynamic functionality
 */
class AjaxHandler
{
    private static $instance = null;
    private $db;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        
        // Register AJAX actions
        add_action('wp_ajax_book_search_authors', [$this, 'searchAuthors']);
        add_action('wp_ajax_book_search_publishers', [$this, 'searchPublishers']);
    }

    /**
     * Search authors via AJAX
     */
    public function searchAuthors()
    {
        check_ajax_referer('book_ajax_nonce', 'nonce');
        
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $authors = $this->db->getAuthors();
        
        if ($search) {
            $authors = array_filter($authors, function($author) use ($search) {
                return stripos($author->name, $search) !== false;
            });
        }
        
        wp_send_json_success($authors);
    }

    /**
     * Search publishers via AJAX
     */
    public function searchPublishers()
    {
        check_ajax_referer('book_ajax_nonce', 'nonce');
        
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $publishers = $this->db->getPublishers();
        
        if ($search) {
            $publishers = array_filter($publishers, function($publisher) use ($search) {
                return stripos($publisher->name, $search) !== false;
            });
        }
        
        wp_send_json_success($publishers);
    }
}