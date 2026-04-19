<?php
/**
 * Plugin Name: DLS Category Archive Template
 * Description: Custom category template with article feed and Jobs-style right sidebar.
 * Version: 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dls_category_archive_template_styles' ) ) {
	/**
	 * Inline styles for category archive template.
	 *
	 * @return string
	 */
	function dls_category_archive_template_styles() {
		return <<<'CSS'
.dls-category-archive-entry .entry-content-wrap {
  padding: clamp(1.15rem, 2vw, 1.75rem);
}

.dls-cat-archive__desc {
  color: rgba(17, 17, 17, 0.75);
  font-size: 1rem;
  line-height: 1.55;
  margin: .55rem 0 0;
}

.dls-cat-archive__content {
  display: grid;
  gap: .9rem;
}

.dls-cat-article {
  background: #fff;
  border: 1px solid rgba(17, 17, 17, 0.08);
  border-radius: 14px;
  display: grid;
  grid-template-columns: minmax(180px, 240px) minmax(0, 1fr);
  overflow: hidden;
}

.dls-cat-article__thumb {
  background: rgba(0, 0, 0, 0.04);
  display: block;
  min-height: 140px;
}

.dls-cat-article__thumb img {
  display: block;
  height: 100%;
  object-fit: cover;
  width: 100%;
}

.dls-cat-article__body {
  padding: .85rem 1rem;
}

.dls-cat-article__meta {
  color: rgba(17, 17, 17, 0.66);
  font-size: .8rem;
  margin-bottom: .35rem;
}

.dls-cat-article__title {
  color: #000;
  font-size: clamp(1.12rem, 1.6vw, 1.45rem);
  line-height: 1.2;
  margin: 0;
}

.dls-cat-article__title a {
  color: inherit;
  text-decoration: none;
}

.dls-cat-article__excerpt {
  color: rgba(17, 17, 17, 0.8);
  font-size: .96rem;
  line-height: 1.58;
  margin: .45rem 0 0;
}

.dls-cat-pagination {
  margin-top: .45rem;
}

.dls-cat-pagination .page-numbers {
  border: 1px solid rgba(17, 17, 17, 0.14);
  border-radius: 8px;
  display: inline-block;
  margin: 0 .2rem .2rem 0;
  min-width: 2rem;
  padding: .4rem .56rem;
  text-align: center;
  text-decoration: none;
}

.dls-cat-pagination .page-numbers.current {
  background: #111;
  border-color: #111;
  color: #fff;
}

.dls-cat-side-list {
  display: grid;
  gap: .55rem;
  list-style: none;
  margin: 0;
  padding: 0;
}

.dls-cat-side-list a {
  color: #111;
  display: inline-block;
  font-size: .93rem;
  line-height: 1.35;
  text-decoration: none;
}

.dls-cat-side-list time {
  color: rgba(17, 17, 17, 0.62);
  display: block;
  font-size: .78rem;
  margin-top: .1rem;
}

.dls-featured-job__logo {
  background: rgba(17, 17, 17, 0.05);
}

.dls-featured-job__logo .dls-job-logo-fallback {
  align-items: center;
  color: rgba(17, 17, 17, 0.7);
  display: flex;
  font-size: .72rem;
  height: 100%;
  justify-content: center;
  min-height: 52px;
  padding: .3rem;
  text-align: center;
}

@media (max-width: 1024px) {
  .dls-cat-article {
    grid-template-columns: 1fr;
  }

  .dls-cat-article__thumb {
    min-height: 180px;
  }
}

@media (max-width: 767px) {
  .dls-category-archive-entry .entry-content-wrap {
    padding: 1rem;
  }

  .dls-cat-article__body {
    padding: .75rem .85rem;
  }
}
CSS;
	}
}

if ( ! function_exists( 'dls_category_archive_template_enqueue_styles' ) ) {
	/**
	 * Enqueue category template styles.
	 *
	 * @return void
	 */
	function dls_category_archive_template_enqueue_styles() {
		if ( ! is_category() ) {
			return;
		}

		$handle = 'dls-category-archive-template';
		wp_register_style( $handle, false, array(), '2.0.0' );
		wp_enqueue_style( $handle );
		wp_add_inline_style( $handle, dls_category_archive_template_styles() );
	}
}
add_action( 'wp_enqueue_scripts', 'dls_category_archive_template_enqueue_styles', 30 );

