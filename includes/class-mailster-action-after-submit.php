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
				'raw'       => __( '<small>Maislter\'s <b>Checkbox</b> works like <b>Acceptance</b> field.<br />Mailster\'s <b>Dropdown</b> works like <b>Select</b> field.</small>', 'integrate-elementor-mailster' ),
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

		// Get submitted Form data
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
		if ( $settings['list'] ) {
			foreach ( $settings['list'] as $item ) {
				$mailster_data[ $item['list_options'] ] = $fields[ $item['list_form_id'] ];
			}
		}

		// Check for required Elementor version
		if ( version_compare( ELEMENTOR_VERSION, '3.0.0', '>=' ) ) {
			$mailster_data['ip'] = \ElementorPro\Core\Utils::get_client_ip();
		} else {
			$mailster_data['ip'] = \ElementorPro\Classes\Utils::get_client_ip();
		}

		$mailster_data['status'] = ( $double_opt_in ? 0 : 1 );

		// add a new subscriber and $overwrite it if exists
		$subscriber_id = mailster( 'subscribers' )->add( $mailster_data, $overwrite );

		// if result isn't a WP_error assign the lists
		if ( ! is_wp_error( $subscriber_id ) ) {

			// your list ids
			mailster( 'subscribers' )->assign_lists( $subscriber_id, $settings['mailster_list'] );

		} else {
			// actions if adding fails. $subscriber_id is a WP_Error object
			return new WP_Error( 'Action failed', $subscriber_id );
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
			$element['mailster_list'],
			$element['mailster_overwrite'],
			$element['mailster_opt_in'],
			$element['mailster_customfield'],
			$element['mailster_options'],
			$element['important_note']
		);
	}
}
