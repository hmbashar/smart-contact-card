<?php
/**
 * Configuration class for Smart Contact Card.
 *
 * This class handles the initialization and configuration of the Smart Contact Card.
 * It ensures compatibility with the required Elementor version and manages the loading of 
 * required assets and functionalities.
 *
 * @package Smartcc\Elementor
 * @since 1.0.0
 */
namespace Smartcc\Elementor;

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly



/**
 * Class Configuration
 *
 * This class handles the initialization and configuration of the Smart Contact Card.
 * It ensures compatibility with the required Elementor version and manages the loading of 
 * required assets and functionalities.
 * 
 * @package Smartcc\Elementor
 * @since 1.0.0
 */
class Config
{


    protected $functions;


    /**
     * plugin Version
     */

    public $version = SMARTCC_VERSION;

    /**
     * Minimum Elementor Version
     */
    const MINIMUM_ELEMENTOR_VERSION = '3.19.0';

    /**
     * Minimum PHP Version
     */
    const MINIMUM_PHP_VERSION = '8.0';

    /**
     * Instance
     */
    private static $_instance = null;

    /**
     * Ensures only one instance of the class is loaded or can be loaded.
     */
    public static function instance()
    {

        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Perform some compatibility checks to make sure basic requirements are meet.
     */
    public function __construct()
    {

        // set the constants.
        $this->setConstants();

        if ($this->is_compatible()) {
            add_action('elementor/init', [$this, 'init']);
        }

        //classes Initialization.
        $this->classes_init();

    }


    /**
     * Compatibility Checks
     */
    public function is_compatible()
    {

        // Check if Elementor installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
            return false;
        }

        // Check for required Elementor version
        if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return false;
        }

        // Check for required PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return false;
        }

        return true;
    }

    /**
     * setConstants.
     */

    public function setConstants()
    {
        define('SMARTCC_ELEMENTOR_ASSETS', plugin_dir_url(__FILE__) . 'Assets');
        define('SMARTCC_ELEMENTOR_PATH', plugin_dir_path(__FILE__));

    }

    /**
     * Warning when the site doesn't have Elementor installed or activated.
     */
    public function admin_notice_missing_main_plugin()
    {
        $message = sprintf(
            // translators: 1 Plugin name, 2 Elementor plugin name, 3 Required Elementor version
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'smart-contact-card'),
            esc_html(SMARTCC_NAME),
            esc_html__('Elementor', 'smart-contact-card'),
            esc_html(self::MINIMUM_ELEMENTOR_VERSION)
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', wp_kses_post($message));
    }

    /**
     * Warning when the site doesn't have a minimum required Elementor version.
     */
    public function admin_notice_minimum_elementor_version()
    {
        $message = sprintf(
            // translators: 1 Plugin name, 2 Elementor plugin name, 3 Required Elementor version
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'smart-contact-card'),
            esc_html(SMARTCC_NAME),
            esc_html__('Elementor', 'smart-contact-card'),
            self::MINIMUM_ELEMENTOR_VERSION
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post($message));
    }

    /**
     * Warning when the site doesn't have a minimum required PHP version.
     */
    public function admin_notice_minimum_php_version()
    {
        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'smart-contact-card'),
            '<strong>' . SMARTCC_NAME . '</strong>',
            '<strong>' . esc_html__('PHP', 'smart-contact-card') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', wp_kses_post($message));
    }

    /**
     * Initializes the classes used by the plugin.
     *
     * This function instantiates the functions and assets classes.
     *
     * @since 1.0.0
     */
    public function classes_init()
    {

    }


    /**
     * Load the addons functionality only after Elementor is initialized.
     */
    public function init()
    {
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }



    /**
     * Register all the widgets.
     *
     * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
     *
     * @return void
     */

    public function register_widgets($widgets_manager)
    {

        $namespace_base = '\Smartcc\Elementor\Widgets\\';

        // Register all widgets
        $this->register_general_widgets($widgets_manager, $namespace_base);
      

    }

    /**
     * Registers the general widgets.
     */
    private function register_general_widgets($widgets_manager, $namespace_base)
    {
        $widgets = [
            'smartcc_contact_card_widget' => 'ContactCard',
        ];
        foreach ($widgets as $option_name => $widget_class) {
            $is_enabled = get_option($option_name, 1); // Get the option value (default to enabled)

            if ($is_enabled) {
                $full_class_name = $namespace_base . $widget_class; // Combine base namespace with class path
                $widgets_manager->register(new $full_class_name());
            }
        }
    }


}