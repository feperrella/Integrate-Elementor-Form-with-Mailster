<?php
/**
 * Class Mailster_Action_After_Submit
 *
 * @package Integrate_Elementor_Form_with_Mailster/Includes/Mailster_Action_After_Submit
 * @since   1.0.0
 * @version 1.0.0
 * @see     https://developers.elementor.com/custom-form-action/
 *
 * Custom elementor form action after submit to add a subsciber to
 * Mailster lists via API
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Mailster_Action_After_Submit extends \ElementorPro\Modules\Forms\Classes\Action_Base {

	/**
	 * Get Name
	 *
	 * Return the action name
	 *
	 * @access public
	 * @return string
	 */
	public function get_name() {
		return 'mailster';
	}

	/**
	 * Get Label
	 *
	 * Returns the action label
	 *
	 * @access public
	 * @return string
	 */
	public function get_label() {
		return __( 'Mailster', 'integrate-elementor-mailster' );
	}

	/**
	 * Register Settings Section
	 *
	 * Registers the Action controls
	 *
	 * @access public
	 * @param \Elementor\Widget_Base $widget
	 */
	public function register_settings_section( $widget ) {
		$widget->start_controls_section(
			'mailster_section',
			[
				'label'     => __( 'Mailster', 'integrate-elementor-mailster' ),
				'condition' => [
					'submit_actions' => $this->get_name(),
				],
			]
		);

		$widget->add_control(
			'mailster_important_note',
			[
				'label'       => __( 'Important Note', 'integrate-elementor-mailster' ),
				'label_block' => true,
				'type'        => \Elementor\Controls_Manager::RAW_HTML,
				'separator'   => 'before',
				'raw'         => __( '<br>For the integration to work you have to rename the IDs for the respective fields with: "<b>firstname</b>", "<b>lastname</b>" and "<b>email</b>", the email ID is required.', 'integrate-elementor-mailster' ),
			]
		);

		$widget->add_control(
			'mailster_list',
			[
				'label'       => __( 'Mailster List ID', 'integrate-elementor-mailster' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'separator'   => 'before',
				'description' => __( 'The list id you want to subscribe a user to, you can type multiple lists separeted by a comma.', 'integrate-elementor-mailster' ),
			]
		);

		$widget->add_control(
			'mailster_overwrite',
			[
				'label'        => __( 'Overwrite', 'integrate-elementor-mailster' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'integrate-elementor-mailster' ),
				'label_off'    => __( 'No', 'integrate-elementor-mailster' ),
				'return_value' => true,
				'default'      => true,
				'description'  => __( 'Overwrite the user with the new information.', 'integrate-elementor-mailster' ),
			]
		);

		$widget->add_control(
			'mailster_opt_in',
			[
				'label'        => __( 'Double Opt in', 'integrate-elementor-mailster' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'integrate-elementor-mailster' ),
				'label_off'    => __( 'No', 'integrate-elementor-mailster' ),
				'return_value' => true,
				'default'      => true,
				'description'  => __( 'Enable the confirmation email for the newsletter signup.', 'integrate-elementor-mailster' ),
			]
		);

		$widget->end_controls_section();
	}

	/**
	 * Run
	 *
	 * Runs the action after submit
	 *
	 * @access public
	 * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record
	 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
	 */
	public function run( $record, $ajax_handler ) {
		$settings = $record->get( 'form_settings' );

		//  Make sure that there is a Mailster list ID
		if ( empty( $settings['mailster_list'] ) ) {
			return;
		}

		// Get submitetd Form data
		$raw_fields = $record->get( 'fields' );

		// Normalize the Form Data
		$fields = [];
		foreach ( $raw_fields as $id => $field ) {
			$fields[ $id ] = $field['value'];
		}

		// Send the request to Mailster
		// define to overwrite existing users
		$overwrite = $settings['mailster_overwrite'];

		// add with double opt in
		$double_opt_in = $settings['mailster_opt_in'];

		// prepare the $mailster_data from the submitted fields. only the email is required
		// Based on the param list at https://kb.mailster.co/mailster-for-developers/
		$mailster_data = [
			'email'     => $fields['email'],
			'firstname' => $fields['firstname'],
			'lastname'  => $fields['lastname'],
			'ip'        => \ElementorPro\Core\Utils::get_client_ip(),
			'status'    => $double_opt_in ? 0 : 1,
		];

		// add a new subscriber and $overwrite it if exists
		$subscriber_id = mailster( 'subscribers' )->add( $mailster_data, $overwrite );

		// if result isn't a WP_error assign the lists
		if ( ! is_wp_error( $subscriber_id ) ) {

			// your list ids
			$list_ids = explode( ',', $settings['mailster_list'] );
			mailster( 'subscribers' )->assign_lists( $subscriber_id, $list_ids );

		} else {
			// actions if adding fails. $subscriber_id is a WP_Error object
		}
	}

	/**
	 * On Export
	 *
	 * Clears form settings on export
	 * @access Public
	 * @param array $element
	 */
	public function on_export( $element ) {
		unset(
			$element['mailster_important_note'],
			$element['mailster_list'],
			$element['mailster_overwrite'],
			$element['mailster_opt_in']
		);
	}
}
