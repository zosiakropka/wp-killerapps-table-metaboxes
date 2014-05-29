(function ($) {
	$.fn.extend ({
		enter: function(callback) {
			if ($.isFunction(callback)) {
				this.keypress(function(e) {
					if (e.which == 13) {
						callback(e);
					}
				});
			}
			return this;
		}
	});
	$(document).ready(function() {
		var metaboxes = $('.killerapps-table-metaboxes');

		function get_table_values(metabox) {
			var values = {dynamic: {}, rows: []};

			var dynamic_headers = metabox.find('input.dynamic-header');
			dynamic_headers.each(function() {
				var slug = $(this).data('killerapps-slug');
				values.dynamic[slug] = $(this).val();
			});

			var rows = metabox.find('tbody tr');
			rows.each(function() {
				var row = {};
				cells = $(this).find('td');
				cells.each(function() {
					if (! $(this).hasClass('rows-actions')) {
						var inputs = $(this).find('input');
						var slug = inputs.data('killerapps-slug');
						var value = row[slug];
						if (inputs.length == 1) {
							var input = inputs.first();
							if (input.attr('type') == 'checkbox') {
								value = input.is(':checked');
							} else {
								value = input.val();
							}
						} else if (inputs.length > 1){
							var input = inputs.filter('input:checked').first();
							value = input.val();
						}
						row[slug] = value;
					}
				})
				values.rows.push(row);
			});
			return values;
		}

		function update_form_input(metabox) {
			var input = metabox.find('.killerapps-table-metaboxes-input').first();
			var values = get_table_values(metabox);
			var string = $.toJSON(values);
			input.val(string);
		}

		function update_no_row_notice(metabox) {
			var rows = metabox.find('table tbody tr');
			var no_rows = metabox.find('table .no-rows');
			if (rows.length) {
				no_rows.addClass('hide');
			} else {
				no_rows.removeClass('hide');
			}
		}

		function update_cols(metabox) {
			var label = metabox.find('.new-col-label').val();
			var slug = $.slugify(label);
			if (label && !$('table thead .' + slug).length) {
				metabox.find('.new-col-label').val('');
				var col = create_col(label, slug);
				
				metabox.find('table thead tr').first().find('.static_end').before(col.th);
				metabox.find('table tbody tr .static_end, table tfoot tr.row-template .static_end').before(col.td);
				metabox.find('table tfoot tr.cols-actions .static_end').before(col.td_remove);
				update_form_input(metabox);
			}
		}

		function update_rows(metabox) {
			var row = create_row_from_tempalte(metabox);
			metabox.find('table tbody').append(row);
			update_no_row_notice(metabox);
			update_form_input(metabox);
		}

		function create_row_from_tempalte(metabox) {
			var template = metabox.find('table tr.row-template').first();
			return template.clone().removeClass('row-template');
		}

		function create_col(label, slug) {
			return {
				th: '<th class="' + slug + '"><input type="text" class="text dynamic-header header" value="' + label + '" data-killerapps-slug="' + slug + '"/></th>',
				td: '<td class="' + slug + '"><input type="text" class="text dynamic-value value" data-killerapps-slug="' + slug + '"/></td>',
				td_remove: '<td class="remove ' + slug + '"><input type="button" class="button" value="X" data-killerapps-slug="' + slug + '"/></td>',
			};
			return 
		}

		metaboxes.each(function() {
			var metabox = $(this);
			var input = metabox.find('table input');
			var new_col_label_input = metabox.find('.new-col-label');
			var add_new_col_button = metabox.find('.add-new-col');
			var add_new_row_button = metabox.find('.add-new-row');
			update_no_row_notice(metabox);
			add_new_row_button.click(function() {
				update_rows(metabox);
			});
			add_new_col_button.click(function() {
				update_cols(metabox);
			});
			new_col_label_input.enter(function(e) {
				update_cols(metabox);
			});
			input.live('change', function() {
				update_form_input(metabox);
			});
		});

	});
})(jQuery);


