<?php

namespace BookManager\Frontend;

use BookManager\Database\DatabaseManager;

/**
 * Frontend Display Class
 * Handles displaying book information on the frontend
 */
class FrontendDisplay
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
        
        // Add book meta to content
        add_filter('the_content', [$this, 'addBookMetaToContent']);
        
        // Add custom CSS
        add_action('wp_head', [$this, 'addCustomCSS']);
        
        // Register shortcode
        add_shortcode('book_info', [$this, 'bookInfoShortcode']);
    }

    /**
     * Add book meta information to the content
     */
    public function addBookMetaToContent($content)
    {
        // Only on single book posts
        if (!is_singular('book')) {
            return $content;
        }

        global $post;
        
        $book_meta = $this->getBookMeta($post->ID);
        
        if (empty($book_meta)) {
            return $content;
        }

        $meta_html = $this->renderBookMeta($book_meta);
        
        // Add meta before content
        return $meta_html . $content;
    }

    /**
     * Get all book meta data
     */
    private function getBookMeta($post_id)
    {
        $author_id = get_post_meta($post_id, '_book_author_id', true);
        $publisher_id = get_post_meta($post_id, '_book_publisher_id', true);
        
        $author = $author_id ? $this->db->getAuthor($author_id) : null;
        $publisher = $publisher_id ? $this->db->getPublisher($publisher_id) : null;

        return [
            'author' => $author,
            'translator' => get_post_meta($post_id, '_book_translator', true),
            'publisher' => $publisher,
            'isbn' => get_post_meta($post_id, '_book_isbn', true),
            'edition' => get_post_meta($post_id, '_book_edition', true),
            'price' => get_post_meta($post_id, '_book_price', true),
            'pages' => get_post_meta($post_id, '_book_pages', true),
            'country' => get_post_meta($post_id, '_book_country', true),
            'language' => get_post_meta($post_id, '_book_language', true),
            'publication_date' => get_post_meta($post_id, '_book_publication_date', true),
        ];
    }

    /**
     * Render book meta HTML
     */
    private function renderBookMeta($meta)
    {
        ob_start();
        ?>
        <div class="book-meta-container">
            <div class="book-meta-card">
                <h3 class="book-meta-title"><?php _e('Book Information', 'book-manager'); ?></h3>
                
                <div class="book-meta-grid">
                    <?php if (!empty($meta['author'])): ?>
                    <div class="book-meta-item">
                        <span class="book-meta-label"><?php _e('Author:', 'book-manager'); ?></span>
                        <span class="book-meta-value"><?php echo esc_html($meta['author']->name); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($meta['translator'])): ?>
                    <div class="book-meta-item">
                        <span class="book-meta-label"><?php _e('Translator:', 'book-manager'); ?></span>
                        <span class="book-meta-value"><?php echo esc_html($meta['translator']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($meta['publisher'])): ?>
                    <div class="book-meta-item">
                        <span class="book-meta-label"><?php _e('Publisher:', 'book-manager'); ?></span>
                        <span class="book-meta-value"><?php echo esc_html($meta['publisher']->name); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($meta['isbn'])): ?>
                    <div class="book-meta-item">
                        <span class="book-meta-label"><?php _e('ISBN:', 'book-manager'); ?></span>
                        <span class="book-meta-value"><?php echo esc_html($meta['isbn']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($meta['edition'])): ?>
                    <div class="book-meta-item">
                        <span class="book-meta-label"><?php _e('Edition:', 'book-manager'); ?></span>
                        <span class="book-meta-value"><?php echo esc_html($meta['edition']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($meta['publication_date'])): ?>
                    <div class="book-meta-item">
                        <span class="book-meta-label"><?php _e('Publication Date:', 'book-manager'); ?></span>
                        <span class="book-meta-value"><?php echo esc_html($meta['publication_date']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($meta['pages'])): ?>
                    <div class="book-meta-item">
                        <span class="book-meta-label"><?php _e('Number of Pages:', 'book-manager'); ?></span>
                        <span class="book-meta-value"><?php echo esc_html($meta['pages']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($meta['language'])): ?>
                    <div class="book-meta-item">
                        <span class="book-meta-label"><?php _e('Language:', 'book-manager'); ?></span>
                        <span class="book-meta-value"><?php echo esc_html($meta['language']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($meta['country'])): ?>
                    <div class="book-meta-item">
                        <span class="book-meta-label"><?php _e('Country:', 'book-manager'); ?></span>
                        <span class="book-meta-value"><?php echo esc_html($meta['country']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($meta['price'])): ?>
                    <div class="book-meta-item">
                        <span class="book-meta-label"><?php _e('Price:', 'book-manager'); ?></span>
                        <span class="book-meta-value"><?php echo esc_html($meta['price']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($meta['author']) && !empty($meta['author']->bio)): ?>
                <div class="book-author-bio">
                    <h4><?php _e('About the Author', 'book-manager'); ?></h4>
                    <p><?php echo wp_kses_post(nl2br($meta['author']->bio)); ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($meta['publisher']) && (!empty($meta['publisher']->address) || !empty($meta['publisher']->website))): ?>
                <div class="book-publisher-info">
                    <h4><?php _e('Publisher Information', 'book-manager'); ?></h4>
                    <?php if (!empty($meta['publisher']->address)): ?>
                        <p><strong><?php _e('Address:', 'book-manager'); ?></strong> <?php echo esc_html($meta['publisher']->address); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($meta['publisher']->website)): ?>
                        <p><strong><?php _e('Website:', 'book-manager'); ?></strong> <a href="<?php echo esc_url($meta['publisher']->website); ?>" target="_blank" rel="noopener"><?php echo esc_html($meta['publisher']->website); ?></a></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Add custom CSS for book meta display
     */
    public function addCustomCSS()
    {
        if (!is_singular('book')) {
            return;
        }
        ?>
        <style type="text/css">
            .book-meta-container {
                margin: 30px 0;
                padding: 0;
            }
            
            .book-meta-card {
                background: #f9f9f9;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                padding: 25px;
                margin-bottom: 30px;
            }
            
            .book-meta-title {
                font-size: 24px;
                font-weight: 600;
                margin: 0 0 20px 0;
                padding-bottom: 15px;
                border-bottom: 2px solid #333;
                color: #333;
            }
            
            .book-meta-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 15px;
                margin-bottom: 20px;
            }
            
            .book-meta-item {
                padding: 12px;
                background: #fff;
                border-radius: 5px;
                border-left: 3px solid #0073aa;
            }
            
            .book-meta-label {
                display: block;
                font-weight: 600;
                color: #555;
                margin-bottom: 5px;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .book-meta-value {
                display: block;
                color: #333;
                font-size: 16px;
                line-height: 1.5;
            }
            
            .book-author-bio,
            .book-publisher-info {
                margin-top: 25px;
                padding: 20px;
                background: #fff;
                border-radius: 5px;
                border: 1px solid #e0e0e0;
            }
            
            .book-author-bio h4,
            .book-publisher-info h4 {
                margin: 0 0 15px 0;
                font-size: 18px;
                color: #333;
                border-bottom: 2px solid #0073aa;
                padding-bottom: 10px;
            }
            
            .book-author-bio p,
            .book-publisher-info p {
                margin: 0 0 10px 0;
                line-height: 1.6;
                color: #555;
            }
            
            .book-publisher-info p:last-child {
                margin-bottom: 0;
            }
            
            .book-publisher-info a {
                color: #0073aa;
                text-decoration: none;
            }
            
            .book-publisher-info a:hover {
                text-decoration: underline;
            }
            
            @media (max-width: 768px) {
                .book-meta-grid {
                    grid-template-columns: 1fr;
                }
                
                .book-meta-card {
                    padding: 20px 15px;
                }
                
                .book-meta-title {
                    font-size: 20px;
                }
            }
        </style>
        <?php
    }

    /**
     * Shortcode to display book info anywhere
     * Usage: [book_info id="123"]
     */
    public function bookInfoShortcode($atts)
    {
        $atts = shortcode_atts([
            'id' => get_the_ID(),
        ], $atts);

        $post_id = intval($atts['id']);
        
        if (get_post_type($post_id) !== 'book') {
            return '';
        }

        $book_meta = $this->getBookMeta($post_id);
        
        if (empty($book_meta)) {
            return '';
        }

        return $this->renderBookMeta($book_meta);
    }
}