<?php
/**
 * Plugin Name: Rather Simple Social Menu
 * Plugin URI:
 * Update URI: false
 * Version: 3.0
 * Requires at least: 6.8
 * Requires PHP: 7.4
 * Author: Oscar Ciutat
 * Author URI: http://oscarciutat.com/code/
 * Text Domain: rather-simple-social-menu
 * Domain Path: /languages
 * Description: A really simple social menu
 * License: GPLv2 or later
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package rather_simple_social_menu
 */

/**
 * Core class used to implement the plugin.
 */
class Rather_Simple_Social_Menu {

	/**
	 * Plugin instance.
	 *
	 * @var object $instance
	 */
	protected static $instance = null;

	/**
	 * Access this plugin’s working instance.
	 */
	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Used for regular plugin work.
	 */
	public function plugin_setup() {

		$this->includes();

		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'wp_nav_menu_item_custom_fields' ), 10, 2 );
		add_action( 'wp_update_nav_menu_item', array( $this, 'wp_update_nav_menu_item' ), 10, 2 );
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'wp_setup_nav_menu_item' ) );
		add_filter( 'nav_menu_css_class', array( $this, 'nav_menu_css_class' ), 10, 2 );
		add_filter( 'nav_menu_item_title', array( $this, 'nav_menu_item_title' ), 10, 2 );

		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue_scripts' ) );
		add_action( 'wp_nav_menu_item_custom_fields_customize_template', array( $this, 'wp_nav_menu_item_custom_fields_customize_template' ) );
		add_action( 'customize_register', array( $this, 'customize_register' ), 1000, 1 );
		add_action( 'customize_save_after', array( $this, 'customize_save_after' ) );
	}

	/**
	 * Constructor. Intentionally left empty and public.
	 */
	public function __construct() {}

	/**
	 * Includes required core files used in admin and on the frontend.
	 */
	protected function includes() {
		require 'classes/class-plugin-svg-icons.php';
	}

	/**
	 * Enqueues scripts and styles in the frontend.
	 */
	public function wp_enqueue_scripts() {
		wp_enqueue_style(
			'wat-style',
			plugins_url( 'style.css', __FILE__ ),
			array(),
			filemtime( plugin_dir_path( __FILE__ ) . '/style.css' )
		);
	}

	/**
	 * Add custom fields (Icon and Hide Title) to only "Custom Link" menu items.
	 *
	 * @param string  $item_id  Menu item ID as a numeric string.
	 * @param WP_Post $item     Menu item data object.
	 */
	public function wp_nav_menu_item_custom_fields( $item_id, $item ) {
		// Only apply to custom links.
		if ( 'custom' !== $item->type ) {
			return;
		}

		if ( ! isset( $item->icon ) ) {
			$item->icon = get_post_meta( $item_id, '_menu_item_icon', true );
		}
		if ( ! isset( $item->hide_title ) ) {
			$item->hide_title = get_post_meta( $item_id, '_menu_item_hide_title', true );
		}
		?>
	<p class="field-icon description description-wide">
		<label for="edit-menu-item-icon-<?php echo $item_id; ?>">
		<?php _e( 'Icon', 'rather-simple-social-menu' ); ?><br />
			<select name="menu-item-icon[<?php echo $item_id; ?>]" id="edit-menu-item-icon-<?php echo $item_id; ?>">
			<option value="" <?php selected( empty( $item->icon ) ); ?>></option>
		<?php
		foreach ( array_keys( Plugin_SVG_Icons::$svg_icons ) as $clave ) :
			echo '<option value="' . esc_attr( $clave ) . '" ' . selected( $clave, $item->icon, false ) . '>' . esc_html( $clave ) . '</option>';
			endforeach;
		?>
			</select>
		</label>
	</p>
	<p class="field-hide-title description description-wide">
		<label for="edit-menu-item-hide-title-<?php echo $item_id; ?>">
			<input type="checkbox" id="edit-menu-item-hide-title-<?php echo $item_id; ?>" name="menu-item-hide-title[<?php echo $item_id; ?>]" value="1" <?php checked( $item->hide_title, '1' ); ?> />
			<?php _e( 'Hide Title', 'rather-simple-social-menu' ); ?>
		</label>
	</p>
		<?php
	}

	/**
	 * Add custom fields (Icon and Hide Title) to menu items in the Customizer.
	 */
	public function wp_nav_menu_item_custom_fields_customize_template() {
		?>
	<# if ( 'custom' === data.item_type ) { #>
		<p class="field-icon description description-wide">
			<label for="edit-menu-item-icon-{{ data.menu_item_id }}">
			<?php _e( 'Icon', 'rather-simple-social-menu' ); ?><br />
				<select data-field="icon">
					<option value=""></option>
				<?php
				foreach ( array_keys( Plugin_SVG_Icons::$svg_icons ) as $key ) :
					?>
						<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $key ); ?></option>
						<?php
						endforeach;
				?>
				</select>
			</label>
		</p>
		<p class="field-hide-title description description-wide">
			<label for="edit-menu-item-hide-title-{{ data.menu_item_id }}">
				<input type="checkbox" data-field="hide_title" value="1" />
				<?php _e( 'Hide Title', 'rather-simple-social-menu' ); ?>
			</label>
		</p>
	<# } #>
			<?php
	}

	/**
	 * Save custom menu item fields (Icon and Hide Title) for all menu item types.
	 *
	 * @param int $menu_id          The ID of the menu. If 0, makes the menu item a draft orphan.
	 * @param int $menu_item_db_id  The ID of the menu item. If 0, creates a new menu item.
	 */
	public function wp_update_nav_menu_item( $menu_id, $menu_item_db_id ) {

		// Detect if update is coming from the Customizer.
		if ( isset( $_POST['customized'] ) ) {
			$customized = json_decode( stripslashes( $_POST['customized'] ), true );
			$key        = 'nav_menu_item[' . $menu_item_db_id . ']';
			if ( isset( $customized[ $key ] ) ) {
				$menu_item_data = $customized[ $key ];
				if ( isset( $menu_item_data['icon'] ) ) {
					update_post_meta( $menu_item_db_id, '_menu_item_icon', sanitize_text_field( $menu_item_data['icon'] ) );
				} else {
					delete_post_meta( $menu_item_db_id, '_menu_item_icon' );
				}
				if ( isset( $menu_item_data['hide_title'] ) ) {
					update_post_meta( $menu_item_db_id, '_menu_item_hide_title', (int) $menu_item_data['hide_title'] );
				} else {
					delete_post_meta( $menu_item_db_id, '_menu_item_hide_title' );
				}
			}
		} else {
			// Fallback if update is coming from Appearance > Menus screen.
			if ( isset( $_POST['menu-item-icon'] ) && is_array( $_POST['menu-item-icon'] ) ) {
				if ( isset( $_POST['menu-item-icon'][ $menu_item_db_id ] ) ) {
					$icon = $_POST['menu-item-icon'][ $menu_item_db_id ];
					update_post_meta( $menu_item_db_id, '_menu_item_icon', $icon );
				} else {
					delete_post_meta( $menu_item_db_id, '_menu_item_icon' );
				}
			}
			if ( isset( $_POST['menu-item-hide-title'] ) && is_array( $_POST['menu-item-hide-title'] ) ) {
				if ( isset( $_POST['menu-item-hide-title'][ $menu_item_db_id ] ) ) {
					update_post_meta( $menu_item_db_id, '_menu_item_hide_title', 1 );
				} else {
					update_post_meta( $menu_item_db_id, '_menu_item_hide_title', 0 );
				}
			} else {
				delete_post_meta( $menu_item_db_id, '_menu_item_hide_title' );
			}
		}
	}

	/**
	 * Add custom field data (Icon and Hide Title) to the menu item object for all types.
	 *
	 * @param WP_Post $menu_item The menu item to modify.
	 */
	public function wp_setup_nav_menu_item( $menu_item ) {
		$menu_item->icon       = get_post_meta( $menu_item->ID, '_menu_item_icon', true );
		$menu_item->hide_title = get_post_meta( $menu_item->ID, '_menu_item_hide_title', true );
		return $menu_item;
	}

	/**
	 * Filter the HTML attributes of a menu item (`<li>` element).
	 *
	 * @param string[] $classes    The HTML attributes applied to the menu item's `<li>` element.
	 * @param WP_Post  $menu_item  The current menu item object.
	 */
	public function nav_menu_css_class( $classes, $menu_item ) {
		if ( ! empty( $menu_item->icon ) ) {
			$classes[] = 'menu-item-has-icon';
		}
		return $classes;
	}

	/**
	 * Output the icon in the menu and conditionally hide title.
	 *
	 * @param string  $title  The menu item’s title.
	 * @param WP_Post $item   The current menu item object.
	 */
	public function nav_menu_item_title( $title, $item ) {
		$svg_markup = '';
		if ( ! empty( $item->icon ) ) {
			$svg_markup = Plugin_SVG_Icons::get_svg( $item->icon, 16 );
		}
		if ( ! empty( $svg_markup ) && ! $item->hide_title ) {
			// Icon before title.
			$title = $svg_markup . ' ' . $title;
		} elseif ( ! empty( $svg_markup ) && $item->hide_title ) {
			// Only the icon.
			$title = $svg_markup . '<span class="screen-reader-text">' . $title . '</span>';
		}
		return $title;
	}

	/**
	 * Enqueue scripts for the Customizer controls.
	 */
	public function customize_controls_enqueue_scripts() {
		wp_enqueue_script(
			'customize-nav-menu-icon',
			plugin_dir_url( __FILE__ ) . 'assets/js/customize.js',
			array( 'customize-nav-menus' ),
			filemtime( __DIR__ . '/assets/js/customize.js' ),
			true
		);
	}

	/**
	 * Preview the nav menu item icon in the Customizer.
	 *
	 * @param WP_Customize_Nav_Menu_Item_Setting $setting The menu item setting.
	 */
	public function preview_nav_menu_item( WP_Customize_Nav_Menu_Item_Setting $setting ) {
		$values = $setting->manager->unsanitized_post_values()[ $setting->id ];
		if ( ! is_array( $values ) ) {
			return;
		}

		add_filter(
			'get_post_metadata',
			static function ( $value, $object_id, $meta_key ) use ( $setting, $values ) {
				if ( $object_id === $setting->post_id ) {
					if ( '_menu_item_icon' === $meta_key ) {
						//error_log( print_r( $values, true ), 1, 'oscarciutat@gmail.com' );
						return array( sanitize_text_field( $values['icon'] ?? '' ) );
					}
					if ( '_menu_item_hide_title' === $meta_key ) {
						return array( $values['hide_title'] ?? '0' );
					}
				}
				return $value;
			},
			10,
			3
		);
	}

	/**
	 * Save the nav menu item icon and hide title settings.
	 *
	 * @param WP_Customize_Nav_Menu_Item_Setting $setting The menu item setting.
	 */
	public function save_nav_menu_item( WP_Customize_Nav_Menu_Item_Setting $setting ) {
		$values = $setting->post_value();
		if ( ! is_array( $values ) ) {
			return;
		}

		if ( isset( $values['icon'] ) ) {
			update_post_meta( $setting->post_id, '_menu_item_icon', sanitize_text_field( $values['icon'] ) );
		}
		if ( isset( $values['hide_title'] ) ) {
			update_post_meta( $setting->post_id, '_menu_item_hide_title', '1' === $values['hide_title'] ? '1' : '0' );
		}
	}

	/**
	 * Register the Customizer settings and controls.
	 *
	 * @param WP_Customize_Manager $wp_customize The Customizer manager instance.
	 */
	public function customize_register( WP_Customize_Manager $wp_customize ) {
		if ( $wp_customize->settings_previewed() ) {
			foreach ( $wp_customize->settings() as $setting ) {
				if ( $setting instanceof WP_Customize_Nav_Menu_Item_Setting ) {
					$this->preview_nav_menu_item( $setting );
				}
			}
		}
	}

	/**
	 * Save the nav menu item icon and hide title settings after Customizer save.
	 *
	 * @param WP_Customize_Manager $wp_customize The Customizer manager instance.
	 */
	public function customize_save_after( WP_Customize_Manager $wp_customize ) {
		foreach ( $wp_customize->settings() as $setting ) {
			if ( $setting instanceof WP_Customize_Nav_Menu_Item_Setting && $setting->check_capabilities() ) {
				$this->save_nav_menu_item( $setting );
			}
		}
	}
}

add_action( 'plugins_loaded', array( Rather_Simple_Social_Menu::get_instance(), 'plugin_setup' ) );
