<?php
/**
 * Plugin Name: DLS Category Template
 * Description: Routes category archives through a dedicated DLS template stored outside the MU root.
 * Version: 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'dls_cat_tpl_file_path' ) ) {
    /**
     * Path to the safe category template file.
     *
     * @return string
     */
    function dls_cat_tpl_file_path() {
        return __DIR__ . '/dls-category-template/template.php';
    }
}

if ( ! function_exists( 'dls_cat_tpl_is_request' ) ) {
    /**
     * Determine whether the current request should use the DLS category template.
     *
     * @return bool
     */
    function dls_cat_tpl_is_request() {
        return ! is_admin() && ! wp_doing_ajax() && ! is_feed() && is_category();
    }
}

if ( ! function_exists( 'dls_cat_tpl_styles' ) ) {
    /**
     * Inline styles for the category template.
     *
     * @return string
     */
    function dls_cat_tpl_styles() {
        return <<<'CSS'
.dls-cat-page-shell {
  align-items: start;
  display: grid;
  gap: clamp(1.25rem, 2vw, 2rem);
  grid-template-columns: minmax(0, 1.7fr) minmax(300px, .9fr);
}

.dls-cat-page__main,
.dls-cat-page__rail,
.dls-cat-page__rail-inner,
.dls-cat-page,
.dls-cat-page__stack,
.dls-cat-page__grid,
.dls-cat-story,
.dls-featured-job,
.dls-featured-job__content {
  min-width: 0;
}

.dls-cat-page {
  color: #151515;
}

.dls-cat-page__hero {
  background: linear-gradient(180deg, rgba(255, 245, 228, 0.92), rgba(255, 251, 245, 0.88));
  border: 1px solid rgba(17, 17, 17, 0.08);
  border-radius: 28px;
  box-shadow: 0 18px 40px rgba(183, 131, 38, 0.08);
  margin-bottom: clamp(1rem, 2vw, 1.4rem);
  padding: clamp(1.25rem, 2vw, 1.8rem);
}

.dls-cat-page__eyebrow {
  color: rgba(17, 17, 17, 0.6);
  font-size: .82rem;
  font-weight: 700;
  letter-spacing: .08em;
  margin: 0 0 .45rem;
  text-transform: uppercase;
}

.dls-cat-page__title {
  color: #000;
  font-size: clamp(2rem, 4vw, 3.6rem);
  letter-spacing: -.03em;
  line-height: .95;
  margin: 0;
  text-wrap: balance;
}

.dls-cat-page__meta {
  color: rgba(17, 17, 17, 0.62);
  font-size: .95rem;
  margin-top: .8rem;
}

.dls-cat-page__description {
  color: rgba(17, 17, 17, 0.8);
  font-size: 1.02rem;
  line-height: 1.65;
  margin: .9rem 0 0;
  max-width: 72ch;
}

.dls-cat-page__stack {
  display: grid;
  gap: clamp(1rem, 1.6vw, 1.35rem);
}

.dls-cat-story {
  background: rgba(255, 251, 245, 0.9);
  border: 1px solid rgba(17, 17, 17, 0.08);
  border-radius: 24px;
  box-shadow: 0 14px 34px rgba(17, 17, 17, 0.04);
  overflow: hidden;
}

.dls-cat-story__media {
  background: rgba(17, 17, 17, 0.05);
  display: block;
}

.dls-cat-story--lead .dls-cat-story__media {
  aspect-ratio: 16 / 8.5;
}

.dls-cat-story--card .dls-cat-story__media {
  aspect-ratio: 16 / 10;
}

.dls-cat-story__media img {
  display: block;
  height: 100%;
  object-fit: cover;
  width: 100%;
}

.dls-cat-story__body {
  padding: 1rem 1.1rem 1.15rem;
}

.dls-cat-story__meta {
  color: rgba(17, 17, 17, 0.58);
  font-size: .8rem;
  font-weight: 600;
  margin-bottom: .42rem;
}

.dls-cat-story__title,
.dls-cat-story__excerpt,
.dls-cat-sidebar-list a,
.dls-featured-job__title,
.dls-featured-job__company,
.dls-featured-job__type,
.dls-cat-empty {
  overflow-wrap: anywhere;
  word-break: break-word;
}

.dls-cat-story__title {
  color: #000;
  letter-spacing: -.02em;
  line-height: 1.06;
  margin: 0;
}

.dls-cat-story--lead .dls-cat-story__title {
  font-size: clamp(1.55rem, 2.6vw, 2.2rem);
}

.dls-cat-story--card .dls-cat-story__title {
  font-size: clamp(1.08rem, 1.55vw, 1.32rem);
}

.dls-cat-story__title a {
  color: inherit;
  text-decoration: none;
}

.dls-cat-story__title a:hover {
  opacity: .8;
}

.dls-cat-story__excerpt {
  color: rgba(17, 17, 17, 0.8);
  line-height: 1.62;
  margin: .6rem 0 0;
}

.dls-cat-page__grid {
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.dls-cat-pagination {
  display: flex;
  flex-wrap: wrap;
  gap: .45rem;
}

.dls-cat-pagination .page-numbers {
  background: rgba(255, 251, 245, 0.9);
  border: 1px solid rgba(17, 17, 17, 0.12);
  border-radius: 999px;
  color: #111;
  display: inline-flex;
  justify-content: center;
  min-width: 2.4rem;
  padding: .5rem .8rem;
  text-decoration: none;
}

.dls-cat-pagination .page-numbers.current {
  background: #111;
  border-color: #111;
  color: #fff;
}

.dls-cat-page__rail-inner {
  display: grid;
  gap: 1rem;
  position: sticky;
  top: 2rem;
}

.dls-cat-sidebar-card {
  background: rgba(255, 251, 245, 0.94);
  border: 1px solid rgba(17, 17, 17, 0.08);
  border-radius: 22px;
  box-shadow: 0 14px 32px rgba(17, 17, 17, 0.04);
  padding: 1rem;
}

.dls-cat-sidebar-card__title {
  color: #111;
  font-size: 1.2rem;
  letter-spacing: -.02em;
  line-height: 1.1;
  margin: 0 0 .85rem;
}

.dls-cat-sidebar-list {
  display: grid;
  gap: .7rem;
  list-style: none;
  margin: 0;
  padding: 0;
}

.dls-cat-sidebar-list li {
  border-top: 1px solid rgba(17, 17, 17, 0.08);
  padding-top: .7rem;
}

.dls-cat-sidebar-list li:first-child {
  border-top: 0;
  padding-top: 0;
}

.dls-cat-sidebar-list a {
  color: #111;
  display: block;
  line-height: 1.35;
  text-decoration: none;
}

.dls-cat-sidebar-list a:hover {
  opacity: .78;
}

.dls-cat-sidebar-list time {
  color: rgba(17, 17, 17, 0.55);
  display: block;
  font-size: .78rem;
  margin-top: .2rem;
}

.dls-featured-jobs-list {
  display: grid;
  gap: .85rem;
}

.dls-featured-job__link {
  background: #fff;
  border: 1px solid rgba(17, 17, 17, 0.08);
  border-radius: 18px;
  display: grid;
  gap: .75rem;
  grid-template-columns: 64px minmax(0, 1fr);
  padding: .8rem;
  text-decoration: none;
}

.dls-featured-job__link:hover {
  box-shadow: 0 12px 28px rgba(17, 17, 17, 0.06);
  transform: translateY(-1px);
}

.dls-featured-job__logo {
  align-items: center;
  background: rgba(17, 17, 17, 0.04);
  border-radius: 16px;
  display: flex;
  height: 64px;
  justify-content: center;
  overflow: hidden;
  width: 64px;
}

.dls-featured-job__logo img {
  display: block;
  height: 100%;
  object-fit: contain;
  width: 100%;
}

.dls-job-logo-fallback {
  align-items: center;
  color: rgba(17, 17, 17, 0.62);
  display: flex;
  font-size: .72rem;
  height: 100%;
  justify-content: center;
  padding: .35rem;
  text-align: center;
  width: 100%;
}

.dls-featured-job__title {
  color: #111;
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.18;
}

.dls-featured-job__company {
  color: rgba(17, 17, 17, 0.72);
  font-size: .9rem;
  line-height: 1.25;
  margin-top: .28rem;
}

.dls-featured-job__meta {
  display: flex;
  flex-wrap: wrap;
  gap: .36rem;
  margin-top: .55rem;
}

.dls-featured-job__type {
  background: rgba(243, 236, 226, 0.95);
  border: 1px solid rgba(17, 17, 17, 0.08);
  border-radius: 999px;
  color: rgba(17, 17, 17, 0.7);
  font-size: .74rem;
  padding: .16rem .52rem;
}

.dls-cat-empty {
  color: rgba(17, 17, 17, 0.68);
  line-height: 1.55;
  margin: 0;
}

@media (max-width: 1120px) {
  .dls-cat-page-shell {
    grid-template-columns: minmax(0, 1fr);
  }

  .dls-cat-page__rail-inner {
    position: static;
    top: auto;
  }
}

@media (max-width: 820px) {
  .dls-cat-page__grid {
    grid-template-columns: minmax(0, 1fr);
  }
}

@media (max-width: 640px) {
  .dls-cat-page__hero,
  .dls-cat-sidebar-card,
  .dls-cat-story {
    border-radius: 18px;
  }

  .dls-featured-job__link {
    grid-template-columns: 56px minmax(0, 1fr);
  }

  .dls-featured-job__logo {
    height: 56px;
    width: 56px;
  }
}
CSS;
    }
}

if ( ! function_exists( 'dls_cat_tpl_enqueue' ) ) {
    /**
     * Enqueue inline styles for the category template.
     *
     * @return void
     */
    function dls_cat_tpl_enqueue() {
        if ( ! dls_cat_tpl_is_request() || ! file_exists( dls_cat_tpl_file_path() ) ) {
            return;
        }

        $handle = 'dls-category-template';
        wp_register_style( $handle, false, array(), '4.0.0' );
        wp_enqueue_style( $handle );
        wp_add_inline_style( $handle, dls_cat_tpl_styles() );
    }
}
add_action( 'wp_enqueue_scripts', 'dls_cat_tpl_enqueue', 40 );

if ( ! function_exists( 'dls_cat_tpl_use_template' ) ) {
    /**
     * Swap in the custom category template.
     *
     * @param string $template Current template path.
     * @return string
     */
    function dls_cat_tpl_use_template( $template ) {
        if ( ! dls_cat_tpl_is_request() ) {
            return $template;
        }

        $custom_template = dls_cat_tpl_file_path();

        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }

        return $template;
    }
}
add_filter( 'template_include', 'dls_cat_tpl_use_template', PHP_INT_MAX );

