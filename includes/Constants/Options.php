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


namespace QeFid\ID\Constants;

/**
 * Constants class
 * Holds constants used for setters and getters in wp - centralised here to avoid typos
 *
 * @package QeFid\Constants
 * @since   1.0.0
 * @author  FiD Auth Access
 *
 */
class Options
{
  /***
   *     _______   ________   ________  ________  ________  ________  ___  ________   _________  ________
   *    |\  ___ \ |\   ___  \|\   ___ \|\   __  \|\   __  \|\   __  \|\  \|\   ___  \|\___   ___\\   ____\
   *    \ \   __/|\ \  \\ \  \ \  \_|\ \ \  \|\  \ \  \|\  \ \  \|\  \ \  \ \  \\ \  \|___ \  \_\ \  \___|_
   *     \ \  \_|/_\ \  \\ \  \ \  \ \\ \ \  \\\  \ \   ____\ \  \\\  \ \  \ \  \\ \  \   \ \  \ \ \_____  \
   *      \ \  \_|\ \ \  \\ \  \ \  \_\\ \ \  \\\  \ \  \___|\ \  \\\  \ \  \ \  \\ \  \   \ \  \ \|____|\  \
   *       \ \_______\ \__\\ \__\ \_______\ \_______\ \__\    \ \_______\ \__\ \__\\ \__\   \ \__\  ____\_\  \
   *        \|_______|\|__| \|__|\|_______|\|_______|\|__|     \|_______|\|__|\|__| \|__|    \|__| |\_________\
   *                                                                                               \|_________|
   *
   *
   */
  static string $LICENSING_API_ENDPOINT = "qe_fid_licensing_api_endpoint_url";
  static string $FID_API_ENDPOINT = "qe_fid_api_endpoint_url";
  static string $FID_PLUGIN_LICENSE_WEBHOOK = "qe-fid-renew-license-webhook";

  /***
   *     ________  ________  ___          ___  __    _______       ___    ___ ________
   *    |\   __  \|\   __  \|\  \        |\  \|\  \ |\  ___ \     |\  \  /  /|\   ____\
   *    \ \  \|\  \ \  \|\  \ \  \       \ \  \/  /|\ \   __/|    \ \  \/  / | \  \___|_
   *     \ \   __  \ \   ____\ \  \       \ \   ___  \ \  \_|/__   \ \    / / \ \_____  \
   *      \ \  \ \  \ \  \___|\ \  \       \ \  \\ \  \ \  \_|\ \   \/  /  /   \|____|\  \
   *       \ \__\ \__\ \__\    \ \__\       \ \__\\ \__\ \_______\__/  / /       ____\_\  \
   *        \|__|\|__|\|__|     \|__|        \|__| \|__|\|_______|\___/ /       |\_________\
   *                                                             \|___|/        \|_________|
   *
   *
   */
  static string $FID_LICENSE_KEY = "qe_fid_license_key";
  //dynamic api keys recieved only when there has been a successful license activation
  //deleted and refreshed when the license has been deactivated
  static string $API_KEY_ACCESS = "qe_fid_access_key";
  static string $API_KEY_REFRESH = "qe_fid_refresh_key";


  /***
   *     ________  ________  _____ ______   ___  ________
   *    |\   __  \|\   ___ \|\   _ \  _   \|\  \|\   ___  \
   *    \ \  \|\  \ \  \_|\ \ \  \\\__\ \  \ \  \ \  \\ \  \
   *     \ \   __  \ \  \ \\ \ \  \\|__| \  \ \  \ \  \\ \  \
   *      \ \  \ \  \ \  \_\\ \ \  \    \ \  \ \  \ \  \\ \  \
   *       \ \__\ \__\ \_______\ \__\    \ \__\ \__\ \__\\ \__\
   *        \|__|\|__|\|_______|\|__|     \|__|\|__|\|__| \|__|
   *
   *
   *
   */
  static string $FID_ADMIN_EMAIL = "qe_fid_admin_email";


