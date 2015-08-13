<?php

class Linkify_Posts_Test extends WP_UnitTestCase {

	private static $post_ids = array();

	function setUp() {
		parent::setUp();
		$this->post_ids = $this->factory->post->create_many( 5 );
	}


	/*
	 *
	 * HELPER FUNCTIONS
	 *
	 */


	function get_slug( $post_id ) {
		return get_post( $post_id )->post_name;
	}

	function expected_output( $count, $lowest_id, $between = ', ', $post_num = 1 ) {
		$str = '';
		$j = $lowest_id;
		for ( $n = 1, $i = $post_num; $n <= $count; $n++, $i++ ) {
			if ( ! empty( $str ) ) {
				$str .= $between;
			}
			$str .= '<a href="http://example.org/?p=' . $j . '" title="View post: Post title ' . $i . '">Post title ' . $i . '</a>';
			$j++;
		}
		return $str;
	}

	function get_results( $args, $direct_call = true, $use_deprecated = false ) {
		ob_start();

		$function = $use_deprecated ? 'linkify_post_ids' : 'c2c_linkify_posts';

		if ( $direct_call ) {
			call_user_func_array( $function, $args );
		} else {
			do_action_ref_array( $function, $args );
		}

		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}


	/*
	 *
	 * TESTS
	 *
	 */


	function test_single_id() {
		$this->assertEquals( $this->expected_output( 1, $this->post_ids[0] ), $this->get_results( array( $this->post_ids[0] ) ) );
		$this->assertEquals( $this->expected_output( 1, $this->post_ids[0] ), $this->get_results( array( $this->post_ids[0], false ) ) );
	}

	function test_array_of_ids() {
		$this->assertEquals( $this->expected_output( 5, $this->post_ids[0] ), $this->get_results( array( $this->post_ids ) ) );
		$this->assertEquals( $this->expected_output( 5, $this->post_ids[0] ), $this->get_results( array( $this->post_ids ), false ) );
	}

	function test_single_slug() {
		$post = get_post( $this->post_ids[0] );
		$this->assertEquals( $this->expected_output( 1, $post->ID ), $this->get_results( array( $post->post_name ) ) );
	}

	function test_array_of_slugs() {
		$post_slugs = array_map( array( $this, 'get_slug' ), $this->post_ids );
		$this->assertEquals( $this->expected_output( 5, $this->post_ids[0] ), $this->get_results( array( $post_slugs ) ) );
		$this->assertEquals( $this->expected_output( 5, $this->post_ids[0] ), $this->get_results( array( $post_slugs ), false ) );
	}

	function test_all_empty_posts() {
		$this->assertEmpty( $this->get_results( array( '' ) ) );
		$this->assertEmpty( $this->get_results( array( array() ) ) );
		$this->assertEmpty( $this->get_results( array( array( array(), '' ) ) ) );
	}

	function test_an_empty_post() {
		$post_ids = array_merge( array( '' ), $this->post_ids );
		$this->assertEquals( $this->expected_output( 5, $this->post_ids[0] ), $this->get_results( array( $post_ids ) ) );
		$this->assertEquals( $this->expected_output( 5, $this->post_ids[0] ), $this->get_results( array( $post_ids ), false ) );
	}

	function test_all_invalid_posts() {
		$this->assertEmpty( $this->get_results( array( 99999999 ) ) );
		$this->assertEmpty( $this->get_results( array( 'not-a-post' ) ) );
		$this->assertEmpty( $this->get_results( array( 'not-a-post' ), false ) );
	}

	function test_an_invalid_post() {
		$post_ids = array_merge( array( 99999999 ), $this->post_ids );
		$this->assertEquals( $this->expected_output( 5, $this->post_ids[0] ), $this->get_results( array( $post_ids ) ) );
		$this->assertEquals( $this->expected_output( 5, $this->post_ids[0] ), $this->get_results( array( $post_ids ), false ) );
	}

	function test_arguments_before_and_after() {
		$expected = '<div>' . $this->expected_output( 5, $this->post_ids[0] ) . '</div>';
		$this->assertEquals( $expected, $this->get_results( array( $this->post_ids, '<div>', '</div>' ) ) );
		$this->assertEquals( $expected, $this->get_results( array( $this->post_ids, '<div>', '</div>' ), false ) );
	}

	function test_argument_between() {
		$expected = '<ul><li>' . $this->expected_output( 5, $this->post_ids[0], '</li><li>' ) . '</li></ul>';
		$this->assertEquals( $expected, $this->get_results( array( $this->post_ids, '<ul><li>', '</li></ul>', '</li><li>' ) ) );
		$this->assertEquals( $expected, $this->get_results( array( $this->post_ids, '<ul><li>', '</li></ul>', '</li><li>' ), false ) );
	}

	function test_argument_before_last() {
		$before_last = ', and ';
		$expected = $this->expected_output( 4, $this->post_ids[0] ) . $before_last . $this->expected_output( 1, $this->post_ids[4], ', ', 5 );
		$this->assertEquals( $expected, $this->get_results( array( $this->post_ids, '', '', ', ', $before_last ) ) );
		$this->assertEquals( $expected, $this->get_results( array( $this->post_ids, '', '', ', ', $before_last ), false ) );
	}

	function test_argument_none() {
		$missing = 'No posts to list.';
		$expected = '<ul><li>' . $missing . '</li></ul>';
		$this->assertEquals( $expected, $this->get_results( array( array(), '<ul><li>', '</li></ul>', '</li><li>', '', $missing ) ) );
		$this->assertEquals( $expected, $this->get_results( array( array(), '<ul><li>', '</li></ul>', '</li><li>', '', $missing ), false ) );
	}

	/**
	 * @expectedDeprecated linkify_post_ids
	 */
	function test_deprecated_function() {
		$this->assertEquals( $this->expected_output( 1, $this->post_ids[0] ), $this->get_results( array( $this->post_ids[0] ), true, true ) );
		$this->assertEquals( $this->expected_output( 5, $this->post_ids[0] ), $this->get_results( array( $this->post_ids ), true, true ) );
		$post = get_post( $this->post_ids[0] );
		$this->assertEquals( $this->expected_output( 1, $post->ID ), $this->get_results( array( $post->post_name ), true, true ) );
		$post_slugs = array_map( array( $this, 'get_slug' ), $this->post_ids );
		$this->assertEquals( $this->expected_output( 5, $this->post_ids[0] ), $this->get_results( array( $post_slugs ), true, true ) );
	}

}
