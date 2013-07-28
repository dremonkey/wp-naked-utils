/**
 * @desc Makes setting up the autosuggest field easier. This is
 *	designed to be use with the 'naked' series of plugin to handle
 *	admin side forms.
 *
 * @see suggest.js
 */

// create closure
(function($) {

	$.fn.naked_autosuggest_handler = function(options)
	{
		// the autosuggest input box
		var $autosuggest = null;

		// Extend our default options with those provided.
	  var opts = $.extend({}, $.fn.naked_autosuggest_handler.defaults, options);

	  // stores the selected autosuggest items
	  var selected = {};

	  // iterate over each matched element
	  return this.each(function() 
	  {
	  	$autosuggest = $(this);

	  	// initialize
			init_auto_suggest();
			init_add_ad_unit_listener();
			init_selected_values();
			init_submit_listener();
	  });


	  /**
	   * Adds previously saved 'selected' values to the array when the 
	   * page first loads
	   */
	  function init_selected_values()
	  {
	  	values = $(opts.selected_field).val();
	  	save_selected_value(values);
	  };


	  /**
	   * On submit, adds values saved in 'selected' to the opts.
	   * selected_field form element so that it will be saved.
	   */
	  function init_submit_listener()
		{
			$form = $autosuggest.closest('form');

			$form.submit(function(e) {
				e.preventDefault();

				// set the hidden form values
				values = '';
				first = true;
				for (value in selected) {
					if (first) {
						values = value;
						first = false;
					}
					else {
						values += ',' + value;
					}
				}

				$(opts.selected_field).val(values);

				// console.log( $( opts.selected_field ) );
			});
		};


	  /**
		 * Function to initialize auto suggest
		 */
	  function init_auto_suggest()
	  {
	  	$autosuggest.suggest(opts.autosuggest_url, {
	  			multiple: true,
	  			multipleSep: ',',
	  			onSelect: function() {
	  				save_selected_value(this.value);
	  			}
	  		});
	  };


	  /**
	   * Initialzes the 'add' button click event listener
	   */
	  function init_add_ad_unit_listener()
	  {
	  	$autosuggest.siblings('input[type=button]').click(function(e) {
	  		e.preventDefault();
	  		value = $autosuggest.val();
	  		save_selected_value(value);
	  	});
	  };


	  function save_selected_value(value)
	  {
			$wrapper = $(opts.selected_list);
			values = value.split(',');
			for (index in values) {
				// add the value if empty and not yet added
				if (values[index] && !selected[values[index]]) {
					var html = '<span><a class="remove" href="#">x</a><em>' + values[index] + '</em></span>';
					selected[values[index]] = values[index];
					$wrapper.append(html);
				}
			}

			// initialize the 'remove' click listener
			init_remove_listener($wrapper);

			$autosuggest.val('');
		};


		/**
		 * Initializes the listener for the remove ad unit link
		 */
		function init_remove_listener($wrapper)
		{
			$wrapper.find('.remove').click(function(e ) {
				e.preventDefault();
				value = $(this).siblings('em').text();

				// remove the ad unit from the ad_units object
				delete selected[value];

				// remove this item
				$(this).parent().remove();
			});
		};

	}


	$.fn.naked_autosuggest_handler.defaults = {
		'selected_list' : '#selected-items-list',
		'selected_field' : 'input#selected-items',
		'autosuggest_url' : ''
	};

})(jQuery);
