<?php

namespace calderawp\view;


class table{

	/**
	 * Will hold final markup
	 *
	 * @since 0.1.0
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $markup;

	/**
	 * Whether we are allowing editing or not.
	 *
	 * @since 0.1.0
	 *
	 * @access protected
	 *
	 * @var bool
	 */
	protected $edit_mode;

	/**
	 * Holds content for full content of the shortended entry displays.
	 *
	 * @since 0.0.1
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $shortened;

	/**
	 * Constructor for class
	 *
	 * @since 0.0.1
	 *
	 * @param array $fields Fields to show
	 * @param array $entries Entries to show
	 * @param null|int $edit_id Optional. ID of page with form we can edit on, or null to disable editing.
	 * @param null|string $edit_permission Optional. Capabiluity for edditing.
	 */
	function __construct( $fields, $entries, $edit_id = null, $edit_permission = null ) {
		if ( $edit_id ) {
			$this->edit_mode = true;
		}
		$header = $this->header( $fields );
		$rows = $this->rows( $entries, $fields, get_permalink( $edit_id ) );

		if ( is_string( $header ) && is_string( $rows ) ) {
			$this->make_table( $header, $rows );
		}
	}

	/**
	 * Get rendered markup
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_markup() {
		return $this->markup;
	}

	/**
	 * Make a table from header, row and extra stuff
	 *
	 * @since 0.1.0
	 *
	 * @access protected
	 *
	 * @param string $header Header
	 * @param string $rows
	 */
	protected function make_table( $header, $rows ) {
		$table = sprintf( '<div id="cf-view"><table class="footable" id="cf-view-table">%1s <tbody>%2s</tbody></table></div>', $header, $rows );
		$table = $this->extra( $table );
		$this->markup = $table;
	}

	/**
	 * Add extra stuff at bottom of tab
	 *
	 * @since 0.1.0
	 *
	 * @access protected
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	protected function extra( $table ) {
		if ( ! empty( $this->shortened ) ) {
			$table .= '<div id="cf-view-full-parts" data-content="' . esc_attr( wp_json_encode( $this->shortened ) ) . '"></div>';
		}else{
			$table .= '<div id="cf-view-full-parts" data-content="' . esc_attr( wp_json_encode( array() ) ) . '"></div>';
		}

		$table .= '<div id="cf-view-full-viewer" style="visibility:hidden;" aria-display="false"></div><a href="#" id="cf-full-close" style="visibility:hidden;" aria-display="false">X</a>';

		return $table;

	}


	/**
	 * Prepare header.
	 *
	 * @since 0.1.0
	 *
	 * @access protected
	 *
	 * @param array $fields Fields to display
	 *
	 * @return string
	 */
	protected function header( $fields ) {
		$out[] = '<thead><tr>';
		foreach( $fields as $field ) {
			$id = $field[ 'ID' ];
			$name = $field[ 'label' ];
			$out[] = sprintf( '<th data-toggle="true" id="header-%1s">%2s</th>', esc_attr( $id ), $name );
		}
		if ( $this->edit_mode ) {
			$out[] = sprintf( '<th data-toggle="false" id="header-edit">%2s</th>', __( 'Edit', 'cf-view' ) );
		}
		$out[] = '</tr></thead>';

		return implode( '', $out );
	}

	/**
	 * Make rows.
	 *
	 * @since 0.1.0
	 *
	 * @access protected
	 *
	 * @param array $entries Entries to display
	 * @param array $fields Fields to display
	 * @param bool|false $edit_link Optional. The link to edit, or false if none is to be used.
	 *
	 * @return string
	 */
	protected function rows( $entries, $fields, $edit_link = false ) {

		foreach( $entries as $entry ) {
			$entry_id = $entry['_entry_id'];

			$data = $entry[ 'data' ];
			$out[] = sprintf( '<tr cf-view-entry-id="%1s" class="cf-view-entry-data">', esc_attr( $entry_id ) );

			foreach( $fields  as $field ) {

				$field_slug = $field[ 'slug' ];
				$field_id = $field[ 'ID' ];
				if ( isset( $data[ $field_slug ] ) ) {
					$value = $data[ $field_slug ];
					$out[] = $this->field_value_td( $value, $field_id, $entry_id );
				}else{
					$out[] = '<td></td>';
				}




			}

			//@todo use $this->edit_td() here
			if ( $this->edit_mode ) {
				$edit = add_query_arg( 'cf_id', (int) $entry_id, $edit_link );
				$edit = sprintf( '<a href="%1s" class="cf-view-edit-link" cf-view-entry-id="%2s">%3s</a>', esc_url( $edit ), esc_attr( $entry_id ), __( 'Edit', 'cf-view' ) );
				$out[] = sprintf( '<td>%1s</td>', $edit );
			}

			$out[] = '</tr>';

		}

		return implode( '', $out );

	}

	/**
	 * Create markup for a td element of a field value.
	 *
	 * @since 0.0.1
	 *
	 * @access protected
	 *
	 * @param string $value Field value
	 * @param string $field_id ID of field
	 * @param int|string $entry_id ID of entry
	 *
	 * @return string
	 */
	protected  function field_value_td( $value, $field_id, $entry_id ) {
		if ( 17 < strlen( $value ) ) {
			$this->shortened[  $entry_id.$field_id ] = $value;
			$value = sprintf( '<span class="cf-view-shortened-preview">%1s</span>',
				substr( $value, 0, 17 ) . ' ' . sprintf( '<a href="#" class="cf-view-shortened-view" data-cf-shortened="%1s">%2s</a>', esc_attr( $entry_id . $field_id ), __( 'See All', 'cf-view' ) )
			);

		}
		$html = sprintf( '<td cf-view-entry-id="%1s" cf-view-field-id="%2s">%3s</td>', esc_attr( $entry_id ), esc_attr( $field_id ), $value );
		return $html;
	}


	protected function edit_td() {

	}
}