if ( ! function_exists( 'dls_cat_tpl_extract_term_name' ) ) {
    /**
     * Extract a taxonomy term label from embedded REST payload.
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
     * Extract job logo URL from embedded media.
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
     * Convert job listing type classes into readable labels.
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
     * Extract company name from title or media alt text.
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
     * Fetch jobs for the category page rail.
     *
     * @param int $limit Item limit.
     * @return array<int,array<string,mixed>>
     */
    function dls_cat_tpl_fetch_jobs( $limit ) {
        $limit = max( 1, min( 12, absint( $limit ) ) );

        $cache_key = 'dls_cat_tpl_jobs_v4';
        $cached    = get_transient( $cache_key );

        if ( is_array( $cached ) ) {
            return array_slice( $cached, 0, $limit );
        }

        $response = wp_remote_get(
            'https://jobs.deadlawyers.org/wp-json/wp/v2/job-listings?per_page=24&_embed=1',
            array(
                'timeout'     => 8,
                'redirection' => 2,
                'user-agent'  => 'DeadLawyers-WWW-CategoryTemplate/4.0; ' . home_url( '/' ),
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
     * Fetch extra posts from the same category for the side rail.
     *
     * @param int   $term_id     Category ID.
     * @param int[] $exclude_ids Posts already displayed in the main query.
     * @param int   $limit       Item limit.
     * @return WP_Post[]
     */
    function dls_cat_tpl_sidebar_posts( $term_id, $exclude_ids, $limit ) {
        $query = new WP_Query(
            array(
                'post_type'              => 'post',
                'post_status'            => 'publish',
                'posts_per_page'         => max( 1, min( 10, absint( $limit ) ) ),
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
     * Build the compact meta line for article cards.
     *
     * @param WP_Post $post Post object.
     * @return string
     */
    function dls_cat_tpl_post_meta_line( $post ) {
        if ( ! ( $post instanceof WP_Post ) ) {
            return '';
        }

        $date   = get_the_date( 'Y-m-d', $post->ID );
        $author = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post->ID ) );
        $parts  = array();

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
     * Render the lead article.
     *
     * @param WP_Post $post Lead post.
     * @return void
     */
    function dls_cat_tpl_render_lead( $post ) {
        if ( ! ( $post instanceof WP_Post ) ) {
            return;
        }

        $title   = get_the_title( $post->ID );
        $url     = get_permalink( $post->ID );
        $excerpt = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post->ID ) ), 42, '…' );
        ?>
        <article class="dls-cat-story dls-cat-story--lead">
            <a class="dls-cat-story__media" href="<?php echo esc_url( $url ); ?>">
                <?php if ( has_post_thumbnail( $post->ID ) ) : ?>
                    <?php echo get_the_post_thumbnail( $post->ID, 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <?php endif; ?>
            </a>
            <div class="dls-cat-story__body">
                <div class="dls-cat-story__meta"><?php echo esc_html( dls_cat_tpl_post_meta_line( $post ) ); ?></div>
                <h2 class="dls-cat-story__title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a></h2>
                <?php if ( '' !== $excerpt ) : ?>
                    <p class="dls-cat-story__excerpt"><?php echo esc_html( $excerpt ); ?></p>
                <?php endif; ?>
            </div>
        </article>
        <?php
    }
}

if ( ! function_exists( 'dls_cat_tpl_render_grid' ) ) {
    /**
     * Render secondary article cards.
     *
     * @param WP_Post[] $posts Posts to render.
     * @return void
     */
    function dls_cat_tpl_render_grid( $posts ) {
        $grid_posts = array();

        foreach ( (array) $posts as $post ) {
            if ( $post instanceof WP_Post ) {
                $grid_posts[] = $post;
            }
        }

        if ( empty( $grid_posts ) ) {
            return;
        }
        ?>
        <div class="dls-cat-page__grid">
            <?php foreach ( $grid_posts as $post ) : ?>
                <?php
                $title   = get_the_title( $post->ID );
                $url     = get_permalink( $post->ID );
                $excerpt = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post->ID ) ), 24, '…' );
                ?>
                <article class="dls-cat-story dls-cat-story--card">
                    <a class="dls-cat-story__media" href="<?php echo esc_url( $url ); ?>">
                        <?php if ( has_post_thumbnail( $post->ID ) ) : ?>
                            <?php echo get_the_post_thumbnail( $post->ID, 'medium_large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php endif; ?>
                    </a>
                    <div class="dls-cat-story__body">
                        <div class="dls-cat-story__meta"><?php echo esc_html( dls_cat_tpl_post_meta_line( $post ) ); ?></div>
                        <h3 class="dls-cat-story__title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a></h3>
                        <?php if ( '' !== $excerpt ) : ?>
                            <p class="dls-cat-story__excerpt"><?php echo esc_html( $excerpt ); ?></p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

if ( ! function_exists( 'dls_cat_tpl_render_jobs' ) ) {
    /**
     * Render featured jobs list.
     *
     * @param array<int,array<string,mixed>> $jobs Jobs payload.
     * @return void
     */
    function dls_cat_tpl_render_jobs( $jobs ) {
        if ( empty( $jobs ) ) {
            echo '<p class="dls-cat-empty">' . esc_html__( 'Вакансії тимчасово недоступні. Перевірте трохи пізніше.', 'default' ) . '</p>';
            return;
        }

        echo '<div class="dls-featured-jobs-list">';

        foreach ( $jobs as $job ) {
            if ( ! is_array( $job ) || empty( $job['title'] ) || empty( $job['link'] ) ) {
                continue;
            }
            ?>
            <article class="dls-featured-job">
                <a class="dls-featured-job__link" href="<?php echo esc_url( (string) $job['link'] ); ?>" target="_blank" rel="noopener noreferrer">
                    <div class="dls-featured-job__logo">
                        <?php if ( ! empty( $job['logo'] ) ) : ?>
                            <img src="<?php echo esc_url( (string) $job['logo'] ); ?>" alt="<?php echo esc_attr( (string) ( $job['company'] ?? $job['title'] ) ); ?>" loading="lazy">
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
            <?php
        }

        echo '</div>';
    }
}
