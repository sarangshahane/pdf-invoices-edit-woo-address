<?php
/**
 * Plugin Name: Edit WooCommerce Order Address for PDF Invoices.
 * Description: Enhance your WooCommerce experience with our plugin that seamlessly integrates with PDF Invoices. Now, easily modify the billing address of your orders before generating PDF Invoices. Our user-friendly solution conveniently places an edit button within the billing section of each order in the my-account area for quick and efficient updates. Elevate your order management process with this essential feature.
 * Author: Sarang A. Shahane
 * Version: 1.0.0
 * License: GPL v3
 * Text Domain: pdf-invoices-edit-woo-address
 *
 * @package pdf-invoices-edit-woo-address
 */

/**
 * The main plugin file.
 */
define( 'PDF_IEWA_FILE', __FILE__ );

require_once 'classes/class-pdf-iewa-loader.php';
