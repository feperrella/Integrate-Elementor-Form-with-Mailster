<?php
/**
 * Main Integrate Elementor Form With Mailster Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @package Integrate_Elementor_Form_With_Mailster/Includes/Integrate_Elementor_Form_With_Mailster
 * @since   1.0.0
 */
final class Integrate_Elementor_Form_With_Mailster {

	/**
	 * Plugin Version
	 *
	 * @var string The plugin version.
	 */
	const VERSION = '1.2.1';

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum Elementor version required to run the plugin.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '3.0.0';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '7.4';

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @static
	 *
	 * @var Integrate_Elementor_Form_With_Mailster The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return Integrate_Elementor_Form_With_Mailster An instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function __construct() {

		add_action( 'init', [ $this, 'i18n' ] );
		add_action( 'plugins_loaded', [ $this, 'init' ] );

	}

	/**
	 * Load Textdomain
	 *
	 * Load plugin localization files.
	 *
	 * Fired by `init` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function i18n() {

		load_plugin_textdomain( 'integrate-elementor-mailster' );

	}

	/**
	 * Initialize the plugin
	 *
	 * Load the plugin only after Elementor and Mailster plugins are loaded.
	 * Checks for basic plugin requirements, if one check fail don't continue,
	 * if all check have passed load the files required to run the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init() {

		// Check if Elementor is installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin1' ] );
			return;
		}

		// Check if Elementor Pro is installed and activated
		if ( ! in_array( 'elementor-pro/elementor-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin2' ] );
			return;
		}

		// Check if Mailster is installed and activated
		if ( ! function_exists( 'mailster' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin3' ] );
			return;
		}

		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return;
		}

		// Add Plugin actions
		add_action( 'elementor_pro/init', [ $this, 'init_mailster_action' ] );
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_missing_main_plugin1() {

		if ( isset( $_GET['activate'] ) )
			unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor Pro */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'integrate-elementor-mailster' ),
			'<strong>' . esc_html__( 'Integrate Elementor Form With Mailster', 'integrate-elementor-mailster' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'integrate-elementor-mailster' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor Pro installed or activated.
	 *
	 * @since 1.1.1
	 *
	 * @access public
	 */
	public function admin_notice_missing_main_plugin2() {

		if ( isset( $_GET['activate'] ) )
			unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor Pro */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'integrate-elementor-mailster' ),
			'<strong>' . esc_html__( 'Integrate Elementor Form With Mailster', 'integrate-elementor-mailster' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor Pro', 'integrate-elementor-mailster' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Mailster installed or activated.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_missing_main_plugin3() {

		if ( isset( $_GET['activate'] ) )
			unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Mailster - Email Newsletter Plugin for WordPress */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'integrate-elementor-mailster' ),
			'<strong>' . esc_html__( 'Integrate Elementor Form With Mailster', 'integrate-elementor-mailster' ) . '</strong>',
			'<strong>' . esc_html__( 'Mailster - Email Newsletter Plugin for WordPress', 'integrate-elementor-mailster' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version() {

		if ( isset( $_GET['activate'] ) )
			unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'integrate-elementor-mailster' ),
			'<strong>' . esc_html__( 'Integrate Elementor Form With Mailster', 'integrate-elementor-mailster' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'integrate-elementor-mailster' ) . '</strong>',
			self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) )
			unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'integrate-elementor-mailster' ),
			'<strong>' . esc_html__( 'Integrate Elementor Form With Mailster', 'integrate-elementor-mailster' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'integrate-elementor-mailster' ) . '</strong>',
			self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Init Mailster Action
	 *
	 * Include action files and register them
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init_mailster_action() {

		// Include Extension files
		require_once dirname( INTEGRATE_ELEMENTOR_MAILSTER_PATH ) . '/includes/class-mailster-action-after-submit.php';

		// Instantiate the action class
		$mailster_action = new Mailster_Action_After_Submit();

		// Register the action with form widget
		\ElementorPro\Plugin::instance()->modules_manager->get_modules( 'forms' )->add_form_action( $mailster_action->get_name(), $mailster_action );

	}

}

Integrate_Elementor_Form_With_Mailster::instance();