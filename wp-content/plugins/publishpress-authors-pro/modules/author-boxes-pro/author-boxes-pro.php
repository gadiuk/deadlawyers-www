<?php
/**
 * @package PublishPress Authors Pro
 * @author  PublishPress
 *
 * Copyright (C) 2021 PublishPress
 *
 * This file is part of PublishPress Authors
 *
 * PublishPress Authors is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * PublishPress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PublishPress.  If not, see <http://www.gnu.org/licenses/>.
 */

use MultipleAuthors\Classes\Legacy\Module;
use MultipleAuthors\Classes\Utils;
use MultipleAuthors\Factory;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Class MA_Author_Boxes_Pro
 *
 * This class adds a review request system for your plugin or theme to the WP dashboard.
 */
class MA_Author_Boxes_Pro extends Module
{

    public $module_name = 'author_boxes_pro';

    public $module_url = '';

    /**
     * Instance for the module
     *
     * @var stdClass
     */
    public $module;

    /**
     * The constructor
     */
    public function __construct()
    {
        global $publishpress;

        $this->module_url = $this->get_module_url(__FILE__);

        // Register the module with PublishPress
        $args = [
            'title' => __('Author Boxes Pro', 'publishpress-authors-pro'),
            'short_description' => false,
            'extended_description' => false,
            'module_url' => $this->module_url,
            'icon_class' => 'dashicons dashicons-feedback',
            'slug' => 'author-boxes-pro',
            'default_options' => [
                'enabled' => 'on',
            ],
            'general_options' => false,
            'autoload' => true,
        ];

        // Apply a filter to the default options
        $args['default_options'] = apply_filters(
            'ppma_author_boxes_pro_default_options',
            $args['default_options']
        );

        $legacyPlugin = Factory::getLegacyPlugin();

        $this->module = $legacyPlugin->register_module($this->module_name, $args);

        parent::__construct();
    }

    /**
     *
     */
    public function init()
    {
        //add_filter('ma_author_boxes_editor_default_tab', [$this, 'authorBoxesDefaultTab']);
        //add_filter('authors_boxes_editor_fields_tabs', [$this, 'authorBoxesTabs'], 11, 2);
    }

    /**
     * Filter author boxes default tab
     *
     * @param string $default_tab
     *
     * @return string
     */
    public function authorBoxesDefaultTab($default_tab) {
        global $post;

        if (Utils::isAuthorsProActive() && (!empty($_GET['author_category_box']) || !empty(get_post_meta($post->ID, \MA_Author_Boxes::META_PREFIX . 'layout_parent_author_box', true)))) {
            $default_tab = 'author_categories';
        }

        return $default_tab;
    }

    /**
     * Filter author boxes tabs for author categories
     *
     * @param array $fields_tabs
     * @param object $post
     *
     * @return array
     */
    public function authorBoxesTabs($fields_tabs, $post)
    {

        if (Utils::isAuthorsProActive() && (!empty($_GET['author_category_box']) || !empty(get_post_meta($post->ID, \MA_Author_Boxes::META_PREFIX . 'layout_parent_author_box', true)))) {
            unset($fields_tabs['title'], $fields_tabs['avatar'], $fields_tabs['name'], $fields_tabs['author_bio'], $fields_tabs['meta'], $fields_tabs['profile_fields'], $fields_tabs['author_recent_posts'], $fields_tabs['box_layout']);
        }

        return $fields_tabs;
    }

    /**
     * Create default author boxes in the database.
     */
    public static function createDefaultAuthorBoxes()
    {
        $defaultAuthorBoxes = array_reverse(self::getAuthorBoxesDefaultList());

        foreach ($defaultAuthorBoxes as $name => $title) {
            self::createLayoutPost($name, $title);
            sleep(2);
        }

        self::reorder_default_boxes();
    }

    /**
     * reorder author boxes.
     */
    public static function reorder_default_boxes()
    {
        $posts = get_posts(
            [
                'post_type' => \MA_Author_Boxes::POST_TYPE_BOXES,
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby' => 'menu_order',
                'order' => 'ASC',
            ]
        );

        if (! empty($posts)) {
            $posts = array_reverse($posts);
            $position = 0;
            $author_category_boxes = [];
            foreach ($posts as $post) {
                if (!empty(get_post_meta($post->ID, \MA_Author_Boxes::META_PREFIX . 'layout_parent_author_box', true))) {
                    $author_category_boxes[] = $post;
                } else {
                    wp_update_post(array(
                        'ID' => $post->ID,
                        'menu_order' => $position,
                    ));
                    $position++;
                }
            }
            if (!empty($author_category_boxes)) {
                foreach ($author_category_boxes as $author_box) {
                    wp_update_post(array(
                        'ID' => $author_box->ID,
                        'menu_order' => $position,
                    ));
                    $position++;
                }
            }

        }
    }

