<?php
namespace BookManager\PostTypes;

use BookManager\Database\DatabaseManager;

/**
 * Book Custom Post Type
 */
class BookPostType
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
        add_action('init', [$this, 'register']);
        
        // Add custom columns to the Books admin list
        add_filter('manage_book_posts_columns', [$this, 'addCustomColumns']);
        add_action('manage_book_posts_custom_column', [$this, 'renderCustomColumns'], 10, 2);
        add_filter('manage_edit-book_sortable_columns', [$this, 'makeSortableColumns']);

        add_action('pre_get_posts', [$this, 'filterBooksByCurrentUser']);


        add_action('after_setup_theme', function () {
            add_post_type_support('book', 'thumbnail');
        });
    }

    /**
     * Show only books added by current user in admin list
     */
    public function filterBooksByCurrentUser($query)
    {
        // Only in admin
        if ( ! is_admin()) {
            return;
        }

        // Only main query
        if ( ! $query->is_main_query() ) {
            return;
        }

        // Only Book post type
        if ( $query->get( 'post_type' ) !== 'book' ) {
            return;
        }
                
        // Allow admins to see all books
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }

        $current_user_id = get_current_user_id();

        $query->set( 'meta_query', [
            [
                'key'     => '_book_added_by',
                'value'   => $current_user_id,
                'compare' => '=',
                'type'    => 'NUMERIC',
            ],
        ] );
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
            'supports' => ['title', 'thumbnail', 'excerpt'],
            'show_in_rest' => true,
        ];

        register_post_type('book', $args);
    }

    /**
     * Add custom columns to Books list
     */
    public function addCustomColumns($columns)
    {
        // Remove the date column temporarily
        $date = $columns['date'];
        unset($columns['date']);

        // Add custom columns
        $columns['book_cover'] = __('Cover', 'book-manager');
        $columns['book_author'] = __('Author', 'book-manager');
        $columns['book_publisher'] = __('Publisher', 'book-manager');
        $columns['book_isbn'] = __('ISBN', 'book-manager');
        $columns['book_price'] = __('Price', 'book-manager');
        $columns['book_stock'] = __('Stock', 'book-manager');
        $columns['book_added_by'] = __('Added By', 'book-manager');
        
        // Add date back at the end
        $columns['date'] = $date;

        return $columns;
    }

    /**
     * Render custom column content
     */
    public function renderCustomColumns($column, $post_id)
    {
        switch ($column) {
            case 'book_cover':
                if (has_post_thumbnail($post_id)) {
                    echo get_the_post_thumbnail($post_id, [50, 70]);
                } else {
                    echo '<span style="color: #999;">—</span>';
                }
                break;

            case 'book_author':
                $author_id = get_post_meta($post_id, '_book_author_id', true);
                if ($author_id) {
                    $author = $this->db->getAuthor($author_id);
                    if ($author) {
                        echo '<strong>' . esc_html($author->name) . '</strong>';
                    } else {
                        echo '<span style="color: #999;">—</span>';
                    }
                } else {
                    echo '<span style="color: #999;">—</span>';
                }
                break;

            case 'book_publisher':
                $publisher_id = get_post_meta($post_id, '_book_publisher_id', true);
                if ($publisher_id) {
                    $publisher = $this->db->getPublisher($publisher_id);
                    if ($publisher) {
                        echo esc_html($publisher->name);
                    } else {
                        echo '<span style="color: #999;">—</span>';
                    }
                } else {
                    echo '<span style="color: #999;">—</span>';
                }
                break;

            case 'book_isbn':
                $isbn = get_post_meta($post_id, '_book_isbn', true);
                echo $isbn ? '<code>' . esc_html($isbn) . '</code>' : '<span style="color: #999;">—</span>';
                break;

            case 'book_price':
                $price = get_post_meta($post_id, '_book_price', true);
                if ($price) {
                    echo '<strong>$' . number_format((float)$price, 2) . '</strong>';
                } else {
                    echo '<span style="color: #999;">—</span>';
                }
                break;

            case 'book_stock':
                $stock = get_post_meta($post_id, '_book_stock_quantity', true);
                if ($stock !== '') {
                    $stock_int = intval($stock);
                    $color = $stock_int > 10 ? 'green' : ($stock_int > 0 ? 'orange' : 'red');
                    echo '<span style="color: ' . $color . '; font-weight: bold;">' . esc_html($stock) . '</span>';
                } else {
                    echo '<span style="color: #999;">—</span>';
                }
                break;

            case 'book_added_by':
                $user_id = get_post_meta($post_id, '_book_added_by', true);
                if ($user_id) {
                    $user = get_userdata($user_id);
                    if ($user) {
                        echo esc_html($user->display_name);
                    } else {
                        echo '<span style="color: #999;">Unknown</span>';
                    }
                } else {
                    echo '<span style="color: #999;">—</span>';
                }
                break;
        }
    }

    /**
     * Make certain columns sortable
     */
    public function makeSortableColumns($columns)
    {
        $columns['book_author'] = 'book_author';
        $columns['book_publisher'] = 'book_publisher';
        $columns['book_price'] = 'book_price';
        $columns['book_stock'] = 'book_stock';
        
        return $columns;
    }
}