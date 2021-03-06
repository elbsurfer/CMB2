<?php

require_once( 'cmb-tests-base.php' );

class CMB2_Field_Test extends CMB2_Test {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();

		$this->post_id = $this->factory->post->create();

		$this->field_args = array(
			'name' => 'Name',
			'id'   => 'test_test',
			'type' => 'text',
			'attributes' => array(
				'type' => 'number',
				'disabled' => 'disabled',
				'data-test' => 'data-value',
				'data-test' => json_encode( array(
					'name' => 'Name',
					'id'   => 'test_test',
					'type' => 'text',
				) ),
			),
			'before_field' => array( $this, 'before_field_cb' ),
			'after_field'  => 'after_field_static',
			'row_classes'  => array( $this, 'row_classes_array_cb' ),
			'default'      => array( $this, 'cb_to_set_default' ),
		);

		$this->object_id   = $this->post_id;
		$this->object_type = 'post';
		$this->group       = false;

		$this->field = new CMB2_Field( array(
			'object_id'   => $this->object_id,
			'object_type' => $this->object_type,
			'group'       => $this->group,
			'field_args'  => $this->field_args,
		) );

	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_cmb2_field_instance() {
		$this->assertInstanceOf( 'CMB2_Field', $this->field  );
	}

	public function test_cmb2_before_and_after_field_callbacks() {
		ob_start();
		$this->field->peform_param_callback( 'before_field' );
		$this->field->peform_param_callback( 'after_field' );
		// grab the data from the output buffer and add it to our $content variable
		$content = ob_get_contents();
		ob_end_clean();

		$this->assertEquals( 'before_field_cb_test_testafter_field_static', $content );
	}

	public function test_cmb2_row_classes_field_callback_with_array() {
		// Add row classes dynamically with a callback that returns an array
		$classes = $this->field->row_classes();
		$this->assertEquals( 'cmb-type-text cmb2-id-test-test table-layout type name desc before after options_cb options attributes protocols default select_all_button multiple repeatable inline on_front show_names date_format time_format description preview_size id before_field after_field row_classes _id _name', $classes );
	}

	public function test_cmb2_default_field_callback_with_array() {
		// Add row classes dynamically with a callback that returns an array
		$default = $this->field->args( 'default' );
		$this->assertEquals( 'type, name, desc, before, after, options_cb, options, attributes, protocols, default, select_all_button, multiple, repeatable, inline, on_front, show_names, date_format, time_format, description, preview_size, id, before_field, after_field, row_classes, _id, _name', $default );
	}

	public function test_cmb2_row_classes_field_callback_with_string() {

		// Test with string
		$args = $this->field_args;

		// Add row classes dynamically with a callback that returns a string
		$args['row_classes'] = array( $this, 'row_classes_string_cb' );

		$field = new CMB2_Field( array(
			'object_id' => $this->object_id,
			'object_type' => $this->object_type,
			'group' => $this->group,
			'field_args' => $args,
		) );

		$classes = $field->row_classes();

		$this->assertEquals( 'cmb-type-text cmb2-id-test-test table-layout callback with string', $classes );
	}

	public function test_cmb2_row_classes_string() {

		// Test with string
		$args = $this->field_args;

		// Add row classes statically as a string
		$args['row_classes'] = 'these are some classes';

		$field = new CMB2_Field( array(
			'object_id' => $this->object_id,
			'object_type' => $this->object_type,
			'group' => $this->group,
			'field_args' => $args,
		) );

		$classes = $field->row_classes();

		$this->assertEquals( 'cmb-type-text cmb2-id-test-test table-layout these are some classes', $classes );
	}

	public function test_empty_field_with_empty_object_id() {
		$field = new CMB2_Field( array(
			'field_args' => $this->field_args,
		) );

		// data should be empty since we have no object id
		$this->assertEmpty( $field->get_data() );

		// add some xss for good measure
		$dirty_val = 'test<html><stuff><script>xss</script><a href="http://xssattackexamples.com/">Click to Download</a>';
		$cleaned_val = sanitize_text_field( $dirty_val );

		// Make sure it sanitizes as expected
		$this->assertEquals( $cleaned_val, $field->sanitization_cb( $dirty_val ) );

		// Sanitize/store the field
		$this->assertTrue( $field->save_field( $dirty_val ) );

		// Retrieve saved value(s)
		$this->assertEquals( $cleaned_val, cmb2_options( 0 )->get( $field->id() ) );
		$this->assertEquals( array( 'test_test' => $cleaned_val ), cmb2_options( 0 )->get_options() );
	}

	public function before_field_cb( $args ) {
		echo 'before_field_cb_' . $args['id'];
	}

	public function row_classes_array_cb( $args ) {
		/**
		 * Side benefit: this will call out when default args change
		 */
		return array_keys( $args );
	}

	public function row_classes_string_cb( $args ) {
		return 'callback with string';
	}

	public function cb_to_set_default( $args ) {
		/**
		 * Side benefit: this will call out when default args change
		 */
		return implode( ', ', array_keys( $args ) );
	}

}
