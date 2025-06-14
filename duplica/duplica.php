<?php
/**
 * Plugin Name:			Duplica
 * Plugin URI:			https://pluggable.io/plugin/duplica
 * Description:			Duplicate Posts, Pages, Custom Posts or Users - everything with a single click.
 * Version:				0.16
 * Requires at least:	6.0
 * Requires PHP:		7.4
 * Author:				Codexpert, Inc
 * Author URI:			https://codexpert.io
 * License:				GPL v2 or later
 * License URI:			https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:			duplica
 * Domain Path:			/languages
 *
 * Duplica is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Duplica is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

namespace Codexpert\Duplica;

use Codexpert\Plugin\Widget;
use Codexpert\Plugin\Notice;
use Pluggable\Marketing\Feature;
use Pluggable\Marketing\Survey;
use Pluggable\Marketing\Deactivator;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for the plugin
 * @package Plugin
 * @author Codexpert <hi@codexpert.io>
 */
final class Plugin {

	public $plugin;
	
	/**
	 * Plugin instance
	 * 
	 * @access private
	 * 
	 * @var Plugin
	 */
	private static $_instance;

	/**
	 * The constructor method
	 * 
	 * @access private
	 * 
	 * @since 0.9
	 */
	private function __construct() {

		/**
		 * Includes required files
		 */
		$this->include();

		/**
		 * Defines contants
		 */
		$this->define();

		/**
		 * Run actual hooks
		 */
		$this->hook();
	}

	/**
	 * Includes files
	 * 
	 * @access private
	 * 
	 * @uses composer
	 * @uses psr-4
	 */
	private function include() {
		require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );
	}

	/**
	 * Define variables and constants
	 * 
	 * @access private
	 * 
	 * @uses get_plugin_data
	 * @uses plugin_basename
	 */
	private function define() {

		/**
		 * Define some constants
		 * 
		 * @since 0.9
		 */
		define( 'DUPLICA', __FILE__ );
		define( 'DUPLICA_DIR', dirname( DUPLICA ) );
		define( 'DUPLICA_ASSET', plugins_url( 'assets', DUPLICA ) );
		define( 'DUPLICA_DEBUG', apply_filters( 'duplica_debug', false ) );

		/**
		 * The plugin data
		 * 
		 * @since 0.9
		 * @var $plugin
		 */
		$this->plugin					= get_plugin_data( DUPLICA, true, false );
		$this->plugin['basename']		= plugin_basename( DUPLICA );
		$this->plugin['file']			= DUPLICA;
		$this->plugin['server']			= apply_filters( 'duplica_server', 'https://my.pluggable.io' );
		$this->plugin['min_php']		= '7.4';
		$this->plugin['min_wp']			= '6.0';
		
		$this->plugin['hash_deactivator'] = '1495015c-6a30-40ff-b198-7cef630460bb';
		$this->plugin['hash_survey']      = '31d033e6-a79b-4e56-8d4f-c0ef0337ce21';
		$this->plugin['hash_wizard']      = '3b6fae32-2103-4429-8451-2029ecd50607';
	}

	/**
	 * Hooks
	 * 
	 * @access private
	 * 
	 * Executes main plugin features
	 *
	 * To add an action, use $instance->action()
	 * To apply a filter, use $instance->filter()
	 * To register a shortcode, use $instance->register()
	 * To add a hook for logged in users, use $instance->priv()
	 * To add a hook for non-logged in users, use $instance->nopriv()
	 * 
	 * @return void
	 */
	private function hook() {

		if( is_admin() ) :

			/**
			 * Admin facing hooks
			 */
			$admin = new Admin( $this->plugin );
			$admin->activate( 'install' );
			$admin->action( 'plugins_loaded', 'i18n' );
			$admin->action( 'admin_footer', 'modal' );
			$admin->action( 'admin_enqueue_scripts', 'enqueue_scripts' );
			$admin->filter( 'post_row_actions', 'post_duplicate_menu', 99, 2 );
			$admin->filter( 'page_row_actions', 'post_duplicate_menu', 99, 2 );
			$admin->filter( 'user_row_actions', 'user_duplicate_menu', 99, 2 );
			$admin->filter( "plugin_action_links", 'plugin_duplicate_menu', 10, 2 );
			$admin->filter( 'plugin_row_meta', 'plugin_row_meta', 10, 2 );
			$admin->action( 'admin_footer_text', 'footer_text' );
			$admin->filter( 'admin_notices', 'show_admin_notices' );
			$admin->filter( 'admin_body_class', 'add_body_class' );
			$admin->action( 'cx-settings-sidebar', 'show_easycommerce_promo' );

			/**
			 * Settings related hooks
			 */
			$settings = new Settings( $this->plugin );
			$settings->action( 'plugins_loaded', 'init_menu' );

			/**
			 * Renders different norices
			 * 
			 * @package Codexpert\Plugin
			 * 
			 * @author Codexpert <hi@codexpert.io>
			 */
			$notice = new Notice( $this->plugin );

			/**
			 * Shows a popup window asking why a user is deactivating the plugin
			 * 
			 * @package Pluggable\Marketing
			 * 
			 * @author Pluggable <hi@pluggable.io>
			 */
			$deactivator = new Deactivator( $this->plugin );

			/**
			 * Alters featured plugins
			 * 
			 * @package Pluggable\Marketing
			 * 
			 * @author Pluggable <hi@pluggable.io>
			 */
			// $feature = new Feature( $this->plugin, [ 'reserved' => [] ] );

			/**
			 * Asks to participate in a survey
			 * 
			 * @package Pluggable\Marketing
			 * 
			 * @author Pluggable <hi@pluggable.io>
			 */
			$survey = new Survey( $this->plugin );

		else : // ! is_admin() ?

			/**
			 * Front facing hooks
			 */
			$front = new Front( $this->plugin );
			$front->action( 'wp_footer', 'modal' );
			$front->action( 'wp_enqueue_scripts', 'enqueue_scripts' );
			$front->action( 'admin_bar_menu', 'add_admin_bar', 99 );

		endif;

		/**
		 * Cron facing hooks
		 */
		$cron = new Cron( $this->plugin );
		$cron->activate( 'install' );
		$cron->deactivate( 'uninstall' );

		/**
		 * AJAX related hooks
		 */
		$ajax = new AJAX( $this->plugin );
		$ajax->priv( 'duplica-duplicate', 'duplicate_post' );
		$ajax->priv( 'duplica-duplicate-user', 'duplicate_user' );
	}

	/**
	 * Cloning is forbidden.
	 * 
	 * @access public
	 */
	public function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 * 
	 * @access public
	 */
	public function __wakeup() { }

	/**
	 * Instantiate the plugin
	 * 
	 * @access public
	 * 
	 * @return $_instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

Plugin::instance();