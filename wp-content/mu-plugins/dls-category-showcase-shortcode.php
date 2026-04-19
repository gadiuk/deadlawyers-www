<?php
/**
 * Plugin Name: DLS Category Showcase Shortcode
 * Description: Front-page style category sections with featured + supporting posts.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dls_category_showcase_styles' ) ) {
	/**
	 * Inline stylesheet for the category showcase shortcode.
	 *
	 * @return string
	 */
	function dls_category_showcase_styles() {
		return <<<'CSS'
.dls-category-showcase {
  --dls-cs-bg: var(--global-palette9, #f4efe4);
  --dls-cs-card: #fff;
  --dls-cs-text: var(--global-palette3, #111111);
  --dls-cs-muted: rgba(17, 17, 17, 0.68);
  --dls-cs-border: rgba(17, 17, 17, 0.08);
  --dls-cs-accent: var(--global-palette1, #111111);
  background: radial-gradient(circle at top right, rgba(205, 178, 130, 0.18), transparent 42%), var(--dls-cs-bg);
  border: 1px solid var(--dls-cs-border);
  border-radius: 20px;
  color: var(--dls-cs-text);
  margin: 1.5rem 0;
  padding: clamp(1rem, 1.6vw, 1.75rem);
}

.dls-category-showcase__header {
  margin-bottom: 1rem;
}

.dls-category-showcase__title {
  font-size: clamp(1.55rem, 2.8vw, 2.3rem);
  line-height: 1.1;
  margin: 0 0 .35rem;
}

.dls-category-showcase__subtitle {
  color: var(--dls-cs-muted);
  margin: 0;
}

.dls-category-showcase__chips {
  display: flex;
  flex-wrap: wrap;
  gap: .45rem;
  margin: .9rem 0 1.2rem;
}

.dls-category-showcase__chip {
  background: rgba(255, 255, 255, .75);
  border: 1px solid var(--dls-cs-border);
  border-radius: 999px;
  color: var(--dls-cs-text);
  font-size: .84rem;
  line-height: 1;
  padding: .5rem .7rem;
  text-decoration: none;
}

.dls-category-showcase__section {
  border-top: 1px solid var(--dls-cs-border);
  margin-top: 1.3rem;
  padding-top: 1.3rem;
}

.dls-category-showcase__section:first-of-type {
  border-top: 0;
  margin-top: 0;
  padding-top: 0;
}

.dls-category-showcase__section-head {
  align-items: baseline;
  display: flex;
  flex-wrap: wrap;
  gap: .6rem;
  justify-content: space-between;
  margin-bottom: .8rem;
}

.dls-category-showcase__section-title {
  font-size: clamp(1.15rem, 2vw, 1.45rem);
  margin: 0;
}

.dls-category-showcase__section-title a {
  color: var(--dls-cs-text);
  text-decoration: none;
}

.dls-category-showcase__all {
  color: var(--dls-cs-accent);
  font-size: .85rem;
  text-decoration: none;
}

.dls-category-showcase__grid {
  display: grid;
  gap: .85rem;
  grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr);
}

.dls-category-showcase__featured,
.dls-category-showcase__card {
  background: var(--dls-cs-card);
  border: 1px solid var(--dls-cs-border);
  border-radius: 14px;
  overflow: hidden;
}

.dls-category-showcase__featured-image,
.dls-category-showcase__card-image {
  aspect-ratio: 16/9;
  background: rgba(0, 0, 0, .04);
  display: block;
  overflow: hidden;
}

.dls-category-showcase__featured-image img,
.dls-category-showcase__card-image img {
  height: 100%;
  object-fit: cover;
  width: 100%;
}

.dls-category-showcase__featured-body,
.dls-category-showcase__card-body {
  padding: .8rem .9rem;
}

.dls-category-showcase__meta {
  color: var(--dls-cs-muted);
  font-size: .76rem;
  margin-bottom: .35rem;
}

.dls-category-showcase__featured-title,
.dls-category-showcase__card-title {
  font-size: clamp(1rem, 1.2vw, 1.2rem);
  line-height: 1.22;
  margin: 0;
}

.dls-category-showcase__featured-title a,
.dls-category-showcase__card-title a {
  color: var(--dls-cs-text);
  text-decoration: none;
}

.dls-category-showcase__featured-excerpt {
  color: var(--dls-cs-muted);
  font-size: .92rem;
  line-height: 1.52;
  margin: .5rem 0 0;
}

.dls-category-showcase__cards {
  display: grid;
  gap: .75rem;
}

@media (max-width: 900px) {
  .dls-category-showcase__grid {
    grid-template-columns: 1fr;
  }

  .dls-category-showcase__cards {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 640px) {
  .dls-category-showcase {
    border-radius: 14px;
    padding: 1rem;
  }

  .dls-category-showcase__cards {
    grid-template-columns: 1fr;
  }
}
CSS;
	}
}

if ( ! function_exists( 'dls_category_showcase_enqueue_styles' ) ) {
	/**
	 * Enqueue shortcode styles once.
	 *
	 * @return void
	 */
	function dls_category_showcase_enqueue_styles() {
		$handle = 'dls-category-showcase-shortcode';

		if ( wp_style_is( $handle, 'enqueued' ) ) {
			return;
		}

		wp_register_style( $handle, false, array(), '1.0.0' );
		wp_enqueue_style( $handle );
		wp_add_inline_style( $handle, dls_category_showcase_styles() );
	}
}

if ( ! function_exists( 'dls_category_showcase_resolve_terms' ) ) {
	/**
	 * Resolve category terms from shortcode attributes.
	 *
	 * @param string $categories Raw category list (IDs or slugs).
	 * @param int    $sections Number of sections.
	 * @return WP_Term[]
	 */
	function dls_category_showcase_resolve_terms( $categories, $sections ) {
		$sections = max( 1, min( 12, absint( $sections ) ) );
		$terms    = array();

		$categories = trim( (string) $categories );
		if ( '' !== $categories ) {
			$parts   = array_filter( array_map( 'trim', explode( ',', $categories ) ) );
			$term_ids = array();
			$slugs    = array();

			foreach ( $parts as $part ) {
				if ( ctype_digit( $part ) ) {
					$term_ids[] = absint( $part );
				} else {
					$slugs[] = sanitize_title( $part );
				}
			}

			if ( ! empty( $term_ids ) ) {
				$by_ids = get_terms(
					array(
						'taxonomy'   => 'category',
						'include'    => $term_ids,
						'hide_empty' => true,
					)
				);
				if ( is_array( $by_ids ) ) {
					$terms = array_merge( $terms, $by_ids );
				}
			}

			if ( ! empty( $slugs ) ) {
				$by_slugs = get_terms(
					array(
						'taxonomy'   => 'category',
						'slug'       => $slugs,
						'hide_empty' => true,
					)
				);
				if ( is_array( $by_slugs ) ) {
					$terms = array_merge( $terms, $by_slugs );
				}
			}

			if ( ! empty( $terms ) ) {
				$unique = array();
				foreach ( $terms as $term ) {
					if ( $term instanceof WP_Term ) {
						$unique[ $term->term_id ] = $term;
					}
				}
				$terms = array_values( $unique );
			}
		}

		if ( empty( $terms ) ) {
			$fallback = get_terms(
				array(
					'taxonomy'   => 'category',
					'hide_empty' => true,
					'orderby'    => 'count',
					'order'      => 'DESC',
					'number'     => $sections,
				)
			);
			$terms = is_array( $fallback ) ? $fallback : array();
		}

		return array_slice( array_values( $terms ), 0, $sections );
	}
}

if ( ! function_exists( 'dls_category_showcase_get_posts' ) ) {
	/**
	 * Query posts for one category.
	 *
	 * @param int $term_id Category term ID.
	 * @param int $per_page Post limit.
	 * @return WP_Post[]
	 */
	function dls_category_showcase_get_posts( $term_id, $per_page ) {
		$query = new WP_Query(
			array(
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => max( 2, min( 12, absint( $per_page ) ) ),
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'cat'                    => absint( $term_id ),
			)
		);

		return is_array( $query->posts ) ? $query->posts : array();
	}
}

if ( ! function_exists( 'dls_category_showcase_render_meta' ) ) {
	/**
	 * Render compact post meta line.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	function dls_category_showcase_render_meta( $post_id ) {
		$date = get_the_date( 'Y-m-d', $post_id );
		$author = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) );

		$parts = array();
		if ( $author ) {
			$parts[] = esc_html( $author );
		}
		if ( $date ) {
			$parts[] = esc_html( $date );
		}

		if ( empty( $parts ) ) {
			return '';
		}

		return implode( ' | ', $parts );
	}
}

if ( ! function_exists( 'dls_category_showcase_shortcode' ) ) {
	/**
	 * Render category showcase.
	 *
	 * Usage:
	 * [dls_category_showcase]
	 * [dls_category_showcase categories="novyny,analityka" sections="2" posts_per_category="5" title="Рубрики"]
	 *
	 * @param array<string,string> $atts Shortcode attributes.
	 * @return string
	 */
	function dls_category_showcase_shortcode( $atts ) {
		dls_category_showcase_enqueue_styles();

		$atts = shortcode_atts(
			array(
				'title'              => 'Рубрики та головні матеріали',
				'subtitle'           => 'Добірка найновіших публікацій у ключових категоріях.',
				'categories'         => '',
				'sections'           => 4,
				'posts_per_category' => 5,
				'show_excerpt'       => 1,
				'excerpt_words'      => 24,
			),
			$atts,
			'dls_category_showcase'
		);

		$sections           = max( 1, min( 12, absint( $atts['sections'] ) ) );
		$posts_per_category = max( 2, min( 12, absint( $atts['posts_per_category'] ) ) );
		$show_excerpt       = ! in_array( strtolower( (string) $atts['show_excerpt'] ), array( '0', 'false', 'no' ), true );
		$excerpt_words      = max( 8, min( 80, absint( $atts['excerpt_words'] ) ) );

		$terms = dls_category_showcase_resolve_terms( $atts['categories'], $sections );
		if ( empty( $terms ) ) {
			return '';
		}

		ob_start();
		?>
		<section class="dls-category-showcase" aria-label="Category showcase">
			<header class="dls-category-showcase__header">
				<h2 class="dls-category-showcase__title"><?php echo esc_html( (string) $atts['title'] ); ?></h2>
				<?php if ( '' !== trim( (string) $atts['subtitle'] ) ) : ?>
					<p class="dls-category-showcase__subtitle"><?php echo esc_html( (string) $atts['subtitle'] ); ?></p>
				<?php endif; ?>
				<nav class="dls-category-showcase__chips" aria-label="Category quick links">
					<?php foreach ( $terms as $term ) : ?>
						<a class="dls-category-showcase__chip" href="#dls-cat-<?php echo esc_attr( (string) $term->term_id ); ?>">
							<?php echo esc_html( $term->name ); ?>
						</a>
					<?php endforeach; ?>
				</nav>
			</header>

			<?php foreach ( $terms as $term ) : ?>
				<?php
				$posts = dls_category_showcase_get_posts( $term->term_id, $posts_per_category );
				if ( empty( $posts ) ) {
					continue;
				}

				$featured = array_shift( $posts );
				?>
				<section class="dls-category-showcase__section" id="dls-cat-<?php echo esc_attr( (string) $term->term_id ); ?>">
					<div class="dls-category-showcase__section-head">
						<h3 class="dls-category-showcase__section-title">
							<a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
						</h3>
						<a class="dls-category-showcase__all" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html__( 'Усі матеріали', 'default' ); ?></a>
					</div>

					<div class="dls-category-showcase__grid">
						<article class="dls-category-showcase__featured">
							<a class="dls-category-showcase__featured-image" href="<?php echo esc_url( get_permalink( $featured->ID ) ); ?>">
								<?php if ( has_post_thumbnail( $featured->ID ) ) : ?>
									<?php echo get_the_post_thumbnail( $featured->ID, 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php endif; ?>
							</a>
							<div class="dls-category-showcase__featured-body">
								<div class="dls-category-showcase__meta"><?php echo esc_html( dls_category_showcase_render_meta( $featured->ID ) ); ?></div>
								<h4 class="dls-category-showcase__featured-title">
									<a href="<?php echo esc_url( get_permalink( $featured->ID ) ); ?>"><?php echo esc_html( get_the_title( $featured->ID ) ); ?></a>
								</h4>
								<?php if ( $show_excerpt ) : ?>
									<p class="dls-category-showcase__featured-excerpt">
										<?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $featured->ID ) ), $excerpt_words, '…' ) ); ?>
									</p>
								<?php endif; ?>
							</div>
						</article>

						<div class="dls-category-showcase__cards">
							<?php foreach ( $posts as $post_item ) : ?>
								<article class="dls-category-showcase__card">
									<a class="dls-category-showcase__card-image" href="<?php echo esc_url( get_permalink( $post_item->ID ) ); ?>">
										<?php if ( has_post_thumbnail( $post_item->ID ) ) : ?>
											<?php echo get_the_post_thumbnail( $post_item->ID, 'medium' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										<?php endif; ?>
									</a>
									<div class="dls-category-showcase__card-body">
										<div class="dls-category-showcase__meta"><?php echo esc_html( dls_category_showcase_render_meta( $post_item->ID ) ); ?></div>
										<h5 class="dls-category-showcase__card-title">
											<a href="<?php echo esc_url( get_permalink( $post_item->ID ) ); ?>"><?php echo esc_html( get_the_title( $post_item->ID ) ); ?></a>
										</h5>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
			<?php endforeach; ?>
		</section>
		<?php
		return (string) ob_get_clean();
	}
}

add_shortcode( 'dls_category_showcase', 'dls_category_showcase_shortcode' );
