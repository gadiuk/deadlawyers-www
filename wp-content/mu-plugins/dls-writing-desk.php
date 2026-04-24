<?php
/**
 * Plugin Name: DLS Writing Desk
 * Description: Clean writing interface for posts.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('dls_writing_desk_page_hook')) {
    function dls_writing_desk_page_hook() {
        static $hook = '';

        return $hook;
    }
}

if (!function_exists('dls_writing_desk_set_page_hook')) {
    function dls_writing_desk_set_page_hook($hook) {
        static $stored = '';

        if (is_string($hook) && $hook !== '') {
            $stored = $hook;
        }

        return $stored;
    }
}

if (!function_exists('dls_writing_desk_languages')) {
    function dls_writing_desk_languages() {
        $languages = [
            'uk' => 'UK',
            'en' => 'EN',
        ];

        if (function_exists('pll_languages_list')) {
            $slugs = (array) pll_languages_list(['fields' => 'slug']);
            $names = (array) pll_languages_list(['fields' => 'name']);

            if (!empty($slugs)) {
                $languages = [];

                foreach ($slugs as $index => $slug) {
                    $slug = strtolower(trim((string) $slug));
                    if ($slug === '') {
                        continue;
                    }

                    $languages[$slug] = isset($names[$index]) && is_string($names[$index]) && $names[$index] !== ''
                        ? $names[$index]
                        : strtoupper($slug);
                }
            }
        }

        return $languages;
    }
}

if (!function_exists('dls_writing_desk_normalize_language')) {
    function dls_writing_desk_normalize_language($lang) {
        $lang = strtolower(trim((string) $lang));
        $languages = dls_writing_desk_languages();

        return isset($languages[$lang]) ? $lang : '';
    }
}

if (!function_exists('dls_writing_desk_get_post_language')) {
    function dls_writing_desk_get_post_language($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return '';
        }

        if (function_exists('pll_get_post_language')) {
            $lang = dls_writing_desk_normalize_language(pll_get_post_language($post_id, 'slug'));
            if ($lang !== '') {
                return $lang;
            }
        }

        if (taxonomy_exists('language')) {
            $slugs = wp_get_post_terms($post_id, 'language', ['fields' => 'slugs']);
            if (is_array($slugs) && !empty($slugs)) {
                $lang = dls_writing_desk_normalize_language(reset($slugs));
                if ($lang !== '') {
                    return $lang;
                }
            }
        }

        return '';
    }
}

if (!function_exists('dls_writing_desk_set_post_language')) {
    function dls_writing_desk_set_post_language($post_id, $lang) {
        $post_id = absint($post_id);
        $lang = dls_writing_desk_normalize_language($lang);

        if ($post_id < 1 || $lang === '') {
            return;
        }

        if (function_exists('pll_set_post_language')) {
            pll_set_post_language($post_id, $lang);
            return;
        }

        if (taxonomy_exists('language')) {
            wp_set_post_terms($post_id, [$lang], 'language', false);
        }
    }
}

if (!function_exists('dls_writing_desk_parse_selection')) {
    function dls_writing_desk_parse_selection($value) {
        if (function_exists('dls_na_ui_parse_selection_value')) {
            return (array) dls_na_ui_parse_selection_value($value);
        }

        return [];
    }
}

if (!function_exists('dls_writing_desk_dropdown_options')) {
    function dls_writing_desk_dropdown_options($post_id, $mode, $ensure_value = '') {
        if (function_exists('dls_na_ui_dropdown_options')) {
            return (array) dls_na_ui_dropdown_options($post_id, $mode, $ensure_value);
        }

        $items = [];
        $users = get_users([
            'orderby' => 'display_name',
            'order'   => 'ASC',
            'number'  => 5000,
        ]);

        foreach ((array) $users as $user) {
            if (!($user instanceof WP_User)) {
                continue;
            }

            if ($mode === 'editor') {
                if (!user_can($user, 'edit_others_posts') && !user_can($user, 'manage_options')) {
                    continue;
                }
            } elseif (!user_can($user, 'edit_posts')) {
                continue;
            }

            $items[] = [
                'value' => 'user:' . (int) $user->ID,
                'label' => (string) $user->display_name,
            ];
        }

        return $items;
    }
}

if (!function_exists('dls_writing_desk_validate_editor_selection')) {
    function dls_writing_desk_validate_editor_selection($selection) {
        if (function_exists('dls_na_ui_validate_editor_selection')) {
            return (array) dls_na_ui_validate_editor_selection($selection);
        }

        if (!is_array($selection) || ($selection['author_type'] ?? 'user') !== 'user') {
            return [];
        }

        $user = get_userdata((int) ($selection['user_id'] ?? 0));
        if (!($user instanceof WP_User)) {
            return [];
        }

        if (!user_can($user, 'edit_others_posts') && !user_can($user, 'manage_options')) {
            return [];
        }

        return $selection;
    }
}

if (!function_exists('dls_writing_desk_get_selected_people')) {
    function dls_writing_desk_get_selected_people($post_id) {
        $selected = [
            'author' => '',
            'editor' => '',
        ];

        if (!function_exists('dls_native_authors_get_post_assignments')) {
            return $selected;
        }

        foreach ((array) dls_native_authors_get_post_assignments($post_id) as $row) {
            $role = strtolower(trim((string) ($row['post_role'] ?? 'author')));
            $type = strtolower(trim((string) ($row['author_type'] ?? 'user')));

            if ($type === 'guest') {
                $value = 'guest:' . absint($row['term_id'] ?? 0);
            } else {
                $value = 'user:' . absint($row['user_id'] ?? 0);
            }

            if ($value === 'guest:0' || $value === 'user:0') {
                continue;
            }

            if ($role === 'editor' && $selected['editor'] === '') {
                $selected['editor'] = $value;
            }

            if ($role !== 'editor' && $selected['author'] === '') {
                $selected['author'] = $value;
            }
        }

        return $selected;
    }
}

if (!function_exists('dls_writing_desk_recent_posts')) {
    function dls_writing_desk_recent_posts() {
        $args = [
            'post_type'      => 'post',
            'post_status'    => ['draft', 'pending', 'future', 'publish', 'private'],
            'posts_per_page' => 24,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ];

        if (!current_user_can('edit_others_posts')) {
            $args['author'] = get_current_user_id();
        }

        return get_posts($args);
    }
}

if (!function_exists('dls_writing_desk_notice_message')) {
    function dls_writing_desk_notice_message($code) {
        $code = strtolower(trim((string) $code));

        if ($code === 'saved') {
            return 'Draft saved.';
        }

        if ($code === 'published') {
            return 'Post published.';
        }

        if ($code === 'updated') {
            return 'Post updated.';
        }

        return '';
    }
}

if (!function_exists('dls_writing_desk_admin_menu')) {
    function dls_writing_desk_admin_menu() {
        $hook = add_menu_page(
            'Writing Desk',
            'Writing Desk',
            'edit_posts',
            'dls-writing-desk',
            'dls_writing_desk_render_page',
            'dashicons-edit-large',
            3
        );

        dls_writing_desk_set_page_hook($hook);
    }
}
add_action('admin_menu', 'dls_writing_desk_admin_menu');

if (!function_exists('dls_writing_desk_enqueue_assets')) {
    function dls_writing_desk_enqueue_assets($hook) {
        if ($hook !== dls_writing_desk_set_page_hook('')) {
            return;
        }

        wp_enqueue_media();

        wp_register_style('dls-writing-desk', false, [], '1.0.0');
        wp_enqueue_style('dls-writing-desk');
        wp_add_inline_style('dls-writing-desk', '
            body.toplevel_page_dls-writing-desk {
                background:
                    radial-gradient(circle at top left, #f8edd1 0, #f8edd1 14%, transparent 40%),
                    linear-gradient(180deg, #f5ecd9 0%, #efe4cf 100%);
                color: #241c15;
            }
            body.toplevel_page_dls-writing-desk #wpadminbar,
            body.toplevel_page_dls-writing-desk #adminmenumain,
            body.toplevel_page_dls-writing-desk #wpfooter,
            body.toplevel_page_dls-writing-desk #screen-meta-links,
            body.toplevel_page_dls-writing-desk .notice:not(.dls-writing-desk__notice),
            body.toplevel_page_dls-writing-desk .update-nag {
                display: none !important;
            }
            body.toplevel_page_dls-writing-desk #wpcontent,
            body.toplevel_page_dls-writing-desk #wpfooter {
                margin-left: 0;
            }
            body.toplevel_page_dls-writing-desk #wpbody-content {
                padding-bottom: 0;
            }
            .dls-writing-desk {
                min-height: 100vh;
                padding: 26px 24px 48px;
                box-sizing: border-box;
                font-family: "Iowan Old Style", "Palatino Linotype", "Book Antiqua", Georgia, serif;
            }
            .dls-writing-desk__shell {
                display: grid;
                grid-template-columns: 320px minmax(0, 1fr);
                gap: 22px;
                align-items: start;
            }
            .dls-writing-desk__panel,
            .dls-writing-desk__editor {
                background: rgba(255,255,255,0.78);
                border: 1px solid rgba(36,28,21,0.12);
                border-radius: 22px;
                box-shadow: 0 18px 40px rgba(66, 44, 14, 0.08);
                backdrop-filter: blur(8px);
            }
            .dls-writing-desk__panel {
                padding: 18px;
                position: sticky;
                top: 24px;
            }
            .dls-writing-desk__editor {
                padding: 24px;
            }
            .dls-writing-desk__topbar {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: center;
                margin-bottom: 18px;
            }
            .dls-writing-desk__brand {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            .dls-writing-desk__eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font: 600 11px/1.2 "Helvetica Neue", Arial, sans-serif;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: #8a6133;
            }
            .dls-writing-desk__title {
                margin: 0;
                font-size: 34px;
                line-height: 1;
                font-weight: 700;
                color: #1f1711;
            }
            .dls-writing-desk__sub {
                margin: 0;
                color: #725743;
                font: 500 14px/1.5 "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__actions,
            .dls-writing-desk__footer-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: center;
            }
            .dls-writing-desk__button,
            .dls-writing-desk__button:visited {
                border: 0;
                border-radius: 999px;
                padding: 12px 18px;
                background: #2d2117;
                color: #fff;
                text-decoration: none;
                cursor: pointer;
                font: 600 13px/1 "Helvetica Neue", Arial, sans-serif;
                letter-spacing: 0.02em;
            }
            .dls-writing-desk__button--soft,
            .dls-writing-desk__button--soft:visited {
                background: #e8d9be;
                color: #2f251d;
            }
            .dls-writing-desk__button--accent,
            .dls-writing-desk__button--accent:visited {
                background: linear-gradient(135deg, #af3d22, #7d2010);
            }
            .dls-writing-desk__notice {
                margin: 0 0 16px;
                border: 0;
                border-radius: 16px;
                background: #ecf7ee;
                color: #17351f;
                box-shadow: none;
            }
            .dls-writing-desk__editor-grid {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 290px;
                gap: 22px;
            }
            .dls-writing-desk__main {
                min-width: 0;
            }
            .dls-writing-desk__side {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }
            .dls-writing-desk__block {
                border: 1px solid rgba(36,28,21,0.1);
                background: rgba(255,255,255,0.68);
                border-radius: 18px;
                padding: 16px;
            }
            .dls-writing-desk__block h2,
            .dls-writing-desk__block h3 {
                margin: 0 0 12px;
                font-size: 15px;
                line-height: 1.3;
                font-family: "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__field {
                margin-bottom: 18px;
            }
            .dls-writing-desk__field:last-child {
                margin-bottom: 0;
            }
            .dls-writing-desk__label {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 8px;
                color: #594333;
                font: 600 12px/1.2 "Helvetica Neue", Arial, sans-serif;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }
            .dls-writing-desk__input,
            .dls-writing-desk__textarea,
            .dls-writing-desk__select {
                width: 100%;
                border: 1px solid rgba(49,35,25,0.16);
                border-radius: 14px;
                padding: 14px 16px;
                box-sizing: border-box;
                background: #fffdf8;
                color: #201814;
                font: 400 16px/1.5 "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__input--title {
                font: 700 32px/1.15 "Iowan Old Style", "Palatino Linotype", Georgia, serif;
                border-radius: 18px;
                padding: 18px 20px;
            }
            .dls-writing-desk__textarea {
                min-height: 120px;
                resize: vertical;
            }
            .dls-writing-desk__muted {
                color: #7b6552;
                font: 500 12px/1.4 "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__posts {
                display: flex;
                flex-direction: column;
                gap: 10px;
                max-height: calc(100vh - 220px);
                overflow: auto;
                padding-right: 4px;
            }
            .dls-writing-desk__post-link {
                display: block;
                padding: 12px 14px;
                border-radius: 16px;
                text-decoration: none;
                background: rgba(255,255,255,0.8);
                color: #231a14;
                border: 1px solid rgba(36,28,21,0.08);
            }
            .dls-writing-desk__post-link.is-active {
                background: linear-gradient(135deg, #2b1f17, #4a3324);
                color: #fff8f0;
            }
            .dls-writing-desk__post-title {
                display: block;
                font-weight: 700;
                margin-bottom: 4px;
                line-height: 1.35;
            }
            .dls-writing-desk__post-meta {
                display: block;
                opacity: 0.72;
                font: 500 12px/1.4 "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__checklist {
                display: grid;
                gap: 8px;
                max-height: 220px;
                overflow: auto;
            }
            .dls-writing-desk__check {
                display: flex;
                gap: 10px;
                align-items: flex-start;
                font: 500 14px/1.35 "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__thumb {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .dls-writing-desk__thumb-preview {
                min-height: 148px;
                border-radius: 16px;
                background: linear-gradient(135deg, #e9dec9, #f8f2e7);
                border: 1px dashed rgba(36,28,21,0.14);
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                color: #81654d;
                text-align: center;
                padding: 12px;
            }
            .dls-writing-desk__thumb-preview img {
                width: 100%;
                height: auto;
                display: block;
            }
            .dls-writing-desk__statline {
                display: flex;
                gap: 16px;
                flex-wrap: wrap;
                margin: 0 0 16px;
                color: #6b5441;
                font: 600 12px/1.4 "Helvetica Neue", Arial, sans-serif;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }
            .dls-writing-desk__footer-actions {
                margin-top: 22px;
                padding-top: 18px;
                border-top: 1px solid rgba(36,28,21,0.08);
            }
            #dls_writing_desk_content_ifr {
                min-height: 520px !important;
                background: #fffdfa;
            }
            @media (max-width: 1180px) {
                .dls-writing-desk__shell,
                .dls-writing-desk__editor-grid {
                    grid-template-columns: 1fr;
                }
                .dls-writing-desk__panel {
                    position: static;
                }
            }
            @media (max-width: 782px) {
                .dls-writing-desk {
                    padding: 16px 12px 32px;
                }
                .dls-writing-desk__title {
                    font-size: 28px;
                }
                .dls-writing-desk__input--title {
                    font-size: 26px;
                }
            }
        ');

        wp_register_script('dls-writing-desk', false, ['jquery'], '1.0.0', true);
        wp_enqueue_script('dls-writing-desk');
        wp_add_inline_script('dls-writing-desk', '
            (function ($) {
                function stripHtml(value) {
                    return String(value || "").replace(/<[^>]*>/g, " ").replace(/\\s+/g, " ").trim();
                }

                function updateCounts() {
                    var title = $("#dls-writing-desk-title").val() || "";
                    $(".dls-writing-desk__title-count").text(title.length + " chars");

                    var excerpt = $("#dls-writing-desk-excerpt").val() || "";
                    $(".dls-writing-desk__excerpt-count").text(excerpt.length + " chars");

                    var editorText = "";
                    if (window.tinyMCE && tinyMCE.get("dls_writing_desk_content")) {
                        editorText = tinyMCE.get("dls_writing_desk_content").getContent({ format: "text" });
                    } else {
                        editorText = $("#dls_writing_desk_content").val() || "";
                    }

                    editorText = stripHtml(editorText);
                    var words = editorText === "" ? 0 : editorText.split(/\\s+/).length;
                    $(".dls-writing-desk__word-count").text(words + " words");
                }

                function setThumb(url) {
                    var preview = $(".dls-writing-desk__thumb-preview");
                    if (!url) {
                        preview.html("<span>No featured image yet</span>");
                        return;
                    }

                    preview.html("<img src=\"" + url + "\" alt=\"\">");
                }

                $(document).on("click", ".dls-writing-desk__select-image", function (event) {
                    event.preventDefault();

                    if (typeof wp === "undefined" || !wp.media) {
                        return;
                    }

                    var frame = wp.media({
                        title: "Choose featured image",
                        library: { type: "image" },
                        button: { text: "Use image" },
                        multiple: false
                    });

                    frame.on("select", function () {
                        var item = frame.state().get("selection").first();
                        if (!item) {
                            return;
                        }

                        var data = item.toJSON();
                        $("#dls-writing-desk-thumbnail-id").val(data.id ? String(data.id) : "");
                        setThumb(data.url || "");
                    });

                    frame.open();
                });

                $(document).on("click", ".dls-writing-desk__remove-image", function (event) {
                    event.preventDefault();
                    $("#dls-writing-desk-thumbnail-id").val("");
                    setThumb("");
                });

                $(document).on("input", "#dls-writing-desk-title, #dls-writing-desk-excerpt", updateCounts);
                $(document).ready(updateCounts);

                if (window.tinyMCE) {
                    $(document).on("tinymce-editor-init", function (event, editor) {
                        if (editor && editor.id === "dls_writing_desk_content") {
                            editor.on("keyup change setcontent", updateCounts);
                            updateCounts();
                        }
                    });
                }
            })(jQuery);
        ');
    }
}
add_action('admin_enqueue_scripts', 'dls_writing_desk_enqueue_assets');

if (!function_exists('dls_writing_desk_save_post')) {
    function dls_writing_desk_save_post() {
        if (!current_user_can('edit_posts')) {
            wp_die('You do not have permission to write posts.');
        }

        check_admin_referer('dls_writing_desk_save', 'dls_writing_desk_nonce');

        $post_id = absint($_POST['dls_writing_desk_post_id'] ?? 0);
        $status_action = strtolower(trim((string) ($_POST['dls_writing_desk_submit'] ?? 'draft')));
        $requested_status = $status_action === 'publish' ? 'publish' : 'draft';

        if ($post_id > 0 && !current_user_can('edit_post', $post_id)) {
            wp_die('You do not have permission to edit this post.');
        }

        $title = sanitize_text_field((string) ($_POST['dls_writing_desk_title'] ?? ''));
        $content = wp_kses_post((string) ($_POST['dls_writing_desk_content'] ?? ''));
        $excerpt = sanitize_textarea_field((string) ($_POST['dls_writing_desk_excerpt'] ?? ''));
        $language = dls_writing_desk_normalize_language($_POST['dls_writing_desk_language'] ?? '');
        $thumbnail_id = absint($_POST['dls_writing_desk_thumbnail_id'] ?? 0);
        $categories = array_values(array_filter(array_map('absint', (array) ($_POST['dls_writing_desk_categories'] ?? []))));

        $postarr = [
            'post_type'    => 'post',
            'post_title'   => $title !== '' ? $title : 'Untitled Draft',
            'post_content' => $content,
            'post_excerpt' => $excerpt,
            'post_status'  => $requested_status,
        ];

        if ($post_id > 0) {
            $postarr['ID'] = $post_id;
        } else {
            $postarr['post_author'] = get_current_user_id();
        }

        $saved_post_id = wp_insert_post(wp_slash($postarr), true);
        if (is_wp_error($saved_post_id)) {
            wp_die(esc_html($saved_post_id->get_error_message()));
        }

        if (!empty($categories)) {
            wp_set_post_terms($saved_post_id, $categories, 'category', false);
        }

        if ($language !== '') {
            dls_writing_desk_set_post_language($saved_post_id, $language);
        }

        if ($thumbnail_id > 0) {
            set_post_thumbnail($saved_post_id, $thumbnail_id);
        } else {
            delete_post_thumbnail($saved_post_id);
        }

        if (function_exists('dls_native_authors_save_assignments_for_post')) {
            $selected_items = [];
            $role_map = [];

            $author_value = sanitize_text_field((string) ($_POST['dls_writing_desk_author'] ?? ''));
            $editor_value = sanitize_text_field((string) ($_POST['dls_writing_desk_editor'] ?? ''));

            $author_selection = dls_writing_desk_parse_selection($author_value);
            if (!empty($author_selection)) {
                $selected_items[] = $author_selection;
                $role_map[$author_value] = 'author';
            }

            $editor_selection = dls_writing_desk_validate_editor_selection(dls_writing_desk_parse_selection($editor_value));
            if (!empty($editor_selection)) {
                $selected_items[] = $editor_selection;
                $role_map[$editor_value] = 'editor';
            }

            dls_native_authors_save_assignments_for_post($saved_post_id, $selected_items, $role_map);
        }

        $notice = $requested_status === 'publish' ? ($post_id > 0 ? 'updated' : 'published') : 'saved';
        $redirect_url = add_query_arg([
            'page'        => 'dls-writing-desk',
            'desk_post'   => $saved_post_id,
            'desk_notice' => $notice,
        ], admin_url('admin.php'));

        wp_safe_redirect($redirect_url);
        exit;
    }
}
add_action('admin_post_dls_writing_desk_save', 'dls_writing_desk_save_post');

if (!function_exists('dls_writing_desk_render_page')) {
    function dls_writing_desk_render_page() {
        if (!current_user_can('edit_posts')) {
            return;
        }

        $post_id = absint($_GET['desk_post'] ?? 0);
        $post = $post_id > 0 ? get_post($post_id) : null;

        if (!($post instanceof WP_Post) || $post->post_type !== 'post' || !current_user_can('edit_post', $post->ID)) {
            $post = null;
            $post_id = 0;
        }

        $selected_people = $post ? dls_writing_desk_get_selected_people($post->ID) : ['author' => '', 'editor' => ''];
        $author_options = dls_writing_desk_dropdown_options($post_id, 'author', $selected_people['author']);
        $editor_options = dls_writing_desk_dropdown_options($post_id, 'editor', $selected_people['editor']);
        $languages = dls_writing_desk_languages();
        $current_language = $post ? dls_writing_desk_get_post_language($post->ID) : '';
        if ($current_language === '' && isset($languages['uk'])) {
            $current_language = 'uk';
        } elseif ($current_language === '') {
            $language_keys = array_keys($languages);
            $current_language = !empty($language_keys) ? (string) reset($language_keys) : '';
        }

        $categories = get_terms([
            'taxonomy'   => 'category',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);
        $selected_categories = $post ? wp_get_post_categories($post->ID) : [];
        $recent_posts = dls_writing_desk_recent_posts();
        $notice_message = dls_writing_desk_notice_message($_GET['desk_notice'] ?? '');
        $thumbnail_id = $post ? (int) get_post_thumbnail_id($post->ID) : 0;
        $thumbnail_url = $thumbnail_id > 0 ? (string) wp_get_attachment_image_url($thumbnail_id, 'medium_large') : '';
        $view_url = '';

        if ($post instanceof WP_Post) {
            $view_url = $post->post_status === 'publish'
                ? (string) get_permalink($post->ID)
                : (string) get_preview_post_link($post->ID);
        }

        echo '<div class="wrap dls-writing-desk">';
        echo '<div class="dls-writing-desk__topbar">';
        echo '<div class="dls-writing-desk__brand">';
        echo '<span class="dls-writing-desk__eyebrow">Dead Lawyers Society / Writing Desk</span>';
        echo '<h1 class="dls-writing-desk__title">Write with less noise.</h1>';
        echo '<p class="dls-writing-desk__sub">This is a clean writing screen for title, body, excerpt, language, categories, author, editor, and featured image.</p>';
        echo '</div>';
        echo '<div class="dls-writing-desk__actions">';
        echo '<a class="dls-writing-desk__button dls-writing-desk__button--soft" href="' . esc_url(add_query_arg(['page' => 'dls-writing-desk'], admin_url('admin.php'))) . '">New Draft</a>';
        if ($post instanceof WP_Post) {
            echo '<a class="dls-writing-desk__button dls-writing-desk__button--soft" href="' . esc_url(get_edit_post_link($post->ID, '')) . '">Open WordPress Editor</a>';
            if ($view_url !== '') {
                echo '<a class="dls-writing-desk__button dls-writing-desk__button--soft" href="' . esc_url($view_url) . '" target="_blank" rel="noopener">Preview</a>';
            }
        }
        echo '<a class="dls-writing-desk__button dls-writing-desk__button--soft" href="' . esc_url(admin_url()) . '">Dashboard</a>';
        echo '</div>';
        echo '</div>';

        if ($notice_message !== '') {
            echo '<div class="notice dls-writing-desk__notice"><p>' . esc_html($notice_message) . '</p></div>';
        }

        echo '<div class="dls-writing-desk__shell">';
        echo '<aside class="dls-writing-desk__panel">';
        echo '<div class="dls-writing-desk__field">';
        echo '<div class="dls-writing-desk__label"><span>Recent Posts</span><span>' . esc_html((string) count($recent_posts)) . '</span></div>';
        echo '<div class="dls-writing-desk__posts">';
        foreach ($recent_posts as $recent_post) {
            if (!($recent_post instanceof WP_Post)) {
                continue;
            }

            $is_active = $post instanceof WP_Post && $post->ID === $recent_post->ID;
            echo '<a class="dls-writing-desk__post-link' . ($is_active ? ' is-active' : '') . '" href="' . esc_url(add_query_arg([
                'page'      => 'dls-writing-desk',
                'desk_post' => $recent_post->ID,
            ], admin_url('admin.php'))) . '">';
            echo '<span class="dls-writing-desk__post-title">' . esc_html(get_the_title($recent_post->ID) !== '' ? get_the_title($recent_post->ID) : 'Untitled Draft') . '</span>';
            echo '<span class="dls-writing-desk__post-meta">' . esc_html(ucfirst((string) $recent_post->post_status)) . ' / ' . esc_html(get_the_modified_date('Y-m-d', $recent_post->ID)) . '</span>';
            echo '</a>';
        }
        echo '</div>';
        echo '</div>';
        echo '</aside>';

        echo '<section class="dls-writing-desk__editor">';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('dls_writing_desk_save', 'dls_writing_desk_nonce');
        echo '<input type="hidden" name="action" value="dls_writing_desk_save">';
        echo '<input type="hidden" name="dls_writing_desk_post_id" value="' . esc_attr((string) $post_id) . '">';

        echo '<div class="dls-writing-desk__statline">';
        echo '<span class="dls-writing-desk__word-count">0 words</span>';
        echo '<span class="dls-writing-desk__title-count">0 chars</span>';
        echo '<span class="dls-writing-desk__excerpt-count">0 chars</span>';
        echo '</div>';

        echo '<div class="dls-writing-desk__editor-grid">';
        echo '<div class="dls-writing-desk__main">';
        echo '<div class="dls-writing-desk__field">';
        echo '<div class="dls-writing-desk__label"><span>Title</span><span>Headline</span></div>';
        echo '<input id="dls-writing-desk-title" class="dls-writing-desk__input dls-writing-desk__input--title" type="text" name="dls_writing_desk_title" value="' . esc_attr($post instanceof WP_Post ? $post->post_title : '') . '" placeholder="Write the headline here">';
        echo '</div>';

        echo '<div class="dls-writing-desk__field">';
        echo '<div class="dls-writing-desk__label"><span>Story</span><span>Body</span></div>';
        wp_editor(
            $post instanceof WP_Post ? $post->post_content : '',
            'dls_writing_desk_content',
            [
                'textarea_name' => 'dls_writing_desk_content',
                'media_buttons' => true,
                'textarea_rows' => 22,
                'teeny'         => false,
                'quicktags'     => true,
                'tinymce'       => [
                    'wp_autoresize_on' => true,
                    'toolbar1'         => 'formatselect,bold,italic,link,bullist,numlist,blockquote,alignleft,aligncenter,alignright,undo,redo',
                    'toolbar2'         => 'pastetext,removeformat,charmap,outdent,indent,hr,fullscreen',
                ],
            ]
        );
        echo '</div>';

        echo '<div class="dls-writing-desk__field">';
        echo '<div class="dls-writing-desk__label"><span>Excerpt</span><span>Deck / teaser</span></div>';
        echo '<textarea id="dls-writing-desk-excerpt" class="dls-writing-desk__textarea" name="dls_writing_desk_excerpt" placeholder="Short summary for listings, previews, and social copy later.">' . esc_textarea($post instanceof WP_Post ? $post->post_excerpt : '') . '</textarea>';
        echo '</div>';
        echo '</div>';

        echo '<div class="dls-writing-desk__side">';
        echo '<div class="dls-writing-desk__block">';
        echo '<h2>Publish</h2>';
        echo '<div class="dls-writing-desk__muted">The current WordPress editor stays untouched. This desk is an isolated writing surface.</div>';
        echo '<div class="dls-writing-desk__footer-actions">';
        echo '<button class="dls-writing-desk__button dls-writing-desk__button--soft" type="submit" name="dls_writing_desk_submit" value="draft">Save Draft</button>';
        echo '<button class="dls-writing-desk__button dls-writing-desk__button--accent" type="submit" name="dls_writing_desk_submit" value="publish">' . esc_html($post instanceof WP_Post && $post->post_status === 'publish' ? 'Update Post' : 'Publish') . '</button>';
        echo '</div>';
        echo '</div>';

        echo '<div class="dls-writing-desk__block">';
        echo '<h3>Language</h3>';
        echo '<div class="dls-writing-desk__field">';
        echo '<select class="dls-writing-desk__select" name="dls_writing_desk_language">';
        foreach ($languages as $slug => $label) {
            echo '<option value="' . esc_attr($slug) . '"' . selected($current_language, $slug, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        echo '</div>';

        echo '<div class="dls-writing-desk__block">';
        echo '<h3>People</h3>';
        echo '<div class="dls-writing-desk__field">';
        echo '<div class="dls-writing-desk__label"><span>Author</span><span>Byline</span></div>';
        echo '<select class="dls-writing-desk__select" name="dls_writing_desk_author">';
        echo '<option value="">Select author</option>';
        foreach ($author_options as $item) {
            $value = (string) ($item['value'] ?? '');
            $label = (string) ($item['label'] ?? '');
            if ($value === '' || $label === '') {
                continue;
            }
            echo '<option value="' . esc_attr($value) . '"' . selected($selected_people['author'], $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        echo '<div class="dls-writing-desk__field">';
        echo '<div class="dls-writing-desk__label"><span>Editor</span><span>Credit</span></div>';
        echo '<select class="dls-writing-desk__select" name="dls_writing_desk_editor">';
        echo '<option value="">Select editor</option>';
        foreach ($editor_options as $item) {
            $value = (string) ($item['value'] ?? '');
            $label = (string) ($item['label'] ?? '');
            if ($value === '' || $label === '') {
                continue;
            }
            echo '<option value="' . esc_attr($value) . '"' . selected($selected_people['editor'], $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        echo '</div>';

        echo '<div class="dls-writing-desk__block">';
        echo '<h3>Categories</h3>';
        echo '<div class="dls-writing-desk__checklist">';
        foreach ((array) $categories as $category) {
            if (!($category instanceof WP_Term)) {
                continue;
            }

            echo '<label class="dls-writing-desk__check">';
            echo '<input type="checkbox" name="dls_writing_desk_categories[]" value="' . esc_attr((string) $category->term_id) . '"' . checked(in_array((int) $category->term_id, $selected_categories, true), true, false) . '>';
            echo '<span>' . esc_html($category->name) . '</span>';
            echo '</label>';
        }
        echo '</div>';
        echo '</div>';

        echo '<div class="dls-writing-desk__block">';
        echo '<h3>Featured Image</h3>';
        echo '<div class="dls-writing-desk__thumb">';
        echo '<input id="dls-writing-desk-thumbnail-id" type="hidden" name="dls_writing_desk_thumbnail_id" value="' . esc_attr((string) $thumbnail_id) . '">';
        echo '<div class="dls-writing-desk__thumb-preview">';
        if ($thumbnail_url !== '') {
            echo '<img src="' . esc_url($thumbnail_url) . '" alt="">';
        } else {
            echo '<span>No featured image yet</span>';
        }
        echo '</div>';
        echo '<div class="dls-writing-desk__actions">';
        echo '<button class="dls-writing-desk__button dls-writing-desk__button--soft dls-writing-desk__select-image" type="button">Choose Image</button>';
        echo '<button class="dls-writing-desk__button dls-writing-desk__button--soft dls-writing-desk__remove-image" type="button">Remove</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        echo '<div class="dls-writing-desk__footer-actions">';
        echo '<button class="dls-writing-desk__button dls-writing-desk__button--soft" type="submit" name="dls_writing_desk_submit" value="draft">Save Draft</button>';
        echo '<button class="dls-writing-desk__button dls-writing-desk__button--accent" type="submit" name="dls_writing_desk_submit" value="publish">' . esc_html($post instanceof WP_Post && $post->post_status === 'publish' ? 'Update Post' : 'Publish') . '</button>';
        echo '</div>';
        echo '</form>';
        echo '</section>';
        echo '</div>';
        echo '</div>';
    }
}
