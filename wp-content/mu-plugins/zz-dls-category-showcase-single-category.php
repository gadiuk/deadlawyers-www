<?php
/**
 * Plugin Name: DLS Category Archive Template
 * Description: Safe category template with homepage-like cards and right rail jobs/content.
 * Version: 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'dls_cat_tpl_styles' ) ) {
    /**
     * Inline CSS for category template.
     *
     * @return string
     */
    function dls_cat_tpl_styles() {
        return <<<'CSS'
.dls-cat-template .entry-content-wrap {
  padding: clamp(1rem, 1.8vw, 1.6rem);
}

.dls-cat-template .dls-cat-kicker {
  color: rgba(17, 17, 17, 0.65);
  font-size: .82rem;
  font-weight: 600;
  letter-spacing: .04em;
  margin: 0 0 .25rem;
  text-transform: uppercase;
}

.dls-cat-template .dls-cat-title {
  color: #000;
  font-size: clamp(1.9rem, 3.2vw, 2.85rem);
  line-height: 1.05;
  margin: 0;
}

.dls-cat-template .dls-cat-desc {
  color: rgba(17, 17, 17, 0.78);
  line-height: 1.56;
  margin: .7rem 0 0;
  max-width: 74ch;
}

.dls-cat-template .dls-cat-main {
  display: grid;
  gap: 1rem;
}

.dls-cat-template .dls-cat-lead {
  background: #fff;
  border: 1px solid rgba(17, 17, 17, 0.08);
  border-radius: 16px;
  overflow: hidden;
}

.dls-cat-template .dls-cat-lead .dls-story-card__media,
.dls-cat-template .dls-story-card .dls-story-card__media {
  background: rgba(17, 17, 17, 0.04);
  display: block;
}

.dls-cat-template .dls-cat-lead .dls-story-card__media {
  aspect-ratio: 16/8.4;
}

.dls-cat-template .dls-story-card .dls-story-card__media {
  aspect-ratio: 16/9;
}

.dls-cat-template .dls-story-card__media img {
  display: block;
  height: 100%;
  object-fit: cover;
  width: 100%;
}

.dls-cat-template .dls-cat-lead .dls-story-card__body,
.dls-cat-template .dls-story-card .dls-story-card__body {
  padding: .95rem 1rem 1rem;
}

.dls-cat-template .dls-story-card__meta {
  color: rgba(17, 17, 17, 0.64);
  font-size: .79rem;
  margin-bottom: .35rem;
}

.dls-cat-template .dls-story-card__title {
  color: #000;
  line-height: 1.15;
  margin: 0;
}

.dls-cat-template .dls-cat-lead .dls-story-card__title {
  font-size: clamp(1.4rem, 2.2vw, 1.9rem);
}

.dls-cat-template .dls-story-card--compact .dls-story-card__title {
  font-size: clamp(1.02rem, 1.2vw, 1.2rem);
}

.dls-cat-template .dls-story-card__title a {
  color: inherit;
  text-decoration: none;
}

.dls-cat-template .dls-story-card__excerpt {
  color: rgba(17, 17, 17, 0.8);
  line-height: 1.54;
  margin: .5rem 0 0;
}

.dls-cat-template .dls-story-grid {
  display: grid;
  gap: .9rem;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.dls-cat-template .dls-story-card {
  background: #fff;
  border: 1px solid rgba(17, 17, 17, 0.08);
  border-radius: 14px;
  overflow: hidden;
}

.dls-cat-template .dls-cat-pagination {
  margin-top: .2rem;
}

.dls-cat-template .dls-cat-pagination .page-numbers {
  border: 1px solid rgba(17, 17, 17, 0.14);
  border-radius: 9px;
  display: inline-block;
  margin: 0 .22rem .22rem 0;
  min-width: 2rem;
  padding: .4rem .58rem;
  text-align: center;
  text-decoration: none;
}

.dls-cat-template .dls-cat-pagination .page-numbers.current {
  background: #111;
  border-color: #111;
  color: #fff;
}

.dls-cat-side-list {
  display: grid;
  gap: .58rem;
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
  color: rgba(17, 17, 17, 0.68);
  display: flex;
  font-size: .72rem;
  height: 100%;
  justify-content: center;
  min-height: 54px;
  padding: .3rem;
  text-align: center;
}

@media (max-width: 1024px) {
  .dls-cat-template .dls-story-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 767px) {
  .dls-cat-template .entry-content-wrap {
    padding: 1rem;
  }
}
CSS;
    }
}

