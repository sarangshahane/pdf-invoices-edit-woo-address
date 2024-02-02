<?php
/**
 * Pdf_Iewa_Loader.
 *
 * @package pdf-invoices-edit-woo-address
 * @since 1.0.0
 */

namespace PDF_IEWA;

use PDF_IEWA\Modules\My_Account\Pdf_Iewa_My_Account;

/**
 * Pdf_Iewa_Loader
 *
 * @since 1.0.0
 */
class Pdf_Iewa_Loader {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class Instance.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->define_constants();
		spl_autoload_register( array( $this, 'autoload_classes' ) );
		add_action( 'plugins_loaded', array( $this, 'load_plugin' ) );
	}

	/**
	 * Define the constants for the plugin use.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function define_constants() {
		define( 'PDF_IEWA_BASE', plugin_basename( PDF_IEWA_FILE ) );
		define( 'PDF_IEWA_DIR', plugin_dir_path( PDF_IEWA_FILE ) );
		define( 'PDF_IEWA_URL', plugins_url( '/', PDF_IEWA_FILE ) );
		define( 'PDF_IEWA_VER', '1.0.0' );
	}

	/**
	 * Autoload classes.
	 *
	 * @param string $class class name.
	 *
	 * @since 1.0.0
	 */
	public function autoload_classes( $class ) {
		if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
			return;
		}

		$class_to_load = $class;

		$filename = strtolower(
			preg_replace(
				[ '/^' . __NAMESPACE__ . '\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
				[ '', '$1-$2', '-', DIRECTORY_SEPARATOR ],
				$class_to_load
			)
		);

		$file = PDF_IEWA_DIR . $filename . '.php';

		// if the file readable, include it.
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}

	/**
	 * Load core functionalities of the plugin.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin() {

		$this->may_be_show_fail_to_load_notices();
		$this->load_textdomain();

		if( is_user_logged_in() ){
			Pdf_Iewa_My_Account::get_instance();
		}
	}

	/**
	 * Function to write all notices to display when required plugin is not installed or activated.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function may_be_show_fail_to_load_notices() {

		// Check is WooCommerce is installed or not.
		if ( ! class_exists( 'woocommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'show_wc_is_not_active_notice' ) );
			return;
		}

		// Check is PDF Invoices & Packing Slips for WooCommerce is installed or not.
		if ( ! class_exists( 'WPO_WCPDF' ) ) {
			add_action( 'admin_notices', array( $this, 'show_wc_api_manager_is_not_active_notice' ) );
			return;
		}
	}

	/**
	 * Display inactive notice if PDF Invoices & Packing Slips for WooCommerce is disabled.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function show_wc_api_manager_is_not_active_notice() {

		$plugin_url = 'https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/';

		echo '<div class="notice notice-error is-dismissible"><p>';
		// translators: 1$-2$: opening and closing <strong> tags, 3$-4$: link tags, takes to PDF Invoices & Packing Slips for WooCommerce on wp.org, 5$-6$: opening and closing link tags, leads to plugins.php in admin.
		echo sprintf( esc_html__( '%1$s"Edit WooCommerce Order Address for PDF Invoices." plugin is inactive.%2$s The %3$sPDF Invoices & Packing Slips for WooCommerce%4$s must be active for "Edit WooCommerce Order Address for PDF Invoices." plugin to work. Please %5$sinstall & activate PDF Invoices & Packing Slips for WooCommerce &raquo;%6$s', 'pdf-invoices-edit-woo-address' ), '<strong>', '</strong>', '<a href="' . esc_url( $plugin_url ) . '" target="_blank">', '</a>', '<a href="' . esc_url( $plugin_url ) . '" target="_blank">', '</a>' );
		echo '</p></div>';
	}

	/**
	 * Display the inactive notice if WooCommerce is disabled.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function show_wc_is_not_active_notice() {
		$install_url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'install-plugin',
					'plugin' => 'woocommerce',
				),
				admin_url( 'update.php' )
			),
			'install-plugin_woocommerce'
		);
		echo '<div class="notice notice-error is-dismissible"><p>';
		// translators: 1$-2$: opening and closing <strong> tags, 3$-4$: link tags, takes to woocommerce plugin on wp.org, 5$-6$: opening and closing link tags, leads to plugins.php in admin.
		echo sprintf( esc_html__( '%1$s"Edit WooCommerce Order Address for PDF Invoices." plugin is inactive.%2$s The %3$sWooCommerce plugin%4$s must be active for "Edit WooCommerce Order Address for PDF Invoices." plugin to work. Please %5$sinstall & activate WooCommerce &raquo;%6$s', 'pdf-invoices-edit-woo-address' ), '<strong>', '</strong>', '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . esc_url( $install_url ) . '">', '</a>' );
		echo '</p></div>';
	}

	/**
	 * Load the plugin's text domain for the translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		// Default languages directory.
		$lang_dir = PDF_IEWA_URL . 'languages/';

		/**
		 * Filters the languages directory path to use for plugin.
		 *
		 * @param string $lang_dir The languages directory path.
		 */
		$lang_dir = apply_filters( 'pdf_iewa_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter.
		global $wp_version;

		$get_locale = get_locale();

		if ( $wp_version >= 4.7 ) {
			$get_locale = get_user_locale();
		}

		/**
		 * Language Locale for plugin
		 *
		 * @var $get_locale The locale to use.
		 * Uses get_user_locale()` in WordPress 4.7 or greater,
		 * otherwise uses `get_locale()`.
		 */
		$locale = apply_filters( 'plugin_locale', $get_locale, 'pdf-invoices-edit-woo-address' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'pdf-invoices-edit-woo-address', $locale );

		// Setup paths to current locale file.
		$mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;
		$mofile_local  = $lang_dir . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/pdf-invoices-edit-woo-address/ folder.
			load_textdomain( 'pdf-invoices-edit-woo-address', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/pdf-invoices-edit-woo-address/languages/ folder.
			load_textdomain( 'pdf-invoices-edit-woo-address', $mofile_local );
		} else {
			// Load the default language files.
			load_plugin_textdomain( 'pdf-invoices-edit-woo-address', false, $lang_dir );
		}
	}

}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Pdf_Iewa_Loader::get_instance();
