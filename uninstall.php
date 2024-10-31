<?php

namespace QeFid\ID;

use QeFid\ID\Constants\Options;
use QeFid\ID\Endpoint\PluginAdmin;

// If uninstall not called from WordPress exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit();
}

require_once __DIR__ . '/autoload.php';

$access_key = get_option(\QeFid\ID\Constants\Options::$API_KEY_ACCESS);
$refresh_key = get_option(Options::$API_KEY_REFRESH);
if ($refresh_key) {
  $deactivated = PluginAdmin::deactivate_license($refresh_key);
  $deleted_access = delete_option(Options::$API_KEY_ACCESS);
  $deleted_refresh = delete_option(Options::$API_KEY_REFRESH);
  $deleted_endpoint = delete_option(Options::$FID_API_ENDPOINT);
  $deleted_endpoint = delete_option(Options::$FID_LICENSE_USER_LIMIT);
}
delete_option(Options::$FID_LICENSE_KEY);
delete_option(Options::$LICENSING_API_ENDPOINT);
delete_option(Options::$FID_API_ENDPOINT);
delete_option(Options::$FID_ADMIN_EMAIL);
delete_option(Options::$FID_PLUGIN_LOGIN_GUARD_STATE);
// Ensure that the user unenrolled from the service before deleting data
// protects against accidental deletion
$enrolled = get_option(Options::$FID_USER_MAPPING_ROSTER);
if ($enrolled || count($enrolled->user_roster) >= 1) {
  $plugin = Plugin::get_instance();
  $plugin->deactivate();
  delete_option(Options::$FID_USER_MAPPING_ROSTER);
}
