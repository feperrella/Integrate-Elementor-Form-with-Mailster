<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor Form Field - Mailster User Lists
 *
 * Add a new "Mailster User Lists" field to Elementor form widget.
 *
 * @since 1.0.0
 */
class Elementor_Mailster_Lists extends \ElementorPro\Modules\Forms\Fields\Field_Base {

	/**
	 * Get field type.
	 *
	 * Retrieve mailster-user-lists field unique ID.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Field type.
	 */
	public function get_type(): string {
		return 'mailster-user-lists';
	}

	/**
	 * Get field name.
	 *
	 * Retrieve mailster-user-lists field label.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Field name.
	 */
	public function get_name(): string {
		return esc_html__( 'Mailster Lists', 'integrate-elementor-mailster' );
	}

	/**
	 * Render field output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param mixed $item
	 * @param mixed $item_index
	 * @param mixed $form
	 * @return void
	 */
	public function render( $item, $item_index, $form ): void {
		$form_id  = $form->get_id();
		$settings = $form->get_settings_for_display();

		// Check if user list selection is enabled and get settings
		$user_selection_enabled = false;
		$show_descriptions = true;
		$user_available_lists = [];


		// Find the Mailster action settings - check multiple possible locations
		if ( ! empty( $settings['submit_actions'] ) && in_array( 'mailster', $settings['submit_actions'], true ) ) {
			$user_selection_enabled = ( $settings['mailster_user_selection'] ?? 'no' ) === 'yes';
			$show_descriptions = ( $settings['mailster_show_descriptions'] ?? 'yes' ) === 'yes';
			$user_available_lists = $settings['mailster_user_available_lists'] ?? [];
		}

		// If still no settings found, also check if there's a direct action configuration
		if ( ! $user_selection_enabled && isset( $settings['form_fields'] ) ) {
			// Alternative way to access settings if the above doesn't work
			foreach ( $settings as $key => $value ) {
				if ( strpos( $key, 'mailster_user_selection' ) !== false && $value === 'yes' ) {
					$user_selection_enabled = true;
				}
				if ( strpos( $key, 'mailster_show_descriptions' ) !== false ) {
					$show_descriptions = $value === 'yes';
				}
				if ( strpos( $key, 'mailster_user_available_lists' ) !== false && is_array( $value ) ) {
					$user_available_lists = $value;
				}
			}
		}


		// If user selection is not enabled, don't render the field
		if ( ! $user_selection_enabled ) {
			return;
		}

		// If no lists are configured for user selection, don't render the field
		if ( empty( $user_available_lists ) || ! is_array( $user_available_lists ) ) {
			return;
		}

		// Get all Mailster lists
		if ( function_exists( 'mailster' ) ) {
			$all_mailster_lists = mailster( 'lists' )->get();
		} else {
			$all_mailster_lists = [];
		}

		// Filter to show only admin-configured lists for user selection
		$mailster_lists = [];
		$user_available_lists_int = array_map( 'intval', $user_available_lists );
		
		foreach ( $all_mailster_lists as $list ) {
			$list_id = intval( $list->ID );
			if ( in_array( $list_id, $user_available_lists_int, false ) ) { // Use loose comparison
				$mailster_lists[] = $list;
			}
		}

		$form->add_render_attribute(
			'mailster_user_lists' . $item_index,
			[ 
				'class' => 'mailster-lists',
				'for'   => $form_id . $item_index,
			]
		);

		$border_radius = '';
		if ( ! empty( $settings['field_border_radius'] ) ) {
			$border_radius = sprintf(
				'%s%s %s%s %s%s %s%s',
				esc_attr( $settings['field_border_radius']['top'] ?? 0 ),
				esc_attr( $settings['field_border_radius']['unit'] ?? 'px' ),
				esc_attr( $settings['field_border_radius']['right'] ?? 0 ),
				esc_attr( $settings['field_border_radius']['unit'] ?? 'px' ),
				esc_attr( $settings['field_border_radius']['bottom'] ?? 0 ),
				esc_attr( $settings['field_border_radius']['unit'] ?? 'px' ),
				esc_attr( $settings['field_border_radius']['left'] ?? 0 ),
				esc_attr( $settings['field_border_radius']['unit'] ?? 'px' )
			);
		}
		?>
		<style>
			.mailster-lists {
				border-radius: <?php echo esc_attr( $border_radius ); ?>;
				border: 1px solid <?php echo esc_attr( $settings['field_border_color'] ?? '#ddd' ); ?>;
				background: <?php echo esc_attr( $settings['field_background_color'] ?? '#ffffff' ); ?>;
				width: 100%;
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
				gap: 15px;
				padding: 20px;
				box-sizing: border-box;
			}

			.mailster-lists legend {
				font-weight: 600;
				color: <?php echo esc_attr( $settings['field_text_color'] ?? '#333' ); ?>;
				margin-bottom: 15px;
				padding: 0 10px;
			}

			.mailster-lists .mailster-list-item {
				display: flex;
				flex-direction: column;
				gap: 5px;
			}

			.mailster-lists .mailster-list-label {
				display: flex;
				align-items: flex-start;
				gap: 8px;
				cursor: pointer;
				font-weight: 500;
				color: <?php echo esc_attr( $settings['field_text_color'] ?? '#333' ); ?>;
			}

			.mailster-lists input[type='checkbox'] {
				margin: 0;
				flex-shrink: 0;
				margin-top: 2px;
			}

			.mailster-lists .mailster-list-description {
				<?php if ( $show_descriptions ) : ?>
				display: block;
				<?php else : ?>
				display: none;
				<?php endif; ?>
				font-size: 0.85em;
				color: <?php echo esc_attr( $settings['field_text_color'] ?? '#666' ); ?>;
				opacity: 0.8;
				line-height: 1.4;
				margin-left: 26px;
				margin-top: -2px;
			}

			@media (max-width: 768px) {
				.mailster-lists {
					grid-template-columns: 1fr;
					gap: 12px;
					padding: 15px;
				}
			}

		</style>
		<fieldset <?php echo $form->get_render_attribute_string( 'mailster_user_lists' . $item_index ); ?>>
			<legend><?php echo esc_html( $item['field_label'] ); ?></legend>
			<!-- Removed the subscriber notice window as it's not needed -->
			<?php
			if ( is_array( $mailster_lists ) && ! empty( $mailster_lists ) ) {
				foreach ( $mailster_lists as $list ) {
					if ( ! is_object( $list ) || empty( $list->ID ) ) {
						continue;
					}
					$id          = 'list-' . esc_attr( $list->ID );
					$name        = esc_html( $list->name ?? '' );
					$description = esc_html( $list->description ?? '' );
					?>
					<div class="mailster-list-item">
						<label for="<?php echo esc_attr( $id ); ?>" class="mailster-list-label">
							<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="form_fields[<?php echo esc_attr( $item['custom_id'] ); ?>][]"
								   value="<?php echo esc_attr( $list->ID ); ?>">
							<span><?php echo $name; ?></span>
						</label>
						<?php if ( $show_descriptions && ! empty( $description ) ) : ?>
							<small class="mailster-list-description"><?php echo $description; ?></small>
						<?php endif; ?>
					</div>
					<?php
				}
			} else {
				?>
				<p style="color: #666; font-style: italic;">
					<?php esc_html_e( 'No mailing lists available for selection.', 'integrate-elementor-mailster' ); ?>
				</p>
				<?php
			}
			?>
		</fieldset>

		<script>
		jQuery(document).ready(function($) {
			var fieldset = $('.mailster-lists');
			var form = fieldset.closest('form');
			var emailField = form.find('input[type="email"]').first();
			
			if (emailField.length) {
				// Debounce function to avoid too many AJAX calls
				var debounceTimer;
				
				emailField.on('blur change', function() {
					clearTimeout(debounceTimer);
					debounceTimer = setTimeout(function() {
						checkSubscriberLists();
					}, 500);
				});
				
				// Listen for successful form submissions to reset the form
				form.on('submit_success', function() {
					resetCheckboxes();
					emailField.val('');
				});
				
				// Alternative: Listen for Elementor form success event
				$(document).on('elementor/forms/form_sent', function(event, form_settings, form_element) {
					if (form_element.is(form)) {
						resetCheckboxes();
						emailField.val('');
					}
				});
				
				function checkSubscriberLists() {
					var email = emailField.val().trim();
					
					if (!email || !isValidEmail(email)) {
						resetCheckboxes();
						return;
					}
					
					$.ajax({
						url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
						type: 'POST',
						data: {
							action: 'get_mailster_subscriber_lists',
							email: email,
							nonce: '<?php echo wp_create_nonce( 'mailster_subscriber_lists' ); ?>'
						},
						success: function(response) {
							if (response.success && response.data.subscriber_exists) {
								// Update checkboxes to show current subscriptions
								updateCheckboxes(response.data.current_lists);
							} else {
								resetCheckboxes();
							}
						},
						error: function() {
							resetCheckboxes();
						}
					});
				}
				
				
				function updateCheckboxes(currentLists) {
					fieldset.find('input[type="checkbox"]').each(function() {
						var listId = parseInt($(this).val());
						var isCurrentlySubscribed = false;
						
						// Check if this list ID is in the current lists (handle both string and int)
						for (var i = 0; i < currentLists.length; i++) {
							if (parseInt(currentLists[i]) === listId) {
								isCurrentlySubscribed = true;
								break;
							}
						}
						
						$(this).prop('checked', isCurrentlySubscribed);
						
						// Add visual indicator for current subscriptions
						var label = $(this).closest('.mailster-list-item');
						if (isCurrentlySubscribed) {
							label.addClass('currently-subscribed');
							if (!label.find('.current-subscription-badge').length) {
								$(this).closest('.mailster-list-label').append(' <span class="current-subscription-badge" style="background: #00a0d2; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.8em; margin-left: 5px;">Current</span>');
							}
						} else {
							label.removeClass('currently-subscribed');
							label.find('.current-subscription-badge').remove();
						}
					});
				}
				
				function resetCheckboxes() {
					fieldset.find('input[type="checkbox"]').prop('checked', false);
					fieldset.find('.current-subscription-badge').remove();
					fieldset.find('.mailster-list-item').removeClass('currently-subscribed');
				}
				
				function isValidEmail(email) {
					var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
					return emailRegex.test(email);
				}
			}
		});
		</script>

		<?php
	}