    /**
     * Get default template list.
     */
    public static function getAuthorBoxesDefaultList()
    {
        $defaultAuthorBoxes = [
            'boxed_categories'                      => __('Boxed (Categories)', 'publishpress-authors-pro'),
            'two_columns_categories'                => __('Two Columns (Categories)', 'publishpress-authors-pro'),
            'list_author_category_inline'           => __('List Authors Inline (Categories)', 'publishpress-authors-pro'),
            'list_author_category_block'            => __('List Authors Block (Categories)', 'publishpress-authors-pro'),
            'simple_name_author_category_block'     => __('Simple Name Authors Block (Categories)', 'publishpress-authors-pro'),
            'simple_name_author_category_inline'    => __('Simple Name Authors Inline (Categories)', 'publishpress-authors-pro')
        ];

        return $defaultAuthorBoxes;
    }

    /**
     * Get a default author box(es) data
     *
     * @param string $default_slug
     * @return array|bool
     */
    public static function getAuthorBoxesDefaultData($default_slug = false)
    {
        $editor_datas = [];
        $editor_datas['boxed_categories']                   = self::getAuthorBoxesBoxedCategoriesEditorData();
        $editor_datas['two_columns_categories']             = self::getAuthorBoxesTwoColumnsCategoriesEditorData();
        $editor_datas['list_author_category_inline']        = self::getAuthorBoxesListCategoryInlineEditorData();
        $editor_datas['list_author_category_block']         = self::getAuthorBoxesListCategoryBlockEditorData();
        $editor_datas['simple_name_author_category_block']  = self::getAuthorBoxesSimpleNameCategoryBlockEditorData();
        $editor_datas['simple_name_author_category_inline'] = self::getAuthorBoxesSimpleNameCategoryInlineEditorData();

        if (!$default_slug) {
            return $editor_datas;
        } elseif (array_key_exists($default_slug, $editor_datas)) {
            return $editor_datas[$default_slug];
        }

        return false;
    }

    /**
     * Create the layout based on name and title
     *
     * @param string $name
     * @param string $title
     */
    protected static function createLayoutPost($name, $title)
    {

        // Check if we already have the layout based on the slug.
        $existingAuthorBox = Utils::get_page_by_title($title, OBJECT, \MA_Author_Boxes::POST_TYPE_BOXES);

        if ($existingAuthorBox && $existingAuthorBox->post_status === 'publish') {
            return;
        }

        $editor_data = self::getAuthorBoxesDefaultData($name);

        if ($editor_data && is_array($editor_data)) {
            $post_id = wp_insert_post(
                [
                    'post_type' => \MA_Author_Boxes::POST_TYPE_BOXES,
                    'post_title' => $title,
                    'post_content' => $title,
                    'post_status' => 'publish',
                    'post_name' => sanitize_title($name),
                ]
            );
            update_post_meta($post_id, \MA_Author_Boxes::META_PREFIX . 'layout_parent_author_box', $name);
            update_post_meta($post_id, \MA_Author_Boxes::META_PREFIX . 'layout_meta_value', $editor_data);
        }
    }

