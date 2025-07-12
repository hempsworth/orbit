<?php
/**
 * Allows us to update raw post content directly via the REST API.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit;

use WP_REST_Request;
use WP_Post;

/**
 * Allows us to update raw post content directly via the REST API.
 * Sadly, the REST API sanitises the post_content field, which strips out Gutenberg code.
 */
class AllowRawContentUpdates {
	use Singleton;

	/**
	 * Register the REST API hooks
	 *
	 * @return void
	 */
	public function setup() {
		add_action( 'rest_insert_post', [ $this, 'update_raw_post_content' ], 10, 3 );
		add_action( 'rest_insert_page', [ $this, 'update_raw_post_content' ], 10, 3 );
	}

	/**
	 * Directly update the post_content field in the posts table if content_raw is set in a REST request.
	 *
	 * @param WP_Post         $post     Inserted or updated post object.
	 * @param WP_REST_Request $request  Request object.
	 * @param bool            $creating True when creating a post, false when updating.
	 *
	 * @return void
	 */
	public function update_raw_post_content( WP_Post $post, WP_REST_Request $request, bool $creating ): void {

		if ( ! isset( $request['content_raw'] ) || empty( $request['content_raw'] ) ) {
			return;
		}

		global $wpdb;

		$wpdb->update(
			$wpdb->posts,
			[ 'post_content' => wp_kses_post( $request['content_raw'] ) ],
			[ 'ID' => $post->ID ],
			[ '%s' ],
			[ '%d' ]
		);
	}
}
