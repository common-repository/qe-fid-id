<?php

/**
 * FiD Facial Recognition Admin Login
 *
 * @package   FiD Access Plugin
 * @author    FiD Access
 * @license   GPL-3.0
 * @copyright FiD Access
 *
 * @wordpress-plugin
 * Plugin Name:       FiD Facial Recognition Admin Login
 * Requires at least: 5.5
 * Requires PHP:      7.2
 * Plugin URI:        https://www.fidaccess.com
 * Description:       Secure your wp-admin with Facial recognition technology powered by FiD Access
 * Version:           1.2.4
 * Author:            FiD Access
 * Text Domain:       qe-fid-v1
 * License:           GPL-3.0 or later
 * License URI:       https://www.gnu.org/licenses/gpl.html
 * Domain Path:       /languages
 */

namespace QeFid\ID;

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}
require_once __DIR__ . '/autoload.php';

/**
 * Initialize Plugin
 *
 * @since 1.0.0
 */
function init()
{
  $wpr = Plugin::get_instance();
  $qe_fid_auth_admin = Admin::get_instance();
  $qe_fid_auth_rest_admin = Endpoint\Admin::get_instance();
  $qe_fid_gat_rest_admin = Endpoint\Gate::get_instance();
  $qe_fid_auth_login = Login::get_instance();
}
add_action('plugins_loaded', 'QeFid\\ID\\init');


/**
 * Register activation and deactivation hooks
 */
register_activation_hook(__FILE__, array('QeFid\\ID\\Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('QeFid\\ID\\Plugin', 'deactivate'));


/**
 * Add login js script file
 */
function collectiveray_load_js_script()
{

  wp_enqueue_script('qe-fid-admin-s-script', plugins_url('assets/js/admin.js', dirname(__FILE__)), array('jquery'), '4.5.6.7');
  wp_enqueue_script('qe-fid-gate-script', plugins_url('assets/js/gate.js', dirname(__FILE__)), array('jquery'), '4.5.6.8');
}

add_action('wp_enqueue_scripts', 'QeFid\\ID\\collectiveray_load_js_script');
