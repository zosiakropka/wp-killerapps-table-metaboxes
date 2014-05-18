(function ($) {
	$(document).ready(function() {
		var metaboxes = $('.killerapps-table-metaboxes');
	
		function update_no_row_notice(metabox) {
			var rows = metabox.find('table tbody tr');
			var no_rows = metabox.find('table .no-rows');
			if (rows.length) {
				no_rows.addClass('hide');
			} else {
				no_rows.removeClass('hide');
			}
		}

		function new_row_from_tempalte(metabox) {
			var template = metabox.find('table tr.row-template').first();
			return template.clone().removeClass('row-template');
		}

		function new_cell(slug) {
			return '<td class="' + slug + '"><input type="text" class="text"/></td>'
		}

		function new_header(slug, label) {
			return '<th class="' + slug + '">' + label + '</th>';
		}

		function new_col(label) {
			var slug = $.slugify(label);
			return {
				th: new_header(slug, label),
				td: new_cell(slug)
			};
			return 
		}

		metaboxes.each(function() {
			var metabox = $(this)
			update_no_row_notice(metabox);
			metabox.find('.add-new-row').click(function() {
				var row = new_row_from_tempalte(metabox);
				metabox.find('table tbody').append(row);
				update_no_row_notice(metabox);
			});
			metabox.find('.add-new-col').click(function() {
				var col_label = metabox.find('.new-col-label').val();
				if (col_label) {
					metabox.find('.new-col-label').val('');
					var col = new_col(col_label);
					
					metabox.find('table thead tr').first().find('.static_end').before(col.th);
					metabox.find('table tbody tr .static_end, table tfoot tr.row-template .static_end').before(col.td);
				}
			});
		});

	});
})(jQuery);


