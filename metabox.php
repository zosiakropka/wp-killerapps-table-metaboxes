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
		$this->static_columns_start = $static_columns['start'] ? $static_columns['start'] : array();
		$this->static_columns_end = $static_columns['end'] ? $static_columns['end'] : array();
		$this->priority = $priority;

		if (is_admin()) {
			add_action('load-post.php', array($this, '_register_meta_box'));
			add_action('load-post-new.php', array($this, '_register_meta_box'));
			add_action('save_post', array( $this, '_save' ) );
		}
	}

	function _get_meta($post) {
		
	}

	function _save($post_id) {
		$param = 'killerapps-table-metaboxes-' . $this->slug;
		if (!isset($_POST[$param]))
			return $post_id;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
		$nonce = $_POST['killerapps-table-metabox-nonce'];
		if ( ! wp_verify_nonce( $nonce, 'killerapps-table-metabox' ) )
			return $post_id;

		// @todo check permissions

		$json = $_POST[$param];
		if ($json) {
			$data = json_decode(str_replace('\\"', '"', $json), TRUE);
	
			$dynamic = $data['dynamic'];
			$rows = $data['rows'];
	
			update_post_meta($post_id, $this->slug, $data);
			// @todo validate&sanitize data
		}
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

	function _render_meta_box($post) {
		wp_nonce_field( 'killerapps-table-metabox', 'killerapps-table-metabox-nonce' );
		?>
		<div class="killerapps-table-metaboxes">
			<input class="killerapps-table-metaboxes-input" id="killerapps-table-metaboxes-<?php echo $this->slug ?>" name="killerapps-table-metaboxes-<?php echo $this->slug ?>" value=""/>
			<div class="manage-columns">
				<input type='text' class='text new-col-label' placeholder='<?php echo $this->labels['new_col_label'] ?>'>
				<input type='button' class='button add-new-col' value='<?php echo $this->labels['add_col'] ?>'/>
			</div>
			<div class="table-wrapper">
				<table>
					<thead><tr>
						<?php foreach ($this->static_columns_start as $column): ?>
							<th class="<?php echo $column['id'] ?> static static_start"><?php echo $column['label'] ?></th>
						<?php endforeach ?>
						<?php foreach ($this->_dynamic_columns($post->ID) as $column): ?>
							<th class="<?php echo $column['id'] ?> dynamic"><input class="text dynamic-header header" value="<?php echo $column['label'] ?>" data-killerapps-slug="<?php echo $column['id'] ?>"/></th>
						<?php endforeach ?>
						<?php foreach ($this->static_columns_end as $column): ?>
							<th class="<?php echo $column['id'] ?> static static_end"><?php echo $column['label'] ?></th>
						<?php endforeach ?>
						<th class="static rows-actions"><?php _e('Actions') ?></th>
					</tr></thead>
					<tbody>
						<?php foreach ($this->_rows($post->ID) as $row): ?>
							<tr>
								<?php foreach ($this->static_columns_start as $cell): $slug = $cell['id']; $value = $row[$slug]; ?>
									<td class="static static_start"><?php $this->_cell_html($cell, $value); ?></td>
								<?php endforeach ?>
								<?php foreach ($this->_dynamic_columns($post->ID) as $cell): $slug = $cell['id']; $value = $row[$slug]; ?>
									<td class="<?php echo $cell['id'] ?> dynamic"><?php $this->_cell_html($cell, $value) ?></td>
								<?php endforeach ?>
								<?php foreach ($this->static_columns_end as $cell): $slug = $cell['id']; $value = $row[$slug]; ?>
									<td class="static static_end"><?php $this->_cell_html($cell, $value); ?></td>
								<?php endforeach ?>
								<td action="rows-actions">x </td>
							</tr>
						<?php endforeach ?>
					</tbody>
					<tfoot>
						<tr class="no-rows hide"><td colspan="100%"><?php echo $this->labels['no_rows'] ?></td></tr>
						<tr class="cols-actions">
						<?php foreach ($this->static_columns_start as $column): ?>
							<td class="<?php echo $column['id'] ?> static static_start"></td>
						<?php endforeach ?>
						<?php foreach ($this->_dynamic_columns($post->ID) as $column): ?>
							<td class="<?php echo $column['id'] ?> dynamic"><input type="button" class="button remove remove_col" value="X"/></td>
						<?php endforeach ?>
						<?php foreach ($this->static_columns_end as $column): ?>
							<td class="<?php echo $column['id'] ?> static static_end"></td>
						<?php endforeach ?>
						<td class="static rows-actions"></td>
						</tr>

						<tr class="row-template">
						<?php foreach ($this->static_columns_start as $column): ?>
							<td class="<?php echo $column['id'] ?> static static_start"><?php $this->_cell_html($column)?></td>
						<?php endforeach ?>
						<?php foreach ($this->_dynamic_columns($post->ID) as $column): ?>
							<td class="<?php echo $column['id'] ?> dynamic"><?php $this->_cell_html($column)?></td>
						<?php endforeach ?>
						<?php foreach ($this->static_columns_end as $column): ?>
							<td class="<?php echo $column['id'] ?> static static_end"><?php $this->_cell_html($column)?></td>
						<?php endforeach ?>
						<td class="static rows-actions"><input type="button" class="button remove remove_row" value="X"/></td>
						</tr>
	
					</tfoot>
				</table>
			</div>
			<form class="manage-rows">
				<input type='button' class='button add-new-row' value='<?php echo $this->labels['add_row'] ?>'/>
			</form>
		</div>
		<?php
	}

	private function _dynamic_columns($post_id) {
		$data = get_post_meta($post_id, $this->slug, TRUE);
		if (!$data) return array();
		$columns = array();
		foreach ($data['dynamic'] as $id => $label) {
			$columns[] = array (
				'id' => $id,
				'label' => $label,
				'type' => $text,
				'dynamic' => TRUE
			);
		}
		return $columns;
	}

	private function _cols($post_id) {
		return array_merge($this->static_columns_start, $this->_dynamic_columns($post_id), $this->static_columns_end);
	}

	private function _rows($post_id) {
		$data = get_post_meta($post_id, $this->slug, TRUE);
		if (!$data) return array();
		return $data['rows'];
	}

	private function _cell_html($cell, $value="") {
		$type = $cell['type'] ? $cell['type'] : 'text';
		$slug = $cell['id'];
		if (!$cell['options'] || !$cell['options']['multi']) {
			switch ($type) {
				case "checkbox":
					$checked = $value?'checked':'';
					echo "<input type='{$type}' class='$type' data-killerapps-slug='{$slug}' value='{$value}' {$checked}/>";
					break;
				default:
					echo "<input type='{$type}' class='$type' data-killerapps-slug='{$slug}' value='{$value}'/>";
					break;
			}
		}
	}
}


