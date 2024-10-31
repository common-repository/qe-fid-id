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
use QeFid\ID\EventPipes\DeletePipe;
use QeFid\ID\EventPipes\LoginPipe;
use QeFid\ID\EventPipes\PipeEvent;
use QeFid\ID\EventPipes\PipeEventReturn;
use QeFid\ID\EventPipes\RegisterPipe;
use QeFid\ID\EventPipes\UpdatePipe;
use QeFid\ID\Models\DeleteRequest;
use QeFid\ID\Models\DeleteResponse;
use QeFid\ID\Models\EnrollRequest;
use QeFid\ID\Models\EnrollResponse;
use QeFid\ID\Models\UpdateRequest;
use QeFid\ID\Models\VerifyRequest;
use QeFid\ID\Models\VerifyResponse;
use QeFid\ID\Users\FidUser;
use QeFid\ID\Users\UserRoster;
use QeFid\ID\Utils\Helpers;
use QeFid\ID\Utils\Networking;
use QeFid\ID\Utils\Validators;

/**
 * @subpackage Plugin
 */
class Plugin
{

  /**
   * The variable name is used as the text domain when internationalizing strings
   * of text. Its value should match the Text Domain file header in the main
   * plugin file.
   *
   * @since    1.0.0
   *
   * @var      string
   */
  protected $plugin_slug = "qe-fid-id";

  /**
   * Plugin Code version
   *
   */
  protected $plugin_version = "1";

  /**
   * Instance of this class.
   *
   * @since    1.0.0
   *
   * @var      object
   */
  protected static $instance = null;


  /**
   *  Pipeline for registering a new user
   *
   * @since    1.0.0
   *
   * @var      object
   */
  protected RegisterPipe $register_pipeline;

  /**
   *  Pipeline for Loggin in an existing user
   *
   * @since    1.0.0
   *
   * @var      object
   */
  protected LoginPipe $login_pipeline;


  /**
   *  Pipeline for updating user details
   *
   *  @since 1.0.0
   *
   *  @var object
   */
  protected UpdatePipe $update_pipeline;

  /**
   *  Pipeline for deleting user details
   *
   *  @since 1.0.0
   *
   *  @var object
   */
  protected DeletePipe $delete_pipeline;

  /**
   * Return the plugin slug.
   *
   * @since    1.0.0
   *
   * @return    string slug variable.
   */
  public function get_plugin_slug()
  {
    return $this->plugin_slug;
  }


  /**
   * Return the plugin version.
   *
   * @since    1.0.0
   *
   * @return    string slug variable.
   */
  public function get_plugin_version()
  {
    return $this->plugin_version;
  }

  /**
   *The plugin nonce to be used for generating various tokens
   *
   * @since 1.0.0
   *
   * @var string
   */
  protected $plugin_nonce;

  public function allow_password_resets($allow, $ID)
  {
    if ((!$allow) || is_wp_error($allow)) {
      // Rejected by a previous filter
      return $allow;
    }

    if (get_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE) != "enabled") {
      return $allow;
    }

    $roster = self::get_user_roster();
    if (!$roster || !$roster instanceof UserRoster || $roster->get_roster() === null || count($roster->get_roster()) === 0) {
      return $allow;
    }

    $user = get_user_by('user_id', $ID);
    $allowed_roles = array('administrator');
    if (count($roster->get_roster()) > 0 && (array_intersect($allowed_roles, $user->roles))) {
      return false;
    }