    /**
     * List category inline editor data
     *
     * @return array
     */
    public static function getAuthorBoxesListCategoryInlineEditorData()
    {
        $editor_data = [];

        //title default
        $editor_data['show_title'] = 0;
        $editor_data['title_text'] = esc_html__('Author');
        $editor_data['title_text_plural'] = esc_html__('Authors', 'publishpress-authors-pro');
        $editor_data['title_html_tag'] = 'h1';
        $editor_data['box_tab_custom_wrapper_class'] = 'pp-multiple-authors-layout-list-category-inline';
        //avatar default
        $editor_data['avatar_show'] = 0;
        $editor_data['avatar_size'] = 80;
        $editor_data['avatar_border_radius'] = 50;
        //name default
        $editor_data['name_show'] = 1;
        $editor_data['name_html_tag'] = 'span';
        //bio default
        $editor_data['avatar_show'] = 0;
        $editor_data['author_bio_html_tag'] = 'div';
        //meta default
        $editor_data['meta_show'] = 0;
        $editor_data['author_bio_show'] = 0;
        $editor_data['meta_email_show'] = 1;
        $editor_data['meta_site_link_show'] = 1;
        $editor_data['meta_html_tag'] = 'p';
        $editor_data['meta_background_color'] = '#655997';
        $editor_data['meta_color'] = '#ffffff';
        $editor_data['meta_link_hover_color'] = '#ffffff';
        //recent posts default
        $editor_data['author_recent_posts_title_show'] = 1;
        $editor_data['author_recent_posts_empty_show'] = 1;
        $editor_data['author_recent_posts_limit'] = 5;
        $editor_data['author_recent_posts_orderby'] = 'date';
        $editor_data['author_recent_posts_order'] = 'DESC';
        $editor_data['author_recent_posts_html_tag'] = 'div';
        $editor_data['author_recent_posts_title_border_bottom_style'] = 'dotted';
        $editor_data['author_recent_posts_alignment'] = 'left';
        //box layout default
        $editor_data['author_inline_display'] = 1;
        $editor_data['box_tab_layout_author_separator'] = ', ';
        $editor_data['box_layout_border_style'] = 'none';
        $editor_data['box_layout_shadow_horizontal_offset'] = 10;
        $editor_data['box_layout_shadow_vertical_offset'] = 10;
        $editor_data['box_layout_shadow_blur'] = 0;
        $editor_data['box_layout_shadow_speed'] = 0;
        $editor_data['box_layout_color'] = '#3c434a';
        //default css
        $editor_data['box_tab_custom_css'] = '.pp-multiple-authors-layout-list-category-inline ul.pp-multiple-authors-boxes-ul {display: flex; } .pp-multiple-authors-layout-list-category-inline ul.pp-multiple-authors-boxes-ul li { margin-right: 10px }';

        //author category
        $editor_data['author_categories_group'] = 1;
        $editor_data['author_categories_layout'] = 'list_author_category_inline';
        $editor_data['author_categories_title_option'] = 'before_group';
        $editor_data['author_categories_title_html_tag'] = 'span';
        $editor_data['author_categories_group_option'] = 'inline';
        $editor_data['author_categories_title_prefix'] = '<span class="square-dot"></span>  ';
        $editor_data['author_categories_title_suffix'] = ':  ';
        $editor_data['author_categories_group_display_style_laptop'] = 'block';
        $editor_data['author_categories_group_display_style_mobile'] = 'block';
        $editor_data['author_categories_right_space'] = '';
        $editor_data['author_categories_font_size'] = 15;
        $editor_data['author_categories_title_font_weight'] = 500;
        // hide all author fields
        $profile_fields   = apply_filters('multiple_authors_author_fields', [], false);

        foreach ($profile_fields as $key => $data) {
            $editor_data['profile_fields_hide_' . $key] = 1;
        }

        $editor_data = \MultipleAuthorBoxes\AuthorBoxesDefault::addEditorDataDefaultValues($editor_data);

        return $editor_data;
    }

