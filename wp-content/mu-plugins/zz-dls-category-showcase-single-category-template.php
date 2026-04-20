<?php
/**
 * Dedicated template for DLS category archives.
 *
 * @package deadlawyers-www
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$term = get_queried_object();

if ( ! ( $term instanceof WP_Term ) || 'category' !== (string) $term->taxonomy ) {
    return;
}

global $wp_query;

$main_posts = array();
foreach ( (array) $wp_query->posts as $maybe_post ) {
    if ( $maybe_post instanceof WP_Post ) {
        $main_posts[] = $maybe_post;
    }
}

$lead_post = ! empty( $main_posts ) ? array_shift( $main_posts ) : null;
$exclude   = array();

foreach ( (array) $wp_query->posts as $query_post ) {
    if ( $query_post instanceof WP_Post ) {
        $exclude[] = (int) $query_post->ID;
    }
}

$sidebar_posts = dls_cat_tpl_sidebar_posts( (int) $term->term_id, $exclude, 6 );
$jobs          = dls_cat_tpl_fetch_jobs( 6 );
$description   = trim( wp_strip_all_tags( (string) term_description( $term, 'category' ) ) );
$post_count    = (int) $term->count;
$pagination    = paginate_links(
    array(
        'type'      => 'plain',
        'current'   => max( 1, (int) get_query_var( 'paged' ) ),
        'total'     => max( 1, (int) $wp_query->max_num_pages ),
        'prev_text' => '←',
        'next_text' => '→',
    )
);

get_header();
?>
<div class="content-container site-container dls-cat-page-shell">
    <main id="primary" class="content-area dls-cat-page__main">
        <div id="main" class="site-main">
            <div class="dls-cat-page">
                <header class="dls-cat-page__hero">
                    <p class="dls-cat-page__eyebrow"><?php echo esc_html__( 'Рубрика', 'default' ); ?></p>
                    <h1 class="dls-cat-page__title"><?php echo esc_html( single_cat_title( '', false ) ); ?></h1>
                    <div class="dls-cat-page__meta">
                        <?php
                        printf(
                            /* translators: %d: number of posts in category. */
                            esc_html__( 'Матеріалів: %d', 'default' ),
                            (int) $post_count
                        );
                        ?>
                    </div>
                    <?php if ( '' !== $description ) : ?>
                        <p class="dls-cat-page__description"><?php echo esc_html( $description ); ?></p>
                    <?php endif; ?>
                </header>

                <div class="dls-cat-page__stack">
                    <?php if ( $lead_post instanceof WP_Post ) : ?>
                        <?php dls_cat_tpl_render_lead( $lead_post ); ?>
                    <?php elseif ( empty( $main_posts ) ) : ?>
                        <article class="dls-cat-story dls-cat-story--lead">
                            <div class="dls-cat-story__body">
                                <p class="dls-cat-empty"><?php echo esc_html__( 'У цій рубриці ще немає опублікованих матеріалів.', 'default' ); ?></p>
                            </div>
                        </article>
                    <?php endif; ?>

                    <?php dls_cat_tpl_render_grid( $main_posts ); ?>

                    <?php if ( $pagination ) : ?>
                        <nav class="dls-cat-pagination" aria-label="<?php echo esc_attr__( 'Навігація рубрики', 'default' ); ?>">
                            <?php echo wp_kses_post( $pagination ); ?>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <aside id="secondary" role="complementary" class="widget-area primary-sidebar dls-cat-page__rail">
        <div class="dls-cat-page__rail-inner">
            <section class="dls-cat-sidebar-card">
                <h2 class="dls-cat-sidebar-card__title"><?php echo esc_html__( 'Контент рубрики', 'default' ); ?></h2>
                <?php if ( ! empty( $sidebar_posts ) ) : ?>
                    <ul class="dls-cat-sidebar-list">
                        <?php foreach ( $sidebar_posts as $side_post ) : ?>
                            <?php if ( ! ( $side_post instanceof WP_Post ) ) { continue; } ?>
                            <li>
                                <a href="<?php echo esc_url( get_permalink( $side_post->ID ) ); ?>"><?php echo esc_html( get_the_title( $side_post->ID ) ); ?></a>
                                <time datetime="<?php echo esc_attr( get_the_date( 'c', $side_post->ID ) ); ?>"><?php echo esc_html( get_the_date( 'Y-m-d', $side_post->ID ) ); ?></time>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p class="dls-cat-empty"><?php echo esc_html__( 'Ще немає додаткових матеріалів у цій рубриці.', 'default' ); ?></p>
                <?php endif; ?>
            </section>

            <section class="dls-cat-sidebar-card">
                <h2 class="dls-cat-sidebar-card__title"><?php echo esc_html__( 'Топові вакансії', 'default' ); ?></h2>
                <?php dls_cat_tpl_render_jobs( $jobs ); ?>
            </section>
        </div>
    </aside>
</div>
<?php
get_footer();
