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

class PluginMeta
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
  protected $plugin_slug = null;

  /**
   * Plugin Code version
   *
   */
  protected $version = null;

  /**
   * Plugin basename.
   *
   * @since    1.0.0
   *
   * @var      string
   */
  protected $plugin_basename = null;
  protected function __construct()
  {
    $plugin = Plugin::get_instance();
    $this->plugin_slug = $plugin->get_plugin_slug();
    $this->version = $plugin->get_plugin_version();

    $this->plugin_basename = plugin_basename(plugin_dir_path(realpath(dirname(__FILE__))) . $this->plugin_slug . '.php');
  }
}
