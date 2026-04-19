<?php
/**
 * Plugin Name: DLS Category Archive Template
 * Description: Custom category archive layout with homepage-like cards and a jobs/content right rail.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('dls_category_archive_template_is_request')) {
    /**
     * Detect whether current request should use the custom category template.
     *
     * @return bool
     */
    function dls_category_archive_template_is_request() {
        return !is_admin() && is_category() && !is_feed() && !is_embed() && !is_trackback();
    }
}

if (!function_exists('dls_category_archive_template_styles')) {
    /**
     * Inline styles for custom category layout.
     *
     * @return string
     */
    function dls_category_archive_template_styles() {
        return <<<'CSS'
.dls-category-template {
  --dls-ct-bg: var(--global-palette9, #f3eee2);
  --dls-ct-card: #ffffff;
  --dls-ct-text: #111111;
  --dls-ct-muted: rgba(17, 17, 17, 0.66);
  --dls-ct-border: rgba(17, 17, 17, 0.08);
  --dls-ct-shadow: 0 16px 34px rgba(0, 0, 0, 0.06);
  background: radial-gradient(circle at 90% 4%, rgba(205, 178, 130, 0.25), transparent 35%), var(--dls-ct-bg);
  border: 1px solid var(--dls-ct-border);
  border-radius: 26px;
  margin: clamp(1.1rem, 2.2vw, 1.9rem) 0;
  padding: clamp(1rem, 2.3vw, 1.8rem);
}

.dls-category-template__head {
  margin-bottom: 1rem;
}

.dls-category-template__kicker {
  color: var(--dls-ct-muted);
  font-family: var(--global-body-font-family, inherit);
  font-size: .8rem;
  letter-spacing: .08em;
  margin: 0 0 .45rem;
  text-transform: uppercase;
}

.dls-category-template__title {
  color: #000 !important;
  font-size: clamp(1.7rem, 3vw, 2.5rem);
  line-height: 1.08;
  margin: 0 0 .5rem;
}

.dls-category-template__desc {
  color: var(--dls-ct-muted);
  margin: 0;
  max-width: 65ch;
}

.dls-category-template__layout {
  display: grid;
  gap: clamp(1rem, 2vw, 1.6rem);
  grid-template-columns: minmax(0, 1.72fr) minmax(265px, 1fr);
}

.dls-category-template__main {
  min-width: 0;
}

.dls-category-template__lead,
.dls-category-template__card,
.dls-category-template__widget,
.dls-category-template__job {
  background: var(--dls-ct-card);
  border: 1px solid var(--dls-ct-border);
  border-radius: 16px;
  overflow: hidden;
}

.dls-category-template__lead {
  box-shadow: var(--dls-ct-shadow);
  margin-bottom: .95rem;
}

.dls-category-template__lead-media {
  aspect-ratio: 16 / 9;
  background: rgba(0, 0, 0, 0.04);
  display: block;
}

.dls-category-template__lead-media img {
  height: 100%;
  object-fit: cover;
  width: 100%;
}

.dls-category-template__lead-body {
  padding: .95rem 1rem 1.05rem;
}

.dls-category-template__meta {
  color: var(--dls-ct-muted);
  font-size: .78rem;
  margin-bottom: .42rem;
}

.dls-category-template__lead-title {
  color: #000 !important;
  font-size: clamp(1.2rem, 1.85vw, 1.6rem);
  line-height: 1.16;
  margin: 0;
}

.dls-category-template__lead-title a,
.dls-category-template__card-title a {
  color: #000;
  text-decoration: none;
}

.dls-category-template__lead-title a:hover,
.dls-category-template__card-title a:hover {
  text-decoration: underline;
}

.dls-category-template__lead-excerpt {
  color: var(--dls-ct-muted);
  line-height: 1.6;
  margin: .55rem 0 0;
}

.dls-category-template__grid {
  display: grid;
  gap: .85rem;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.dls-category-template__card-body {
  padding: .8rem .85rem .9rem;
}

.dls-category-template__card-title {
  color: #000 !important;
  font-size: 1.04rem;
  line-height: 1.2;
  margin: 0;
}

.dls-category-template__rail {
  min-width: 0;
}

.dls-category-template__rail-inner {
  display: grid;
  gap: .82rem;
  position: sticky;
  top: 1rem;
}

.dls-category-template__widget {
  padding: .82rem;
}

.dls-category-template__widget-title {
  color: #000;
  font-size: 1rem;
  line-height: 1.2;
  margin: 0 0 .68rem;
}

.dls-category-template__links {
  list-style: none;
  margin: 0;
  padding: 0;
}

.dls-category-template__links li + li {
  border-top: 1px solid var(--dls-ct-border);
}

.dls-category-template__links a {
  color: #000;
  display: block;
  font-size: .95rem;
  line-height: 1.35;
  padding: .62rem 0;
  text-decoration: none;
}

.dls-category-template__links a:hover {
  text-decoration: underline;
}

.dls-category-template__jobs {
  display: grid;
  gap: .62rem;
}

.dls-category-template__job {
  display: grid;
  gap: .65rem;
  grid-template-columns: 56px minmax(0, 1fr);
  padding: .58rem;
}

.dls-category-template__job-logo {
  align-items: center;
  border: 1px solid var(--dls-ct-border);
  border-radius: 10px;
  display: flex;
  height: 56px;
  justify-content: center;
  overflow: hidden;
  width: 56px;
}

.dls-category-template__job-logo img {
  height: 100%;
  object-fit: cover;
  width: 100%;
}

.dls-category-template__job-logo-fallback {
  align-items: center;
  background: rgba(17, 17, 17, 0.05);
  color: var(--dls-ct-muted);
  display: flex;
  font-size: .68rem;
  height: 100%;
  justify-content: center;
  text-transform: uppercase;
  width: 100%;
}

.dls-category-template__job-title {
  font-size: .95rem;
  line-height: 1.24;
  margin: 0 0 .35rem;
}

.dls-category-template__job-title a {
  color: #000;
  text-decoration: none;
}

.dls-category-template__job-title a:hover {
  text-decoration: underline;
}

.dls-category-template__job-meta {
  color: var(--dls-ct-muted);
  font-size: .78rem;
}

.dls-category-template__pagination {
  display: flex;
  flex-wrap: wrap;
  gap: .45rem;
  margin-top: 1rem;
}

.dls-category-template__pagination .page-numbers {
  background: #fff;
  border: 1px solid var(--dls-ct-border);
  border-radius: 999px;
  color: #000;
  font-size: .86rem;
  line-height: 1;
  padding: .42rem .72rem;
  text-decoration: none;
}

.dls-category-template__pagination .page-numbers.current {
  background: #000;
  border-color: #000;
  color: #fff;
}

@media (max-width: 1100px) {
  .dls-category-template__layout {
    grid-template-columns: 1fr;
  }

  .dls-category-template__rail-inner {
    position: static;
  }
}

@media (max-width: 720px) {
  .dls-category-template {
    border-radius: 16px;
    padding: .92rem;
  }

  .dls-category-template__grid {
    grid-template-columns: 1fr;
  }
}
CSS;
    }
}

if (!function_exists('dls_category_archive_template_enqueue_styles')) {
    /**
     * Enqueue custom archive styles.
     *
     * @return void
     */
    function dls_category_archive_template_enqueue_styles() {
        if (!dls_category_archive_template_is_request()) {
            return;
        }

        $handle = 'dls-category-archive-template';
        wp_register_style($handle, false, [], '1.0.0');
        wp_enqueue_style($handle);
        wp_add_inline_style($handle, dls_category_archive_template_styles());
    }
}
add_action('wp_enqueue_scripts', 'dls_category_archive_template_enqueue_styles', 40);

if (!function_exists('dls_category_archive_template_fetch_jobs')) {
    /**
     * Fetch featured jobs from Jobs site API with short transient cache.
     *
     * @return array<int, array<string, mixed>>
     */
    function dls_category_archive_template_fetch_jobs() {
        $api_url = (string) apply_filters(
            'dls_category_archive_template_jobs_api_url',
            'https://jobs.deadlawyers.org/wp-json/wp/v2/job-listings?per_page=6&orderby=date&order=desc&_embed=1'
        );

        if ($api_url === '') {
            return [];
        }

        $cache_key = 'dls_ct_jobs_' . md5($api_url);
        $cached = get_transient($cache_key);

        if (is_array($cached)) {
            return $cached;
        }

        $response = wp_remote_get(
            $api_url,
            [
                'timeout'    => 6,
                'sslverify'  => true,
                'user-agent' => 'deadlawyers-www/category-template',
            ]
        );

        if (is_wp_error($response)) {
            set_transient($cache_key, [], 180);
            return [];
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = (string) wp_remote_retrieve_body($response);

        if ($code < 200 || $code >= 300 || $body === '') {
            set_transient($cache_key, [], 180);
            return [];
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            set_transient($cache_key, [], 180);
            return [];
        }

        set_transient($cache_key, $data, 600);
        return $data;
    }
}

if (!function_exists('dls_category_archive_template_job_terms')) {
    /**
     * Resolve taxonomy terms from embedded job payload.
     *
     * @param array<string, mixed> $job Job payload.
     * @param string[]             $taxonomies Candidate taxonomy slugs.
     * @return string
     */
    function dls_category_archive_template_job_terms($job, $taxonomies) {
        if (!is_array($job) || empty($job['_embedded']['wp:term']) || !is_array($job['_embedded']['wp:term'])) {
            return '';
        }

        foreach ((array) $job['_embedded']['wp:term'] as $term_group) {
            if (!is_array($term_group)) {
                continue;
            }

            foreach ($term_group as $term) {
                if (!is_array($term)) {
                    continue;
                }

                $taxonomy = (string) ($term['taxonomy'] ?? '');
                $name = trim((string) ($term['name'] ?? ''));

                if ($name === '' || !in_array($taxonomy, $taxonomies, true)) {
                    continue;
                }

                return $name;
            }
        }

        return '';
    }
}

if (!function_exists('dls_category_archive_template_job_logo_url')) {
    /**
     * Extract thumbnail URL for job card.
     *
     * @param array<string, mixed> $job Job payload.
     * @return string
     */
    function dls_category_archive_template_job_logo_url($job) {
        if (!is_array($job) || empty($job['_embedded']['wp:featuredmedia'][0]) || !is_array($job['_embedded']['wp:featuredmedia'][0])) {
            return '';
        }

        $media = $job['_embedded']['wp:featuredmedia'][0];

        if (!empty($media['media_details']['sizes']['thumbnail']['source_url'])) {
            return esc_url_raw((string) $media['media_details']['sizes']['thumbnail']['source_url']);
        }

        if (!empty($media['source_url'])) {
            return esc_url_raw((string) $media['source_url']);
        }

        return '';
    }
}

if (!function_exists('dls_category_archive_template_post_meta')) {
    /**
     * Build compact meta line for post cards.
     *
     * @param int $post_id Post ID.
     * @return string
     */
    function dls_category_archive_template_post_meta($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return '';
        }

        $pieces = [];
        $author = get_the_author_meta('display_name', (int) get_post_field('post_author', $post_id));

        if (is_string($author) && $author !== '') {
            $pieces[] = $author;
        }

        $pieces[] = get_the_date('Y-m-d', $post_id);

        return implode(' | ', array_filter($pieces));
    }
}

if (!function_exists('dls_category_archive_template_render')) {
    /**
     * Render custom category archive page and stop default template loading.
     *
     * @return void
     */
    function dls_category_archive_template_render() {
        if (!dls_category_archive_template_is_request()) {
            return;
        }

        $term = get_queried_object();
        if (!($term instanceof WP_Term)) {
            return;
        }

        $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));

        $main_query = new WP_Query([
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'cat'                 => (int) $term->term_id,
            'posts_per_page'      => 10,
            'ignore_sticky_posts' => true,
            'paged'               => $paged,
        ]);

        $content_query = new WP_Query([
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'cat'                 => (int) $term->term_id,
            'posts_per_page'      => 8,
            'ignore_sticky_posts' => true,
        ]);

        $jobs = dls_category_archive_template_fetch_jobs();
        $current_title = single_cat_title('', false);
        $term_description = trim((string) term_description($term->term_id, 'category'));

        $term_link = get_term_link($term);
        if (is_wp_error($term_link)) {
            $term_link = home_url('/');
        }

        status_header(200);

        get_header();
        ?>
        <main id="primary" class="site-main">
            <div class="content-container site-container">
                <section class="dls-category-template" aria-label="Category archive">
                    <header class="dls-category-template__head">
                        <p class="dls-category-template__kicker"><?php echo esc_html__('Рубрика', 'default'); ?></p>
                        <h1 class="dls-category-template__title"><?php echo esc_html($current_title !== '' ? $current_title : $term->name); ?></h1>
                        <?php if ($term_description !== '') : ?>
                            <p class="dls-category-template__desc"><?php echo esc_html(wp_strip_all_tags($term_description)); ?></p>
                        <?php endif; ?>
                    </header>

                    <div class="dls-category-template__layout">
                        <div class="dls-category-template__main">
                            <?php if ($main_query->have_posts()) : ?>
                                <?php
                                $index = 0;
                                while ($main_query->have_posts()) :
                                    $main_query->the_post();
                                    $post_id = (int) get_the_ID();
                                    $is_lead = $index === 0;
                                    ?>
                                    <?php if ($is_lead) : ?>
                                        <article class="dls-category-template__lead">
                                            <a class="dls-category-template__lead-media" href="<?php the_permalink(); ?>">
                                                <?php if (has_post_thumbnail($post_id)) : ?>
                                                    <?php echo get_the_post_thumbnail($post_id, 'large'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                <?php endif; ?>
                                            </a>
                                            <div class="dls-category-template__lead-body">
                                                <div class="dls-category-template__meta"><?php echo esc_html(dls_category_archive_template_post_meta($post_id)); ?></div>
                                                <h2 class="dls-category-template__lead-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                                <p class="dls-category-template__lead-excerpt">
                                                    <?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt($post_id)), 28, '…')); ?>
                                                </p>
                                            </div>
                                        </article>
                                        <div class="dls-category-template__grid">
                                    <?php else : ?>
                                        <article class="dls-category-template__card">
                                            <div class="dls-category-template__card-body">
                                                <div class="dls-category-template__meta"><?php echo esc_html(dls_category_archive_template_post_meta($post_id)); ?></div>
                                                <h3 class="dls-category-template__card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                            </div>
                                        </article>
                                    <?php endif; ?>
                                    <?php
                                    $index++;
                                endwhile;

                                if ($index > 0) {
                                    echo '</div>';
                                }
                                ?>

                                <?php
                                $pagination = paginate_links([
                                    'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                                    'format'    => '?paged=%#%',
                                    'current'   => $paged,
                                    'total'     => max(1, (int) $main_query->max_num_pages),
                                    'type'      => 'array',
                                    'mid_size'  => 1,
                                    'end_size'  => 1,
                                    'prev_text' => '←',
                                    'next_text' => '→',
                                ]);
                                ?>
                                <?php if (is_array($pagination) && !empty($pagination)) : ?>
                                    <nav class="dls-category-template__pagination" aria-label="Pagination">
                                        <?php foreach ($pagination as $link_item) : ?>
                                            <?php echo wp_kses_post($link_item); ?>
                                        <?php endforeach; ?>
                                    </nav>
                                <?php endif; ?>
                            <?php else : ?>
                                <article class="dls-category-template__lead">
                                    <div class="dls-category-template__lead-body">
                                        <h2 class="dls-category-template__lead-title"><?php echo esc_html__('У цій рубриці поки немає матеріалів.', 'default'); ?></h2>
                                    </div>
                                </article>
                            <?php endif; ?>
                        </div>

                        <aside class="dls-category-template__rail" aria-label="Right panel">
                            <div class="dls-category-template__rail-inner">
                                <section class="dls-category-template__widget">
                                    <h2 class="dls-category-template__widget-title"><?php echo esc_html__('Контент рубрики', 'default'); ?></h2>
                                    <ul class="dls-category-template__links">
                                        <?php if ($content_query->have_posts()) : ?>
                                            <?php while ($content_query->have_posts()) : $content_query->the_post(); ?>
                                                <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
                                            <?php endwhile; ?>
                                        <?php else : ?>
                                            <li><a href="<?php echo esc_url($term_link); ?>"><?php echo esc_html__('Усі матеріали рубрики', 'default'); ?></a></li>
                                        <?php endif; ?>
                                    </ul>
                                </section>

                                <section class="dls-category-template__widget">
                                    <h2 class="dls-category-template__widget-title"><?php echo esc_html__('Топові вакансії', 'default'); ?></h2>
                                    <div class="dls-category-template__jobs">
                                        <?php if (!empty($jobs)) : ?>
                                            <?php foreach ($jobs as $job) : ?>
                                                <?php
                                                if (!is_array($job)) {
                                                    continue;
                                                }

                                                $job_title = trim((string) ($job['title']['rendered'] ?? ''));
                                                $job_url = trim((string) ($job['link'] ?? ''));
                                                $company = dls_category_archive_template_job_terms($job, ['company', 'job_company', 'job_listing_company', 'listing_company']);
                                                $region = dls_category_archive_template_job_terms($job, ['region', 'job_region', 'job_listing_region']);
                                                $mode = dls_category_archive_template_job_terms($job, ['job_listing_type', 'job_type', 'employment_type', 'work_mode']);
                                                $logo_url = dls_category_archive_template_job_logo_url($job);
                                                ?>
                                                <?php if ($job_title !== '' && $job_url !== '') : ?>
                                                    <article class="dls-category-template__job">
                                                        <div class="dls-category-template__job-logo">
                                                            <?php if ($logo_url !== '') : ?>
                                                                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($job_title); ?>">
                                                            <?php else : ?>
                                                                <span class="dls-category-template__job-logo-fallback">Job</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div>
                                                            <h3 class="dls-category-template__job-title">
                                                                <a href="<?php echo esc_url($job_url); ?>" target="_blank" rel="noopener"><?php echo esc_html(wp_strip_all_tags($job_title)); ?></a>
                                                            </h3>
                                                            <div class="dls-category-template__job-meta">
                                                                <?php
                                                                $job_meta = array_filter([$company, $region, $mode]);
                                                                echo esc_html(!empty($job_meta) ? implode(' | ', $job_meta) : 'jobs.deadlawyers.org');
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </article>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <article class="dls-category-template__job">
                                                <div class="dls-category-template__job-logo">
                                                    <span class="dls-category-template__job-logo-fallback">Job</span>
                                                </div>
                                                <div>
                                                    <h3 class="dls-category-template__job-title">
                                                        <a href="https://jobs.deadlawyers.org/" target="_blank" rel="noopener"><?php echo esc_html__('Переглянути вакансії', 'default'); ?></a>
                                                    </h3>
                                                    <div class="dls-category-template__job-meta">jobs.deadlawyers.org</div>
                                                </div>
                                            </article>
                                        <?php endif; ?>
                                    </div>
                                </section>
                            </div>
                        </aside>
                    </div>
                </section>
            </div>
        </main>
        <?php
        wp_reset_postdata();
        get_footer();
        exit;
    }
}
add_action('template_redirect', 'dls_category_archive_template_render', 20);
