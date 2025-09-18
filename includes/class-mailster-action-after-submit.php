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
				'label'       => __( 'Default Mailster Lists', 'integrate-elementor-mailster' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $mailster_lists_options,
				'description' => __( 'These lists will be used as defaults. Users can select additional lists if the user selection field is enabled.', 'integrate-elementor-mailster' ),
			]
		);

		$widget->add_control(
			'mailster_user_selection',
			[ 
				'label'        => __( 'Allow User List Selection', 'integrate-elementor-mailster' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'integrate-elementor-mailster' ),
				'label_off'    => __( 'No', 'integrate-elementor-mailster' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => __( 'Allow users to select additional mailing lists from the form. User selections will be added to the default lists above.', 'integrate-elementor-mailster' ),
			]
		);

		$widget->add_control(
			'mailster_user_available_lists',
			[ 
				'label'       => __( 'Lists Available for User Selection', 'integrate-elementor-mailster' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $mailster_lists_options,
				'condition'   => [
					'mailster_user_selection' => 'yes',
				],
				'description' => __( 'Choose which mailing lists users can select from in the form. Default lists above will always be applied.', 'integrate-elementor-mailster' ),
			]
		);

		$widget->add_control(
			'mailster_show_descriptions',
			[ 
				'label'        => __( 'Show List Descriptions', 'integrate-elementor-mailster' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'integrate-elementor-mailster' ),
				'label_off'    => __( 'No', 'integrate-elementor-mailster' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'mailster_user_selection' => 'yes',
				],
				'description'  => __( 'Show descriptions below each mailing list option.', 'integrate-elementor-mailster' ),
			]
		);

		$widget->add_control(
			'mailster_overwrite',
			[ 
				'label'        => __( 'Overwrite', 'integrate-elementor-mailster' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'integrate-elementor-mailster' ),
				'label_off'    => __( 'No', 'integrate-elementor-mailster' ),
				'return_value' => 'yes',
				'default'      => 'yes',
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
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => __( 'Enable the confirmation email for the newsletter signup.', 'integrate-elementor-mailster' ),
			]
		);

		// Removed GDPR control - handle GDPR as a regular field mapping instead

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
			'gdpr'      => __( 'GDPR Consent', 'integrate-elementor-mailster' ),
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
				'raw'       => __( '<small>Mailster\'s <b>Checkbox</b> works like <b>Acceptance</b> field.<br />Mailster\'s <b>Dropdown</b> works like <b>Select</b> field.<br />For <b>GDPR compliance</b>: Add an Acceptance field to your form, then map it to "GDPR Consent" in the Custom Fields section above. This will store a timestamp when consent is given.</small>', 'integrate-elementor-mailster' ),
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
		// Check if Mailster is active and available
		if ( ! function_exists( 'mailster' ) ) {
			$ajax_handler->add_error_message( 'Mailster plugin is not active or available.' );
			return;
		}

		$settings = $record->get( 'form_settings' );

		// Get submitted Form data
		$raw_fields = $record->get( 'fields' );

		// Normalize and sanitize the Form Data
		$fields = [];
		foreach ( $raw_fields as $id => $field ) {
			if ( is_array( $field['value'] ) ) {
				$fields[ sanitize_key( $id ) ] = array_map( 'sanitize_text_field', $field['value'] );
			} else {
				$fields[ sanitize_key( $id ) ] = sanitize_text_field( $field['value'] );
			}
		}

		// Get admin-configured default lists
		$admin_list_ids = [];
		if ( ! empty( $settings['mailster_list'] ) && is_array( $settings['mailster_list'] ) ) {
			foreach ( $settings['mailster_list'] as $list_id ) {
				// Ensure proper type checking to prevent fatal errors
				$list_id = is_string( $list_id ) ? $list_id : (string) $list_id;
				if ( is_numeric( $list_id ) && intval( $list_id ) > 0 ) {
					$admin_list_ids[] = intval( $list_id );
				}
			}
		}

		// Get user-selected lists from frontend field and validate against admin-configured available lists
		$user_list_ids = [];
		if ( ! empty( $settings['mailster_user_selection'] ) && $settings['mailster_user_selection'] === 'yes' ) {
			// Get admin-configured available lists for users
			$available_for_users = $settings['mailster_user_available_lists'] ?? [];
			if ( ! empty( $available_for_users ) && is_array( $available_for_users ) ) {
				// Process user selections from form - look for any field with mailster-user-lists type
				$mailster_field_found = false;
				foreach ( $raw_fields as $field_id => $field_data ) {
					if ( isset( $field_data['type'] ) && $field_data['type'] === 'mailster-user-lists' ) {
						$mailster_field_found = true;
						
						// Get the value - prefer raw_value for arrays (checkboxes), fallback to value
						$field_value = $field_data['raw_value'] ?? $field_data['value'] ?? '';
						
						if ( is_array( $field_value ) && ! empty( $field_value ) ) {
							foreach ( $field_value as $list_id ) {
								$list_id = intval( $list_id );
								// Ensure type safety and only accept user selections that are in the admin-configured available lists
								if ( $list_id > 0 && in_array( $list_id, array_map( 'intval', (array) $available_for_users ), true ) ) {
									$user_list_ids[] = $list_id;
								}
							}
						}
						break; // Found the field, no need to continue
					}
				}
			}
		}

		// Simple logic: Always combine admin defaults + user selections (remove duplicates)
		$final_list_ids = array_unique( array_merge( $admin_list_ids, $user_list_ids ) );

		// Ensure we have at least one valid list
		if ( empty( $final_list_ids ) ) {
			$ajax_handler->add_error_message( 'No valid Mailster lists specified.' );
			return;
		}

		// Use the final list IDs directly - trust that Elementor form configuration is correct
		// The assign_lists method will handle invalid IDs gracefully
		$valid_list_ids = array_map( 'intval', $final_list_ids );
		
		// Debug logging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Mailster Debug - Final list IDs: ' . print_r( $final_list_ids, true ) );
			error_log( 'Mailster Debug - Valid list IDs: ' . print_r( $valid_list_ids, true ) );
		}

		// Initialize mailster data array
		$mailster_data = [];

		// Send the request to Mailster
		// define to overwrite existing users
		$overwrite = ! empty( $settings['mailster_overwrite'] ) && $settings['mailster_overwrite'] === 'yes';

		// add with double opt in - check if explicitly set to 'yes'
		$double_opt_in = isset( $settings['mailster_opt_in'] ) && $settings['mailster_opt_in'] === 'yes';

		// GDPR is now handled as a regular field mapping - no special processing needed

		// prepare the $mailster_data from the submitted fields. only the email is required
		// Based on the param list at https://kb.mailster.co/mailster-for-developers/
		if ( ! empty( $settings['list'] ) && is_array( $settings['list'] ) ) {
			foreach ( $settings['list'] as $item ) {
				if ( ! empty( $item['list_options'] ) && ! empty( $item['list_form_id'] ) && isset( $fields[ $item['list_form_id'] ] ) ) {
					$field_key = sanitize_key( $item['list_options'] );
					$field_value = $fields[ $item['list_form_id'] ];
					
					// Special handling for GDPR field - convert checkbox to timestamp for built-in field
					if ( $field_key === 'gdpr' ) {
						// If GDPR field is checked/accepted, store timestamp; otherwise don't store anything
						if ( ! empty( $field_value ) && ( $field_value === '1' || $field_value === 'yes' || $field_value === 'on' ) ) {
							$mailster_data[ $field_key ] = time();
						}
						// If not checked, don't add the gdpr field at all
					} else {
						// For all other fields, including custom GDPR checkbox fields
						$mailster_data[ $field_key ] = $field_value;
					}
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

		// Set status: 0 for double opt-in (unconfirmed), 1 for confirmed
		$mailster_data['status'] = $double_opt_in ? 0 : 1;

		// add a new subscriber and $overwrite it if exists
		$subscriber_id = mailster( 'subscribers' )->add( $mailster_data, $overwrite );

		// Debug logging (remove in production)
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Mailster Integration Debug - Subscriber ID: ' . ( is_wp_error( $subscriber_id ) ? 'ERROR' : $subscriber_id ) );
			error_log( 'Mailster Integration Debug - Double Opt-in: ' . ( $double_opt_in ? 'true' : 'false' ) );
			error_log( 'Mailster Integration Debug - Overwrite: ' . ( $overwrite ? 'true' : 'false' ) );
			error_log( 'Mailster Integration Debug - List IDs: ' . implode( ', ', $valid_list_ids ) );
		}

		// if result isn't a WP_error assign the lists and handle GDPR
		if ( ! is_wp_error( $subscriber_id ) ) {

			// Handle list assignments and removals intelligently
			$list_confirmation_status = $double_opt_in ? false : time();
			
			// Check if subscriber already exists to determine if we need to handle removals
			$existing_subscriber = mailster( 'subscribers' )->get_by_mail( $mailster_data['email'] );
			$is_existing_subscriber = ! empty( $existing_subscriber );
			
			if ( $is_existing_subscriber && ! empty( $user_list_ids ) ) {
				// For existing subscribers with user selections, handle selective list management
				// Get current lists (IDs only)
				$current_user_lists = mailster( 'subscribers' )->get_lists( $subscriber_id, true );
				$available_for_users = $settings['mailster_user_available_lists'] ?? [];
				$available_for_users_int = array_map( 'intval', (array) $available_for_users );
				
				// Find lists to remove (were in available lists but not selected by user)
				$currently_subscribed_user_lists = array_intersect( $current_user_lists, $available_for_users_int );
				$lists_to_remove = array_diff( $currently_subscribed_user_lists, $user_list_ids );
				
				// Remove unselected lists
				if ( ! empty( $lists_to_remove ) ) {
					mailster( 'subscribers' )->unassign_lists( $subscriber_id, $lists_to_remove );
				}
				
				// Assign new lists (admin defaults + user selections)
				$list_assignment_success = mailster( 'subscribers' )->assign_lists( $subscriber_id, $valid_list_ids, false, $list_confirmation_status );
			} else {
				// For new subscribers or when overwriting, use the standard approach
				$remove_old_lists = $overwrite && $is_existing_subscriber;
				$list_assignment_success = mailster( 'subscribers' )->assign_lists( $subscriber_id, $valid_list_ids, $remove_old_lists, $list_confirmation_status );
			}

			// Debug logging for list assignment
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Mailster Debug - List assignment success: ' . ( $list_assignment_success ? 'true' : 'false' ) );
				error_log( 'Mailster Debug - Existing subscriber: ' . ( $is_existing_subscriber ? 'true' : 'false' ) );
				error_log( 'Mailster Debug - Overwrite mode: ' . ( $overwrite ? 'true' : 'false' ) );
				if ( isset( $lists_to_remove ) ) {
					error_log( 'Mailster Debug - Lists removed: ' . implode( ', ', $lists_to_remove ) );
				}
			}

			// Don't fail the form if list assignment returns false - the subscriber was created/updated successfully
			// The assign_lists method can return false even when lists are properly assigned (e.g., if lists were already assigned)
			if ( ! $list_assignment_success ) {
				// Log the issue but don't stop the form submission
				error_log( 'Mailster list assignment returned false for subscriber ID: ' . $subscriber_id . ', but continuing with form submission.' );
			}

			// GDPR compliance is properly handled via the built-in 'gdpr' field mapping

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
			$element['mailster_user_selection'],
			$element['mailster_user_available_lists'],
			$element['mailster_show_descriptions'],
			$element['mailster_overwrite'],
			$element['mailster_opt_in'],
			$element['mailster_customfield'],
			$element['mailster_options'],
			$element['important_note']
		);
		
		return $element;
	}
}