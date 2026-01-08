<?php

namespace BookManager\Admin;

use BookManager\Database\DatabaseManager;

/**
 * Meta Boxes for Book Post Type
 */
class MetaBoxes
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
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('save_post_book', [$this, 'saveMetaBoxData'], 10, 2);
    }

    /**
     * Add meta boxes
     */
    public function addMetaBoxes()
    {
        add_meta_box(
            'book_details',
            __('Book Details', 'book-manager'),
            [$this, 'renderBookDetailsMetaBox'],
            'book',
            'normal',
            'high'
        );
    }

    /**
     * Render book details meta box
     */
    public function renderBookDetailsMetaBox($post)
    {
        wp_nonce_field('book_details_nonce', 'book_details_nonce_field');

        // Get saved values
        $author_id = get_post_meta($post->ID, '_book_author_id', true);
        $translator = get_post_meta($post->ID, '_book_translator', true);
        $publisher_id = get_post_meta($post->ID, '_book_publisher_id', true);
        $isbn = get_post_meta($post->ID, '_book_isbn', true);
        $edition = get_post_meta($post->ID, '_book_edition', true);
        $price = get_post_meta($post->ID, '_book_price', true);
        $pages = get_post_meta($post->ID, '_book_pages', true);
        $country = get_post_meta($post->ID, '_book_country', true);
        $language = get_post_meta($post->ID, '_book_language', true);
        $publication_date = get_post_meta($post->ID, '_book_publication_date', true);

        // Get authors and publishers
        $authors = $this->db->getAuthors();
        $publishers = $this->db->getPublishers();

        ?>
        <style>
            .book-meta-field { margin-bottom: 20px; }
            .book-meta-field label { display: block; font-weight: 600; margin-bottom: 5px; }
            .book-meta-field input[type="text"],
            .book-meta-field input[type="number"],
            .book-meta-field input[type="date"],
            .book-meta-field select,
            .book-meta-field textarea { width: 100%; max-width: 500px; }
            .book-meta-field textarea { height: 80px; }
            .book-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
            @media (max-width: 782px) { .book-meta-grid { grid-template-columns: 1fr; } }
        </style>

        <div class="book-meta-grid">
            <div>
                <!-- Author -->
                <div class="book-meta-field">
                    <label for="book_author_id"><?php _e('Author', 'book-manager'); ?> <span style="color:red;">*</span></label>
                    <select id="book_author_id" name="book_author_id" required>
                        <option value=""><?php _e('Select Author', 'book-manager'); ?></option>
                        <?php foreach ($authors as $author): ?>
                            <option value="<?php echo esc_attr($author->id); ?>" <?php selected($author_id, $author->id); ?>>
                                <?php echo esc_html($author->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">
                        <a href="<?php echo admin_url('admin.php?page=book-authors'); ?>" target="_blank">
                            <?php _e('Manage Authors', 'book-manager'); ?>
                        </a>
                    </p>
                </div>

                <!-- Translator -->
                <div class="book-meta-field">
                    <label for="book_translator"><?php _e('Translator (Optional)', 'book-manager'); ?></label>
                    <input type="text" id="book_translator" name="book_translator" value="<?php echo esc_attr($translator); ?>" />
                </div>

                <!-- Publisher -->
                <div class="book-meta-field">
                    <label for="book_publisher_id"><?php _e('Publisher', 'book-manager'); ?> <span style="color:red;">*</span></label>
                    <select id="book_publisher_id" name="book_publisher_id" required>
                        <option value=""><?php _e('Select Publisher', 'book-manager'); ?></option>
                        <?php foreach ($publishers as $publisher): ?>
                            <option value="<?php echo esc_attr($publisher->id); ?>" <?php selected($publisher_id, $publisher->id); ?>>
                                <?php echo esc_html($publisher->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">
                        <a href="<?php echo admin_url('admin.php?page=book-publishers'); ?>" target="_blank">
                            <?php _e('Manage Publishers', 'book-manager'); ?>
                        </a>
                    </p>
                </div>

                <!-- ISBN -->
                <div class="book-meta-field">
                    <label for="book_isbn"><?php _e('ISBN', 'book-manager'); ?></label>
                    <input type="text" id="book_isbn" name="book_isbn" value="<?php echo esc_attr($isbn); ?>" />
                </div>

                <!-- Edition -->
                <div class="book-meta-field">
                    <label for="book_edition"><?php _e('Edition', 'book-manager'); ?></label>
                    <input type="text" id="book_edition" name="book_edition" value="<?php echo esc_attr($edition); ?>" />
                </div>

                <!-- Price -->
                <div class="book-meta-field">
                    <label for="book_price"><?php _e('Price', 'book-manager'); ?></label>
                    <input type="number" id="book_price" name="book_price" value="<?php echo esc_attr($price); ?>" step="0.01" min="0" />
                </div>
            </div>

            <div>
                <!-- Number of Pages -->
                <div class="book-meta-field">
                    <label for="book_pages"><?php _e('Number of Pages', 'book-manager'); ?></label>
                    <input type="number" id="book_pages" name="book_pages" value="<?php echo esc_attr($pages); ?>" min="1" />
                </div>

                <!-- Country -->
                <div class="book-meta-field">
                    <label for="book_country"><?php _e('Country', 'book-manager'); ?></label>
                    <input type="text" id="book_country" name="book_country" value="<?php echo esc_attr($country); ?>" />
                </div>

                <!-- Language -->
                <div class="book-meta-field">
                    <label for="book_language"><?php _e('Language', 'book-manager'); ?></label>
                    <input type="text" id="book_language" name="book_language" value="<?php echo esc_attr($language); ?>" />
                </div>

                <!-- Publication Date -->
                <div class="book-meta-field">
                    <label for="book_publication_date"><?php _e('Publication Date', 'book-manager'); ?></label>
                    <input type="text" id="book_publication_date" name="book_publication_date" 
                           value="<?php echo esc_attr($publication_date); ?>" 
                           placeholder="<?php _e('e.g., June 2022', 'book-manager'); ?>" />
                    <p class="description"><?php _e('Enter publication date in any format (e.g., "June 2022", "2022", etc.)', 'book-manager'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Save meta box data
     */
    public function saveMetaBoxData($post_id, $post)
    {
        // Verify nonce
        if (!isset($_POST['book_details_nonce_field']) || 
            !wp_verify_nonce($_POST['book_details_nonce_field'], 'book_details_nonce')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save fields
        $fields = [
            'book_author_id',
            'book_translator',
            'book_publisher_id',
            'book_isbn',
            'book_edition',
            'book_price',
            'book_pages',
            'book_country',
            'book_language',
            'book_publication_date'
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }

        // Save the user ID who added the book
        update_post_meta($post_id, '_book_added_by', get_current_user_id());
    }
}