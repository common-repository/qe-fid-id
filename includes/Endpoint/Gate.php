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

namespace QeFid\ID\Endpoint;

use QeFid\ID\Plugin;
use QeFid\ID\PluginMeta;
use QeFid\ID\Constants\Options;

// use  PremierID\ACL;
/**
 * @subpackage REST_Controller
 */
class Gate extends PluginMeta
{
  /**
   * Instance of this class.
   *
   * @since    0.8.1
   *
   * @var      object
   */
  protected static $instance = null;

  /**
   * Initialize the plugin by setting localization and loading public scripts
   * and styles.
   *
   * @since     0.8.1
   */
  private function __construct()
  {
    parent::__construct();
  }

  /**
   * Set up WordPress hooks and filters
   *
   * @return void
   */
  public function do_hooks()
  {
    add_action('rest_api_init', array($this, 'register_routes'));
  }

  /**
   * Return an instance of this class.
   *
   * @since     0.8.1
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
   * Register the routes for the objects of the controller.
   */
  public function register_routes()
  {
    $version = '1';
    $namespace = $this->plugin_slug . '/v' . $version;
    $login_verify = '/login/';

    register_rest_route($namespace, $login_verify, array(
      array(
        'methods'               => \WP_REST_Server::CREATABLE,
        'callback'              => array($this, 'fid_verify_login'),
        'permission_callback'   => array($this, 'gate_permissions_check'),
        'args'                  => array(
          'user_pin' => array(
            'required' => true,
            'type' => 'string',
            'description' => 'The user\'s pin number',
            'validate_callback' => function ($param, $request, $key) {
              if (preg_match('/^[A-Z0-9]{4}$/', $param) == 1) {
                return true;
              }
              return false;
            }
          ),
        ),
      ),
    ));
  }

  /**
   * Verify Login with api
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function fid_verify_login($request)
  {
    $file = $request->get_file_params();
    $user_pin = $request->get_param('user_pin');
    if (!isset($file['file']['tmp_name']) || !isset($user_pin)) {
      return new \WP_REST_Response(array(
        'success'   => false,
        'error' => 'Missing Parameters',
      ), 400);
    }

    $response = Plugin::get_instance()->get_login_pipeline()->run_pipe(array(
      "file_name" => $file['file']['tmp_name'],
      "pin" => $user_pin
    ));

    $has_error = $response["status"] === "error";

    return new \WP_REST_Response(array(
      'success'   => !$has_error,
      'message' => !$has_error ? "Successfully verified user" : null,
      'error' => $has_error ? $response["result"] : null,
      'data' => $response,
    ), $has_error ? 400 : 200);

    exit();
  }

  /**
   * Check if the api nonce is valid and a given user has access to update a setting
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function gate_permissions_check($request)
  {
    $api_nonce = $request->get_header(Options::$HEADER_WP_REST_NONCE);
    if (!$api_nonce || !wp_verify_nonce($api_nonce, Options::$WP_REST_NONCE_KEY)) {
      return false;
    }
    return true;
  }
}
