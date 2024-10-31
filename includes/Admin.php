<?php
/**
 * FiD Facial Recognition Admin Login
 *
 * @package   FiD Access Plugin
 * @author    FiD Access
 * @license   GPL-3.0
 * @copyright FiD Access
 * @link      https://www.fidaccess.com
 *
 */

namespace QeFid\ID;

use QeFid\ID\Models\RestConfig;
use QeFid\ID\Constants\Options;

/**
 * @subpackage Admin
 */
class Admin extends PluginMeta
{
  /**
   * Instance of this class.
   *
   * @since    1.0.0
   *
   * @var      object
   */
  protected static $instance = null;


  /**
   * Slug of the plugin screen.
   *
   * @since    1.0.0
   *
   * @var      string
   */
  protected $plugin_screen_hook_suffix = null;


  /**
   * Return an instance of this class.
   *
   * @since     1.0.0
   *
   * @return    object    A single instance of this class.
   */
  public static function get_instance()
  {

    // If the single instance hasn't been set, set it now.
    if (null == self::$instance) {
      self::$instance = new self;
      self::$instance->do_hooks();
    }

    return self::$instance;
  }

  /**
   * Initialize the plugin by loading admin scripts & styles and adding a
   * settings page and menu.
   *
   * @since     1.0.0
   */
  private function __construct()
  {
    parent::__construct();
  }


  /**
   * Handle WP actions and filters.
   *
   * @since 	1.0.0
   */
  private function do_hooks()
  {
    // Load admin style sheet and JavaScript.
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

    // Add the options page and menu item.
    add_action('admin_menu', array($this, 'add_plugin_admin_menu'));

    // Add plugin action link point to settings page
    add_filter('plugin_action_links_' . $this->plugin_basename, array($this, 'add_action_links'));
  }

  /**
   * Register and enqueue admin-specific style sheet.
   *
   * @since     1.0.0
   *
   * @return    null    Return early if no settings page is registered.
   */
  public function enqueue_admin_styles()
  {
    if (!isset($this->plugin_screen_hook_suffix)) {
      return;
    }

    $screen = get_current_screen();
    if ($this->plugin_screen_hook_suffix == $screen->id) {
      wp_register_style($this->plugin_slug . '-admin-style', plugins_url('assets/css/admin.css', dirname(__FILE__)));
      wp_enqueue_style($this->plugin_slug . '-admin-style');
    }
  }

  /**
   * Register and enqueue admin-specific javascript
   *
   * @since     1.0.0
   *
   * @return    null    Return early if no settings page is registered.
   */
  public function enqueue_admin_scripts()
  {
    if (!isset($this->plugin_screen_hook_suffix)) {
      return;
    }

    $screen = get_current_screen();
    if ($this->plugin_screen_hook_suffix == $screen->id) {
      wp_enqueue_script(
        $this->plugin_slug . '-admin-script',
        plugins_url(
          'assets/js/admin.js',
          dirname(__FILE__)
        ),
        array('jquery'),
        $this->version
      );

      $configs = new RestConfig(
        wp_create_nonce(Options::$WP_REST_NONCE_KEY),
        rest_url($this->plugin_slug . '/v1/'),
        trailingslashit(plugin_dir_url(dirname(__FILE__))) . 'assets/images/',
      );

      wp_localize_script(
        $this->plugin_slug . '-admin-script',
        'qe_fid_auth_object',
        $configs->toMap()
      );
    }
  }

  /**
   * Register the administration menu for this plugin into the WordPress Dashboard menu.
   *
   * @since    1.0.0
   */
  public function add_plugin_admin_menu()
  {
    /*
     * Add a settings page for this plugin to the Settings menu.
     */
    $this->plugin_screen_hook_suffix =
      add_menu_page(
        'FiD Admin', // page_title
        __('FiD Admin', $this->plugin_slug),
        'manage_options', // capability
        'settings-' . $this->plugin_slug,
        array($this, 'display_plugin_admin_page'),
        'dashicons-admin-generic',
        10
      );
  }

  /**
   * Render the settings page for this plugin.
   *
   * @since    1.0.0
   */
  public function display_plugin_admin_page()
  {
?><div id="qe-fid-auth-admin"></div>
<?php
  }

  /**
   * Add settings action link to the plugins page, eg settings, more info, etc.
   *
   * @since    1.0.0
   */
  public function add_action_links($links)
  {
    return array_merge(
      array(
        'settings' => '<a href="' . admin_url('admin.php?page=' . 'settings-' . $this->plugin_slug) . '">' . __('Settings', $this->plugin_slug) . '</a>',
      ),
      $links
    );
  }
}