    /**
     * List category block editor data
     *
     * @return array
     */
    public static function getAuthorBoxesListCategoryBlockEditorData()
    {
        $editor_data = [];

        //title default
        $editor_data['show_title'] = 0;
        $editor_data['title_text'] = esc_html__('Author');
        $editor_data['title_text_plural'] = esc_html__('Authors', 'publishpress-authors-pro');
        $editor_data['title_html_tag'] = 'h1';
        $editor_data['box_tab_custom_wrapper_class'] = 'pp-multiple-authors-layout-list-category-block';
        //avatar default
        $editor_data['avatar_show'] = 0;
        $editor_data['avatar_size'] = 80;
        $editor_data['avatar_border_radius'] = 50;
        //name default
        $editor_data['name_show'] = 1;
        $editor_data['name_html_tag'] = 'span';
        //bio default
        $editor_data['avatar_show'] = 0;
        $editor_data['author_bio_html_tag'] = 'div';
        //meta default
        $editor_data['meta_show'] = 0;
        $editor_data['author_bio_show'] = 0;
        $editor_data['meta_email_show'] = 1;
        $editor_data['meta_site_link_show'] = 1;
        $editor_data['meta_html_tag'] = 'p';
        $editor_data['meta_background_color'] = '#655997';
        $editor_data['meta_color'] = '#ffffff';
        $editor_data['meta_link_hover_color'] = '#ffffff';
        //recent posts default
        $editor_data['author_recent_posts_title_show'] = 1;
        $editor_data['author_recent_posts_empty_show'] = 1;
        $editor_data['author_recent_posts_limit'] = 5;
        $editor_data['author_recent_posts_orderby'] = 'date';
        $editor_data['author_recent_posts_order'] = 'DESC';
        $editor_data['author_recent_posts_html_tag'] = 'div';
        $editor_data['author_recent_posts_title_border_bottom_style'] = 'dotted';
        $editor_data['author_recent_posts_alignment'] = 'left';
        //box layout default
        $editor_data['author_inline_display'] = 1;
        $editor_data['box_tab_layout_author_separator'] = ', ';
        $editor_data['box_layout_border_style'] = 'none';
        $editor_data['box_layout_shadow_horizontal_offset'] = 10;
        $editor_data['box_layout_shadow_vertical_offset'] = 10;
        $editor_data['box_layout_shadow_blur'] = 0;
        $editor_data['box_layout_shadow_speed'] = 0;
        $editor_data['box_layout_color'] = '#3c434a';
        //default css
        $editor_data['box_tab_custom_css'] = '.pp-multiple-authors-layout-list-category-block ul.pp-multiple-authors-boxes-ul {display: flex; } .pp-multiple-authors-layout-list-category-block ul.pp-multiple-authors-boxes-ul li { margin-right: 10px }';

        //author category
        $editor_data['author_categories_group'] = 1;
        $editor_data['author_categories_layout'] = 'list_author_category_block';
        $editor_data['author_categories_title_option'] = 'before_group';
        $editor_data['author_categories_title_html_tag'] = 'span';
        $editor_data['author_categories_group_option'] = 'block';
        $editor_data['author_categories_title_prefix'] = '<span class="square-dot"></span> ';
        $editor_data['author_categories_title_suffix'] = ':  ';
        $editor_data['author_categories_group_display_style_laptop'] = 'block';
        $editor_data['author_categories_group_display_style_mobile'] = 'block';
        $editor_data['author_categories_right_space'] = '';
        $editor_data['author_categories_font_size'] = 15;
        $editor_data['author_categories_title_font_weight'] = 500;
        // hide all author fields
        $profile_fields   = apply_filters('multiple_authors_author_fields', [], false);

        foreach ($profile_fields as $key => $data) {
            $editor_data['profile_fields_hide_' . $key] = 1;
        }

        $editor_data = \MultipleAuthorBoxes\AuthorBoxesDefault::addEditorDataDefaultValues($editor_data);

        return $editor_data;
    }

    /**
     * Simple name category block editor data
     *
     * @return array
     */
    public static function getAuthorBoxesSimpleNameCategoryBlockEditorData()
    {
        $editor_data = [];

        //title default
        $editor_data['show_title'] = 1;
        $editor_data['title_text'] = esc_html__('Author');
        $editor_data['title_text_plural'] = esc_html__('Authors', 'publishpress-authors-pro');
        $editor_data['title_html_tag'] = 'h1';
        $editor_data['box_tab_custom_wrapper_class'] = 'pp-multiple-authors-layout-simple-name-category-block';
        //avatar default
        $editor_data['avatar_show'] = 0;
        $editor_data['avatar_size'] = 80;
        $editor_data['avatar_border_radius'] = 50;
        //name default
        $editor_data['name_show'] = 1;
        $editor_data['name_html_tag'] = 'span';
        //bio default
        $editor_data['avatar_show'] = 0;
        $editor_data['author_bio_html_tag'] = 'div';
        //meta default
        $editor_data['meta_show'] = 0;
        $editor_data['author_bio_show'] = 0;
        $editor_data['meta_email_show'] = 1;
        $editor_data['meta_site_link_show'] = 1;
        $editor_data['meta_html_tag'] = 'p';
        $editor_data['meta_background_color'] = '#655997';
        $editor_data['meta_color'] = '#ffffff';
        $editor_data['meta_link_hover_color'] = '#ffffff';
        //recent posts default
        $editor_data['author_recent_posts_title_show'] = 1;
        $editor_data['author_recent_posts_empty_show'] = 1;
        $editor_data['author_recent_posts_limit'] = 5;
        $editor_data['author_recent_posts_orderby'] = 'date';
        $editor_data['author_recent_posts_order'] = 'DESC';
        $editor_data['author_recent_posts_html_tag'] = 'div';
        $editor_data['author_recent_posts_title_border_bottom_style'] = 'dotted';
        $editor_data['author_recent_posts_alignment'] = 'left';
        //box layout default
        $editor_data['author_inline_display'] = 1;
        $editor_data['box_tab_layout_author_separator'] = '<br />';
        $editor_data['box_layout_border_style'] = 'none';
        $editor_data['box_layout_shadow_horizontal_offset'] = 10;
        $editor_data['box_layout_shadow_vertical_offset'] = 10;
        $editor_data['box_layout_shadow_blur'] = 0;
        $editor_data['box_layout_shadow_speed'] = 0;
        $editor_data['box_layout_color'] = '#3c434a';
        //default css
        $editor_data['box_tab_custom_css'] = '.pp-multiple-authors-layout-simple-name-category-block ul.pp-multiple-authors-boxes-ul {display: flex; } .pp-multiple-authors-layout-simple-name-category-block ul.pp-multiple-authors-boxes-ul li { margin-right: 10px }';

        //author category
        $editor_data['author_categories_group'] = 1;
        $editor_data['author_categories_layout'] = 'simple_name_author_category_block';
        $editor_data['author_categories_title_option'] = 'after_individual';
        $editor_data['author_categories_title_html_tag'] = 'span';
        $editor_data['author_categories_group_option'] = 'block';
        $editor_data['author_categories_title_prefix'] = ' (';
        $editor_data['author_categories_title_suffix'] = ')';
        $editor_data['author_categories_group_display_style_laptop'] = 'block';
        $editor_data['author_categories_group_display_style_mobile'] = 'block';
        $editor_data['author_categories_right_space'] = '';
        // hide all author fields
        $profile_fields   = apply_filters('multiple_authors_author_fields', [], false);

        foreach ($profile_fields as $key => $data) {
            $editor_data['profile_fields_hide_' . $key] = 1;
        }

        $editor_data = \MultipleAuthorBoxes\AuthorBoxesDefault::addEditorDataDefaultValues($editor_data);

        return $editor_data;
    }

