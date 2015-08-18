<?php
/*
 Plugin Name: CF View
 */
if ( ! defined( 'CFCORE_PATH') ) {
	return;
}


function cf_view( $form_id, $editor_id = null, $fields = array()  ) {
	if ( ! is_array( Caldera_Forms::get_form( $form_id ) ) ) {
		return;
	}

	require_once( CFCORE_PATH . 'classes/admin.php' );

	$data = Caldera_Forms_Admin::get_entries( $form_id );
	if( is_array( $data ) && isset( $data[ 'entries' ] ) && ! empty( $data ) ) {
		$entries = $data[ 'entries' ];
		if ( empty( $fields ) ) {
			$fields = $data['fields'];
		}

		$_fields = $fields;
		$fields = array();
		$form = Caldera_Forms::get_form( $form_id );

		$index = array_merge( wp_list_pluck( $form[ 'fields' ], 'ID' ),  wp_list_pluck( $form[ 'fields' ], 'slug' )  );

		//Josh - this array flip seems silly, but doing the array_merge the other way didn't work, trust me -Josh
		$index = array_flip( $index );
		foreach( $_fields as $slug => $label ) {
			if ( isset( $index[ $slug ])  ) {
				$id = $index[ $slug ];
				$fields[] = array(
					'label' => $label,
					'slug' => $slug,
					'ID' => $id
				);
			}
		}

		$class =  new cf_view( $fields, $entries, $editor_id );
		return $class->get_markup();
	}
}



class cf_view{

	private $markup;

	private $edit_mode;

	private $shortened;

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

	public function get_markup() {
		return $this->markup;
	}

	function make_table( $header, $rows ) {
		$table = sprintf( '<table class="footable">%1s <tbody>%2s</tbody></table>', $header, $rows );
		if ( ! empty( $this->shortened ) ) {
			$table .= '<div id="cf-view-full-parts" data-content="' . esc_attr( wp_json_encode( $this->shortened ) ) . '"></div>';
		}
		$this->markup = $table;
	}


	function header( $fields ) {
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

	function rows( $entries, $fields, $edit_link = false ) {


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

			if ( $this->edit_mode ) {
				$edit = add_query_arg( 'cf_id', (int) $entry_id, $edit_link );
				$edit = sprintf( '<a href="%1s" class="cf-view-edit-link" cf-view-entry-id="%2s">%3s</a>', esc_url( $edit ), esc_attr( $entry_id ), __( 'Edit', 'cf-view' ) );
				$out[] = sprintf( '<td>%1s</td>', $edit );
			}

			$out[] = '</tr>';

		}

		return implode( '', $out );

	}

	function field_value_td( $value, $field_id, $entry_id ) {
		if ( 17 < strlen( $value ) ) {
			$this->shortened[  $entry_id.$field_id ] = $value;
			$value = sprintf( '<span class="cf-view-shortened-preview">%1s</span>',
				substr( $value, 0, 17 ) . ' ' . sprintf( '<a href="#" class="cf-view-shortened-view" data-cf-shortened="%1s">%2s</a>', esc_attr( $entry_id . $field_id ), __( 'See All', 'cf-view' ) )
			);

		}
		$html = sprintf( '<td cf-view-entry-id="%1s" cf-view-field-id="%2s">%3s</td>', esc_attr( $entry_id ), esc_attr( $field_id ), $value );
		return $html;
	}

	function edit_td() {

	}
}
