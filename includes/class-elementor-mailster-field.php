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
		if ( function_exists( 'mailster' ) ) {
			$mailster_lists = mailster( 'lists' )->get();
		} else {
			$mailster_lists = [ 'none' ];
		}

		$form_id  = $form->get_id();
		$settings = $form->get_settings_for_display();

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
				width: 100%;
				display: grid;
				grid-template-columns: 1fr 1fr;
				gap: 20px;
			}

			.mailster-lists input[type='checkbox'] {
				vertical-align: middle;
			}

			.mailster-lists small {
				display: block;
			}

		</style>
		<fieldset <?php echo $form->get_render_attribute_string( 'checkbox' . $item_index ); ?>>
			<legend><?php echo esc_html( $item['field_label'] ); ?></legend>
			<?php
			if ( is_array( $mailster_lists ) ) {
				foreach ( $mailster_lists as $list ) {
					if ( ! is_object( $list ) || empty( $list->ID ) ) {
						continue;
					}
					$id          = 'list-' . esc_attr( $list->ID );
					$name        = esc_html( $list->name ?? '' );
					$description = esc_html( $list->description ?? '' );
					?>
					<div>
						<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="form_fields[mailster_lists][]"
							   value="<?php echo esc_attr( $list->ID ); ?>">
						<label for="<?php echo esc_attr( $id ); ?>"><?php echo $name; ?></label>
						<?php
						if ( ! empty( $description ) ) {
							echo '<small>' . $description . '</small>';
						}
						?>
					</div>
					<?php
				}
			}
			?>
		</fieldset>

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
		if ( function_exists( 'mailster' ) ) {
			$mailster_lists = mailster( 'lists' )->get();
		} else {
			$mailster_lists = [ 'none' ];
		}
		?>
		<style>
			.mailster-lists {
				border-radius: 4px;
				border: 1px solid #ddd;
				width: 100%;
				display: grid;
				grid-template-columns: 1fr 1fr;
				gap: 20px;
				padding: 15px;
				box-sizing: border-box;
			}

			.mailster-lists input[type='checkbox'] {
				vertical-align: middle;
				margin-right: 8px;
			}

			.mailster-lists small {
				display: block;
				color: #666;
				font-size: 0.9em;
				margin-top: 4px;
			}

			.mailster-lists legend {
				font-weight: bold;
				margin-bottom: 10px;
			}

			@media (max-width: 768px) {
				.mailster-lists {
					grid-template-columns: 1fr;
				}
			}
		</style>
		<script>
			jQuery(document).ready(function($) {
				if (typeof elementor !== 'undefined' && elementor.hooks) {
					elementor.hooks.addFilter(
						'elementor_pro/forms/content_template/field/<?php echo esc_js( $this->get_type() ); ?>',
						function (inputField, item, i) {
							const fieldId = 'mailster_user_lists_' + i;
							const fieldClass = 'mailster-lists ' + (item.css_classes || '');
							const title = item.field_label || '';
							const mailster_lists = <?php echo wp_json_encode( $mailster_lists ); ?>;
							
							let list_content = '';
							if (Array.isArray(mailster_lists)) {
								mailster_lists.forEach(function (list) {
									if (list && list.ID) {
										const id = 'list-' + list.ID;
										const name = list.name || '';
										const description = list.description || '';
										list_content += '<div>';
										list_content += '<input type="checkbox" id="' + id + '" name="form_fields[mailster_lists][]" value="' + list.ID + '"> ';
										list_content += '<label for="' + id + '">' + name + '</label>';
										if (description) {
											list_content += '<small>' + description + '</small>';
										}
										list_content += '</div>';
									}
								});
							}

							return '<fieldset id="' + fieldId + '" class="' + fieldClass + '"><legend>' + title + '</legend>' + list_content + '</fieldset>';
						}, 10, 3
					);
				}
			});
		</script>
		<?php
	}

}