if ( ! function_exists( 'dls_category_archive_template_extract_term_name' ) ) {
	/**
	 * Extract first taxonomy term name from embedded REST payload.
	 *
	 * @param array<string,mixed> $job      Job payload.
	 * @param string              $taxonomy Taxonomy name.
	 * @return string
	 */
	function dls_category_archive_template_extract_term_name( $job, $taxonomy ) {
		if ( empty( $job['_embedded']['wp:term'] ) || ! is_array( $job['_embedded']['wp:term'] ) ) {
			return '';
		}

		foreach ( $job['_embedded']['wp:term'] as $term_group ) {
			if ( ! is_array( $term_group ) ) {
				continue;
			}

			foreach ( $term_group as $term ) {
				if ( ! is_array( $term ) ) {
					continue;
				}

				if ( isset( $term['taxonomy'], $term['name'] ) && $taxonomy === (string) $term['taxonomy'] ) {
					return ltrim( trim( wp_strip_all_tags( (string) $term['name'] ) ), '_' );
				}
			}
		}

		return '';
	}
}

if ( ! function_exists( 'dls_category_archive_template_extract_logo_url' ) ) {
	/**
	 * Extract logo URL from embedded media payload.
	 *
	 * @param array<string,mixed> $job Job payload.
	 * @return string
	 */
	function dls_category_archive_template_extract_logo_url( $job ) {
		if ( empty( $job['_embedded']['wp:featuredmedia'][0] ) || ! is_array( $job['_embedded']['wp:featuredmedia'][0] ) ) {
			return '';
		}

		$media = $job['_embedded']['wp:featuredmedia'][0];

		$sizes = array(
			'medium',
			'medium_large',
			'large',
			'cariera-avatar',
		);

		foreach ( $sizes as $size_key ) {
			if ( ! empty( $media['media_details']['sizes'][ $size_key ]['source_url'] ) ) {
				return esc_url_raw( (string) $media['media_details']['sizes'][ $size_key ]['source_url'] );
			}
		}

		if ( ! empty( $media['source_url'] ) ) {
			return esc_url_raw( (string) $media['source_url'] );
		}

		return '';
	}
}

if ( ! function_exists( 'dls_category_archive_template_extract_work_mode' ) ) {
	/**
	 * Extract readable work mode from job class list.
	 *
	 * @param array<string,mixed> $job Job payload.
	 * @return string
	 */
	function dls_category_archive_template_extract_work_mode( $job ) {
		$map = array(
			'hybrid'    => 'Гібридно',
			'gibrydno'  => 'Гібридно',
			'remote'    => 'Віддалено',
			'viddaleno' => 'Віддалено',
			'office'    => 'В офісі',
			'offfice'   => 'В офісі',
			'v-ofisi'   => 'В офісі',
		);

		if ( ! empty( $job['class_list'] ) && is_array( $job['class_list'] ) ) {
			foreach ( $job['class_list'] as $class_name ) {
				$class_name = (string) $class_name;

				if ( 0 === strpos( $class_name, 'job_listing_type-' ) ) {
					$slug = strtolower( (string) substr( $class_name, strlen( 'job_listing_type-' ) ) );
					if ( isset( $map[ $slug ] ) ) {
						return $map[ $slug ];
					}
				}

				if ( 0 === strpos( $class_name, 'job-type-' ) ) {
					$slug = strtolower( (string) substr( $class_name, strlen( 'job-type-' ) ) );
					if ( isset( $map[ $slug ] ) ) {
						return $map[ $slug ];
					}
				}
			}
		}

		return '';
	}
}

if ( ! function_exists( 'dls_category_archive_template_extract_company' ) ) {
	/**
	 * Extract company name from title/media fallback.
	 *
	 * @param string              $title Job title.
	 * @param array<string,mixed> $job   Job payload.
	 * @return string
	 */
	function dls_category_archive_template_extract_company( $title, $job ) {
		$title = trim( (string) $title );

		if ( false !== strpos( $title, '@' ) ) {
			$parts = explode( '@', $title, 2 );
			if ( ! empty( $parts[1] ) ) {
				return trim( wp_strip_all_tags( (string) $parts[1] ) );
			}
		}

		if ( ! empty( $job['_embedded']['wp:featuredmedia'][0]['alt_text'] ) ) {
			return trim( wp_strip_all_tags( (string) $job['_embedded']['wp:featuredmedia'][0]['alt_text'] ) );
		}

		return '';
	}
}

