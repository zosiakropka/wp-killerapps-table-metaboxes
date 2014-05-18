<?php

class killerapps_TableMetabox {

	private $slug;
	private $title;
	private $post_types;
	private $labels;
	private $static_columns_start;
	private $static_columns_end;
	private $priority;

	static $default_labels = array (
		'add_row' => "Add row",
		'add_col' => "Add column",
		'new_col_label' => "New column label",
		'no_rows' => "No rows are added"
	);

	/**
	 * @param string $slug
	 * @param string $title
	 * @param string|array $post_types
	 * @param array $static_columns_start (optional) array {$slug: $label, (...)} ($cell_callback takes $row_id parameter)
	 * @param array $static_columns_end (optional) array {$slug: $label, (...)} ($cell_callback takes $row_id parameter)
	 * @param string $priority ('high', 'core', 'default' or 'low')
	 */
	function killerapps_TableMetabox($slug, $title, $post_types, $labels=NULL, $static_columns, $priority='default') {
		$this->slug = $slug;
		$this->title = $title;
		if (is_string($post_types)) $this->post_types = array( $post_types ); else $this->post_types = $post_types;
		$this->labels = array();
		foreach (killerapps_TableMetabox::$default_labels as $label => $default) {
			$this->labels[$label] = $labels[$label]?$labels[$label]: $default;
		}
		$this->static_columns_start = $static_columns['start'];
		$this->static_columns_end = $static_columns['end'];
		$this->priority = $priority;

		add_action('load-post.php', array($this, '_register_meta_box'));
		add_action('load-post-new.php', array($this, '_register_meta_box'));
	}

	function _register_meta_box() {
		foreach ($this->post_types as $post_type) {
			add_meta_box(
				$this->slug,
				$this->title,
				array( $this, '_render_meta_box' ),
				$this->post_type,
				'normal',
				$this->priority
			);
		}
	}

	function _render_meta_box() {
		?>
		<div class="killerapps-table-metaboxes">
			<div class="manage-columns">
				<input type='text' class='text new-col-label' placeholder='<?php echo $this->labels['new_col_label'] ?>'>
				<input type='button' class='button add-new-col' value='<?php echo $this->labels['add_col'] ?>'/>
			</div>
			<table>
				<thead><tr>
					<?php foreach ($this->static_columns_start as $column): ?>
						<th class="<?php echo $column['id'] ?> static static_start"><?php echo $column['label'] ?></th>
					<?php endforeach ?>
					<?php foreach ($this->static_columns_end as $column): ?>
						<th class="<?php echo $column['id'] ?> static static_end"><?php echo $column['label'] ?></th>
					<?php endforeach ?>
					<th class="static actions">Actions</th>
				</tr></thead>
				<tbody>
				</tbody>
				<tfoot>
					<tr class="no-rows hide"><td colspan="100%"><?php echo $this->labels['no_rows'] ?></td></tr>
					<tr class="remove-cols">
					<?php foreach ($this->static_columns_start as $column): ?>
						<td class="<?php echo $column['id'] ?> static static_start"></td>
					<?php endforeach ?>
					<?php foreach ($this->_dynamic_columns() as $column): ?>
						<td class="<?php echo $column['id'] ?> dynamic"><input type="button" class="button remove remove_col" value="X"/></td>
					<?php endforeach ?>
					<?php foreach ($this->static_columns_end as $column): ?>
						<td class="<?php echo $column['id'] ?> static static_end"></td>
					<?php endforeach ?>
					<td class="static actions"></td>
					</tr>

					<tr class="row-template">
					<?php foreach ($this->static_columns_start as $column): ?>
						<td class="<?php echo $column['id'] ?> static static_start"><?php $this->_cell_html($column)?></td>
					<?php endforeach ?>
					<?php foreach ($this->_dynamic_columns() as $column): ?>
						<td class="<?php echo $column['id'] ?> dynamic"><?php $this->_cell_html($column)?></td>
					<?php endforeach ?>
					<?php foreach ($this->static_columns_end as $column): ?>
						<td class="<?php echo $column['id'] ?> static static_end"><?php $this->_cell_html($column)?></td>
					<?php endforeach ?>
					<td class="static actions"><input type="button" class="button remove remove_row" value="X"/></td>
					</tr>

				</tfoot>
			</table>
			<div class="manage-rows">
				<input type='button' class='button add-new-row' value='<?php echo $this->labels['add_row'] ?>'/>
			</div>
		</div>
		<?php
	}

	private function _dynamic_columns() {
		return array();
	}
	private function _cell_html($column) {
		$type = $column['type'] ? $column['type'] : 'text';
		if (!$column['options'] || !$column['options']['multi']) {
			echo "<input type='{$type}' class='$type'/>";
		}
	}
}