if ( ! function_exists( 'dls_cat_tpl_enqueue' ) ) {
    /**
     * Enqueue styles on category pages only.
     *
     * @return void
     */
    function dls_cat_tpl_enqueue() {
        if ( ! is_category() ) {
            return;
        }

        $frontpage_css = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/css/dls-frontpage.css';
        wp_enqueue_style( 'dls-frontpage-category', $frontpage_css, array(), null );

        $handle = 'dls-category-template';
        wp_register_style( $handle, false, array(), '2.1.0' );
        wp_enqueue_style( $handle );
        wp_add_inline_style( $handle, dls_cat_tpl_styles() );
    }
}
add_action( 'wp_enqueue_scripts', 'dls_cat_tpl_enqueue', 40 );

if ( ! function_exists( 'dls_cat_tpl_extract_term_name' ) ) {
    /**
     * Extract first taxonomy term name from embedded REST payload.
     *
     * @param array<string,mixed> $job      Job payload.
     * @param string              $taxonomy Taxonomy name.
     * @return string
     */
    function dls_cat_tpl_extract_term_name( $job, $taxonomy ) {
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

if ( ! function_exists( 'dls_cat_tpl_extract_logo_url' ) ) {
    /**
     * Extract logo URL from embedded featured media payload.
     *
     * @param array<string,mixed> $job Job payload.
     * @return string
     */
    function dls_cat_tpl_extract_logo_url( $job ) {
        if ( empty( $job['_embedded']['wp:featuredmedia'][0] ) || ! is_array( $job['_embedded']['wp:featuredmedia'][0] ) ) {
            return '';
        }

        $media = $job['_embedded']['wp:featuredmedia'][0];
        $sizes = array( 'medium', 'medium_large', 'large', 'cariera-avatar' );

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

if ( ! function_exists( 'dls_cat_tpl_extract_work_mode' ) ) {
    /**
     * Extract readable work mode from job class list.
     *
     * @param array<string,mixed> $job Job payload.
     * @return string
     */
    function dls_cat_tpl_extract_work_mode( $job ) {
        $map = array(
            'hybrid'    => 'Гібридно',
            'gibrydno'  => 'Гібридно',
            'remote'    => 'Віддалено',
            'viddaleno' => 'Віддалено',
            'office'    => 'В офісі',
            'offfice'   => 'В офісі',
            'v-ofisi'   => 'В офісі',
        );

        if ( empty( $job['class_list'] ) || ! is_array( $job['class_list'] ) ) {
            return '';
        }

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

        return '';
    }
}

if ( ! function_exists( 'dls_cat_tpl_extract_company' ) ) {
    /**
     * Extract company name from title or image alt.
     *
     * @param string              $title Job title.
     * @param array<string,mixed> $job   Job payload.
     * @return string
     */
    function dls_cat_tpl_extract_company( $title, $job ) {
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

if ( ! function_exists( 'dls_cat_tpl_fetch_jobs' ) ) {
    /**
     * Fetch jobs for right rail.
     *
     * @param int $limit Item limit.
     * @return array<int,array<string,mixed>>
     */
    function dls_cat_tpl_fetch_jobs( $limit ) {
        $limit = max( 1, min( 12, absint( $limit ) ) );

        $cache_key = 'dls_cat_tpl_jobs_v1';
        $cached = get_transient( $cache_key );
        if ( is_array( $cached ) ) {
            return array_slice( $cached, 0, $limit );
        }

        $response = wp_remote_get(
            'https://jobs.deadlawyers.org/wp-json/wp/v2/job-listings?per_page=24&_embed=1',
            array(
                'timeout'     => 8,
                'redirection' => 2,
                'user-agent'  => 'DeadLawyers-WWW-CategoryTemplate/2.1; ' . home_url( '/' ),
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
        $regular = array();

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $title = wp_strip_all_tags( (string) ( $row['title']['rendered'] ?? '' ) );
            $link = esc_url_raw( (string) ( $row['link'] ?? '' ) );

            if ( '' === $title || '' === $link ) {
                continue;
            }

            $job = array(
                'title'       => $title,
                'link'        => $link,
                'company'     => dls_cat_tpl_extract_company( $title, $row ),
                'region'      => dls_cat_tpl_extract_term_name( $row, 'job_listing_region' ),
                'work_mode'   => dls_cat_tpl_extract_work_mode( $row ),
                'logo'        => dls_cat_tpl_extract_logo_url( $row ),
                'is_featured' => in_array( (string) ( $row['meta']['_featured'] ?? '' ), array( '1', 'true' ), true ),
            );

            if ( $job['is_featured'] ) {
                $featured[] = $job;
            } else {
                $regular[] = $job;
            }
        }

        $jobs = array_slice( array_merge( $featured, $regular ), 0, 12 );
        set_transient( $cache_key, $jobs, 5 * MINUTE_IN_SECONDS );

        return array_slice( $jobs, 0, $limit );
    }
}

if ( ! function_exists( 'dls_cat_tpl_sidebar_posts' ) ) {
    /**
     * Fetch extra posts for right rail from same category.
     *
     * @param int   $term_id      Category ID.
     * @param int[] $exclude_ids  IDs already in main feed.
     * @param int   $limit        Item limit.
     * @return WP_Post[]
     */
    function dls_cat_tpl_sidebar_posts( $term_id, $exclude_ids, $limit ) {
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

if ( ! function_exists( 'dls_cat_tpl_post_meta_line' ) ) {
    /**
     * Build compact meta line for post cards.
     *
     * @param WP_Post $post Post object.
     * @return string
     */
    function dls_cat_tpl_post_meta_line( $post ) {
        if ( ! ( $post instanceof WP_Post ) ) {
            return '';
        }

        $date = get_the_date( 'Y-m-d', $post->ID );
        $author = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post->ID ) );

        $parts = array();
        if ( '' !== trim( (string) $author ) ) {
            $parts[] = trim( (string) $author );
        }
        if ( '' !== trim( (string) $date ) ) {
            $parts[] = trim( (string) $date );
        }

        return implode( ' | ', $parts );
    }
}

if ( ! function_exists( 'dls_cat_tpl_render_lead' ) ) {
    /**
     * Render lead article card.
     *
     * @param WP_Post $post Lead post.
     * @return void
     */
    function dls_cat_tpl_render_lead( $post ) {
        if ( ! ( $post instanceof WP_Post ) ) {
            return;
        }

        $title = get_the_title( $post->ID );
        $url = get_permalink( $post->ID );
        $excerpt = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post->ID ) ), 38, '…' );
        ?>
        <article class="dls-story-card dls-story-card--lead dls-cat-lead">
            <a class="dls-story-card__media" href="<?php echo esc_url( $url ); ?>">
                <?php if ( has_post_thumbnail( $post->ID ) ) : ?>
                    <?php echo get_the_post_thumbnail( $post->ID, 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <?php endif; ?>
            </a>
            <div class="dls-story-card__body">
                <div class="dls-story-card__meta"><?php echo esc_html( dls_cat_tpl_post_meta_line( $post ) ); ?></div>
                <h2 class="dls-story-card__title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a></h2>
                <p class="dls-story-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
            </div>
        </article>
        <?php
    }
}

if ( ! function_exists( 'dls_cat_tpl_render_compact' ) ) {
    /**
     * Render compact article cards.
     *
     * @param WP_Post[] $posts Compact posts.
     * @return void
     */
    function dls_cat_tpl_render_compact( $posts ) {
        if ( empty( $posts ) ) {
            return;
        }
        ?>
        <div class="dls-story-grid dls-cat-grid">
            <?php foreach ( $posts as $post ) : ?>
                <?php if ( ! ( $post instanceof WP_Post ) ) { continue; } ?>
                <?php
                $title = get_the_title( $post->ID );
                $url = get_permalink( $post->ID );
                $excerpt = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post->ID ) ), 24, '…' );
                ?>
                <article class="dls-story-card dls-story-card--compact">
                    <a class="dls-story-card__media" href="<?php echo esc_url( $url ); ?>">
                        <?php if ( has_post_thumbnail( $post->ID ) ) : ?>
                            <?php echo get_the_post_thumbnail( $post->ID, 'medium_large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php endif; ?>
                    </a>
                    <div class="dls-story-card__body">
                        <div class="dls-story-card__meta"><?php echo esc_html( dls_cat_tpl_post_meta_line( $post ) ); ?></div>
                        <h3 class="dls-story-card__title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a></h3>
                        <p class="dls-story-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

if ( ! function_exists( 'dls_cat_tpl_render' ) ) {
    /**
     * Render category template output.
     *
     * @return void
     */
    function dls_cat_tpl_render() {
        if ( is_admin() || wp_doing_ajax() || is_feed() || ! is_category() ) {
            return;
        }

        $term = get_queried_object();
        if ( ! ( $term instanceof WP_Term ) || 'category' !== (string) $term->taxonomy ) {
            return;
        }

        global $wp_query;

        $posts = is_array( $wp_query->posts ) ? $wp_query->posts : array();
        $lead = null;
        if ( ! empty( $posts ) ) {
            $lead = array_shift( $posts );
        }

        $main_ids = array();
        if ( is_array( $wp_query->posts ) ) {
            foreach ( $wp_query->posts as $main_post ) {
                if ( $main_post instanceof WP_Post ) {
                    $main_ids[] = (int) $main_post->ID;
                }
            }
        }

        $sidebar_posts = dls_cat_tpl_sidebar_posts( (int) $term->term_id, $main_ids, 8 );
        $jobs = dls_cat_tpl_fetch_jobs( 8 );

        get_header();
        ?>
        <div id="primary" class="content-area dls-cat-template">
            <div class="content-container site-container">
                <div id="main" class="site-main">
                    <div class="content-wrap">
                        <article class="entry content-bg single-entry dls-category-archive-entry">
                            <div class="entry-content-wrap">
                                <header class="entry-header post-title title-align-left title-tablet-align-inherit title-mobile-align-inherit">
                                    <p class="dls-cat-kicker"><?php echo esc_html__( 'Рубрика', 'default' ); ?></p>
                                    <h1 class="entry-title dls-cat-title"><?php echo esc_html( single_cat_title( '', false ) ); ?></h1>
                                    <?php $description = trim( wp_strip_all_tags( (string) term_description( $term, 'category' ) ) ); ?>
                                    <?php if ( '' !== $description ) : ?>
                                        <p class="dls-cat-desc"><?php echo esc_html( $description ); ?></p>
                                    <?php endif; ?>
                                </header>

                                <div class="entry-content single-content dls-cat-main">
                                    <?php if ( $lead instanceof WP_Post ) : ?>
                                        <?php dls_cat_tpl_render_lead( $lead ); ?>
                                    <?php endif; ?>

                                    <?php dls_cat_tpl_render_compact( $posts ); ?>

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
                                        <?php foreach ( $sidebar_posts as $side_post ) : ?>
                                            <?php if ( ! ( $side_post instanceof WP_Post ) ) { continue; } ?>
                                            <li>
                                                <a href="<?php echo esc_url( get_permalink( $side_post->ID ) ); ?>"><?php echo esc_html( get_the_title( $side_post->ID ) ); ?></a>
                                                <time datetime="<?php echo esc_attr( get_the_date( 'c', $side_post->ID ) ); ?>"><?php echo esc_html( get_the_date( 'Y-m-d', $side_post->ID ) ); ?></time>
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
add_action( 'template_redirect', 'dls_cat_tpl_render', 9 );
