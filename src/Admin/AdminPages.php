<?php

namespace BookManager\Admin;

use BookManager\Database\DatabaseManager;

/**
 * Admin Pages for Authors and Publishers
 */
class AdminPages
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
        add_action('admin_menu', [$this, 'addMenuPages']);
        add_action('pre_get_posts', [$this, 'book_manager_filter_books_by_user']);
        add_action('admin_post_add_author', [$this, 'handleAddAuthor']);
        add_action('admin_post_edit_author', [$this, 'handleEditAuthor']);
        add_action('admin_post_delete_author', [$this, 'handleDeleteAuthor']);
        add_action('admin_post_add_publisher', [$this, 'handleAddPublisher']);
        add_action('admin_post_edit_publisher', [$this, 'handleEditPublisher']);
        add_action('admin_post_delete_publisher', [$this, 'handleDeletePublisher']);
        
        // Enqueue DataTables scripts and styles
        add_action('admin_enqueue_scripts', [$this, 'enqueueDataTablesAssets']);
    }

    /**
     * Enqueue DataTables CSS and JS
     */
    public function enqueueDataTablesAssets($hook)
    {
        // Only load on our specific admin pages
        if ($hook !== 'book_page_book-authors' && $hook !== 'book_page_book-publishers') {
            return;
        }

        // Enqueue DataTables CSS from CDN
        wp_enqueue_style(
            'datatables-css',
            'https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css',
            [],
            '1.13.7'
        );

        // Enqueue DataTables responsive CSS
        wp_enqueue_style(
            'datatables-responsive-css',
            'https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css',
            ['datatables-css'],
            '2.5.0'
        );

        wp_enqueue_style(
            'style',
            plugins_url(
                'assets/css/style.css',
                __FILE__
            ),
            [ 'datatables-css' ],
            '2.5.0'
        );


        // Enqueue DataTables JS from CDN
        wp_enqueue_script(
            'datatables-js',
            'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js',
            ['jquery'],
            '1.13.7',
            true
        );

        // Enqueue DataTables responsive JS
        wp_enqueue_script(
            'datatables-responsive-js',
            'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js',
            ['datatables-js'],
            '2.5.0',
            true
        );

        // Enqueue custom initialization script
        wp_enqueue_script(
            'book-manager-datatables',
            plugins_url('assets/js/admin-datatables.js', dirname(__FILE__, 2)),
            ['datatables-js'],
            '1.0.0',
            true
        );

        // Add custom CSS for better WordPress integration
        wp_add_inline_style('datatables-css', '
            .dataTables_wrapper {
                margin-top: 20px;
            }
            .dataTables_filter input {
                border: 1px solid #8c8f94;
                border-radius: 4px;
                padding: 5px 10px;
            }
            .dataTables_length select {
                border: 1px solid #8c8f94;
                border-radius: 4px;
                padding: 3px 8px;
            }
            table.dataTable thead th {
                background: #f0f0f1;
                font-weight: 600;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 5px 10px;
                margin: 0 2px;
                border: 1px solid #8c8f94;
                border-radius: 3px;
                background: #fff;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button.current {
                background: #2271b1;
                color: #fff !important;
                border-color: #2271b1;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                background: #f0f0f1;
                border-color: #8c8f94;
            }
        ');
    }

    function book_manager_filter_books_by_user( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        if ( $query->get( 'post_type' ) !== 'book' ) {
            return;
        }

        if ( current_user_can( 'manage_options' ) ) {
            return;
        }

        $query->set( 'meta_query', [
            [
                'key'   => '_book_added_by',
                'value' => get_current_user_id(),
            ]
        ] );
    }

    /**
     * Add admin menu pages
     */
    public function addMenuPages()
    {
        add_submenu_page(
            'edit.php?post_type=book',
            __('Authors', 'book-manager'),
            __('Authors', 'book-manager'),
            'manage_options',
            'book-authors',
            [$this, 'renderAuthorsPage']
        );

        add_submenu_page(
            'edit.php?post_type=book',
            __('Publishers', 'book-manager'),
            __('Publishers', 'book-manager'),
            'manage_options',
            'book-publishers',
            [$this, 'renderPublishersPage']
        );
    }

    /**
     * Render authors page
     */
    public function renderAuthorsPage()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        $author_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($action === 'edit' && $author_id) {
            $this->renderEditAuthorForm($author_id);
        } elseif ($action === 'add') {
            $this->renderAddAuthorForm();
        } else {
            $this->renderAuthorsListTable();
        }
    }

    /**
     * Render publishers page
     */
    public function renderPublishersPage()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        $publisher_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($action === 'edit' && $publisher_id) {
            $this->renderEditPublisherForm($publisher_id);
        } elseif ($action === 'add') {
            $this->renderAddPublisherForm();
        } else {
            $this->renderPublishersListTable();
        }
    }

    /**
     * Render authors list table
     */
    private function renderAuthorsListTable()
    {
        $authors = $this->db->getAuthors();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('Authors', 'book-manager'); ?></h1>
            <a href="<?php echo admin_url('admin.php?page=book-authors&action=add'); ?>" class="page-title-action">
                <?php _e('Add New', 'book-manager'); ?>
            </a>
            <hr class="wp-header-end">

            <?php if (isset($_GET['message'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html($this->getMessageText($_GET['message'])); ?></p>
                </div>
            <?php endif; ?>

            <table id="authors-table" class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'book-manager'); ?></th>
                        <th><?php _e('Name', 'book-manager'); ?></th>
                        <th><?php _e('Bio', 'book-manager'); ?></th>
                        <th data-orderable="false"><?php _e('Actions', 'book-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($authors)): ?>
                        <tr>
                            <td colspan="4"><?php _e('No authors found.', 'book-manager'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($authors as $author): ?>
                            <tr>
                                <td><?php echo esc_html($author->id); ?></td>
                                <td><strong><?php echo esc_html($author->name); ?></strong></td>
                                <td><?php echo esc_html(wp_trim_words($author->bio, 15)); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=book-authors&action=edit&id=' . $author->id); ?>">
                                        <?php _e('Edit', 'book-manager'); ?>
                                    </a> |
                                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=delete_author&id=' . $author->id), 'delete_author_' . $author->id); ?>" 
                                       onclick="return confirm('<?php _e('Are you sure you want to delete this author?', 'book-manager'); ?>');" 
                                       style="color: #b32d2e;">
                                        <?php _e('Delete', 'book-manager'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render add author form
     */
    private function renderAddAuthorForm()
    {
        ?>
        <div class="wrap">
            <h1><?php _e('Add New Author', 'book-manager'); ?></h1>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('add_author_nonce'); ?>
                <input type="hidden" name="action" value="add_author">
                <table class="form-table">
                    <tr>
                        <th><label for="name"><?php _e('Name', 'book-manager'); ?> <span style="color:red;">*</span></label></th>
                        <td><input type="text" id="name" name="name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="bio"><?php _e('Bio', 'book-manager'); ?></label></th>
                        <td><textarea id="bio" name="bio" rows="5" class="large-text"></textarea></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button button-primary" value="<?php _e('Add Author', 'book-manager'); ?>">
                    <a href="<?php echo admin_url('admin.php?page=book-authors'); ?>" class="button">
                        <?php _e('Cancel', 'book-manager'); ?>
                    </a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Render edit author form
     */
    private function renderEditAuthorForm($author_id)
    {
        $author = $this->db->getAuthor($author_id);
        if (!$author) {
            wp_die(__('Author not found.', 'book-manager'));
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Edit Author', 'book-manager'); ?></h1>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('edit_author_nonce'); ?>
                <input type="hidden" name="action" value="edit_author">
                <input type="hidden" name="id" value="<?php echo esc_attr($author->id); ?>">
                <table class="form-table">
                    <tr>
                        <th><label for="name"><?php _e('Name', 'book-manager'); ?> <span style="color:red;">*</span></label></th>
                        <td><input type="text" id="name" name="name" class="regular-text" value="<?php echo esc_attr($author->name); ?>" required></td>
                    </tr>
                    <tr>
                        <th><label for="bio"><?php _e('Bio', 'book-manager'); ?></label></th>
                        <td><textarea id="bio" name="bio" rows="5" class="large-text"><?php echo esc_textarea($author->bio); ?></textarea></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button button-primary" value="<?php _e('Update Author', 'book-manager'); ?>">
                    <a href="<?php echo admin_url('admin.php?page=book-authors'); ?>" class="button">
                        <?php _e('Cancel', 'book-manager'); ?>
                    </a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Render publishers list table
     */
    private function renderPublishersListTable()
    {
        $publishers = $this->db->getPublishers();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('Publishers', 'book-manager'); ?></h1>
            <a href="<?php echo admin_url('admin.php?page=book-publishers&action=add'); ?>" class="page-title-action">
                <?php _e('Add New', 'book-manager'); ?>
            </a>
            <hr class="wp-header-end">

            <?php if (isset($_GET['message'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html($this->getMessageText($_GET['message'])); ?></p>
                </div>
            <?php endif; ?>

            <table id="publishers-table" class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'book-manager'); ?></th>
                        <th><?php _e('Name', 'book-manager'); ?></th>
                        <th><?php _e('Address', 'book-manager'); ?></th>
                        <th><?php _e('Website', 'book-manager'); ?></th>
                        <th data-orderable="false"><?php _e('Actions', 'book-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($publishers)): ?>
                        <tr>
                            <td colspan="5"><?php _e('No publishers found.', 'book-manager'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($publishers as $publisher): ?>
                            <tr>
                                <td><?php echo esc_html($publisher->id); ?></td>
                                <td><strong><?php echo esc_html($publisher->name); ?></strong></td>
                                <td><?php echo esc_html(wp_trim_words($publisher->address, 10)); ?></td>
                                <td><?php echo $publisher->website ? '<a href="' . esc_url($publisher->website) . '" target="_blank">' . esc_html($publisher->website) . '</a>' : '—'; ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=book-publishers&action=edit&id=' . $publisher->id); ?>">
                                        <?php _e('Edit', 'book-manager'); ?>
                                    </a> |
                                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=delete_publisher&id=' . $publisher->id), 'delete_publisher_' . $publisher->id); ?>" 
                                       onclick="return confirm('<?php _e('Are you sure you want to delete this publisher?', 'book-manager'); ?>');" 
                                       style="color: #b32d2e;">
                                        <?php _e('Delete', 'book-manager'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render add publisher form
     */
    private function renderAddPublisherForm()
    {
        ?>
        <div class="wrap">
            <h1><?php _e('Add New Publisher', 'book-manager'); ?></h1>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('add_publisher_nonce'); ?>
                <input type="hidden" name="action" value="add_publisher">
                <table class="form-table">
                    <tr>
                        <th><label for="name"><?php _e('Name', 'book-manager'); ?> <span style="color:red;">*</span></label></th>
                        <td><input type="text" id="name" name="name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="address"><?php _e('Address', 'book-manager'); ?></label></th>
                        <td><textarea id="address" name="address" rows="3" class="large-text"></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="website"><?php _e('Website', 'book-manager'); ?></label></th>
                        <td><input type="url" id="website" name="website" class="regular-text" placeholder="https://"></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button button-primary" value="<?php _e('Add Publisher', 'book-manager'); ?>">
                    <a href="<?php echo admin_url('admin.php?page=book-publishers'); ?>" class="button">
                        <?php _e('Cancel', 'book-manager'); ?>
                    </a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Render edit publisher form
     */
    private function renderEditPublisherForm($publisher_id)
    {
        $publisher = $this->db->getPublisher($publisher_id);
        if (!$publisher) {
            wp_die(__('Publisher not found.', 'book-manager'));
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Edit Publisher', 'book-manager'); ?></h1>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('edit_publisher_nonce'); ?>
                <input type="hidden" name="action" value="edit_publisher">
                <input type="hidden" name="id" value="<?php echo esc_attr($publisher->id); ?>">
                <table class="form-table">
                    <tr>
                        <th><label for="name"><?php _e('Name', 'book-manager'); ?> <span style="color:red;">*</span></label></th>
                        <td><input type="text" id="name" name="name" class="regular-text" value="<?php echo esc_attr($publisher->name); ?>" required></td>
                    </tr>
                    <tr>
                        <th><label for="address"><?php _e('Address', 'book-manager'); ?></label></th>
                        <td><textarea id="address" name="address" rows="3" class="large-text"><?php echo esc_textarea($publisher->address); ?></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="website"><?php _e('Website', 'book-manager'); ?></label></th>
                        <td><input type="url" id="website" name="website" class="regular-text" value="<?php echo esc_attr($publisher->website); ?>" placeholder="https://"></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button button-primary" value="<?php _e('Update Publisher', 'book-manager'); ?>">
                    <a href="<?php echo admin_url('admin.php?page=book-publishers'); ?>" class="button">
                        <?php _e('Cancel', 'book-manager'); ?>
                    </a>
                </p>
            </form>
        </div>
        <?php
    }

    // Handler methods for form submissions
    public function handleAddAuthor()
    {
        check_admin_referer('add_author_nonce');
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'book-manager'));
        }

        $name = sanitize_text_field($_POST['name']);
        $bio = sanitize_textarea_field($_POST['bio']);

        $this->db->insertAuthor($name, $bio);
        wp_redirect(admin_url('admin.php?page=book-authors&message=added'));
        exit;
    }

    public function handleEditAuthor()
    {
        check_admin_referer('edit_author_nonce');
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'book-manager'));
        }

        $id = intval($_POST['id']);
        $name = sanitize_text_field($_POST['name']);
        $bio = sanitize_textarea_field($_POST['bio']);

        $this->db->updateAuthor($id, $name, $bio);
        wp_redirect(admin_url('admin.php?page=book-authors&message=updated'));
        exit;
    }

    public function handleDeleteAuthor()
    {
        $id = intval($_GET['id']);
        check_admin_referer('delete_author_' . $id);
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'book-manager'));
        }

        $this->db->deleteAuthor($id);
        wp_redirect(admin_url('admin.php?page=book-authors&message=deleted'));
        exit;
    }

    public function handleAddPublisher()
    {
        check_admin_referer('add_publisher_nonce');
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'book-manager'));
        }

        $name = sanitize_text_field($_POST['name']);
        $address = sanitize_textarea_field($_POST['address']);
        $website = esc_url_raw($_POST['website']);

        $this->db->insertPublisher($name, $address, $website);
        wp_redirect(admin_url('admin.php?page=book-publishers&message=added'));
        exit;
    }

    public function handleEditPublisher()
    {
        check_admin_referer('edit_publisher_nonce');
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'book-manager'));
        }

        $id = intval($_POST['id']);
        $name = sanitize_text_field($_POST['name']);
        $address = sanitize_textarea_field($_POST['address']);
        $website = esc_url_raw($_POST['website']);

        $this->db->updatePublisher($id, $name, $address, $website);
        wp_redirect(admin_url('admin.php?page=book-publishers&message=updated'));
        exit;
    }

    public function handleDeletePublisher()
    {
        $id = intval($_GET['id']);
        check_admin_referer('delete_publisher_' . $id);
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'book-manager'));
        }

        $this->db->deletePublisher($id);
        wp_redirect(admin_url('admin.php?page=book-publishers&message=deleted'));
        exit;
    }

    private function getMessageText($message)
    {
        $messages = [
            'added' => __('Item added successfully.', 'book-manager'),
            'updated' => __('Item updated successfully.', 'book-manager'),
            'deleted' => __('Item deleted successfully.', 'book-manager'),
        ];
        return isset($messages[$message]) ? $messages[$message] : '';
    }
}