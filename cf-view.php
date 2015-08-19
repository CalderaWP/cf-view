<?php
/*
 Plugin Name: CF View
 */
if ( ! defined( 'CFCORE_PATH') ) {
	return;
}

/**
 * Load the JS.
 *
 * @todo include FooTable
 *
 * @since 0.0.1
 */
add_action( 'wp_enqueue_scripts', function() {
	wp_register_script( 'cf-view', plugin_dir_url( __FILE__ ) .'/assets/js/cf-view.js', array( 'jquery', 'footable-min' ), false, true );
});

/**
 * Create CF View interface.
 *
 * @since 0.0.1
 *
 * @param int $form_id ID of form to view
 * @param array $fields Optional. An array of fields to show. If empty, the default, all fields of form are shown.
 * @param null|int $editor_id Optional. ID of a page with the form on it, used for editing. If null, the default, no edit links are shown.
 *
 * @return string|void
 */
function cf_view( $form_id, $fields = array(), $editor_id = null  ) {
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

		include_once dirname( __FILE__ ) . '/classes/table.php';

		wp_enqueue_script( 'cf-view' );
		$class = new \calderawp\view\table( $fields, $entries, $editor_id );

		return $class->get_markup();
	}

}

