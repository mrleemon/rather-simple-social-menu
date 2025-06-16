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
		control.iconSelect = control.container.find('.nav_menu_icon select');
		control.hideTitleCheckbox = control.container.find('.nav_menu_hide_title input[type="checkbox"]');

		// Inicializa la UI
		updateControlFields(control);

		// Actualiza la UI si cambia el setting por código
		control.setting.bind(() => {
			updateControlFields(control);
		});

		// Escucha cambios en el select de icono
		control.iconSelect.on('change', function () {
			setSettingFields(control.setting, {
				icon: this.value
			});
		});

		// Escucha cambios en el checkbox de ocultar título
		control.hideTitleCheckbox.on('change', function () {
			setSettingFields(control.setting, {
				hide_title: this.checked
			});
		});
	}

	/**
	 * Actualiza el objeto setting con nuevos valores.
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
	 * Actualiza la UI del control con los valores actuales del setting.
	 *
	 * @param {wp.customize.Menus.MenuItemControl} control
	 */
	function updateControlFields(control) {
		const value = control.setting();

		control.iconSelect.val(value.icon || '');
		control.hideTitleCheckbox.prop('checked', !!value.hide_title);
	}
})();