if ( ! function_exists( 'dls_category_archive_template_fetch_jobs' ) ) {
	/**
	 * Fetch featured jobs from Jobs site REST API.
	 *
	 * @param int $limit Number of jobs to return.
	 * @return array<int,array<string,mixed>>
	 */
	function dls_category_archive_template_fetch_jobs( $limit = 8 ) {
		$limit = max( 1, min( 12, absint( $limit ) ) );

		$transient_key = 'dls_cat_archive_jobs_v2';
		$cached        = get_transient( $transient_key );
		if ( is_array( $cached ) ) {
			return array_slice( $cached, 0, $limit );
		}

		$url = 'https://jobs.deadlawyers.org/wp-json/wp/v2/job-listings?per_page=24&_embed=1';

		$response = wp_remote_get(
			$url,
			array(
				'timeout'     => 8,
				'redirection' => 2,
				'user-agent'  => 'DeadLawyers-WWW-CategoryTemplate/2.0; ' . home_url( '/' ),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return array();
		}

		$rows = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $rows ) ) {
			return array();
		}

		$featured = array();
		$regular  = array();

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$title = wp_strip_all_tags( (string) ( $row['title']['rendered'] ?? '' ) );
			$link  = esc_url_raw( (string) ( $row['link'] ?? '' ) );

			if ( '' === $title || '' === $link ) {
				continue;
			}

			$item = array(
				'title'       => $title,
				'link'        => $link,
				'company'     => dls_category_archive_template_extract_company( $title, $row ),
				'region'      => dls_category_archive_template_extract_term_name( $row, 'job_listing_region' ),
				'work_mode'   => dls_category_archive_template_extract_work_mode( $row ),
				'logo'        => dls_category_archive_template_extract_logo_url( $row ),
				'is_featured' => in_array( (string) ( $row['meta']['_featured'] ?? '' ), array( '1', 'true' ), true ),
			);

			if ( $item['is_featured'] ) {
				$featured[] = $item;
			} else {
				$regular[] = $item;
			}
		}

		$jobs = array_slice( array_merge( $featured, $regular ), 0, 12 );

		set_transient( $transient_key, $jobs, 5 * MINUTE_IN_SECONDS );

		return array_slice( $jobs, 0, $limit );
	}
}

if ( ! function_exists( 'dls_category_archive_template_get_sidebar_posts' ) ) {
	/**
	 * Collect compact sidebar content links.
	 *
	 * @param int   $term_id      Category ID.
	 * @param int[] $exclude_ids  Post IDs to exclude.
	 * @param int   $limit        Number of items.
	 * @return WP_Post[]
	 */
	function dls_category_archive_template_get_sidebar_posts( $term_id, $exclude_ids = array(), $limit = 8 ) {
		$query = new WP_Query(
			array(
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => max( 1, min( 12, absint( $limit ) ) ),
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'cat'                    => absint( $term_id ),
				'post__not_in'           => array_map( 'absint', (array) $exclude_ids ),
			)
		);

		return is_array( $query->posts ) ? $query->posts : array();
	}
}