    return $allow;
  }

  /**
   * Fired when the plugin is activated.
   *
   * If the plugin was previously active, the user credentials will be reinstated from the database
   * otherwise creates a new user store to be used for the plugin
   *
   * This UserRoster is then used to create a new user for the plugin
   * and store the credentials in the database.
   *
   * The current password hash is stored in the database for future use or if the user deactvates the plugin.
   *
   * @since    1.0.0
   */
  public static function activate()
  {
    global $wpdb;
    if (!Validators::validate_plugin_nonce(self::get_instance()->get_plugin_nonce())) {
      $plugin_nonce = sprintf('#%08X', mt_rand(0, 0xFFFFFFFF));
      update_option(Options::$FID_PLUGIN_NONCE, $plugin_nonce);
    }

    $guard_state = get_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE);
    if (!$guard_state) {
      update_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE, "disabled");
    }

    self::get_instance()->set_plugin_nonce(get_option(Options::$FID_PLUGIN_NONCE));

    $roster = self::get_user_roster();
    if (
      $roster &&
      (UserRoster::$ROSTER_STATUS_ACTIVE === $roster->roster_status)
    ) {
      return;
    }

    if (!$roster || !$roster instanceof UserRoster || $roster->get_roster() === null || count($roster->get_roster()) === 0) {
      update_option(Options::$FID_USER_MAPPING_ROSTER, new UserRoster(array(), self::get_instance()->get_plugin_nonce()));
      return;
    }

    // Ensure the user has an activated license to be able to make api calls before locking the user credentials
    $active_license = get_option(Options::$FID_LICENSE_KEY);
    $access_token = get_option(Options::$API_KEY_ACCESS);
    $refresh_token = get_option(Options::$API_KEY_REFRESH);
    if (!$active_license || !$access_token || !$refresh_token) {
      return;
    }


    if (
      $roster->roster_status === UserRoster::$ROSTER_STATUS_ACTIVE ||
      get_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE) == "disabled"
    ) {
      return;
    }

    add_filter('allow_password_reset',  array('QeFid\\ID\\Plugin', 'allow_password_resets'));

    foreach ($roster->get_roster() as $key => $value) {
      if (!($value instanceof FidUser) || !str_starts_with($value->get_user_access(), "$")) {
        continue;
      }

      $wpdb->update(
        $wpdb->users,
        array(
          'user_pass' => $value->get_user_access(),
        ),
        array('ID' => $value->user_id)
      );
      try {

        $wp_user = get_user_by("id", $value->user_id);
        if (!Validators::is_wp_user($wp_user)) {
          continue;
        }
        $roster->update_user(
          $key,
          FidUser::fromJson(
            array_merge(
              json_decode($value->toJson(), true),
              array("user_access" => "", "old_user_access" => $wp_user->user_pass)
            )
          )
        );
      } catch (\Exception $e) {
      }
    }
    $roster->roster_status = UserRoster::$ROSTER_STATUS_ACTIVE;
    update_option(Options::$FID_USER_MAPPING_ROSTER, $roster);
  }

  /**
   * Fired when the plugin is deactivated.
   *
   * @since    1.0.0
   */
  public static function deactivate()
  {
    global $wpdb;

    remove_filter('allow_password_reset',  array('QeFid\\ID\\Plugin', 'allow_password_resets'));
    //reset user passwords from roster to original hashes so they don't get locked out
    $roster = self::get_user_roster();

    if (UserRoster::$ROSTER_STATUS_INACTIVE === $roster->roster_status) {
      return;
    }


    if (!$roster instanceof UserRoster || $roster->get_roster() === null || count($roster->get_roster()) === 0) {
      delete_option(Options::$FID_USER_MAPPING_ROSTER);
      delete_option(Options::$FID_PLUGIN_NONCE);
      return;
    }
    foreach ($roster->get_roster() as $key => $value) {
      $user = get_user_by("id", $value->user_id);
      if (!(Validators::is_wp_user($user)) || !$value->get_old_user_access()) {
        continue;
      }
      $pass_holder = $user->user_pass;

      $wpdb->update(
        $wpdb->users,
        array(
          'user_pass' => $value->get_old_user_access()
        ),
        array('ID' => $value->user_id)
      );

      try {
        // set the fid user password to the user access temporarily
        // Do this after the user password has been reset to the original hash so that the user can login
        // with the original password and if any issues arise, the user is still able to login
        $roster->update_user($key, FidUser::fromJson(array_merge(json_decode($value->toJson(), true), array("user_access" => $pass_holder, "old_user_access" => ""))));
      } catch (\Exception $e) {
        continue;
      }
    }
    $roster->roster_status = UserRoster::$ROSTER_STATUS_INACTIVE;
    update_option(Options::$FID_USER_MAPPING_ROSTER, $roster);
  }


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
    }

    return self::$instance;
  }

  /**
   * Return the plugin nonce
   *
   * @since 1.0.0
   *
   * @return string
   */
  public function get_plugin_nonce()
  {
    return $this->plugin_nonce ?: get_option(Options::$FID_PLUGIN_NONCE);
  }

  /**
   * Set the plugin nonce
   *
   * @since 1.0.0
   *
   * @param string $nonce
   */
  public function set_plugin_nonce($nonce)
  {
    $this->plugin_nonce = $nonce;
  }

  /**
   * Returns the register pipeline
   *
   * @since    1.0.0
   *
   * @return    object    A single instance of this class.
   */
  public function get_register_pipeline()
  {
    return $this->register_pipeline;
  }

  /**
   * Returns the login pipeline
   *
   * @since    1.0.0
   *
   * @return    object    A single instance of this class.
   */
  public function get_login_pipeline()
  {
    return $this->login_pipeline;
  }

  /**
   * Returns the update pipeline
   *
   * @since    1.0.0
   *
   * @return    object    A single instance of this class.
   */
  public function get_update_pipeline()
  {
    return $this->update_pipeline;
  }

  /**
   * Returns the delete pipeline
   *
   * @since    1.0.0
   *
   * @return    object    A single instance of this class.
   */
  public function get_delete_pipeline()
  {
    return $this->delete_pipeline;
  }

  /**
   * Value obfuscator that will hash any value with the plugin nonce
   *
   * @param $value string | int
   * @return string
   * */
  public static function obfuscate_value($value)
  {
    return  hash(
      'sha256',
      '' . self::get_instance()->get_plugin_slug() . self::get_instance()->get_user_roster()->roster_nonce . $value
    );
  }

  /**
   * Hashing passwords throughout the plugin and plugin functions
   *
   * @param  FidUser $user - contains other fields required for generating the password
   * @param string $access_token
   * @param string $pin
   */
  public static function fid_hash_password(FidUser $user, string $access_token, string $pin): string
  {
    return  hash(
      'sha256',
      '' . self::get_instance()->get_plugin_slug() . $user->nonce . $user->bucket_id . $access_token . $pin
    );
  }

  /**
   * Get the user Rostester
   *
   * @since     1.0.0
   * @return    UserRoster    A single instance of this class.
   */
  public static function get_user_roster()
  {
    return get_option(Options::$FID_USER_MAPPING_ROSTER);
  }

  /**
   * Set the user Roster
   *
   * @since     1.0.0
   * @param    UserRoster    A single instance of this class.
   */
  public static function set_user_roster(UserRoster $roster)
  {
    $updated = update_option(Options::$FID_USER_MAPPING_ROSTER, $roster);
    if (!$updated) {
      $updated = add_option(Options::$FID_USER_MAPPING_ROSTER, $roster);
    }
    return $updated;
  }

  public static function update_user_based_on_lock_active_status($id, $roster_key, $password)
  {
    if (get_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE) == "enabled") {
      return wp_set_password($password, $id);
    }

    $user_roster = static::$instance->get_user_roster();
    if (UserRoster::$ROSTER_STATUS_ACTIVE === $user_roster->roster_status) {
      $user_roster->roster_status = UserRoster::$ROSTER_STATUS_INACTIVE;
    }

    $user_from_roster = $user_roster->get_user_by('user_id', $id);
    if ($user_from_roster !== null && $user_from_roster->user_id >= 0) {
      $user_from_roster->user_access = wp_hash_password($password);
      $user_roster->update_user($roster_key, $user_from_roster);
    }
    $ok = update_option(Options::$FID_USER_MAPPING_ROSTER, $user_roster);
    return $ok;
  }

  public static function process_api_errors($error_message)
  {
    $enrolled = get_option(Options::$FID_USER_MAPPING_ROSTER);
    $activated_guard = get_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE);
    if (
      (($enrolled &&
        $enrolled->roster_status != 'active') ||
        $activated_guard) &&
      in_array($error_message, Options::$FID_ERROR_CODES)
    ) {
      $plugin = static::get_instance();
      $plugin->deactivate();
      update_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE, "disabled");
      return get_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE) === "disabled";
    }
  }

  /**
   * Initializes the plugin by setting filters and administration functions.
   *
   * Setup instance attributes
   *
   * @since     1.0.0
   */
  private function __construct()
  {
    $this->plugin_version = QE_FID_VERSION;
    $plugin = $this;

    $this->register_pipeline = new RegisterPipe(array(
      "get_user_id" => new class implements PipeEvent
      {
        public function run($data): PipeEventReturn
        {
          if (
            !isset($data["user_id"]) ||
            !isset($data["pin"]) ||
            !isset($data["file_name"]) ||
            !Validators::validate_user_id_pin($data["user_id"], $data["pin"])
          ) {
            return new PipeEventReturn("", "Invalid file, user id or pin");
          }

          $user = get_user_by("id", $data["user_id"]);
          $pin = $data["pin"];
          return $user->ID !== null ? new PipeEventReturn(array("file_name" => $data["file_name"], "user" => $user, "pin" => $pin)) : new PipeEventReturn("", "User not found");
        }
      },

      "generate_plugin_user_id" => new class($plugin) implements PipeEvent
      {
        private $plugin;
        public function __construct($plugin)
        {
          $this->plugin = $plugin;
        }

        public function run($data): PipeEventReturn
        {
          if (
            !isset($data["user"]) ||
            !isset($data["pin"]) ||
            !isset($data["file_name"]) ||
            !Validators::validate_user_pin($data["pin"])
          ) {
            return new PipeEventReturn("", "Invalid user object or pin");
          }

          $wp_user = $data["user"];
          $nonce = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
          $user = FidUser::fromValues(
            hash(
              'sha256',
              $this->plugin->get_plugin_slug() . $this->plugin->get_user_roster()->roster_nonce . $nonce . $wp_user->ID
            ),
            $wp_user->ID,
            "",
            $nonce
          );

          if (get_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE) == "enabled") {
            $user->update_old_user_access($wp_user->user_pass);
          }
          return $user ?
            new PipeEventReturn(array("file_name" => $data["file_name"], "fid_user" => $user, "pin" => $data["pin"])) :
            new PipeEventReturn("", "Failed to generate user token");
        }
      },

      "register_to_api" => new class implements PipeEvent
      {
        public function run($data): PipeEventReturn
        {
          if (
            !isset($data["fid_user"]) ||
            !isset($data["pin"]) ||
            !isset($data["file_name"]) ||
            !Validators::validate_user_id_pin($data["fid_user"]->user_id, $data["pin"]) ||
            !get_option(Options::$FID_API_ENDPOINT)
          ) {
            return new PipeEventReturn("", array(
              "error" => "Invalid url, user object or pin",
              "data" => $data,
            ));
          }

          if (!get_option(Options::$API_KEY_ACCESS)) {
            return new PipeEventReturn("", array(
              "error" => "Invalid API key, please ensure you have activated the license from the FiD Auth settings page",
              "data" => $data,
            ));
          }

          $fid_user = $data["fid_user"];
          $pin = $data["pin"];
          $url = get_option(Options::$FID_API_ENDPOINT) . '/enroll';

          $response = Networking::post_to_url_with_image(
            $url,
            $data["file_name"],
            array('data' => EnrollRequest::fromValues(get_site_url(), array($fid_user->user_token), $pin, 1)->toJson()),
          );

          if (Validators::is_wp_error($response)) return new PipeEventReturn("", $response->get_error_message());

          $parsed_response = json_decode($response["body"], true);

          if (isset($parsed_response["error"]) && $parsed_response["error"] != "") {
            Plugin::process_api_errors($parsed_response["error"]);
            return new PipeEventReturn("", $parsed_response);
          }

          return new PipeEventReturn(array(
            "enroll_response" => EnrollResponse::fromJson($parsed_response["payload"]),
            "fid_user" => $fid_user,
            "pin" => $pin
          ));
        }
      },

      "update_user_roster" => new class($plugin) implements PipeEvent
      {
        private $plugin;
        public function __construct($plugin)
        {
          $this->plugin = $plugin;
        }

        public function run($data): PipeEventReturn
        {
          if (
            !isset($data["enroll_response"]) ||
            !isset($data["fid_user"]) ||
            !isset($data["pin"]) ||
            !Validators::validate_user_id_pin($data["fid_user"]->user_id, $data["pin"])
          ) {
            return new PipeEventReturn("", "Invalid response, user_id, user object or pin");
          }

          $enroll_response  = $data["enroll_response"];
          $fid_user = clone $data["fid_user"];

          $fid_user->bucket_id = $enroll_response->bucket_id;

          $user_key = $this->plugin->obfuscate_value($fid_user->user_id);
          $user_roster = $this->plugin->get_user_roster();
          $user_roster->add_user($user_key, $fid_user);

          $ok = update_option(Options::$FID_USER_MAPPING_ROSTER, $user_roster);
          if (!$ok) return new PipeEventReturn("", "Failed to save new user");
          return new PipeEventReturn(array(
            "fid_user" => $fid_user,
            "access_token" => $enroll_response->access_signature,
            "pin" => $data["pin"],
          ));
        }
      },

      "update_user_password" => new class($plugin) implements PipeEvent
      {
        private $plugin;
        public function __construct($plugin)
        {
          $this->plugin = $plugin;
        }

        public function run($data): PipeEventReturn
        {
          if (
            !isset($data["access_token"]) ||
            !isset($data["fid_user"]) ||
            !isset($data["pin"]) ||
            !Validators::validate_user_id_pin($data["fid_user"]->user_id, $data["pin"])
          ) {
            return new PipeEventReturn("", "Invalid access, user object or pin");
          }
          $fid_user = $data["fid_user"];
          $new_password = $this->plugin->fid_hash_password($fid_user, $data["access_token"], $data["pin"]);
          $user_key = $this->plugin->obfuscate_value($fid_user->user_id);
          Plugin::update_user_based_on_lock_active_status($fid_user->user_id, $user_key, $new_password);
          return new PipeEventReturn(wp_hash_password($new_password));
        }
      }
    )); //Register Pipeline

    $this->login_pipeline = new LoginPipe(array(
      "send_to_api" => new class($plugin) implements PipeEvent
      {
        private $plugin;
        public function __construct($plugin)
        {
          $this->plugin = $plugin;
        }

        public function run($data): PipeEventReturn
        {
          if (
            !isset($data["pin"]) ||
            !isset($data["file_name"]) ||
            !Validators::validate_user_pin($data["pin"]) ||
            !get_option(Options::$FID_API_ENDPOINT)
          ) {
            return new PipeEventReturn("", "Invalid endpoint file object or pin");
          }
          if (!get_option(Options::$API_KEY_ACCESS)) {
            return new PipeEventReturn("", array(
              "error" => "Unauthorized access",
              "data" => $data,
            ));
          }

          $url = get_option(Options::$FID_API_ENDPOINT) . '/verify';
          $response = Networking::post_to_url_with_image(
            $url,
            $data["file_name"],
            array('data' => VerifyRequest::fromValues(get_site_url(), $data["pin"])->toJson()),
          );

          if (is_wp_error($response)) return new PipeEventReturn("", Helpers::get_error($response));

          $parsed_response = json_decode($response["body"], true);

          if (isset($parsed_response["error"]) && $parsed_response["error"] != "") {
            Plugin::process_api_errors($parsed_response["error"]);
            return new PipeEventReturn("", $parsed_response);
          }

          return new PipeEventReturn(
            array(
              "verify_response" => VerifyResponse::fromJson($parsed_response["payload"]),
              "pin" => $data["pin"]
            )
          );
        }
      },

      "verify_against_user" => new class($plugin) implements PipeEvent
      {
        private $plugin;
        public function __construct($plugin)
        {
          $this->plugin = $plugin;
        }

        public function run($data): PipeEventReturn
        {
          if (
            !isset($data["pin"]) ||
            !isset($data["verify_response"]) ||
            !Validators::validate_user_pin($data["pin"])
          ) {
            return new PipeEventReturn("", "Invalid pin or response");
          }

          $verify_response = $data["verify_response"];
          $pin = $data["pin"];

          $user_roster = $this->plugin->get_user_roster();
          $user_key = $verify_response->label;
          $fid_user = $user_roster->get_user_by("user_token", $user_key);

          $attempted_password = $this->plugin->fid_hash_password($fid_user, $verify_response->access_token, $pin);
          $user = get_user_by("id", $fid_user->user_id);
          $passes = wp_authenticate($user->user_login, $attempted_password);
          $roster = $this->plugin->get_user_roster();
          return (Validators::is_wp_user($passes)) ?
            new PipeEventReturn(array("user" => $user, "password" => $attempted_password)) :
            new PipeEventReturn("", array("error" => "Incorrect login", "extra" => ["error" => $passes, "user" => $user, "password" => $attempted_password, "roster" => $roster]));
        }
      },

      "sign_user_in" => new class implements PipeEvent
      {
        public function run($data): PipeEventReturn
        {
          if (
            !isset($data["user"]) ||
            !isset($data["password"])
          ) {
            return new PipeEventReturn("", "Invalid user or password");
          }
          $user = $data["user"];
          $password = $data["password"];
          $creds = array(
            'user_login'    => $user->user_login,
            'user_password' => $password,
            'remember'      => true,
          );

          wp_set_current_user($user->ID, $user->user_login);
          wp_set_auth_cookie($user->ID);
          $user = wp_signon($creds, true);
          if (is_wp_error($user)) {
            return new PipeEventReturn("", "Incorrect Login Credentials, please try again");
          }
          do_action('wp_login', $user->user_login, $user);
          return new PipeEventReturn("Login Successful");
        }
      },

    ));

    $this->update_pipeline = new UpdatePipe(array(
      "send_to_api" => new class($plugin) implements PipeEvent
      {
        private $plugin;
        public function __construct($plugin)
        {
          $this->plugin = $plugin;
        }

        public function run($data): PipeEventReturn
        {
          if (
            !isset($data["old_pin"]) ||
            !isset($data["new_pin"]) ||
            !isset($data["user_id"]) ||
            !Validators::validate_user_pin($data["old_pin"]) ||
            !Validators::validate_user_pin($data["new_pin"]) ||
            !Validators::validate_user_id($data["user_id"]) ||
            !get_option(Options::$FID_API_ENDPOINT)
          ) {
            return new PipeEventReturn("", "Invalid endpoint, old pin or new pin");
          }
          $fid_user = $this->plugin->get_user_roster()->get_user_by("id", $this->plugin->obfuscate_value($data["user_id"]));
          if (!$fid_user || $fid_user->user_id < 0) return new PipeEventReturn("", "Plugin user not found");

          if (!get_option(Options::$API_KEY_ACCESS)) {
            return new PipeEventReturn("", array(
              "error" => "Invalid API key, please ensure you have activated the license from the FiD Auth settings page",
              "data" => $data,
            ));
          }

          $url = get_option(Options::$FID_API_ENDPOINT) . '/update';
          $response = Networking::post_to_url(
            $url,
            array(
              'data' => UpdateRequest::fromValues(
                $fid_user->bucket_id,
                get_site_url(),
                $data["old_pin"],
                $fid_user->user_token,
                $data["new_pin"]
              )->toJson()
            ),
          );

          if (is_wp_error($response)) return new PipeEventReturn("", Helpers::get_error($response));

          $parsed_response = json_decode($response["body"], true);

          if (isset($parsed_response["error"]) && $parsed_response["error"] != "") {
            Plugin::process_api_errors($parsed_response["error"]);
            return new PipeEventReturn("", $parsed_response);
          }

          return new PipeEventReturn(array(
            "update_response" => EnrollResponse::fromJson($parsed_response["payload"]),
            "new_pin" => $data["new_pin"]
          ));
        }
      },

      "verify_and_update_user" => new class($plugin) implements PipeEvent
      {
        private $plugin;
        public function __construct($plugin)
        {
          $this->plugin = $plugin;
        }

        public function run($data): PipeEventReturn
        {
          if (
            !isset($data["new_pin"]) ||
            !isset($data["update_response"]) ||
            !Validators::validate_user_pin($data["new_pin"])
          ) {
            return new PipeEventReturn("", "Invalid pin or update response");
          }

          $update_response = $data["update_response"];
          $new_pin = $data["new_pin"];

          $user_roster = $this->plugin->get_user_roster();
          $user_key = $update_response->user_token;
          $fid_user = $user_roster->get_user_by("user_token", $user_key);

          $user = get_user_by("id", $fid_user->user_id);

          if (!Validators::is_wp_user($user)) {
            return new PipeEventReturn("", "Could not update user, please re-enroll this user from the admin panel");
          }

          $new_pass_to_set = $this->plugin->fid_hash_password($fid_user, $update_response->access_signature, $new_pin);
          Plugin::update_user_based_on_lock_active_status($user->ID, $user_key, $new_pass_to_set);

          return new PipeEventReturn("Update Successful");
        }
      },
    ));

    $this->delete_pipeline = new DeletePipe(array(
      "send_to_api" => new class($plugin) implements PipeEvent
      {
        private $plugin;

        public function __construct($plugin)
        {
          $this->plugin = $plugin;
        }

        public function run($data): PipeEventReturn
        {
          if (
            !isset($data["user_id"]) ||
            !Validators::validate_user_id($data["user_id"]) ||
            !get_option(Options::$FID_API_ENDPOINT)
          ) {
            return new PipeEventReturn("", "Invalid endpoint or user id");
          }
          $fid_user = $this->plugin->get_user_roster()->get_user_by("id", $this->plugin->obfuscate_value($data["user_id"]));
          if (!$fid_user || $fid_user->user_id < 0) return new PipeEventReturn("", "Plugin user not found");

          if (!get_option(Options::$API_KEY_ACCESS)) {
            return new PipeEventReturn("", array(
              "error" => "Invalid API key, please ensure you have activated the license from the FiD Auth settings page",
              "data" => $data,
            ));
          }

          $url = get_option(Options::$FID_API_ENDPOINT) . '/delete';
          $response = Networking::post_to_url(
            $url,
            array(
              'data' => DeleteRequest::fromValues(
                $fid_user->bucket_id,
                get_site_url(),
                $fid_user->user_token
              )->toJson()
            ),
          );

          if (is_wp_error($response)) return new PipeEventReturn("", Helpers::get_error($response));

          $parsed_response = json_decode($response["body"], true);

          if (isset($parsed_response["error"]) && $parsed_response["error"] != "") {
            Plugin::process_api_errors($parsed_response["error"]);
            return new PipeEventReturn("", $parsed_response);
          }

          return new PipeEventReturn(array(
            "delete_response" => DeleteResponse::fromJson($parsed_response["payload"]),
            "user_id" => $data["user_id"]
          ));
        }
      },
      "delete_from_roster" => new class($plugin) implements PipeEvent
      {
        private $plugin;

        public function __construct($plugin)
        {
          $this->plugin = $plugin;
        }

        public function run($data): PipeEventReturn
        {
          if (
            !isset($data["user_id"]) ||
            !isset($data["delete_response"]) ||
            !Validators::validate_user_id($data["user_id"])
          ) {
            return new PipeEventReturn("", "Invalid user id or api response");
          }
          $delete_response = $data["delete_response"];
          $roster = $this->plugin->get_user_roster();
          $fid_user = $roster->get_user_by("user_token", $delete_response->user_token);
          if (!$fid_user || $fid_user->user_id < 0) return new PipeEventReturn("", "Plugin user not found");

          $roster->remove_user($this->plugin->obfuscate_value($fid_user->user_id));
          $successfully_updated = $this->plugin->set_user_roster($roster);

          return ($successfully_updated !== false) ?
            new PipeEventReturn(array("removed_user" => $fid_user)) :
            new PipeEventReturn("", "Could not update local roster, please check site permission settings");
        }
      },
      "reinstate_user_credentials" => new class($plugin) implements PipeEvent
      {
        private $plugin;

        public function __construct($plugin)
        {
          $this->plugin = $plugin;
        }

        public function run($data): PipeEventReturn
        {
          global $wpdb;
          $gateEnbled = get_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE) == "enabled";
          if (
            !isset($data["removed_user"]) ||
            $data["removed_user"]->user_id < 0 ||
            ($gateEnbled && $data["removed_user"]->old_user_access == "") ||
            $data["removed_user"]->user_id < 0
          ) {
            return new PipeEventReturn("", "Invalid user to delete");
          }

          if (!$gateEnbled) {
            return new PipeEventReturn("Successfully Deleted User");
          }

          $fid_user = $data["removed_user"];

          $user = get_user_by("id", $fid_user->user_id);

          if ($user) {
            $wpdb->update(
              $wpdb->users,
              array(
                'user_pass' => $fid_user->get_old_user_access()
              ),
              array('ID' => $user->ID)
            );

            return new PipeEventReturn("Successfully Deleted User");
          }
          return new PipeEventReturn("", "Could not reinstate user credentials");
        }
      }
    ));
  }
}
