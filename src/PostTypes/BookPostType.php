<?php

namespace BookManager\PostTypes;

/**
 * Book Custom Post Type
 */
class BookPostType
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('init', [$this, 'register']);
    }

    /**
     * Register the Book custom post type
     */
    public function register()
    {
        $labels = [
            'name' => __('Books', 'book-manager'),
            'singular_name' => __('Book', 'book-manager'),
            'menu_name' => __('Books', 'book-manager'),
            'name_admin_bar' => __('Book', 'book-manager'),
            'add_new' => __('Add New', 'book-manager'),
            'add_new_item' => __('Add New Book', 'book-manager'),
            'new_item' => __('New Book', 'book-manager'),
            'edit_item' => __('Edit Book', 'book-manager'),
            'view_item' => __('View Book', 'book-manager'),
            'all_items' => __('All Books', 'book-manager'),
            'search_items' => __('Search Books', 'book-manager'),
            'parent_item_colon' => __('Parent Books:', 'book-manager'),
            'not_found' => __('No books found.', 'book-manager'),
            'not_found_in_trash' => __('No books found in Trash.', 'book-manager'),
            'featured_image' => __('Book Cover', 'book-manager'),
            'set_featured_image' => __('Set book cover', 'book-manager'),
            'remove_featured_image' => __('Remove book cover', 'book-manager'),
            'use_featured_image' => __('Use as book cover', 'book-manager'),
            'archives' => __('Book Archives', 'book-manager'),
            'insert_into_item' => __('Insert into book', 'book-manager'),
            'uploaded_to_this_item' => __('Uploaded to this book', 'book-manager'),
            'filter_items_list' => __('Filter books list', 'book-manager'),
            'items_list_navigation' => __('Books list navigation', 'book-manager'),
            'items_list' => __('Books list', 'book-manager'),
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'books'],
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-book',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'show_in_rest' => true,
        ];

        register_post_type('book', $args);
    }
}