<?php
/**
 * Plugin Name: Famous Birthdays
 * Description: Place a widget and/or shortcode to display three famous birthdays that fall on the current day.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: http://famousbirthdayplugin.com
 */

// This file may not be executed directly
if (!defined('ABSPATH')) {
 exit;
}

if (!class_exists('FamousBirthdays')):

 final class FamousBirthdays
 {
   private static $instance;

   public $settings;

    public static function instantiate()
    {
  		if (!isset(self::$instance) && !self::$instance instanceof FamousBirthdays) {
  			self::$instance = new FamousBirthdays;
  			self::$instance->includes();
        self::$instance->loadSettings();
  		}
  		return self::$instance;
    }

    public function includes()
    {

      if (!defined('FAMBDAY_PATH')) {
			  define('FAMBDAY_PATH', plugin_dir_path( __FILE__ ));
		  }

      if (!defined('FAMBDAY_URL')) {
        define('FAMBDAY_URL', plugins_url('', __FILE__));
      }

      if (!defined('FAMBDAY_REMOTE_FILE_URL')) {
        define('FAMBDAY_REMOTE_FILE_URL', plugins_url('birthdaylist.txt', __FILE__));
      }

      if (!defined('FAMBDAY_PLUGIN_FILE')) {
        define('FAMBDAY_PLUGIN_FILE', __FILE__);
      }

      /*
       * The number of days to cache the remote file containing
       * the birthdays before updating it. Set to 0 to disable
       * caching.
       */
      if (!defined('FAMBDAY_CACHE_FILE_EXPIRY')) {
        define('FAMBDAY_CACHE_FILE_EXPIRY', 1);
      }

      require_once FAMBDAY_PATH . 'hooks.php';
      require_once FAMBDAY_PATH . 'FamousBirthdaysWidget.php';
      require_once FAMBDAY_PATH . 'FamousBirthdayUtility.php';

    }

    public function loadSettings()
    {
      $this->settings                          = new stdClass();
      $this->settings->date_text_color         = get_option('fambday_date_color', '#FFFFFF');
      $this->settings->primary_text_color      = get_option('fambday_text_color', '#000000');
      $this->settings->header_background_color = get_option('fambday_header_color', '#4c90af');
      $this->settings->body_background_color   = get_option('fambday_body_color', '#FFFFFF');
      $this->settings->title                   = stripslashes(get_option('fambday_text', 'Famous Birthdays On This Day'));
    }

    public static function shortcode()
    {
      ob_start();
      include FAMBDAY_PATH . 'widget-template.php';
      $content = ob_get_contents();
      ob_end_clean();
      return $content;
    }

    public static function load_scripts()
    {
      if (is_admin() && isset($_GET['page']) && $_GET['page'] == 'fambday') {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script('fambday', FAMBDAY_URL . '/admin-script.js', array('jquery', 'wp-color-picker'));
      }
    }

    public static function load_frontend_scripts()
    {
      wp_enqueue_style('fambday-widget-template', FAMBDAY_URL . '/widget-template.css');
    }

    public static function add_settings_page_to_menu()
    {
      add_options_page('Famous Birthdays', 'Famous Birthdays', 'manage_options', 'fambday', array('FamousBirthdays', 'display_settings_page_content'));
    }

    public static function display_settings_page_content()
    {
      include_once FAMBDAY_PATH . 'settings.php';
    }

    public static function add_famous_birthdays_shortcode()
    {
      add_shortcode('famous_birthdays', array('FamousBirthdays', 'shortcode'));
    }

    public static function register_famous_birthdays_widget()
    {
      register_widget('FamousBirthdaysWidget');
    }

    public static function handle_settings_save()
    {
      if (!isset($_POST['fambday_submit'])) {
        return;
      }

      $colors = array(
        'fambday_date_color'   => $_POST['fambday_date_color'],
        'fambday_text_color'   => $_POST['fambday_text_color'],
        'fambday_header_color' => $_POST['fambday_header_color'],
        'fambday_body_color'   => $_POST['fambday_body_color']
      );

      $hexPattern = '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/';

      foreach ($colors as $optionSlug => $color) {
        if (strlen($color) !== 4 && strlen($color) !== 7) {
          FamousBirthdaysUtility::add_notice(array('error', $color . ' is not a valid color code'));
          return;
        }

        if (preg_match($hexPattern, $color) !== 1) {
          FamousBirthdaysUtility::add_notice(array('error', $color . ' is not a valid color code'));
          return;
        }
      }

      $headerText = sanitize_text_field($_POST['fambday_text']);

      foreach ($colors as $optionSlug => $color) {
        update_option($optionSlug, $color);
      }

      update_option('fambday_text', $headerText);

      FAMBDAY_get_instance()->loadSettings();
      FamousBirthdaysUtility::add_notice(array('updated', 'Famous Birthdays Settings Saved'));
      return;
    }

  }

endif;

function FAMBDAY_get_instance()
{
  return FamousBirthdays::instantiate();
}

FAMBDAY_get_instance();
