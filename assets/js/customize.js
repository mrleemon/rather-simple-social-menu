(function () {

	// Augment each menu item control once it is added and embedded.
	wp.customize.control.bind('add', (control) => {
		if (control.extended(wp.customize.Menus.MenuItemControl)) {
			control.deferred.embedded.done(() => {
				extendControl(control);
			});
		}
	});

	/**
	 * Extend the control with custom fields: icon (select) and hide_title (checkbox).
	 *
	 * @param {wp.customize.Menus.MenuItemControl} control
	 */
	function extendControl(control) {
		control.iconSelect = control.container.find('.field-icon select');
		control.hideTitleCheckbox = control.container.find('.field-hide-title input[type="checkbox"]');

		// Initialize the UI
		updateControlFields(control);

		// Update the UI if the setting changes programmatically
		control.setting.bind(() => {
			updateControlFields(control);
		});

		// Listen for changes in the icon select
		control.iconSelect.on('change', function () {
			setSettingFields(control.setting, {
				icon: this.value
			});
		});

		// Listen for changes in the hide title checkbox
		control.hideTitleCheckbox.on('change', function () {
			setSettingFields(control.setting, {
				hide_title: this.checked ? '1' : '0'
			});
		});
	}

	/**
	 * Update the setting object with new values.
	 *
	 * @param {wp.customize.Setting} setting
	 * @param {Object} updates
	 */
	function setSettingFields(setting, updates) {
		setting.set(
			Object.assign(
				{},
				_.clone(setting()),
				updates
			)
		);
	}

	/**
	 * Update the UI of the control with the current setting values.
	 *
	 * @param {wp.customize.Menus.MenuItemControl} control
	 */
	function updateControlFields(control) {
		const value = control.setting();

		control.iconSelect.val(value.icon || '');
		control.hideTitleCheckbox.prop('checked', value.hide_title === '1');
	}
})();