    /**
     * Simple name category inline editor data
     *
     * @return array
     */
    public static function getAuthorBoxesSimpleNameCategoryInlineEditorData()
    {
        $editor_data = [];

        //title default
        $editor_data['show_title'] = 1;
        $editor_data['title_text'] = esc_html__('Author');
        $editor_data['title_text_plural'] = esc_html__('Authors', 'publishpress-authors-pro');
        $editor_data['title_html_tag'] = 'h1';
        $editor_data['box_tab_custom_wrapper_class'] = 'pp-multiple-authors-layout-simple-name-category-inline';
        //avatar default
        $editor_data['avatar_show'] = 0;
        $editor_data['avatar_size'] = 80;
        $editor_data['avatar_border_radius'] = 50;
        //name default
        $editor_data['name_show'] = 1;
        $editor_data['name_html_tag'] = 'span';
        //bio default
        $editor_data['avatar_show'] = 0;
        $editor_data['author_bio_html_tag'] = 'div';
        //meta default
        $editor_data['meta_show'] = 0;
        $editor_data['author_bio_show'] = 0;
        $editor_data['meta_email_show'] = 1;
        $editor_data['meta_site_link_show'] = 1;
        $editor_data['meta_html_tag'] = 'p';
        $editor_data['meta_background_color'] = '#655997';
        $editor_data['meta_color'] = '#ffffff';
        $editor_data['meta_link_hover_color'] = '#ffffff';
        //recent posts default
        $editor_data['author_recent_posts_title_show'] = 1;
        $editor_data['author_recent_posts_empty_show'] = 1;
        $editor_data['author_recent_posts_limit'] = 5;
        $editor_data['author_recent_posts_orderby'] = 'date';
        $editor_data['author_recent_posts_order'] = 'DESC';
        $editor_data['author_recent_posts_html_tag'] = 'div';
        $editor_data['author_recent_posts_title_border_bottom_style'] = 'dotted';
        $editor_data['author_recent_posts_alignment'] = 'left';
        //box layout default
        $editor_data['author_inline_display'] = 1;
        $editor_data['box_tab_layout_author_separator'] = '';
        $editor_data['box_layout_border_style'] = 'none';
        $editor_data['box_layout_shadow_horizontal_offset'] = 10;
        $editor_data['box_layout_shadow_vertical_offset'] = 10;
        $editor_data['box_layout_shadow_blur'] = 0;
        $editor_data['box_layout_shadow_speed'] = 0;
        $editor_data['box_layout_color'] = '#3c434a';
        //default css
        $editor_data['box_tab_custom_css'] = '.pp-multiple-authors-layout-simple-name-category-inline ul.pp-multiple-authors-boxes-ul {display: flex; } .pp-multiple-authors-layout-simple-name-category-inline ul.pp-multiple-authors-boxes-ul li { margin-right: 10px }';

        //author category
        $editor_data['author_categories_group'] = 1;
        $editor_data['author_categories_layout'] = 'simple_name_author_category_inline';
        $editor_data['author_categories_title_option'] = 'after_individual';
        $editor_data['author_categories_title_html_tag'] = 'span';
        $editor_data['author_categories_group_option'] = 'inline';
        $editor_data['author_categories_title_prefix'] = ' (';
        $editor_data['author_categories_title_suffix'] = ')';
        $editor_data['author_categories_group_display_style_laptop'] = 'block';
        $editor_data['author_categories_group_display_style_mobile'] = 'block';
        $editor_data['author_categories_right_space'] = '';
        // hide all author fields
        $profile_fields   = apply_filters('multiple_authors_author_fields', [], false);

        foreach ($profile_fields as $key => $data) {
            $editor_data['profile_fields_hide_' . $key] = 1;
        }

        $editor_data = \MultipleAuthorBoxes\AuthorBoxesDefault::addEditorDataDefaultValues($editor_data);

        return $editor_data;
    }

