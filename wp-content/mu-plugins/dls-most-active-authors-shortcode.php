<?php
/**
 * Plugin Name: DLS Most Active Authors Shortcode
 * Description: Provides [dls_most_active_authors] shortcode for rendering top active authors.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dls_most_active_authors_get_rows' ) ) {
	/**
	 * Query top authors by number of published posts.
	 *
	 * Counts posts where the user is either:
	 * - primary author (posts.post_author), or
	 * - additional native author (_dls_post_author meta).
	 *
	 * @param int $limit Number of authors.
	 * @param int $days Optional lookback window in days; 0 means all time.
	 * @return array<int, array{author_id:int,total_posts:int}>
	 */
	function dls_most_active_authors_get_rows( $limit, $days ) {
		global $wpdb;

		$limit = max( 1, min( 50, absint( $limit ) ) );
		$days  = max( 0, absint( $days ) );

		$cache_key = sprintf( 'dls_most_active_authors_%d_%d', $limit, $days );
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$params = array();
		$date_sql_primary = '';
		$date_sql_meta    = '';

		if ( $days > 0 ) {
			$cutoff = gmdate( 'Y-m-d H:i:s', time() - ( DAY_IN_SECONDS * $days ) );
			$date_sql_primary = ' AND p.post_date_gmt >= %s';
			$date_sql_meta    = ' AND p.post_date_gmt >= %s';
			$params[]         = $cutoff;
			$params[]         = $cutoff;
		}

		$sql = "
			SELECT t.author_id, COUNT(DISTINCT t.post_id) AS total_posts
			FROM (
				SELECT p.ID AS post_id, p.post_author AS author_id
				FROM {$wpdb->posts} p
				WHERE p.post_type = 'post'
					AND p.post_status = 'publish'
					AND p.post_author > 0
					{$date_sql_primary}

				UNION ALL

				SELECT p.ID AS post_id, CAST(pm.meta_value AS UNSIGNED) AS author_id
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm
					ON pm.post_id = p.ID
					AND pm.meta_key = '_dls_post_author'
				WHERE p.post_type = 'post'
					AND p.post_status = 'publish'
					AND CAST(pm.meta_value AS UNSIGNED) > 0
					{$date_sql_meta}
			) t
			INNER JOIN {$wpdb->users} u ON u.ID = t.author_id
			GROUP BY t.author_id
			ORDER BY total_posts DESC, t.author_id ASC
			LIMIT %d
		";

		$params[] = $limit;

		$prepared = $wpdb->prepare( $sql, $params );
		$rows     = $wpdb->get_results( $prepared, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$out = array();
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$out[] = array(
					'author_id'   => absint( $row['author_id'] ),
					'total_posts' => absint( $row['total_posts'] ),
				);
			}
		}

		set_transient( $cache_key, $out, 10 * MINUTE_IN_SECONDS );
		return $out;
	}
}

if ( ! function_exists( 'dls_most_active_authors_shortcode' ) ) {
	/**
	 * Render [dls_most_active_authors].
	 *
	 * Attributes:
	 * - limit: number of authors (default 10, max 50)
	 * - days: lookback window in days (default 365, 0 = all time)
	 * - show_count: 1/0 to show post count (default 1)
	 * - title: optional heading text
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	function dls_most_active_authors_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'limit'      => 10,
				'days'       => 365,
				'show_count' => 1,
				'title'      => '',
			),
			$atts,
			'dls_most_active_authors'
		);

		$limit      = max( 1, min( 50, absint( $atts['limit'] ) ) );
		$days       = max( 0, absint( $atts['days'] ) );
		$show_count = ! in_array( strtolower( (string) $atts['show_count'] ), array( '0', 'false', 'no' ), true );
		$title      = sanitize_text_field( (string) $atts['title'] );

		$rows = dls_most_active_authors_get_rows( $limit, $days );
		if ( empty( $rows ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="dls-most-active-authors">
			<?php if ( '' !== $title ) : ?>
				<h3 class="dls-most-active-authors__title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
			<ol class="dls-most-active-authors__list">
				<?php foreach ( $rows as $row ) : ?>
					<?php
					$author_id = absint( $row['author_id'] );
					$count     = absint( $row['total_posts'] );
					$user      = get_userdata( $author_id );
					if ( ! ( $user instanceof WP_User ) ) {
						continue;
					}
					?>
					<li class="dls-most-active-authors__item">
						<a class="dls-most-active-authors__link" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>">
							<?php echo esc_html( $user->display_name ); ?>
						</a>
						<?php if ( $show_count ) : ?>
							<span class="dls-most-active-authors__count">(<?php echo esc_html( (string) $count ); ?>)</span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}

add_shortcode( 'dls_most_active_authors', 'dls_most_active_authors_shortcode' );
