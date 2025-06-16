( function( wp, $ ) {
	wp.customize.controlConstructor.nav_menu_item = wp.customize.Control.extend( {
		ready: function() {
			var control = this;

			// Ensure the UI is bound when the container is ready.
			control.container.on( 'change', 'select[name^="menu-item-icon"], input[name^="menu-item-hide-title"]', function() {
				var menuItemId = control.params.menu_item_id;

				// Get selected icon
				var icon = control.container.find( 'select[name="menu-item-icon[' + menuItemId + ']"]' ).val();

				// Get hide-title checkbox
				var hideTitle = control.container.find( 'input[name="menu-item-hide-title[' + menuItemId + ']"]' ).is(':checked') ? '1' : '0';

				control.setting.set({
					...control.setting(),
					icon: icon,
					hide_title: hideTitle
				});
			} );
		}
	} );
} )( window.wp, jQuery );
