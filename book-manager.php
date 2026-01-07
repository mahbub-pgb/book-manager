<?php
/**
 * Plugin Name: Book Manager
 * Plugin URI: https://example.com/book-manager
 * Description: A comprehensive book management system with authors and publishers
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: book-manager
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

namespace BookManager;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BOOK_MANAGER_VERSION', '1.0.0');
define('BOOK_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BOOK_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BOOK_MANAGER_PLUGIN_FILE', __FILE__);

// Require Composer autoloader
require_once BOOK_MANAGER_PLUGIN_DIR . 'vendor/autoload.php';

/**
 * Main plugin class
 */
class BookManagerPlugin
{
    /**
     * Plugin instance
     *
     * @var BookManagerPlugin
     */
    private static $instance = null;

    /**
     * Get plugin instance
     *
     * @return BookManagerPlugin
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * Initialize plugin
     */
    private function init()
    {
        // Register activation hook
        register_activation_hook(BOOK_MANAGER_PLUGIN_FILE, [$this, 'activate']);
        
        // Register deactivation hook
        register_deactivation_hook(BOOK_MANAGER_PLUGIN_FILE, [$this, 'deactivate']);

        // Initialize components
        add_action('plugins_loaded', [$this, 'loadComponents']);
    }

    /**
     * Load all plugin components
     */
    public function loadComponents()
    {
        // Load text domain
        load_plugin_textdomain('book-manager', false, dirname(plugin_basename(BOOK_MANAGER_PLUGIN_FILE)) . '/languages');

        // Initialize database
        Database\DatabaseManager::getInstance();

        // Initialize custom post type
        PostTypes\BookPostType::getInstance();

        // Initialize meta boxes
        Admin\MetaBoxes::getInstance();

        // Initialize admin pages
        Admin\AdminPages::getInstance();

        // Initialize AJAX handlers
        Ajax\AjaxHandler::getInstance();
    }

    /**
     * Plugin activation
     */
    public function activate()
    {
        // Create custom tables
        Database\DatabaseManager::getInstance()->createTables();

        // Register post type for rewrite rules
        PostTypes\BookPostType::getInstance()->register();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate()
    {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize the plugin
BookManagerPlugin::getInstance();