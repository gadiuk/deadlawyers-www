<?php
/**
 * @package PublishPress Authors
 * @author  PublishPress
 *
 * Copyright (C) 2018 PublishPress
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

namespace PPAuthorsPro;

use MA_Author_Boxes_Pro;
use MA_Author_Custom_Fields;
use MultipleAuthors\Classes\Objects\Author;

class Installer
{
    public function init()
    {
        add_action('admin_init', [$this, 'checkAndTriggerInstaller'], 2010);
    }

    public function checkAndTriggerInstaller()
    {
        $optionName = 'PP_AUTHORS_PRO_VERSION';

        $previousVersion = get_option($optionName);
        $currentVersion = PP_AUTHORS_PRO_VERSION;

        if (! apply_filters('publishpress_authors_pro_skip_installation', false, $previousVersion, $currentVersion)) {
            if (empty($previousVersion)) {
                $this->install($currentVersion);

                /**
                 * Action called when the plugin is installed.
                 *
                 * @param string $currentVersion
                 */
                do_action('publishpress_authors_pro_install', $currentVersion);
            } elseif (version_compare($previousVersion, $currentVersion, '>')) {
                /**
                 * Action called when the plugin is downgraded.
                 *
                 * @param string $previousVersion
                 */
                do_action('publishpress_authors_pro_downgrade', $previousVersion);
            } elseif (version_compare($previousVersion, $currentVersion, '<')) {
                $this->upgrade($previousVersion);

                /**
                 * Action called when the plugin is upgraded.
                 *
                 * @param string $previousVersion
                 */
                do_action('publishpress_authors_pro_upgrade', $previousVersion);
            }
        }

        if ($currentVersion !== $previousVersion) {
            update_option($optionName, $currentVersion, true);
        }
    }

    /**
     * Runs methods when the plugin is running for the first time.
     *
     * @param string $current_version
     */
    private function install($current_version)
    {
        self::create_default_custom_fields();
        self::createProDefaultAuthorBoxes();
        self::reorder_default_custom_fields();
    }

    /**
     * Runs methods when the plugin is updated.
     *
     * @param string $previous_version
     */
    private function upgrade($previous_version)
    {

        if (version_compare($previous_version, '2.4.0', '<=')) {
            self::add_post_custom_fields();
        }

        if (version_compare($previous_version, '4.0.0', '<=')) {
            self::create_default_custom_fields();
        }

        if (version_compare($previous_version, '4.4.1', '<=')) {
            self::createProDefaultAuthorBoxes();
            self::reorder_default_custom_fields();
        }
    }

    /**
     * Add custom field with authors' name on all posts.
     *
     * @since 2.4.0
     */
    private function add_post_custom_fields()
    {
        global $wpdb;

        // Get the authors
        $terms = $this->get_all_author_terms();
        $names = $this->get_terms_author_names($terms);

        // Get all different combinations of authors to make a cache and save connections to the db.
        $posts_author_names = static::get_post_author_names($names);

        // Update all posts.
        foreach ($posts_author_names as $post_id => $post_names) {
            $post_names = implode(', ', $post_names);

            update_post_meta($post_id, 'ppma_authors_name', $post_names);
        }
    }

    /**
     * Return a list with al the author terms.
     *
     * @return array
     *
     * @since 2.4.0
     */
    private function get_all_author_terms()
    {
        global $wpdb;
        // Get list of authors with mapped users.
        $authors = $wpdb->get_results(
            "SELECT taxonomy.term_id
                   FROM {$wpdb->term_taxonomy} AS taxonomy
                   WHERE taxonomy.`taxonomy` = 'author'"
        );
        return $authors;
    }

    /**
     * Map a list of author terms to a list of author names indexed by the term id.
     *
     * @param array $authors
     *
     * @return array
     *
     * @since 2.4.0
     */
    private function get_terms_author_names($authors)
    {
        if (empty($authors)) {
            return;
        }
        $mappedList = [];
        foreach ($authors as $term) {
            $author = Author::get_by_term_id($term->term_id);
            $mappedList[$term->term_id] = $author->name;
        }
        return $mappedList;
    }

    /**
     * @param array $author_names
     *
     * @return array
     *
     * @since 2.4.0
     */
    private function get_post_author_names($author_names)
    {
        $term_ids = array_keys($author_names);
        $combination_names = [];
        $combinations = $this->get_taxonomy_ids_combinations($term_ids);
        foreach ($combinations as $combination_str) {
            $combination_list = explode(',', $combination_str->taxonomy_ids);
            $names = array_map(
                function ($id) use ($author_names) {
                    return $author_names[$id];
                },
                $combination_list
            );
            $combination_names[$combination_str->object_id] = $names;
        }
        return $combination_names;
    }

    /**
     *
     * @param array $term_ids
     *
     * @return mixed
     *
     * @since 2.4.0
     */
    private function get_taxonomy_ids_combinations($term_ids)
    {
        global $wpdb;
        $term_ids = array_map('esc_sql', $term_ids);
        $term_ids = implode(',', $term_ids);

        $ids = $wpdb->get_results( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT object_id, group_concat(r.term_taxonomy_id) as taxonomy_ids
                FROM {$wpdb->term_relationships} AS r
                WHERE r.term_taxonomy_id in ({$term_ids})
                GROUP BY r.object_id
                ORDER BY r.term_order"
        );
        return $ids;
    }

    /**
     * Create default custom fields.
     *
     * @param string $name
     * @param string $title
     */
    protected static function create_custom_fields_post($name, $data)
    {
        // Check if we already have the layout based on the slug.
        $existingCustomField = get_page_by_title($data['post_title'], OBJECT, MA_Author_Custom_Fields::POST_TYPE_CUSTOM_FIELDS);
        if ($existingCustomField) {
            return;
        }

        $post_id = wp_insert_post(
            [
                'post_type' => MA_Author_Custom_Fields::POST_TYPE_CUSTOM_FIELDS,
                'post_title' => $data['post_title'],
                'post_content' => $data['post_title'],
                'post_status' => 'publish',
                'post_name' => $data['post_name'],
            ]
        );
        update_post_meta($post_id, MA_Author_Custom_Fields::META_PREFIX . 'slug', $data['post_name']);
        update_post_meta($post_id, MA_Author_Custom_Fields::META_PREFIX . 'type', $data['type']);
        update_post_meta($post_id, MA_Author_Custom_Fields::META_PREFIX . 'field_status', $data['field_status']);
        update_post_meta($post_id, MA_Author_Custom_Fields::META_PREFIX . 'description', $data['description']);
        update_post_meta($post_id, MA_Author_Custom_Fields::META_PREFIX . 'social_profile', $data['social_profile']);
        update_post_meta($post_id, MA_Author_Custom_Fields::META_PREFIX . 'social', 1);
    }

    /**
     * Create default custom fields.
     */
    public static function create_default_custom_fields()
    {
        $defaultCustomFields = array_reverse(self::get_pro_social_custom_fields());

        foreach ($defaultCustomFields as $name => $data) {
            self::create_custom_fields_post($name, $data);
            sleep(2);
        }
    }

    /**
     * reorder default custom fields.
     */
    public static function reorder_default_custom_fields()
    {
        $posts = get_posts(
            [
                'post_type' => MA_Author_Custom_Fields::POST_TYPE_CUSTOM_FIELDS,
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby' => 'menu_order',
                'order' => 'ASC',
            ]
        );

        if (! empty($posts)) {
            $position = 0;
            $social_fields = [];
            foreach ($posts as $post) {
                $social_profile = get_post_meta($post->ID, 'ppmacf_social_profile', true);
                if ((int) $social_profile === 1) {
                    $social_fields[] = $post;
                } else {
                    wp_update_post(array(
                        'ID' => $post->ID,
                        'menu_order' => $position,
                    ));
                    $position++;
                }
            }
            if (!empty($social_fields)) {
                foreach ($social_fields as $social_field) {
                    wp_update_post(array(
                        'ID' => $social_field->ID,
                        'menu_order' => $position,
                    ));
                    $position++;
                }
            }

        }
    }

    /**
     * Get pro social fields
     *
     * @return arary $social_custom_fields
     */
    private static function get_pro_social_custom_fields() 
    {
        $social_custom_fields = [];
        //add Facebook
        $social_custom_fields['facebook'] = [
            'post_title'   => __('Facebook', 'publishpress-authors-pro'),
            'post_name'    => 'facebook',
            'type'         => 'url',
            'social_profile'  => 1,
            'field_status'  => 'off',
            'description'  => __('Please enter the full URL to your Facebook profile.', 'publishpress-authors-pro'),
        ];
        //add X
        $social_custom_fields['x'] = [
            'post_title'   => __('X', 'publishpress-authors-pro'),
            'post_name'    => 'x',
            'type'         => 'url',
            'social_profile'  => 1,
            'field_status'  => 'off',
            'description'  => __('Please enter the full URL to your X profile.', 'publishpress-authors-pro'),
        ];
        //add Instagram
        $social_custom_fields['instagram'] = [
            'post_title'   => __('Instagram', 'publishpress-authors-pro'),
            'post_name'    => 'instagram',
            'type'         => 'url',
            'social_profile'  => 1,
            'field_status'  => 'off',
            'description'  => __('Please enter the full URL to your Instagram page.', 'publishpress-authors-pro'),
        ];
        //add LinkedIn
        $social_custom_fields['linkedIn'] = [
            'post_title'   => __('LinkedIn', 'publishpress-authors-pro'),
            'post_name'    => 'linkedIn',
            'type'         => 'url',
            'social_profile'  => 1,
            'field_status'  => 'off',
            'description'  => __('Please enter the full URL to your LinkedIn profile.', 'publishpress-authors-pro'),
        ];
        //add YouTube
        $social_custom_fields['youtube'] = [
            'post_title'   => __('YouTube', 'publishpress-authors-pro'),
            'post_name'    => 'youtube',
            'type'         => 'url',
            'social_profile'  => 1,
            'field_status'  => 'off',
            'description'  => __('Please enter the full URL to your YouTube channel.', 'publishpress-authors-pro'),
        ];
        //add TikTok
        $social_custom_fields['tiktok'] = [
            'post_title'   => __('TikTok', 'publishpress-authors-pro'),
            'post_name'    => 'tiktok',
            'type'         => 'url',
            'social_profile'  => 1,
            'field_status'  => 'off',
            'description'  => __('Please enter the full URL to your TikTok profile.', 'publishpress-authors-pro'),
        ];

        return $social_custom_fields;
    }

    public static function createProDefaultAuthorBoxes() {
        MA_Author_Boxes_Pro::createDefaultAuthorBoxes();
    }
}
