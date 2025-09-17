<?php
/**
 * Class Mailster_Action_After_Submit
 *
 * @package Integrate_Elementor_Form_with_Mailster/Includes/Mailster_Action_After_Submit
 * @since   1.0.0
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
	public function get_name(): string {
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
	public function get_label(): string {
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
	public function register_settings_section( $widget ): void {
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
			'mailster_options',
			[ 
				'label' => __( 'Mailster Options', 'integrate-elementor-mailster' ),
				'type'  => \Elementor\Controls_Manager::HEADING,
			]
		);

		// Get all Mailster lists.
		$mailster_lists         = mailster( 'lists' )->get();
		$mailster_lists_options = [];

		foreach ( $mailster_lists as $list ) :
			$mailster_lists_options[ $list->ID ] = $list->name;
		endforeach;

		$widget->add_control(
			'mailster_list',
			[ 
				'label'       => __( 'Mailster Lists', 'integrate-elementor-mailster' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $mailster_lists_options,
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

		if ( mailster_option( 'gdpr_forms' ) ) {
			$widget->add_control(
				'mailster_gdpr',
				[ 
					'label'        => __( 'GDPR Compliance', 'integrate-elementor-mailster' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => __( 'Yes', 'integrate-elementor-mailster' ),
					'label_off'    => __( 'No', 'integrate-elementor-mailster' ),
					'return_value' => true,
					'default'      => true,
					'description'  => __( 'Enable GDPR compliance, you need to create an Acceptance Field for the GDPR information and set the Required option to Yes.', 'integrate-elementor-mailster' ),
				]
			);
		}

		$widget->add_control(
			'mailster_customfield',
			[ 
				'label'     => __( 'Mailster Custom Fields', 'integrate-elementor-mailster' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$maislter_repeater = new \Elementor\Repeater();

		// Get Mailster custom fields.
		$customfields      = mailster()->get_custom_fields();
		$customfield_array = [];

		foreach ( $customfields as $id => $data ) :
			$customfield_array[ $id ] = $data['name'];
		endforeach;

		$defaultfields_array = [ 
			'email'     => __( 'Email', 'integrate-elementor-mailster' ),
			'firstname' => __( 'Name', 'integrate-elementor-mailster' ),
			'lastname'  => __( 'Last Name', 'integrate-elementor-mailster' ),
		];

		// Create options array for repeater field.
		$options_array = array_merge( $defaultfields_array, $customfield_array );

		$maislter_repeater->add_control(
			'list_options',
			[ 
				'label'   => __( 'Custom Fields', 'integrate-elementor-mailster' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $options_array,
			]
		);

		$maislter_repeater->add_control(
			'list_form_id',
			[ 
				'label'       => __( 'Field ID', 'integrate-elementor-mailster' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => __( 'field ID', 'integrate-elementor-mailster' ),
			]
		);

		$widget->add_control(
			'list',
			[ 
				'label'       => __( 'Custom Fields', 'integrate-elementor-mailster' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $maislter_repeater->get_controls(),
				'default'     => [ 
					[ 
						'list_options' => 'email',
						'list_form_id' => 'email',
					],
				],
				'title_field' => '{{{ list_options }}}',
			]
		);

		$widget->add_control(
			'important_note',
			[ 
				'label'     => 'NOTE',
				'type'      => \Elementor\Controls_Manager::RAW_HTML,
				'separator' => 'before',
				'raw'       => __( '<small>Maislter\'s <b>Checkbox</b> works like <b>Acceptance</b> field.<br />Mailster\'s <b>Dropdown</b> works like <b>Select</b> field.<br />Enable <b>GDPR Compliance</b> in Mailster Privacy tab to show the option in this screen.</small>', 'integrate-elementor-mailster' ),
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
	public function run( $record, $ajax_handler ): void {
		$settings = $record->get( 'form_settings' );

		//  Make sure that there is a Mailster list ID and it's properly formatted
		if ( empty( $settings['mailster_list'] ) || ! is_array( $settings['mailster_list'] ) ) {
			return;
		}

		// Validate list IDs are numeric and exist
		$valid_list_ids = [];
		foreach ( $settings['mailster_list'] as $list_id ) {
			if ( is_numeric( $list_id ) && intval( $list_id ) > 0 ) {
				$valid_list_ids[] = intval( $list_id );
			}
		}

		if ( empty( $valid_list_ids ) ) {
			$ajax_handler->add_error_message( 'No valid Mailster lists specified.' );
			return;
		}

		// Get submitted Form data
		$raw_fields = $record->get( 'fields' );

		// Normalize and sanitize the Form Data
		$fields = [];
		foreach ( $raw_fields as $id => $field ) {
			$fields[ sanitize_key( $id ) ] = sanitize_text_field( $field['value'] );
		}

		// Initialize mailster data array
		$mailster_data = [];

		// Send the request to Mailster
		// define to overwrite existing users
		$overwrite = ! empty( $settings['mailster_overwrite'] );

		// add with double opt in
		$double_opt_in = ! empty( $settings['mailster_opt_in'] );

		// add with GDPR Compliance
		if ( ! empty( $settings['mailster_gdpr'] ) ) {
			$mailster_data['gdpr'] = time();
		}

		// prepare the $mailster_data from the submitted fields. only the email is required
		// Based on the param list at https://kb.mailster.co/mailster-for-developers/
		if ( ! empty( $settings['list'] ) && is_array( $settings['list'] ) ) {
			foreach ( $settings['list'] as $item ) {
				if ( ! empty( $item['list_options'] ) && ! empty( $item['list_form_id'] ) && isset( $fields[ $item['list_form_id'] ] ) ) {
					$mailster_data[ sanitize_key( $item['list_options'] ) ] = $fields[ $item['list_form_id'] ];
				}
			}
		}

		// Validate email is present and valid
		if ( empty( $mailster_data['email'] ) || ! is_email( $mailster_data['email'] ) ) {
			$ajax_handler->add_error_message( 'Valid email address is required for Mailster subscription.' );
			return;
		}

		// Get client IP using Elementor Pro Core Utils
		$mailster_data['ip'] = \ElementorPro\Core\Utils::get_client_ip();

		$mailster_data['status'] = ( $double_opt_in ? 0 : 1 );

		// add a new subscriber and $overwrite it if exists
		$subscriber_id = mailster( 'subscribers' )->add( $mailster_data, $overwrite );

		// if result isn't a WP_error assign the lists
		if ( ! is_wp_error( $subscriber_id ) ) {

			// your list ids
			mailster( 'subscribers' )->assign_lists( $subscriber_id, $valid_list_ids );

		} else {
			// actions if adding fails. $subscriber_id is a WP_Error object
			$ajax_handler->add_error_message( 'Mailster subscription failed: ' . $subscriber_id->get_error_message() );
		}
	}

	/**
	 * On Export
	 *
	 * Clears form settings on export
	 * @access Public
	 * @param array $element
	 */
	public function on_export( $element ): array {
		unset(
			$element['mailster_list'],
			$element['mailster_overwrite'],
			$element['mailster_opt_in'],
			$element['mailster_customfield'],
			$element['mailster_options'],
			$element['important_note']
		);
		
		return $element;
	}
}