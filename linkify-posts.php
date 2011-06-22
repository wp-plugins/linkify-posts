<?php
/**
 * @package Linkify_Posts
 * @author Scott Reilly
 * @version 2.1
 */
/*
Plugin Name: Linkify Posts
Version: 2.1
Plugin URI: http://coffee2code.com/wp-plugins/linkify-posts/
Author: Scott Reilly
Author URI: http://coffee2code.com
Description: Turn a string, list, or array of post IDs and/or slugs into a list of links to those posts.

Compatible with WordPress 2.8+, 2.9+, 3.0+, 3.1+.

=>> Read the accompanying readme.txt file for instructions and documentation.
=>> Also, visit the plugin's homepage for additional information and updates.
=>> Or visit: http://wordpress.org/extend/plugins/linkify-posts/

*/

/*
Copyright (c) 2007-2011 by Scott Reilly (aka coffee2code)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy,
modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

require_once( dirname( __FILE__ ) . '/linkify-posts.widget.php' );

if ( ! function_exists( 'c2c_linkify_posts' ) ) :
/**
 * Displays links to each of any number of posts specified via post IDs and/or slugs
 *
 * @since 2.0
 *
 * @param int|array $posts A single post ID/slug, or multiple post IDs/slugs defined via an array, or multiple posts IDs/slugs defined via a comma-separated and/or space-separated string
 * @param string $before (optional) To appear before the entire post listing (if posts exist or if 'none' setting is specified)
 * @param string $after (optional) To appear after the entire post listing (if posts exist or if 'none' setting is specified)
 * @param string $between (optional) To appear between all posts
 * @param string $before_last (optional) To appear between the second-to-last and last element, if not specified, value of $between is used
 * @param string $none (optional) To appear when no posts have been found.  If blank, then the entire function doesn't display anything
 */
function c2c_linkify_posts( $posts, $before = '', $after = '', $between = ', ', $before_last = '', $none = '' ) {
	if ( empty( $posts ) )
		$posts = array();
	elseif ( ! is_array( $posts ) )
		$posts = explode( ',', str_replace( array( ', ', ' ', ',' ), ',', $posts ) );

	if ( empty( $posts ) ) {
		$response = '';
	} else {
		$links = array();
		foreach ( $posts as $id ) {
			if ( 0 == (int) $id ) {
				$my_q = new WP_Query( array( 'name' => $id ) );
				if ( $my_q->have_posts() )
					$id = $my_q->posts[0]->ID;
			}
			if ( 0 == (int) $id )
				continue;
			$title = get_the_title( $id );
			if ( $title )
				$links[] = sprintf(
					'<a href="%1$s" title="%2$s">%3$s</a>',
					get_permalink( $id ),
					esc_attr( sprintf( __( "View post: %s" ), $title ) ),
					$title
				);
		}
		if ( empty( $before_last ) ) {
			$response = implode( $between, $links );
		} else {
			switch ( $size = sizeof( $links ) ) {
				case 1:
					$response = $links[0];
					break;
				case 2:
					$response = $links[0] . $before_last . $links[1];
					break;
				default:
					$response = implode( $between, array_slice( $links, 0, $size-1 ) ) . $before_last . $links[$size-1];
			}
		}
	}
	if ( empty( $response ) ) {
		if ( empty( $none ) )
			return;
		$response = $none;
	}
	echo $before . $response . $after;
}
add_action( 'c2c_linkify_posts', 'c2c_linkify_posts', 10, 6 );
endif;

if ( ! function_exists( 'linkify_post_ids' ) ) :
/**
 * Displays links to each of any number of posts specified via post IDs and/or slugs
 *
 * @since 1.0
 * @deprecated 2.0 Use c2c_linkify_posts() instead
 */
function linkify_post_ids( $posts, $before = '', $after = '', $between = ', ', $before_last = '', $none = '' ) {
	_deprecated_function( 'linkify_post_ids', '2.0', 'c2c_linkify_posts' );
	return c2c_linkify_posts( $posts, $before, $after, $between, $before_last, $none );
}
endif;
?>