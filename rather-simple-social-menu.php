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

		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'wp_nav_menu_item_custom_fields' ), 10, 2 );
		add_action( 'wp_nav_menu_item_custom_fields_customize_template', array( $this, 'wp_nav_menu_item_custom_fields_customize_template' ) );
		add_action( 'wp_update_nav_menu_item', array( $this, 'wp_update_nav_menu_item' ), 10, 2 );
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'wp_setup_nav_menu_item' ) );
		add_filter( 'nav_menu_css_class', array( $this, 'nav_menu_css_class' ), 10, 2 );
		add_filter( 'nav_menu_item_title', array( $this, 'nav_menu_item_title' ), 10, 2 );
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
	 * Add custom fields (SVG URL and Hide Text) to only "Custom Link" menu items.
	 *
	 * @param string  $item_id  Menu item ID as a numeric string.
	 * @param WP_Post $item     Menu item data object.
	 */
	public function wp_nav_menu_item_custom_fields( $item_id, $item ) {
		// Only apply to custom links.
		if ( 'custom' !== $item->type ) {
			return;
		}

		if ( ! isset( $item->svg_icon ) ) {
			$item->svg_icon = get_post_meta( $item_id, '_menu_item_svg_icon', true );
		}
		if ( ! isset( $item->hide_title ) ) {
			$item->hide_title = get_post_meta( $item_id, '_menu_item_hide_text', true );
		}
		?>
	<p class="field-svg-icon description description-wide">
		<label for="edit-menu-item-svg-icon-<?php echo $item_id; ?>">
			<?php _e( 'Icon', 'rather-simple-social-menu' ); ?><br />
			<select name="menu-item-svg-icon[<?php echo $item_id; ?>]" id="edit-menu-item-svg-icon-<?php echo $item_id; ?>">
			<option value="" <?php selected( empty( $item->svg_icon ) ); ?>></option>
			<?php
			foreach ( array_keys( Plugin_SVG_Icons::$svg_icons ) as $clave ) :
				echo '<option value="' . esc_attr( $clave ) . '" ' . selected( $clave, $item->svg_icon, false ) . '>' . esc_html( $clave ) . '</option>';
			endforeach;
			?>
			</select>
		</label>
	</p>
	<p class="field-hide-menu-text description description-wide">
		<label for="edit-menu-item-hide-text-<?php echo $item_id; ?>">
			<input type="checkbox" id="edit-menu-item-hide-text-<?php echo $item_id; ?>" name="menu-item-hide-text[<?php echo $item_id; ?>]" value="1" <?php checked( $item->hide_title, '1' ); ?> />
			<?php _e( 'Hide Title', 'rather-simple-social-menu' ); ?>
		</label>
	</p>
		<?php
	}

	/**
	 * Add custom fields (SVG Icon and Hide Text) to menu items in the Customizer.
	 */
	public function wp_nav_menu_item_custom_fields_customize_template() {
		?>
	<# console.log(data); if ( 'custom' === data.item_type ) { #>
		<p class="field-svg-icon description description-wide">
			<label for="edit-menu-item-svg-icon-{{ data.menu_item_id }}">
				<?php _e( 'Icon', 'rather-simple-social-menu' ); ?><br />
				<select data-field="svg_icon">
					<option value="" <# if ( ! data.svg_icon ) { #> selected="selected" <# } #>></option>
					<?php
					foreach ( array_keys( Plugin_SVG_Icons::$svg_icons ) as $clave ) :
						?>
						<option value="<?php echo esc_attr( $clave ); ?>" <# if ( data.svg_icon === '<?php echo esc_attr( $clave ); ?>' ) { #> selected="selected" <# } #>><?php echo esc_html( $clave ); ?></option>
						<?php
						endforeach;
					?>
				</select>
			</label>
		</p>
		<p class="field-hide-menu-text description description-wide">
			<label for="edit-menu-item-hide-text-{{ data.menu_item_id }}">
				<input type="checkbox" data-field="hide_title" value="1" <# if ( data.hide_title ) { #> checked="checked" <# } #> />
					<?php _e( 'Hide Title', 'rather-simple-social-menu' ); ?>
			</label>
		</p>
	<# } #>
		<?php
	}

	/**
	 * Save custom menu item fields (SVG Icon and Hide Text) for all menu item types.
	 *
	 * @param int $menu_id          The ID of the menu. If 0, makes the menu item a draft orphan.
	 * @param int $menu_item_db_id  The ID of the menu item. If 0, creates a new menu item.
	 */
	public function wp_update_nav_menu_item( $menu_id, $menu_item_db_id ) {
		// Save SVG Icon.
		if ( isset( $_POST['menu-item-svg-icon'] ) && is_array( $_POST['menu-item-svg-icon'] ) ) {
			if ( isset( $_POST['menu-item-svg-icon'][ $menu_item_db_id ] ) ) {
				$svg_icon = $_POST['menu-item-svg-icon'][ $menu_item_db_id ];
				update_post_meta( $menu_item_db_id, '_menu_item_svg_icon', $svg_icon );
			} else {
				delete_post_meta( $menu_item_db_id, '_menu_item_svg_icon' );
			}
		}

		// Save Hide Text Checkbox.
		if ( isset( $_POST['menu-item-hide-text'] ) && is_array( $_POST['menu-item-hide-text'] ) ) {
			if ( isset( $_POST['menu-item-hide-text'][ $menu_item_db_id ] ) ) {
				update_post_meta( $menu_item_db_id, '_menu_item_hide_text', 1 );
			} else {
				update_post_meta( $menu_item_db_id, '_menu_item_hide_text', 0 );
			}
		} else {
			delete_post_meta( $menu_item_db_id, '_menu_item_hide_text' );
		}
	}

	/**
	 * Add custom field data (SVG icon and Hide Text) to the menu item object for all types.
	 *
	 * @param WP_Post $menu_item The menu item to modify.
	 */
	public function wp_setup_nav_menu_item( $menu_item ) {
		$menu_item->svg_icon       = get_post_meta( $menu_item->ID, '_menu_item_svg_icon', true );
		$menu_item->hide_title = get_post_meta( $menu_item->ID, '_menu_item_hide_text', true );
		return $menu_item;
	}

	/**
	 * Filter the HTML attributes of a menu item (`<li>` element).
	 *
	 * @param string[] $classes    The HTML attributes applied to the menu item's `<li>` element.
	 * @param WP_Post  $menu_item  The current menu item object.
	 */
	public function nav_menu_css_class( $classes, $menu_item ) {
		if ( ! empty( $menu_item->svg_icon ) ) {
			$classes[] = 'menu-item-has-icon';
		}
		return $classes;
	}

	/**
	 * Output the SVG in the menu and conditionally hide text.
	 *
	 * @param string  $title  The menu item’s title.
	 * @param WP_Post $item   The current menu item object.
	 */
	public function nav_menu_item_title( $title, $item ) {
		$svg_markup = '';
		if ( ! empty( $item->svg_icon ) ) {
			$svg_markup = Plugin_SVG_Icons::get_svg( $item->svg_icon, 16 );
		}
		if ( ! empty( $svg_markup ) && ! $item->hide_title ) {
			// Icon before text.
			$title = $svg_markup . ' ' . $title;
		} elseif ( ! empty( $svg_markup ) && $item->hide_title ) {
			// Only the icon.
			$title = $svg_markup . '<span class="screen-reader-text">' . $title . '</span>';
		}
		return $title;
	}
}

add_action( 'plugins_loaded', array( Rather_Simple_Social_Menu::get_instance(), 'plugin_setup' ) );