    /**
     * boxed categories editor data
     *
     * @return array
     */
    public static function getAuthorBoxesBoxedCategoriesEditorData()
    {
        $editor_data = [];

        //title default
        $editor_data['show_title'] = 0;
        $editor_data['title_text'] = esc_html__('Author');
        $editor_data['title_text_plural'] = esc_html__('Authors', 'publishpress-authors-pro');
        $editor_data['title_html_tag'] = 'h1';
        $editor_data['box_tab_custom_wrapper_class'] = 'pp-multiple-authors-layout-boxed-categories';
        //avatar default
        $editor_data['avatar_show'] = 1;
        $editor_data['avatar_size'] = 80;
        $editor_data['avatar_border_radius'] = 50;
        //name default
        $editor_data['name_show'] = 1;
        $editor_data['name_html_tag'] = 'span';
        $editor_data['display_name_position'] = 'infront_of_avatar';

        //bio default
        $editor_data['author_bio_show'] = 1;
        $editor_data['author_bio_html_tag'] = 'div';
        $editor_data['author_bio_hide_categories'] = [];
        $editor_data['author_bio_display_position'] = 'first';
        //meta default
        $editor_data['meta_show'] = 0;
        $editor_data['meta_email_show'] = 1;
        $editor_data['meta_site_link_show'] = 1;
        $editor_data['meta_html_tag'] = 'p';
        $editor_data['meta_background_color'] = '#655997';
        $editor_data['meta_color'] = '#ffffff';
        $editor_data['meta_link_hover_color'] = '#ffffff';
        //recent posts default
        $editor_data['author_recent_posts_title_show'] = 1;
        $editor_data['author_recent_posts_empty_show'] = 1;
        $editor_data['author_recent_posts_limit'] = 5;
        $editor_data['author_recent_posts_orderby'] = 'date';
        $editor_data['author_recent_posts_order'] = 'DESC';
        $editor_data['author_recent_posts_html_tag'] = 'div';
        $editor_data['author_recent_posts_title_border_bottom_style'] = 'dotted';
        $editor_data['author_recent_posts_alignment'] = 'left';
        //box layout default
        $editor_data['author_inline_display'] = 0;
        $editor_data['box_tab_layout_author_separator'] = '';
        $editor_data['box_layout_border_style'] = 'none';
        $editor_data['box_layout_shadow_horizontal_offset'] = 10;
        $editor_data['box_layout_shadow_vertical_offset'] = 10;
        $editor_data['box_layout_shadow_blur'] = 0;
        $editor_data['box_layout_shadow_speed'] = 0;
        $editor_data['box_layout_color'] = '#3c434a';
        //default css
        $editor_data['box_tab_custom_css'] = '.pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-boxed-categories .pp-author-boxes-avatar img {
            border-style: none !important;
            border-radius: 50% !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-boxed-categories {
            border: 1px solid #999 !important;
            padding: 25px !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-boxed-categories .pp-author-boxes-avatar {
              display: flex !important;
              gap: 10px !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-boxed-categories .pp-multiple-authors-boxes-ul.author-ul-0 .pp-author-boxes-avatar img {
              height: 80px !important;
              object-fit: cover;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-boxed-categories  .pp-multiple-authors-boxes-ul:not(.author-ul-0) .pp-author-boxes-avatar img {
              width: 40px !important;
              height: 40px !important;
              object-fit: cover;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-boxed-categories .ppma-author-category-wrap .ppma-category-group:not(.category-index-0) {
              flex: none !important;
              margin-right: 10px !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-boxed-categories .ppma-author-category-wrap .ppma-category-group:not(.category-index-0) {
              display: grid !important;
              margin-right: 10px !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-boxed-categories .ppma-category-group-other-wraps > ul:first-child {
            margin-bottom: 0 !important;
           }

           .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-boxed-categories .ppma-author-category-wrap .ppma-category-group:not(.category-index-0) ul {
               margin-top: 0 !important;
               margin-bottom: 0 !important;
           }

           .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-boxed-categories .ppma-author-category-wrap .ppma-category-group.category-index-0 {
                flex: 0 0 75%;
           }

           .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-boxed-categories .ppma-author-category-wrap {
                justify-content: space-between;
           }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-boxed-categories .pp-author-boxes-description {
              margin-top: 20px !important;
              padding-right: 15px;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-boxed-categories .pp-multiple-authors-boxes-ul.author-ul-0 .pp-author-boxes-name .ppma-category-group-title {
            margin-bottom: 2px !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-boxed-categories .pp-multiple-authors-boxes-ul:not(.author-ul-0) .pp-author-boxes-name .ppma-category-group-title {
            margin-bottom: 2px !important;
            margin-top: 0 !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-boxed-categories .pp-multiple-authors-boxes-ul {
            padding-left: 0 !important;
          }';

        //author category
        $editor_data['author_categories_group'] = 1;
        $editor_data['author_categories_layout'] = 'boxed_categories';
        $editor_data['author_categories_title_option'] = 'before_individual';
        $editor_data['author_categories_title_html_tag'] = 'p';
        $editor_data['author_categories_group_option'] = 'inline';
        $editor_data['author_categories_title_suffix'] = ': ';
        $editor_data['author_categories_group_display_style_laptop'] = 'flex';
        $editor_data['author_categories_group_display_style_mobile'] = 'flex';
        $editor_data['author_categories_right_space'] = '';
        // hide all author fields
        $profile_fields   = apply_filters('multiple_authors_author_fields', [], false);

        foreach ($profile_fields as $key => $data) {
            $editor_data['profile_fields_hide_' . $key] = 1;
        }

        $editor_data = \MultipleAuthorBoxes\AuthorBoxesDefault::addEditorDataDefaultValues($editor_data);

        return $editor_data;
    }



    /**
     * two columns categories editor data
     *
     * @return array
     */
    public static function getAuthorBoxesTwoColumnsCategoriesEditorData()
    {
        $editor_data = [];

        //title default
        $editor_data['show_title'] = 0;
        $editor_data['title_text'] = esc_html__('Author');
        $editor_data['title_text_plural'] = esc_html__('Authors', 'publishpress-authors-pro');
        $editor_data['title_html_tag'] = 'h1';
        $editor_data['box_tab_custom_wrapper_class'] = 'pp-multiple-authors-layout-two-columns-categories';
        //avatar default
        $editor_data['avatar_show'] = 1;
        $editor_data['avatar_size'] = 80;
        $editor_data['avatar_border_radius'] = 50;
        //name default
        $editor_data['name_show'] = 1;
        $editor_data['name_html_tag'] = 'span';
        $editor_data['display_name_position'] = 'infront_of_avatar';

        //bio default
        $editor_data['author_bio_show'] = 0;
        $editor_data['author_bio_html_tag'] = 'div';
        //meta default
        $editor_data['meta_show'] = 0;
        $editor_data['meta_email_show'] = 1;
        $editor_data['meta_site_link_show'] = 1;
        $editor_data['meta_html_tag'] = 'p';
        $editor_data['meta_background_color'] = '#655997';
        $editor_data['meta_color'] = '#ffffff';
        $editor_data['meta_link_hover_color'] = '#ffffff';
        //recent posts default
        $editor_data['author_recent_posts_title_show'] = 1;
        $editor_data['author_recent_posts_empty_show'] = 1;
        $editor_data['author_recent_posts_limit'] = 5;
        $editor_data['author_recent_posts_orderby'] = 'date';
        $editor_data['author_recent_posts_order'] = 'DESC';
        $editor_data['author_recent_posts_html_tag'] = 'div';
        $editor_data['author_recent_posts_title_border_bottom_style'] = 'dotted';
        $editor_data['author_recent_posts_alignment'] = 'left';
        //box layout default
        $editor_data['author_inline_display'] = 0;
        $editor_data['box_tab_layout_author_separator'] = '';
        $editor_data['box_layout_border_style'] = 'none';
        $editor_data['box_layout_shadow_horizontal_offset'] = 10;
        $editor_data['box_layout_shadow_vertical_offset'] = 10;
        $editor_data['box_layout_shadow_blur'] = 0;
        $editor_data['box_layout_shadow_speed'] = 0;
        $editor_data['box_layout_color'] = '#3c434a';
        //default css
        $editor_data['box_tab_custom_css'] = '.pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-two-columns-categories .pp-author-boxes-avatar img {
            border-style: none !important;
            border-radius: 50% !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-two-columns-categories {
            border: 1px solid #999 !important;
            padding: 25px !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-two-columns-categories .pp-author-boxes-avatar {
              display: flex !important;
              gap: 10px !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-two-columns-categories .pp-multiple-authors-boxes-ul.author-ul-0 .pp-author-boxes-avatar img {
              height: 80px !important;
              object-fit: cover;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-two-columns-categories  .pp-multiple-authors-boxes-ul:not(.author-ul-0) .pp-author-boxes-avatar img {
              width: 40px !important;
              height: 40px !important;
              object-fit: cover;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-two-columns-categories .ppma-author-category-wrap .ppma-category-group:not(.category-index-0) {
              display: grid !important;
              margin-right: 10px !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-two-columns-categories .pp-author-boxes-description {
              margin-top: 20px !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-two-columns-categories .pp-multiple-authors-boxes-ul:not(.author-ul-0) .pp-author-boxes-avatar .avatar-image {
              display: none !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-two-columns-categories .pp-multiple-authors-boxes-ul.author-ul-0 .pp-author-boxes-name .ppma-category-group-title {
            margin-bottom: 2px !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-two-columns-categories .pp-multiple-authors-boxes-ul:not(.author-ul-0) .pp-author-boxes-name .ppma-category-group-title {
            margin-bottom: 2px !important;
            margin-top: 0 !important;
            font-size: 13px !important;
            line-height: 1.5 !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-two-columns-categories .pp-multiple-authors-boxes-ul:not(.author-ul-0) .pp-author-boxes-name a.author {
            font-size: 13px !important;
            line-height: 1.5 !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-two-columns-categories .pp-multiple-authors-boxes-ul {
            padding-left: 0 !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-two-columns-categories .pp-multiple-authors-boxes-ul:not(.author-ul-0) .pp-multiple-authors-boxes-li .pp-author-boxes-name {
            display: flex !important;
            gap: 10px !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-two-columns-categories .pp-multiple-authors-boxes-ul.author-ul-0 .pp-multiple-authors-boxes-li .pp-author-boxes-name .ppma-category-group-title span.dashicons {
            display: none !important;
          }

          .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-two-columns-categories .ppma-category-group-other-wraps > ul:first-child {
            margin-bottom: 0 !important;
           }

           .pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-two-columns-categories .ppma-author-category-wrap .ppma-category-group:not(.category-index-0) ul {
               margin-top: 0 !important;
               margin-bottom: 0 !important;
           }
        ';

        //author category
        $editor_data['author_categories_group'] = 1;
        $editor_data['author_categories_layout'] = 'two_columns_categories';
        $editor_data['author_categories_title_option'] = 'before_individual';
        $editor_data['author_categories_title_html_tag'] = 'p';
        $editor_data['author_categories_group_option'] = 'inline';
        $editor_data['author_categories_title_prefix'] = '<span class="dashicons dashicons-yes-alt"></span> ';
        $editor_data['author_categories_title_suffix'] = ': ';
        $editor_data['author_categories_group_display_style_laptop'] = 'flex';
        $editor_data['author_categories_group_display_style_mobile'] = 'flex';
        $editor_data['author_categories_right_space'] = '';
        // hide all author fields
        $profile_fields   = apply_filters('multiple_authors_author_fields', [], false);

        foreach ($profile_fields as $key => $data) {
            $editor_data['profile_fields_hide_' . $key] = 1;
        }

        $editor_data = \MultipleAuthorBoxes\AuthorBoxesDefault::addEditorDataDefaultValues($editor_data);

        return $editor_data;
    }
}
