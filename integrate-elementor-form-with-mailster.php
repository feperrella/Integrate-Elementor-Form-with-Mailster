<?php
/**
 * Plugin Name:       Integrate Elementor Form with Mailster
 * Plugin URI:        https://github.com/feperrella/Integrate-Elementor-Form-with-Mailster
 * Description:       Adds Mailster as an Action After Submit in Elementor Pro Forms.
 * Author:            Fernando A. Perrella
 * Author URI:        https://www.fapnet.com.br
 * Version:           1.1.3
 * License:           GPLv3
 * Text Domain:       integrate-elementor-mailster
 * Domain Path:       /languages
 * Requires at least: 4.9
 * Requires PHP:      7.0
 *
 * Integrate Elementor Form with Mailster is free software: you can
 * redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or any later version.
 *
 * Integrate Elementor Form with Mailster is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Integrate Elementor Form with Mailster. If not, see
 * <https://www.gnu.org/licenses/gpl-3.0.txt>.
 *
 * @package Integrate_Elementor_Form_with_Mailster
 * @author  Fernando Perrella <fernando@fapnet.com.br>
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'INTEGRATE_ELEMENTOR_MAILSTER_PATH' ) ) {
	define( 'INTEGRATE_ELEMENTOR_MAILSTER_PATH', __FILE__ );
}

// Include the main Integrate Elementor Form with Mailster class.
if ( ! class_exists( 'Integrate_Elementor_Form_With_Mailster', false ) ) {
	include_once dirname( INTEGRATE_ELEMENTOR_MAILSTER_PATH ) . '/includes/class-integrate-elementor-form-with-mailster.php';
}
