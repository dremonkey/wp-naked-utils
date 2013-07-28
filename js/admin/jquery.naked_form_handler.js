/**
 * Saves form data. This is designed to be use with the 'naked'
 * series of plugins to handle admin side forms.
 *
 * - uses jquery-ui sortable if opts.sortable is set
 * - naked_form_handler should be attached to the form id
 *
 * @see jquery-ui sortable
 */

// create closure
(function($) {

	$.fn.naked_form_handler = function(options)
	{
		var delete_ids = [];

		// Extend our default options with those provided.
	  var opts = $.extend({}, $.fn.naked_form_handler.defaults, options);

		// iterate over each matched element
	  return this.each(function() 
	  {
	  	if (!opts.nonce) {
	  		alert('This form has not been setup correctly. You must set a nonce.');
	  		return false;
	  	}

	  	init_sortable();

	  	// Remove item from the list
			$(opts.delete_class).click(function(e) {
				e.preventDefault();
				remove_item(this);
			});

	  	$(this).submit(function(e) {
				// prevent the default action from firing
				e.preventDefault();
				save($(this));
				return false;
			});
	  });


	  function init_sortable()
		{
			if (opts.sortable) {
				$(opts.sortable).sortable({
					cancel: opts.list_header,
					update: function(event, ui) {
						// reset the row classnames (so that even and odd are correct )
						set_rows_class();

						// display a warning message
						msg = 'Nothing saved yet. To save your changes click the update button';
						$.fn.naked_form_handler.show_msg('warning', msg, false);

					}
				}).disableSelection();
			}
		}


		function save($form)
		{
			// put the new order in a hidden field so that they submitted
			if (opts.sortable) {
				var order = $(opts.row).map(function() {
					return $(this).attr('id').replace(opts.row_id_base, '');
				}).get().join(',');

				$('input[name=order]').val(order);
			}

			// put the ids to delete in a hidden field so that they submitted
			$delete_ids = $('input[name=delete_ids]');
			if ($delete_ids.length != 0) {
				var val = delete_ids.join(',');
				$('input[name=delete_ids]').val(delete_ids);
			}

			// get the nonce
			console.log(opts.nonce);

			// prepare our data to be saved
			var data = $form.serialize();
			data = decodeURIComponent(data);
			data += '&nonce=' + opts.nonce;
			console.log(data);

			// ajaxurl is automatically set in the admin part of wordpress
			$.post(ajaxurl, data, function(response) {

				console.log(response);

				try {
					response = jQuery.parseJSON(response);

					$.fn.naked_form_handler.show_msg(response.status, response.msg);

					if (response.redirect) {
						t = setTimeout(function() {
							console.log(response.redirect);
							window.location.replace(response.redirect);
						}, 1000);
					}
				}
				catch (error) {
					console.log(response);
					console.log(error);
				}

			});

		};


		function remove_item(delete_link)
		{
			var msg = '';
			var reset_row_classes = true;

			$row = $(delete_link).closest(opts.row);

			// console.log( opts.row );

			// if row length is zero, then we assume that we are removing everything
			// so find all occurences of row on the page
			if ($row.length == 0) {
				reset_row_classes = false;

				var msg_type = 'confirm';
				// msg = 'You are about to delete this. Click <strong>confirm</strong> to continue or reload the page to cancel.'

				$row = $(opts.row);

				$('input[type=submit]').val('Confirm Delete');
			}
			else {
				var msg_type = 'warning';
				msg = 'Nothing saved yet. To save your changes click the update button';
			}

			// save the ids to delete
			id = $(delete_link).attr('id').replace(opts.delete_id_base, '');
			delete_ids.push(id);

			console.log(id);

			// remove the row and button that was deleted
			$(delete_link).remove();
			$row.remove();

			// reset the row classnames (so that even and odd are correct )
			if (reset_row_classes)
				set_rows_class();

			console.log(delete_ids);

			// display a warning message
			$.fn.naked_form_handler.show_msg(msg_type, msg, false);
		};


		function set_rows_class()
		{
			$(opts.row).each(function(index ) {
				// remove the first character because it denotes an a html selector '.' or a '#'
				base_row_class = opts.row.substr(1);

				// console.log( base_row_class );

				classname = index % 2 == 0 ? 'odd' : 'even';
				$(this).removeClass().addClass(base_row_class + ' ' + classname);
			});
		}

	}


	$.fn.naked_form_handler.show_msg = function(type, msg, fade)
	{
		var fade = fade === false ? fade : true;

		if (msg == '') {
			messages = {
				'success' : 'Successfully saved',
				'warning' : 'Undefined warning',
				'error' : 'Undefined error',
				'confirm' : 'Are you sure?'
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


	$.fn.naked_form_handler.fade_msg = function() 
	{
		$('#msg-box').fadeOut(1000);
		clearTimeout(t);
	};


	$.fn.naked_form_handler.defaults = {
		/**
		 * The classname (or id) for a row. 'Row' refers to the wrapper 
		 * around an item in a list, table, etc.
		 */
		'row' : '.row',
		/**
		 * The basename for the row id ( i.e. if the id is 'row-32' the 
		 * base is 'row-' and '32' is the post_id or other id that maps 
		 * to an equivalent id in the database of the item contained in 
		 * the row
		 */
		'row_id_base' : 'row-',
		/**
		 * The classname for the delete link
		 */
		 'delete_class' : '.delete',
		 /**
		 * The classname for the delete link
		 */
		 'delete_id_base' : 'delete-',
		 /**
		  * The classname (or id) for the wrapper element that will be 
		  * made sortable Default is blank so that by default it is not 
		  * initializd
		  */
		 'sortable' : '',
		 /**
		  * The classname (or id) for the wrapper element that will be 
		  * made sortable. Default is blank so that by default it is not 
		  * initializd
		  */
		 'list_header' : '.list-header',
		 /**
		  * The nonce to check before data from this form will be 
		  * processed. This should injected onto the page from your 
		  * controller using wp_localize_script()
		  */
		  'nonce' : ''
	};

})(jQuery);