	/**
	 * Field constructor.
	 *
	 * Used to add a script to the Elementor editor preview.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'elementor/preview/init', [ $this, 'editor_preview_footer' ] );
	}

	/**
	 * Elementor editor preview.
	 *
	 * Add a script to the footer of the editor preview screen.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function editor_preview_footer(): void {
		add_action( 'wp_footer', [ $this, 'content_template_script' ] );
	}

	/**
	 * Content template script.
	 *
	 * Add content template alternative, to display the field in Elementor editor.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function content_template_script(): void {
		// Get all available Mailster lists for the JavaScript
		if ( function_exists( 'mailster' ) ) {
			$all_mailster_lists = mailster( 'lists' )->get();
		} else {
			$all_mailster_lists = [];
		}
		?>
		<style>
			.mailster-lists {
				border-radius: 4px;
				border: 1px solid #ddd;
				background: #ffffff;
				width: 100%;
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
				gap: 15px;
				padding: 20px;
				box-sizing: border-box;
			}

			.mailster-lists legend {
				font-weight: 600;
				color: #333;
				margin-bottom: 15px;
				padding: 0 10px;
			}

			.mailster-lists .mailster-list-item {
				display: flex;
				flex-direction: column;
				gap: 5px;
			}

			.mailster-lists .mailster-list-label {
				display: flex;
				align-items: flex-start;
				gap: 8px;
				cursor: pointer;
				font-weight: 500;
				color: #333;
			}

			.mailster-lists input[type='checkbox'] {
				margin: 0;
				flex-shrink: 0;
				margin-top: 2px;
			}

			.mailster-lists .mailster-list-description {
				display: block;
				font-size: 0.85em;
				color: #666;
				opacity: 0.8;
				line-height: 1.4;
				margin-left: 26px;
				margin-top: -2px;
			}

			@media (max-width: 768px) {
				.mailster-lists {
					grid-template-columns: 1fr;
					gap: 12px;
					padding: 15px;
				}
			}
		</style>
		<script>
			jQuery(document).ready(function($) {
				if (typeof elementor !== 'undefined' && elementor.hooks) {
					elementor.hooks.addFilter(
						'elementor_pro/forms/content_template/field/<?php echo esc_js( $this->get_type() ); ?>',
						function (inputField, item, i, settings) {
							const fieldId = 'mailster_user_lists_' + i;
							const fieldClass = 'mailster-lists ' + (item.css_classes || '');
							const title = item.field_label || '';
							const all_mailster_lists = <?php echo wp_json_encode( $all_mailster_lists ); ?>;
							
							// Get form settings to determine which lists should be shown
							let user_selection_enabled = false;
							let show_descriptions = true;
							let user_available_lists = [];
							
							if (settings) {
								user_selection_enabled = settings.mailster_user_selection === 'yes';
								show_descriptions = settings.mailster_show_descriptions === 'yes';
								user_available_lists = settings.mailster_user_available_lists || [];
							}
							
							// If user selection is not enabled, show a message
							if (!user_selection_enabled) {
								return '<fieldset id="' + fieldId + '" class="' + fieldClass + '"><legend>' + title + '</legend><p style="color: #666; font-style: italic;">User list selection is disabled. Enable it in the Mailster action settings.</p></fieldset>';
							}
							
							// If no lists are configured for users, show a message
							if (!Array.isArray(user_available_lists) || user_available_lists.length === 0) {
								return '<fieldset id="' + fieldId + '" class="' + fieldClass + '"><legend>' + title + '</legend><p style="color: #666; font-style: italic;">No lists configured for user selection. Configure them in the Mailster action settings.</p></fieldset>';
							}
							
							// Filter lists to show only those configured for user selection
							let filtered_lists = [];
							if (Array.isArray(all_mailster_lists)) {
								all_mailster_lists.forEach(function(list) {
									if (list && list.ID && user_available_lists.includes(list.ID.toString())) {
										filtered_lists.push(list);
									}
								});
							}
							
							// Generate the list content
							let list_content = '';
							if (filtered_lists.length > 0) {
								filtered_lists.forEach(function (list) {
									const id = 'list-' + list.ID;
									const name = list.name || '';
									const description = list.description || '';
									list_content += '<div class="mailster-list-item">';
									list_content += '<label for="' + id + '" class="mailster-list-label">';
									list_content += '<input type="checkbox" id="' + id + '" name="form_fields[' + item.custom_id + '][]" value="' + list.ID + '">';
									list_content += '<span>' + name + '</span>';
									list_content += '</label>';
									if (show_descriptions && description) {
										list_content += '<small class="mailster-list-description">' + description + '</small>';
									}
									list_content += '</div>';
								});
							} else {
								list_content = '<p style="color: #666; font-style: italic;">The configured lists are not available. Please check your Mailster lists.</p>';
							}

							return '<fieldset id="' + fieldId + '" class="' + fieldClass + '"><legend>' + title + '</legend>' + list_content + '</fieldset>';
						}, 10, 4
					);
				}
			});
		</script>
		<?php
	}

}