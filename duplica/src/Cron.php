<?php
namespace Codexpert\Duplica;

use Codexpert\Plugin\Base;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Cron
 * @author Codexpert <hi@codexpert.io>
 */
class Cron extends Base {

	public $plugin;
	public $slug;
	public $name;
	public $server;
	public $version;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $plugin['TextDomain'];
		$this->name		= $plugin['Name'];
		$this->server	= $plugin['server'];
		$this->version	= $plugin['Version'];
	}

	/**
	 * Installer. Runs once when the plugin in activated.
	 *
	 * @since 1.0
	 */
	public function install() {
		/**
		 * Schedule an event to sync help docs
		 */
		if ( ! wp_next_scheduled ( 'codexpert-daily' )) {
		    wp_schedule_event( time(), 'daily', 'codexpert-daily' );
		}
	}

	/**
	 * Uninstaller. Runs once when the plugin in deactivated.
	 *
	 * @since 1.0
	 */
	public function uninstall() {
		/**
		 * Remove scheduled hooks
		 */
		wp_clear_scheduled_hook( 'codexpert-daily' );
	}
}