if ( ! function_exists( 'dls_category_archive_template_render' ) ) {
	/**
	 * Render custom category archive template.
	 *
	 * @return void
	 */
	function dls_category_archive_template_render() {
		if ( is_admin() || wp_doing_ajax() || is_feed() || ! is_category() ) {
			return;
		}

		$term = get_queried_object();
		if ( ! ( $term instanceof WP_Term ) || 'category' !== $term->taxonomy ) {
			return;
		}

		global $wp_query;
		$main_post_ids = is_array( $wp_query->posts ?? null ) ? wp_list_pluck( $wp_query->posts, 'ID' ) : array();

		$sidebar_posts = dls_category_archive_template_get_sidebar_posts( (int) $term->term_id, $main_post_ids, 8 );
		$jobs          = dls_category_archive_template_fetch_jobs( 8 );

		get_header();
		?>
		<div id="primary" class="content-area dls-category-archive-area">
			<div class="content-container site-container">
				<div id="main" class="site-main">
					<div class="content-wrap">
						<article class="entry content-bg single-entry dls-category-archive-entry">
							<div class="entry-content-wrap">
								<header class="entry-header post-title title-align-left title-tablet-align-inherit title-mobile-align-inherit">
									<div class="entry-taxonomies">
										<span class="category-links term-links category-style-normal"><?php echo esc_html__( 'Рубрика', 'default' ); ?></span>
									</div>
									<h1 class="entry-title"><?php echo esc_html( single_cat_title( '', false ) ); ?></h1>
									<?php $description = term_description( $term, 'category' ); ?>
									<?php if ( '' !== trim( wp_strip_all_tags( (string) $description ) ) ) : ?>
										<p class="dls-cat-archive__desc"><?php echo esc_html( wp_strip_all_tags( (string) $description ) ); ?></p>
									<?php endif; ?>
								</header>

								<div class="entry-content single-content dls-cat-archive__content">
									<?php if ( have_posts() ) : ?>
										<?php while ( have_posts() ) : the_post(); ?>
											<article class="dls-cat-article">
												<a class="dls-cat-article__thumb" href="<?php the_permalink(); ?>">
													<?php if ( has_post_thumbnail() ) : ?>
														<?php the_post_thumbnail( 'medium_large' ); ?>
													<?php endif; ?>
												</a>

												<div class="dls-cat-article__body">
													<div class="dls-cat-article__meta">
														<?php echo esc_html( get_the_date( 'Y-m-d' ) ); ?>
													</div>
													<h2 class="dls-cat-article__title">
														<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
													</h2>
													<p class="dls-cat-article__excerpt">
														<?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 34, '…' ) ); ?>
													</p>
												</div>
											</article>
										<?php endwhile; ?>

										<?php
										$pagination = paginate_links(
											array(
												'type'      => 'list',
												'prev_text' => '←',
												'next_text' => '→',
											)
										);
										if ( $pagination ) :
											?>
											<nav class="dls-cat-pagination" aria-label="<?php echo esc_attr__( 'Навігація рубрики', 'default' ); ?>">
												<?php echo wp_kses_post( $pagination ); ?>
											</nav>
										<?php endif; ?>
									<?php else : ?>
										<p><?php echo esc_html__( 'У цій рубриці ще немає матеріалів.', 'default' ); ?></p>
									<?php endif; ?>
								</div>
							</div>
						</article>
					</div>
				</div>

				<aside id="secondary" role="complementary" class="primary-sidebar widget-area sidebar-link-style-plain">
					<div class="sidebar-inner-wrap dls-core-sidebar-inner">
						<div class="dls-side-stack">
							<section class="dls-sidebar-card">
								<h2 class="dls-sidebar-title"><?php echo esc_html__( 'Контент рубрики', 'default' ); ?></h2>
								<?php if ( ! empty( $sidebar_posts ) ) : ?>
									<ul class="dls-cat-side-list">
										<?php foreach ( $sidebar_posts as $sidebar_post ) : ?>
											<li>
												<a href="<?php echo esc_url( get_permalink( $sidebar_post->ID ) ); ?>"><?php echo esc_html( get_the_title( $sidebar_post->ID ) ); ?></a>
												<time datetime="<?php echo esc_attr( get_the_date( 'c', $sidebar_post->ID ) ); ?>"><?php echo esc_html( get_the_date( 'Y-m-d', $sidebar_post->ID ) ); ?></time>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php else : ?>
									<p class="dls-sidebar-empty"><?php echo esc_html__( 'Ще немає додаткових матеріалів у цій рубриці.', 'default' ); ?></p>
								<?php endif; ?>
							</section>

							<section class="dls-sidebar-card">
								<h2 class="dls-sidebar-title"><?php echo esc_html__( 'Топові вакансії', 'default' ); ?></h2>
								<div class="dls-featured-jobs-list">
									<?php if ( ! empty( $jobs ) ) : ?>
										<?php foreach ( $jobs as $job ) : ?>
											<article class="dls-featured-job">
												<a class="dls-featured-job__link" href="<?php echo esc_url( (string) $job['link'] ); ?>" target="_blank" rel="noopener noreferrer">
													<div class="dls-featured-job__logo">
														<?php if ( ! empty( $job['logo'] ) ) : ?>
															<img src="<?php echo esc_url( (string) $job['logo'] ); ?>" alt="<?php echo esc_attr( (string) $job['company'] ); ?>" loading="lazy">
														<?php else : ?>
															<div class="dls-job-logo-fallback"><?php echo esc_html__( 'Logo', 'default' ); ?></div>
														<?php endif; ?>
													</div>

													<div class="dls-featured-job__content">
														<div class="dls-featured-job__title"><?php echo esc_html( (string) $job['title'] ); ?></div>
														<?php if ( ! empty( $job['company'] ) ) : ?>
															<div class="dls-featured-job__company"><?php echo esc_html( (string) $job['company'] ); ?></div>
														<?php endif; ?>
														<div class="dls-featured-job__meta">
															<?php if ( ! empty( $job['region'] ) ) : ?>
																<span class="dls-featured-job__type"><?php echo esc_html( (string) $job['region'] ); ?></span>
															<?php endif; ?>
															<?php if ( ! empty( $job['work_mode'] ) ) : ?>
																<span class="dls-featured-job__type"><?php echo esc_html( (string) $job['work_mode'] ); ?></span>
															<?php endif; ?>
														</div>
													</div>
												</a>
											</article>
										<?php endforeach; ?>
									<?php else : ?>
										<p class="dls-sidebar-empty"><?php echo esc_html__( 'Вакансії тимчасово недоступні. Перевірте трохи пізніше.', 'default' ); ?></p>
									<?php endif; ?>
								</div>
							</section>
						</div>
					</div>
				</aside>
			</div>
		</div>
		<?php

		get_footer();
		exit;
	}
}
add_action( 'template_redirect', 'dls_category_archive_template_render', 9 );
