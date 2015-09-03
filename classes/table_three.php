<?php
/**
 * Create a view table using FooTable 3
 *
 * @package   cf_view
 * @author    Josh Pollock <Josh@CalderaWP.com>
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 Josh Pollock for CalderaWP LLC
 */

namespace calderawp\view;


class table_three {

	/**
	 * The html for the table
	 *
	 * @since 0.0.3
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $html;

	/**
	 * Data for the rows.
	 *
	 * @since 0.0.3
	 *
	 * @access private
	 *
	 * @var array
	 */
	private $rows;

	/**
	 * Data for the columns
	 *
	 *
	 * @since 0.0.3
	 *
	 * @access private
	 *
	 *
	 * @var array
	 */
	private $columns;

	/**
	 * The URL for editing,
	 *
	 *
	 * @since 0.0.3
	 *
	 * @access private
	 *
	 */
	private $edit_url;

	/**
	 * Whether we are allowing editing or not.
	 *
	 * @since 0.0.3
	 *
	 * @access protected
	 *
	 * @var bool
	 */
	protected $edit_mode;

	/**
	 * ID fo form we are displaying entries for.
	 *
	 * @since 0.0.3
	 *
	 * @access protected
	 *
	 * @var string
	 */
	private $form_id;



	/**
	 * Constructor for class
	 *
	 * @since 0.0.3
	 *
	 * @param array $fields Fields to show
	 * @param array $entries Entries to show
	 * @param string $form_id The ID of form showing entries for.
	 * @param null|int $edit_id Optional. ID of page with form we can edit on, or null to disable editing.
	 * @param null|string $edit_permission Optional. Capability for editing.
	 */
	function __construct( $fields, $entries, $form_id, $edit_id = null, $edit_permission = null ) {

		if ( $edit_id ) {
			$this->edit_mode = true;
		}

		$this->form_id = $form_id;

		/**
		 * Filter fields for this CF View Table
		 *
		 * @since 0.0.3
		 *
		 * @param array $fields Fields of this form.
		 * @param string $form_id ID of this form.
		 */
		$fields = apply_filters( 'cf_view_table_fields', $fields, $this->form_id );

		$this->set_edit_url( $edit_id );
		$this->set_columns( $fields );
		$this->set_rows( $entries, $fields );
		$this->set_html();

	}

	/**
	 * Get config options for FooTable JS
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public function get_js_config() {
		$config = array(
			'cascade' => "true",
			'paging' => "true",
			'sorting' => "true",
			'showToggle' => "true",
			'columns' => $this->columns,
			'rows' => $this->rows,
		);

		/**
		 * Filter config for this view table
		 *
		 * @since 0.0.3
		 *
		 * @param array $config JS Config for this view table.
		 * @param string $form_id ID of this form.
		 */
		return apply_filters( 'cf_view_js_config', $config, $this->form_id );
	}

	/**
	 * Get HTML markup
	 *
	 * @since 0.0.3
	 *
	 * @return string
	 */
	public function get_html() {
		return $this->html;
	}

	/**
	 * Get row data
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public function get_rows() {
		return $this->rows;
	}

	/**
	 * Get column data
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public function get_columns() {
		return $this->columns;
	}

	/**
	 * Set markup for table
	 *
	 * @since 0.0.3
	 *
	 * @access protected
	 */
	protected function set_html(  ) {
		$table = sprintf( '<div id="cf-view-%1s"><table class="cf-view-table" id="cf-view-table-%2s"></table></div>', $this->form_id, $this->form_id );
		$this->html = $table;
	}

	/**
	 * Set columns
	 *
	 * @since 0.3.0
	 *
	 * @access protected
	 *
	 * @param array $fields Fields to display

	 *
	 * @return string
	 */
	protected function set_columns( $fields ) {

		$this->columns[] = array(
			'name' => 'entry_id',
			'title' => __( 'Entry ID', 'cf-view' )
		);


		foreach( $fields as $field ) {

			$title = $field[ 'label' ];
			$slug = $field[ 'slug' ];

			$this->columns[] = array(
				'name' => $slug,
				'title' => $title
			);

		}
		if ( $this->edit_mode ) {
			$this->columns[] = array(
				'name' => 'edit',
				'title' => __( 'Edit', 'cf-view' )
			);
		}

		/**
		 * Filter columns
		 *
		 * @since 0.0.3
		 *
		 * @param array $columns Columns for this view table
		 * @param string $form_id ID of this form.
		 */
		$this->columns = apply_filters( 'cf_view_columns', $this->columns, $this->form_id );

	}


	/**
	 * Make rows.
	 *
	 * @since 0.0.3
	 *
	 * @access protected
	 *
	 * @param array $entries Entries to display
	 * @param array $fields Fields to display
	 *
	 * @return string
	 */
	protected function set_rows( $entries, $fields ) {
		$default = apply_filters( 'cf_view_row_options_defualt', array(
			'expanded' => false
		), $this->form_id );

		$row_id = 1;
		foreach( $entries as $entry ) {
			$_row = array();
			$_row[ 'entry_id' ] = $entry_id = $entry['_entry_id'];

			$data = $entry[ 'data' ];


			foreach( $fields  as $field ) {

				$field_slug = $field[ 'slug' ];
				$field_id = $field[ 'ID' ];
				if ( isset( $data[ $field_slug ] ) ) {
					$value = $data[ $field_slug ];
				}else{
					$value = null;
				}

				$_row[ $field_slug ] = $value;




			}


			if ( $this->edit_mode ) {
				$_row[ 'edit' ] = $this->make_edit_link( $entry_id );
			}



			$this->rows[] = array(
				'options' => apply_filters( 'cf_view_row_options', $default, $entry_id, $row_id, $this->form_id ),
				'value' => $_row
			);

			$row_id++;

		}

		/**
		 * Filter rows for this view table.
		 *
		 * @since 0.0.3
		 *
		 * @param array $rows Rows for this view table
		 * @param string $form_id ID of this form.
		 */
		$this->rows = apply_filters( 'cf_view_rows', $this->rows, $this->form_id );

	}

	/**
	 * Make an edit link for a specific entry
	 *
	 * @since 0.0.3
	 *
	 * @access protected
	 *
	 * @param $entry_id
	 *
	 * @return string
	 */
	protected function make_edit_link( $entry_id ) {
		if ( $this->edit_url ) {
			$edit = add_query_arg( 'cf_id', (int) $entry_id, $this->edit_link );
			$edit = sprintf( '<a href="%1s" class="cf-view-edit-link" cf-view-entry-id="%2s">%3s</a>', esc_url( $edit ), esc_attr( $entry_id ), __( 'Edit', 'cf-view' ) );

			return $edit;

		}
	}

	/**
	 * Set edit link.
	 *
	 * @since 0.0.3
	 *
	 * @access protected
	 *
	 * @param $edit_id
	 */
	protected function set_edit_url( $edit_id ) {
		$this->edit_url = get_permalink( $edit_id );

	}

}
