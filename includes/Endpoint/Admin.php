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

use QeFid\ID;
use QeFid\ID\Constants\Options;
use QeFid\ID\Plugin;
use QeFid\ID\PluginMeta;
use QeFid\ID\Users\FidUser;
use QeFid\ID\Users\UserRoster;

// use  PremierID\ACL;
/**
 * @subpackage REST_Controller
 */
class Admin extends PluginMeta
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
    $license_settings = '/qe-fid-licenses/';
    $license_activation = '/qe-fid-license-activation/';
    $renew_license_endpoint = Options::$FID_PLUGIN_LICENSE_WEBHOOK;
    $users = '/eligible-users/';
    $register_enroll = '/enroll/';
    $update = '/update-user/';
    $unregister_delete = '/unregister-user/';
    $login_gate_lockdown = '/login-lockdown-state/';

    register_rest_route($namespace, $renew_license_endpoint, array(
      array(
        'methods'               => \WP_REST_Server::READABLE,
        'callback'              => array($this, 'renew_qe_fid_plugin_license'),
        'args' => array(
          'license_to_renew' => array(
            'validate_callback' => function ($param, $request, $key) {
              if (preg_match('/^[a-zA-Z0-9-]{35,}$/', $param) == 1) {
                return true;
              }
              return false;
            }
          ),
        ),
      ),
    ));

    register_rest_route($namespace, $register_enroll, array(
      array(
        'methods'               => \WP_REST_Server::CREATABLE,
        'callback'              => array($this, 'fid_register_enroll'),
        'permission_callback'   => array($this, 'admin_permissions_check'),
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

    register_rest_route($namespace, $update, array(
      array(
        'methods'               => \WP_REST_Server::CREATABLE,
        'callback'              => array($this, 'fid_update_user'),
        'permission_callback'   => array($this, 'admin_permissions_check'),
        'args'                  => array(
          'old_pin' => array(
            'required' => true,
            'type' => 'string',
            'description' => 'The user\'s current pin number',
            'validate_callback' => function ($param, $request, $key) {
              if (preg_match('/^[A-Z0-9]{4}$/', $param) == 1) {
                return true;
              }
              return false;
            }
          ),
          'new_pin' => array(
            'required' => true,
            'type' => 'string',
            'description' => 'The user\'s new pin number',
            'validate_callback' => function ($param, $request, $key) {
              if (preg_match('/^[A-Z0-9]{4}$/', $param) == 1) {
                return true;
              }
              return false;
            }
          ),
          'user_id' => array(
            'required' => true,
            'type' => 'string',
            'description' => 'The user\'s id number',
          ),
        ),
      ),
    ));

    register_rest_route($namespace, $unregister_delete, array(
      array(
        'methods'               => \WP_REST_Server::CREATABLE,
        'callback'              => array($this, 'fid_unregister_delete'),
        'permission_callback'   => array($this, 'admin_permissions_check'),
        'args'                  => array(
          'user_id' => array(
            'required' => true,
            'type' => 'string',
            'description' => 'The users id',
          ),
        ),
      ),
    ));

    register_rest_route($namespace, $users, array(
      array(
        'methods'               => \WP_REST_Server::CREATABLE,
        'callback'              => array($this, 'get_users_for_admin'),
        'permission_callback'   => array($this, 'admin_permissions_check'),
        'args'                  => array(
          'page' => array(
            'required' => true,
            'type' => 'integer',
          ),
          'rows_per_page' => array(
            'required' => true,
            'type' => 'integer',
          ),
        ),
      ),
    ));


    register_rest_route($namespace, $login_gate_lockdown, array(
      array(
        'methods'               => \WP_REST_Server::CREATABLE,
        'callback'              => array($this, 'fid_login_lockdown'),
        'permission_callback'   => array($this, 'admin_permissions_check'),
        'args'                  => array(
          'status' => array(
            'required' => true,
            'type' => 'string',
            'description' => 'The desired status of the login gate',
          ),
        ),
      ),
    ));

    register_rest_route($namespace, $login_gate_lockdown, array(
      array(
        'methods'               => \WP_REST_Server::READABLE,
        'callback'              => array($this, 'get_gate_lockdown_status'),
        'permission_callback'   => array($this, 'admin_permissions_check'),
      ),
    ));

    register_rest_route($namespace, $license_settings, array(
      array(
        'methods'               => \WP_REST_Server::READABLE,
        'callback'              => array($this, 'get_site_settings'),
        'permission_callback'   => array($this, 'admin_permissions_check'),
      ),
    ));

    register_rest_route($namespace, $license_settings, array(
      array(
        'methods'               => \WP_REST_Server::CREATABLE,
        'callback'              => array($this, 'update_site_settings'),
        'permission_callback'   => array($this, 'admin_permissions_check'),
        'args'                  => array(
          'email' => array(
            'required' => true,
            'type' => 'string',
            'description' => 'The user\'s email address',
            'format' => 'email'
          ),

          'endpoint_url' => array(
            'required' => true,
            'type' => 'string',
            'description' => 'The endpoint for the fid license server',
          ),

          'license_key' => array(
            'required' => true,
            'type' => 'string',
            'description' => 'The fid license key',
          ),
        ),
      ),
    ));

    register_rest_route($namespace, $license_settings, array(
      array(
        'methods'               => \WP_REST_Server::DELETABLE,
        'callback'              => array($this, 'delete_site_settings'),
        'permission_callback'   => array($this, 'admin_permissions_check'),
        'args'                  => array(),
      ),
    ));

    register_rest_route($namespace, $license_activation, array(
      array(
        'methods'               => \WP_REST_Server::EDITABLE,
        'callback'              => array($this, 'activate_fid_license'),
        'permission_callback'   => array($this, 'admin_permissions_check'),
      ),
    ));

    register_rest_route($namespace, $license_activation, array(
      array(
        'methods'               => \WP_REST_Server::DELETABLE,
        'callback'              => array($this, 'deactivate_fid_license'),
        'permission_callback'   => array($this, 'admin_permissions_check'),
        'args'                  => array(),
      ),
    ));
  }

  /**
   * Get a list of the eligible plugin users
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function get_users_for_admin($request)
  {
    $args = array(
      'role' => 'administrator',
      'fields' => array('ID', 'display_name', 'user_email'),
      'orderby' => 'ID',
      'order'   => 'ASC',
      'number' => $request->get_param('rows_per_page'),
      'offset' => ($request->get_param('page') - 1) * $request->get_param('rows_per_page'),
    );

    $users = get_users($args);
    $count = count_users();
    $total = $count['avail_roles']['administrator'];

    $user_roster = Plugin::get_user_roster();
    $eligible_users = array_map(function ($user) use ($user_roster) {
      return \QeFid\ID\Models\EligibleUser::fromValues(
        $user->ID,
        $user->display_name,
        $user->user_email,
        ($this->get_user_from_roster($user->ID, $user_roster) !== null)
      );
    }, $users);

    $allowed_user_count = get_option(Options::$FID_LICENSE_USER_LIMIT);

    return new \WP_REST_Response(array(
      'success' => true,
      'users' => array("maximum_license_users" => $allowed_user_count, "count" => $total, "results" => $eligible_users),
    ), 200);
  }

  private function get_user_from_roster(int $id, UserRoster $roster)
  {
    if (!($roster instanceof UserRoster)) {
      return null;
    }

    $user = $roster->get_user_by("user_id", $id);
    if ($user instanceof FidUser && $user->user_id > 0) {
      return $user;
    }

    return null;
  }

  /**
   * Get the plugin's licensing information settings
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function get_site_settings($request)
  {
    $endpoint_option = get_option(Options::$LICENSING_API_ENDPOINT);
    $license_key = get_option(Options::$FID_LICENSE_KEY);
    $access = get_option(Options::$API_KEY_ACCESS);
    $refresh = get_option(Options::$API_KEY_REFRESH);
    $contact_email_option = get_option(Options::$FID_ADMIN_EMAIL);

    return new \WP_REST_Response(array(
      'success' => true,
      'email' => !$contact_email_option ? '' : $contact_email_option,
      'endpoint_url' => !$endpoint_option ? '' : $endpoint_option,
      'license_key' => !$license_key ? '' : $license_key,
      'access_key' => !$access ? '' : '********',
      'refresh_key' => !$refresh ? '' : '********'
    ), 200);
  }

  /**
   * Update the plugin's licensing information settings
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function update_site_settings($request)
  {
    $updated = update_option(Options::$FID_ADMIN_EMAIL, $request->get_param('email'));
    $endpoint_option = update_option(Options::$LICENSING_API_ENDPOINT, $request->get_param('endpoint_url'));
    $license_key = update_option(Options::$FID_LICENSE_KEY, $request->get_param('license_key'));

    $ok = $updated || $endpoint_option || $license_key;
    return new \WP_REST_Response(array(
      'success'   => $ok,
      'email'     => $request->get_param('email'),
      'endpoint_url' => $request->get_param('endpoint_url'),
      'license_key' => $request->get_param('license_key'),
    ), 200);
  }

  /**
   * Delete all the plugin's licensing information settings
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function delete_site_settings($request)
  {
    $deleted = false;
    $access_key = get_option(Options::$API_KEY_ACCESS);
    $refresh_key = get_option(Options::$API_KEY_REFRESH);
    if (!$access_key && !$refresh_key) {
      $lk = delete_option(Options::$FID_LICENSE_KEY);
      $endpoint_option = delete_option(Options::$LICENSING_API_ENDPOINT);
      $deleted = delete_option(Options::$FID_API_ENDPOINT);
      $deleted = delete_option(Options::$FID_ADMIN_EMAIL);
    }

    return new \WP_REST_Response(array(
      'success'   => $deleted,
      'email'     => '',
      'endpoint_url' => '',
    ), $deleted ? 200 : 400);
  }

  /**
   * Activate the plugin's license
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function activate_fid_license($request)
  {
    $activation_key = get_option(Options::$FID_LICENSE_KEY);
    $activated = ID\Endpoint\PluginAdmin::activate_license($activation_key);
    if ($activated['success'] === true) {
      ID\Login::get_instance()->activate_login_form();
      $access = get_option(Options::$API_KEY_ACCESS);
      $refresh = get_option(Options::$API_KEY_REFRESH);
      return new \WP_REST_Response(array(
        'success'   => true,
        'license_key'     => $request->get_param('license_key'),
        'access_key' => !$access ? '' : '********',
        'refresh_key' => !$refresh ? '' : '********',
        'error' => '',
      ), 200);
    }
    if (
      $activated['error'] == ""
    ) {
      $activated['error'] = array(
        'error' => 'Could not verify endpoint, please check the Endpoint URL matches the one sent to your email.',
        'code' => 501,
      );
    }
    $access = get_option(Options::$API_KEY_ACCESS);
    $refresh = get_option(Options::$API_KEY_REFRESH);
    return new \WP_REST_Response(array(
      'success'   => false,
      'license_key'     => $request->get_param('license_key'),
      'access_key' => !$access ? '' : '********',
      'refresh_key' => !$refresh ? '' : '********',
      'error' => $activated['error'],
    ), 200);
  }

  /**
   * Deactivate the plugin's license
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function deactivate_fid_license($request)
  {
    $refresh_key = get_option(Options::$API_KEY_REFRESH);
    $deactivated = ID\Endpoint\PluginAdmin::deactivate_license($refresh_key);
    if ($deactivated['success'] === true) {
      $deleted_access = delete_option(Options::$API_KEY_ACCESS);
      $deleted_refresh = delete_option(Options::$API_KEY_REFRESH);
      $deleted_endpoint = delete_option(Options::$FID_API_ENDPOINT);
      $deleted_endpoint = delete_option(Options::$FID_LICENSE_USER_LIMIT);

      $ok = $deleted_access && $deleted_refresh && $deleted_endpoint;

      return new \WP_REST_Response(array(
        'success'   => $ok,
        'license_key'     =>  get_option(Options::$FID_LICENSE_KEY),
        'access_key' => '',
        'refresh_key' => '',
        'error' => $ok ? '' : 'Could not deactivate license',
      ), $ok ? 200 : 500);
    }

    return new \WP_REST_Response(array(
      'success'   => false,
      'license_key'     =>  get_option(Options::$FID_LICENSE_KEY),
      'access_key' => !get_option(Options::$API_KEY_ACCESS) ? '' : '********',
      'refresh_key' => !get_option(Options::$API_KEY_REFRESH) ? '' : '********',
      'error' => $deactivated['error'],
    ), 200);
  }

  public function fid_register_enroll($request)
  {
    $file = $request->get_file_params();
    $user_pin = $request->get_param('user_pin');
    $user_id = $request->get_param('user_id');
    if (!isset($file['file']['tmp_name']) || !isset($user_pin)) {
      return new \WP_REST_Response(array(
        'success'   => false,
        'error' => 'Missing Parameters',
      ), 400);
    }
    $response = Plugin::get_instance()->get_register_pipeline()->run_pipe(
      array(
        "user_id" => $user_id,
        "pin" => $user_pin,
        "file_name" => $file["file"]["tmp_name"]
      )
    );

    $has_error = $response["status"] === "error";
    return new \WP_REST_Response(array(
      'success'   => !$has_error,
      'message' => !$has_error ? "Successfully Registered user" : null,
      'error' => $has_error ? $response["result"] : null,
      'data' => $response,
    ), $has_error ? 400 : 200);
  }

  public function fid_update_user($request)
  {
    $user_old_pin = $request->get_param('old_pin');
    $user_new_pin = $request->get_param('new_pin');
    $user_id = $request->get_param('user_id');

    $response = Plugin::get_instance()->get_update_pipeline()->run_pipe(array(
      "user_id" => $user_id,
      "old_pin" => $user_old_pin,
      "new_pin" => $user_new_pin
    ));

    $has_error = $response["status"] === "error";
    return new \WP_REST_Response(array(
      'success'   => !$has_error,
      'message' => !$has_error ? "Successfully updated user" : null,
      'error' => $has_error ? $response["result"] : null,
      'data' => $response,
    ), $has_error ? 400 : 200);
  }

  public function fid_unregister_delete($request)
  {
    $user_id = $request->get_param('user_id');
    $response = Plugin::get_instance()->get_delete_pipeline()->run_pipe(array(
      "user_id" => $user_id
    ));

    $has_error = $response["status"] === "error";
    return new \WP_REST_Response(array(
      'success'   => !$has_error,
      'message' => !$has_error ? "Successfully unregistered user" : null,
      'error' => $has_error ? $response["result"] : null,
      'data' => $response,
    ), $has_error ? 400 : 200);
  }

  public function fid_login_lockdown($request)
  {
    $status = $request->get_param('status');
    switch ($status) {
      case 'enabled': {
          update_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE, "enabled");
          $ok = get_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE) === "enabled";
          $plugin = ID\Plugin::get_instance();
          $plugin->activate();
          if (!$ok) {
            return new \WP_REST_Response(array(
              'success' => false,
              'message' => null,
              'error' => 'Could not set the login state',
              'status' => $status,
              'data' => get_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE),
            ), 500);
          }
          break;
        }
      case 'disabled': {
          $plugin = ID\Plugin::get_instance();
          $plugin->deactivate();
          update_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE, "disabled");
          $ok = get_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE) === "disabled";
          if (!$ok) {
            return new \WP_REST_Response(array(
              'success' => false,
              'message' => null,
              'error' => 'Could not set the login state',
              'status' => $status,
              'data' => get_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE),
            ), 500);
          }
          break;
        }
      default: {
          return new \WP_REST_Response(array(
            'success' => false,
            'message' => null,
            'error' => 'No valid state provided',
            'data' => [],
            'data' => get_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE),
          ), 400);
        }
    }

    Plugin::get_instance()->activate();
    return new \WP_REST_Response(array(
      'success'   => true,
      'message' => "The login gate has been " . $status . ". " . ($status == "enabled") ? "Please note, only Facial recognition logins are allowed until this is disabled." : "",
      'error' =>  null,
      'status' => $status,
    ), 200);
  }

  /**
   * Get the plugin's licensing information settings
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function get_gate_lockdown_status($request)
  {
    $gate_lockdown_status = get_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE);

    return new \WP_REST_Response(array(
      'success' => true,
      'status' => $gate_lockdown_status,
    ), 200);
  }

  /**
   * Webhook fired by the server to tell the plugin to update it's licenses
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function renew_qe_fid_plugin_license($request)
  {
    $lcnse = $request->get_param('license_to_renew');
    $savedKey = get_option(Options::$FID_LICENSE_KEY);
    if ($savedKey !== $lcnse) {
      return new \WP_REST_Response(array(), 421);
    }
    $renewed = ID\Endpoint\PluginAdmin::renew_license();
    return new \WP_REST_Response(array(
      'success'   => true,
      'license_key'     => get_option(Options::$FID_LICENSE_KEY),
      'access_key' => get_option(Options::$API_KEY_ACCESS),
      'refresh_key' => get_option(Options::$API_KEY_REFRESH),
      'error' => $renewed['error'],
    ), 200);
  }

  /**
   * Check if the api nonce is valid and a given user has access to update a setting
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function admin_permissions_check($request)
  {
    $api_nonce = $request->get_header(Options::$HEADER_WP_REST_NONCE);
    if (!$api_nonce || !wp_verify_nonce($api_nonce, Options::$WP_REST_NONCE_KEY)) {
      return false;
    }

    return current_user_can('manage_options');
  }
}