  /***
   *     ________   _______    ________   ___      ___  ___   ________   _______
   *    |\   ____\ |\  ___ \  |\   __  \ |\  \    /  /||\  \ |\   ____\ |\  ___ \
   *    \ \  \___|_\ \   __/| \ \  \|\  \\ \  \  /  / /\ \  \\ \  \___| \ \   __/|
   *     \ \_____  \\ \  \_|/__\ \   _  _\\ \  \/  / /  \ \  \\ \  \     \ \  \_|/__
   *      \|____|\  \\ \  \_|\ \\ \  \\  \|\ \    / /    \ \  \\ \  \____ \ \  \_|\ \
   *        ____\_\  \\ \_______\\ \__\\ _\ \ \__/ /      \ \__\\ \_______\\ \_______\
   *       |\_________\\|_______| \|__|\|__| \|__|/        \|__| \|_______| \|_______|
   *       \|_________|
   *
   *
   */
  static string $FID_USER_MAPPING_ROSTER = "qe_fid_id_user_mapping_roster";
  static string $FID_PLUGIN_NONCE = "qe_fid_plugin_nonce";
  static string $FID_LICENSE_USER_LIMIT = "qe_fid_license_user_limit";

  static string $WP_REST_NONCE_KEY = "wp_rest";



  /***
   *     ___  ___  _______   ________  ________  _______   ________  ________
   *    |\  \|\  \|\  ___ \ |\   __  \|\   ___ \|\  ___ \ |\   __  \|\   ____\
   *    \ \  \\\  \ \   __/|\ \  \|\  \ \  \_|\ \ \   __/|\ \  \|\  \ \  \___|_
   *     \ \   __  \ \  \_|/_\ \   __  \ \  \ \\ \ \  \_|/_\ \   _  _\ \_____  \
   *      \ \  \ \  \ \  \_|\ \ \  \ \  \ \  \_\\ \ \  \_|\ \ \  \\  \\|____|\  \
   *       \ \__\ \__\ \_______\ \__\ \__\ \_______\ \_______\ \__\\ _\ ____\_\  \
   *        \|__|\|__|\|_______|\|__|\|__|\|_______|\|_______|\|__|\|__|\_________\
   *                                                                   \|_________|
   *
   *
   */
  static string $HEADER_WP_REST_NONCE = "X-WP-Nonce";


  /***
   *     ________  _______  _________  _________  ___  ________   ________  ________
   *    |\   ____\|\  ___ \|\___   ___\\___   ___\\  \|\   ___  \|\   ____\|\   ____\
   *    \ \  \___|\ \   __/\|___ \  \_\|___ \  \_\ \  \ \  \\ \  \ \  \___|\ \  \___|_
   *     \ \_____  \ \  \_|/__  \ \  \     \ \  \ \ \  \ \  \\ \  \ \  \  __\ \_____  \
   *      \|____|\  \ \  \_|\ \  \ \  \     \ \  \ \ \  \ \  \\ \  \ \  \|\  \|____|\  \
   *        ____\_\  \ \_______\  \ \__\     \ \__\ \ \__\ \__\\ \__\ \_______\____\_\  \
   *       |\_________\|_______|   \|__|      \|__|  \|__|\|__| \|__|\|_______|\_________\
   *       \|_________|                                                       \|_________|
   *
   *
   */
  static string $FID_PLUGIN_DISPLAY_SETTINGS = "qe_fid_plugin_display_settings";
  static string $FID_PLUGIN_LOGIN_GUARD_STATE = "qe_fid_plugin_login_guard_state";

  /***
   *     _______   ________  ________  ________  ________  ________
   *    |\  ___ \ |\   __  \|\   __  \|\   __  \|\   __  \|\   ____\
   *    \ \   __/|\ \  \|\  \ \  \|\  \ \  \|\  \ \  \|\  \ \  \___|_
   *     \ \  \_|/_\ \   _  _\ \   _  _\ \  \\\  \ \   _  _\ \_____  \
   *      \ \  \_|\ \ \  \\  \\ \  \\  \\ \  \\\  \ \  \\  \\|____|\  \
   *       \ \_______\ \__\\ _\\ \__\\ _\\ \_______\ \__\\ _\ ____\_\  \
   *        \|_______|\|__|\|__|\|__|\|__|\|_______|\|__|\|__|\_________\
   *                                                         \|_________|
   *
   */

  static string $FID_ERROR_CODE_INVALID_API_LICENSE = "Invalid token";
  static array $FID_ERROR_CODES = [
    "License Is SUSPENDED",
    "Invalid License Token",
    "Invalid License user",
    "Invalid License user Allowance",
    "Invalid License Allowance",
    "License Is SUSPENDED",
    "No License Supplied",
    "License Is Not Active",
    "Invalid Plugin Version Please upgrade",
    "Invalid License Version Please upgrade",
  ];
}
