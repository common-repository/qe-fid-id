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

use QeFid\ID\Constants\Options;
use QeFid\ID\Models\RestConfig;

/**
 * @subpackage Login
 */
class Login extends PluginMeta
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
    //add login button to wp form
    add_action('login_head', array($this, 'login_with_fid_button'));
    // Load admin style sheet and JavaScript.
    add_action('login_enqueue_scripts', array($this, 'enqueue_gate_styles'));
    add_action('login_enqueue_scripts', array($this, 'enqueue_gate_scripts'));
    //
    add_filter('wp_login_errors',  array($this, 'my_login_form_lock_down'), 90, 2);
  }

  // Add custom login form to wp-login.php
  // Ensure all required fields are set
  public function login_with_fid_button()
  {
    $enrolled = get_option(Options::$FID_USER_MAPPING_ROSTER);
    $active_license = get_option(Options::$FID_LICENSE_KEY);
    $activated_service = get_option(Options::$API_KEY_ACCESS);
    $activated_service_key = get_option(Options::$API_KEY_REFRESH);
    $api_valid  = get_option(Options::$FID_API_ENDPOINT);
    $activated_guard = get_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE);
    $valid_plugin_config = $active_license && $activated_service && $activated_service_key && $api_valid;
    if (
      !$enrolled ||
      $enrolled->roster_status != 'active' ||
      (count($enrolled->user_roster) < 1) ||
      !$valid_plugin_config ||
      $activated_guard == 'disabled'
    ) {
      return;
    }
?>
    <style type="text/css">
      .login h1 {
        display: none;
      }
    </style>
    <div id="qe-fid-auth-gate"></div>
<?php
  }
  /**
   * Shows the regular wp login form if the user is not enrolled in the fid plugin roster
   * Used to ensure no lockouts happen even if the plugin is active but the user is not enrolled.
   * Once the user is enrolled, the fid login form will be shown and this function will only return wp errors
   *
   */
  public function my_login_form_lock_down($errors, $redirect_to)
  {
    $enrolled = get_option(Options::$FID_USER_MAPPING_ROSTER);
    $active_license = get_option(Options::$FID_LICENSE_KEY);
    $activated_service = get_option(Options::$API_KEY_ACCESS);
    $activated_service_key = get_option(Options::$API_KEY_REFRESH);
    $api_valid  = get_option(Options::$FID_API_ENDPOINT);
    $valid_plugin_config = $active_license && $activated_service && $activated_service_key && $api_valid;
    if (
      !$enrolled ||
      $enrolled->roster_status != 'active' ||
      (count($enrolled->user_roster) < 1) ||
      !$valid_plugin_config
    ) {
      return $errors;
    }

    login_header(__('Log In'), '', $errors);
    do_action('login_footer');

    exit;
  }

  public function activate_login_form()
  {
    $this->do_hooks();
  }

  public function deactivate_login_form()
  {
  }

  // add_filter('wp_authenticate_user','wdm_validate_login_captcha',10,2);

  /**
   * Register and enqueue admin-specific style sheet.
   *
   * @since     1.0.0
   *
   * @return    null    Return early if no settings page is registered.
   */
  public function enqueue_gate_styles()
  {
    wp_register_style($this->plugin_slug . '-admin-style', plugins_url('assets/css/admin.css', dirname(__FILE__)));
    wp_register_style($this->plugin_slug . '-gate-style', plugins_url('assets/css/modal.css', dirname(__FILE__)));
    wp_enqueue_style($this->plugin_slug . '-admin-style');
    wp_enqueue_style($this->plugin_slug . '-gate-style');
  }

  /**
   * Register and enqueue admin-specific javascript
   *
   * @since     1.0.0
   *
   * @return    null    Return early if no settings page is registered.
   */
  public function enqueue_gate_scripts()
  {
    $redirect = admin_url();
    $config = new RestConfig(
      wp_create_nonce(Options::$WP_REST_NONCE_KEY),
      rest_url($this->plugin_slug . '/v1/'),
      trailingslashit(plugin_dir_url(dirname(__FILE__))) . 'assets/images/',
      $redirect,
      array("allow_powered_by" => get_option(Options::$FID_PLUGIN_DISPLAY_SETTINGS))
    );
    wp_enqueue_script($this->plugin_slug . '-gate-script', plugins_url('assets/js/gate.js', dirname(__FILE__)), array('jquery'), $this->version);
    wp_localize_script(
      $this->plugin_slug . '-gate-script',
      'qe_fid_auth_object',
      $config->toMap(),
    );
  }
}
