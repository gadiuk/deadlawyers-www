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
 * Class MA_Author_List_Pro
 *
 * This class adds a review request system for your plugin or theme to the WP dashboard.
 */
class MA_Author_List_Pro extends Module
{

    public $module_name = 'author_list_pro';

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
            'title' => __('Author List Pro', 'publishpress-authors-pro'),
            'short_description' => false,
            'extended_description' => false,
            'module_url' => $this->module_url,
            'icon_class' => 'dashicons dashicons-feedback',
            'slug' => 'author-list-pro',
            'default_options' => [
                'enabled' => 'on',
            ],
            'general_options' => false,
            'autoload' => true,
        ];

        // Apply a filter to the default options
        $args['default_options'] = apply_filters(
            'ppma_author_list_pro_default_options',
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
        add_filter('authors_lists_editor_fields', [$this, 'authorListFields'], 10, 2);
    }

    /**
     * Filter author list fields
     *
     * @param array $fields
     * @param array $author_field_options
     * 
     * @return array
     */
    public function authorListFields($fields, $author_field_options)
    {

        // add options fields
        $fields['limit_per_page'] = [
            'label'             => esc_html__('Authors Per Page', 'publishpress-authors-pro'),
            'description'       => esc_html__('You can set the number of authors to show per page.', 'publishpress-authors-pro'),
            'type'              => 'number',
            'min'               => 1,
            'max'               => 9999,
            'sanitize'          => 'sanitize_text_field',
            'field_visibility'  => [],
            'tab'               => 'options',
        ];
        $fields['show_empty']   = [
            'label'             => esc_html__('Show Empty', 'publishpress-authors-pro'),
            'description'       => esc_html__('Enable this option to show all authors, including those without any posts. Disable this option to show only authors who are assigned to posts.', 'publishpress-authors-pro'),
            'type'              => 'checkbox',
            'sanitize'          => 'absint',
            'field_visibility'  => [],
            'tab'               => 'options',
        ];
        $fields['orderby']   = [
            'label'             => esc_html__('Order By', 'publishpress-authors-pro'),
            'description'       => '',
            'type'              => 'select',
            'options'           => [
                'name'          => esc_html__('Name', 'publishpress-authors-pro'),
                'count'         => esc_html__('Post Counts', 'publishpress-authors-pro'),
                'first_name'    => esc_html__('First Name', 'publishpress-authors-pro'),
                'last_name'     => esc_html__('Last Name', 'publishpress-authors-pro')
            ],
            'sanitize'          => 'sanitize_text_field',
            'field_visibility'  => [],
            'tab'               => 'options',
        ];
        $fields['order']   = [
            'label'             => esc_html__('Order', 'publishpress-authors-pro'),
            'description'       => '',
            'type'              => 'select',
            'options'           => [
                'asc'   => esc_html__('Ascending', 'publishpress-authors-pro'),
                'desc'  => esc_html__('Descending', 'publishpress-authors-pro')
            ],
            'sanitize'          => 'sanitize_text_field',
            'field_visibility'  => [],
            'tab'               => 'options',
        ];
        $fields['last_article_date']   = [
            'label'             => esc_html__('Last Article Date', 'publishpress-authors-pro'),
            'description'       => esc_html__('You can limit the author list to users with a published post within a specific time. This option accepts date values such as 1 week ago, 1 month ago, 6 months ago, 1 year ago etc.', 'publishpress-authors-pro'),
            'type'              => 'text',
            'sanitize'          => 'sanitize_text_field',
            'field_visibility'  => [],
            'tab'               => 'options',
        ];
        
        // add search fields
        $fields['search_box']   = [
            'label'             => esc_html__('Show Search Box', 'publishpress-authors-pro'),
            'description'       => '',
            'type'              => 'checkbox',
            'sanitize'          => 'absint',
            'field_visibility'  => [],
            'tab'               => 'search',
        ];
        $fields['search_field']   = [
            'label'             => esc_html__('Search Field Dropdown', 'publishpress-authors-pro'),
            'description'       => esc_html__('You can also show a dropdown menu that allows users to search on specific author fields.', 'publishpress-authors-pro'),
            'type'              => 'select',
            'multiple'          => true,
            'options'           => $author_field_options,
            'sanitize'          => 'sanitize_text_field',
            'field_visibility'  => [],
            'tab'               => 'search',
        ];

        return $fields;
    }
}
