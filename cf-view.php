<?php
/*
 Plugin Name: CF View
 */
if ( ! defined( 'CFCORE_PATH') ) {
	return;
}

define( 'CF_VIEW_VER', '0.0.2' );

/**
 * Load the JS.
 *
 * @since 0.0.1
 */
add_action( 'wp_enqueue_scripts', function() {
	$footable_ver = '3.0.1';

	$foos = cf_view_componets();
	foreach( $foos as $foo ) {
		wp_register_script( 'footable-' .$foo , plugin_dir_url( __FILE__ ) . "/assets/js/foo/footable.{$foo}.min.js", false, $footable_ver, true );
		wp_register_style( 'footable-' .$foo, plugin_dir_url( __FILE__ ) . "/assets/css/foo/footable.{$foo}.min.css", false, $footable_ver, false );
	}


	wp_register_style( 'footable-core', plugin_dir_url( __FILE__ ) . "/assets/css/foo/footable.core.bootstrap.css", false, $footable_ver, false );
	wp_register_script( 'footable-core', plugin_dir_url( __FILE__ ) . "/assets/js/foo/footable.core.min.js", array( 'jquery'), $footable_ver, true );

	wp_register_script( 'cf-view', plugin_dir_url( __FILE__ ) .'/assets/js/cf-view.js', array( 'jquery', 'footable-core' ), CF_VIEW_VER, true );

});

function cf_view_componets() {
	$foos = array(
		'filtering',
		'paging',
		'sorting'
	);

	return apply_filters( 'cf_view_foo_table_components', $foos );
}

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

		include_once dirname( __FILE__ ) . '/classes/table_three.php';

		wp_enqueue_script( 'footable-core' );
		wp_enqueue_style( 'footable-core' );
		$foos = cf_view_componets();
		foreach( $foos as $foo ) {
			wp_enqueue_script( 'footable-' .$foo );
			wp_enqueue_style( 'footable-' .$foo );
		}

		$class = new \calderawp\view\table_three( $fields, $entries, $form_id, $editor_id );
		wp_enqueue_script( 'cf-view' );
		wp_localize_script( 'cf-view', 'CF_VIEW_FOO_TABLE_OPTIONS', $class->get_js_config()  );

		return $class->get_html();

	}

}

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
function cf_view_two( $form_id, $fields = array(), $editor_id = null  ) {

	_deprecated_function( __FUNCTION__, 'cf_view' );
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

		wp_enqueue_script( 'footable' );
		wp_enqueue_style( 'footable' );
		wp_enqueue_script( 'cf-view' );
		$class = new \calderawp\view\table( $fields, $entries, $form_id, $editor_id );

		return $class->get_markup();

	}

}

/**
 * Shortcode callback for cf_view callback
 *
 * @since 0.0.2
 *
 * @param array $atts
 *
 * @return string|void
 */
function cf_view_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'form_id' => null,
			'fields' => array(),
			'editor_id' => 0
		),
		$atts, 'cf_view'
	);

	if ( is_null( $atts[ 'form_id' ] ) ) {
		return;
	}

	$maybe_form = Caldera_Forms::get_form( $atts[ 'form_id' ] );
	if ( is_array( $maybe_form ) ) {
		$form_id = $atts[ 'form_id' ];
	}else{
		return;

	}

	if ( ! empty( $atts[ 'fields' ] ) ) {
		$fields = explode( ',', $atts[ 'fields' ] );
	}else{
		$fields = $atts[ 'fields' ];
	}

	if ( 0 < absint( $atts[ 'editor_id' ] ) ) {
		$maybe_post = get_post( $atts[ 'editor_id' ] );
		if ( ! is_object( $maybe_post ) ) {
			$editor_id = null;
		}else{
			$editor_id = $atts[ 'editor_id' ];
		}
	}else{
		$editor_id = null;
	}

	return cf_view( $form_id, $fields, $editor_id );
}

/**
 * Add cf_view shortcode
 *
 * @since 0.0.2
 */
add_shortcode( 'cf_view', 'cf_view_shortcode' );
