/**
 * Admin Features Page and Submenu Page Javascript
 */

// closure
(function($) {

	$(function() {

		// Section Page Script
		if (('form#edit-section').length) {

			$.fn.naked_form_handler.show_msg = function(type, msg, fade )
			{
				var fade = fade === false ? fade : true;

				if (msg == '') {
					messages = {
						'success' : 'Successfully saved',
						'warning' : 'Undefined warning',
						'error' : 'Undefined error',
						'confirm' : 'Are you sure you want to delete this section? All data associated with this section will also be removed. Click <strong>confirm</strong> to delete.'
					};

					var msg = messages[type];
				}

				$('#msg-box').html('<div class="' + type + '">' + msg + '</div>').show();

				if (fade) {
					t = setTimeout(function() {
						$.fn.naked_form_handler.fade_msg();
					}, 15000);
				}
			};

			$('form#edit-section').naked_form_handler({
				'row' : 'fieldset',
				'row_id_base' : '',
				'nonce' : naked_feature._naked_feature_nonce
			});
		}



		// Features List Page Script
		$('form#edit-features-list').naked_form_handler({
			'row' : '.feature',
			'row_id_base' : 'feature-',
			'sortable' : '.sortable',
			'nonce' : naked_feature._naked_feature_nonce
		});



	});

})(jQuery);
