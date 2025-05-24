<?php
/**
 * Plugin Name: LF Team Profiles
 * Plugin URI: https://github.com/yourusername/lf-team-profiles
 * Description: Display team members with ACF, department filtering, and native HTML popovers
 * Version: 2.1.0
 * Author: Luis Alvarez
 * License: GPL v2 or later
 * Text Domain: lf-team-profiles
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LF_TEAM_PROFILES_VERSION', '2.1.0');
define('LF_TEAM_PROFILES_URL', plugin_dir_url(__FILE__));
define('LF_TEAM_PROFILES_PATH', plugin_dir_path(__FILE__));

/**
 * Main plugin class
 */
class LF_Team_Profiles {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_shortcode('team_profiles', array($this, 'render_shortcode'));
        
        // Enqueue assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        
        // Register Gutenberg block
        add_action('init', array($this, 'register_block'));
    }
    
    public function init() {
        $this->register_post_type();
        $this->register_taxonomy();
        $this->register_acf_fields();
    }
    
    private function register_post_type() {
        $labels = array(
            'name'                  => __('Team', 'lf-team-profiles'),
            'singular_name'         => __('Team Member', 'lf-team-profiles'),
            'menu_name'             => __('Team Profiles', 'lf-team-profiles'),
            'all_items'             => __('All Team Members', 'lf-team-profiles'),
            'add_new_item'          => __('Add New Team Member', 'lf-team-profiles'),
            'add_new'               => __('Add New', 'lf-team-profiles'),
            'edit_item'             => __('Edit Team Member', 'lf-team-profiles'),
            'update_item'           => __('Update Team Member', 'lf-team-profiles'),
            'search_items'          => __('Search Team', 'lf-team-profiles'),
            'not_found'             => __('No team members found', 'lf-team-profiles'),
        );
        
        register_post_type('team_member', array(
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 25,
            'menu_icon'           => 'dashicons-groups',
            'supports'            => array('title', 'thumbnail', 'page-attributes'),
            'has_archive'         => false,
            'publicly_queryable'  => false,
            'capability_type'     => 'post',
            'show_in_rest'        => true, // Enable for Gutenberg
        ));
    }
    
    private function register_taxonomy() {
        $labels = array(
            'name'              => __('Departments', 'lf-team-profiles'),
            'singular_name'     => __('Department', 'lf-team-profiles'),
            'menu_name'         => __('Departments', 'lf-team-profiles'),
            'all_items'         => __('All Departments', 'lf-team-profiles'),
            'add_new_item'      => __('Add New Department', 'lf-team-profiles'),
            'edit_item'         => __('Edit Department', 'lf-team-profiles'),
            'update_item'       => __('Update Department', 'lf-team-profiles'),
            'search_items'      => __('Search Departments', 'lf-team-profiles'),
        );
        
        register_taxonomy('team_department', array('team_member'), array(
            'labels'            => $labels,
            'hierarchical'      => true,
            'public'            => false,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => false,
            'rewrite'           => false,
            'show_in_rest'      => true, // Enable for Gutenberg
        ));
    }
    
    private function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }
        
        acf_add_local_field_group(array(
            'key' => 'group_team_member_details',
            'title' => 'Team Member Details',
            'fields' => array(
                array(
                    'key' => 'field_team_priority',
                    'label' => 'Priority',
                    'name' => 'team_priority',
                    'type' => 'number',
                    'instructions' => 'Lower numbers appear first. Default is 0.',
                    'default_value' => 0,
                    'min' => '',
                    'max' => '',
                    'step' => 1,
                ),
                array(
                    'key' => 'field_team_photo',
                    'label' => 'Photo',
                    'name' => 'team_photo',
                    'type' => 'image',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                ),
                array(
                    'key' => 'field_team_job_title',
                    'label' => 'Job Title',
                    'name' => 'team_job_title',
                    'type' => 'text',
                    'placeholder' => 'e.g. Country Coordinator',
                ),
                array(
                    'key' => 'field_team_team',
                    'label' => 'Team',
                    'name' => 'team_team',
                    'type' => 'text',
                    'placeholder' => 'e.g. Team Colombia, Team Corporate, etc.',
                ),
                array(
                    'key' => 'field_team_bio',
                    'label' => 'Biography',
                    'name' => 'team_bio',
                    'type' => 'wysiwyg',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 0,
                ),
                array(
                    'key' => 'field_team_linkedin',
                    'label' => 'LinkedIn Profile URL',
                    'name' => 'team_linkedin',
                    'type' => 'url',
                    'placeholder' => 'https://www.linkedin.com/in/username',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'team_member',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
        ));
    }
    
    public function enqueue_frontend_assets() {
        if (is_admin()) {
            return;
        }
        
        // Check if we're on a page that might use the shortcode or block
        global $post;
        if (!$post) {
            return;
        }
        
        $content = $post->post_content;
        $has_shortcode = has_shortcode($content, 'team_profiles');
        $has_block = has_block('lf-team-profiles/team-profiles', $post);
        
        if ($has_shortcode || $has_block) {
            wp_enqueue_style(
                'lf-team-profiles',
                LF_TEAM_PROFILES_URL . 'assets/css/lf-team-profiles.min.css',
                array(),
                LF_TEAM_PROFILES_VERSION
            );
            
            wp_enqueue_script(
                'lf-team-profiles',
                LF_TEAM_PROFILES_URL . 'assets/js/lf-team-profiles.min.js',
                array(),
                LF_TEAM_PROFILES_VERSION,
                true
            );
        }
    }
    
    public function enqueue_block_editor_assets() {
        wp_enqueue_script(
            'lf-team-profiles-block',
            LF_TEAM_PROFILES_URL . 'blocks/team-profiles-block.js',
            array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'),
            LF_TEAM_PROFILES_VERSION
        );
        
        // Enqueue CSS in editor
        wp_enqueue_style(
            'lf-team-profiles',
            LF_TEAM_PROFILES_URL . 'assets/css/lf-team-profiles.min.css',
            array(),
            LF_TEAM_PROFILES_VERSION
        );
    }
    
    public function register_block() {
        register_block_type('lf-team-profiles/team-profiles', array(
            'render_callback' => array($this, 'render_block'),
            'attributes' => array(
                'department' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'columns' => array(
                    'type' => 'string',
                    'default' => '4',
                ),
                'orderby' => array(
                    'type' => 'string',
                    'default' => 'menu_order',
                ),
                'order' => array(
                    'type' => 'string',
                    'default' => 'ASC',
                ),
            ),
        ));
    }
    
    public function render_block($attributes) {
        return $this->render_shortcode($attributes);
    }
    
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'department' => '',
            'columns' => '4',
            'orderby' => 'menu_order',
            'order' => 'ASC',
        ), $atts);
        
        $args = array(
            'post_type' => 'team_member',
            'posts_per_page' => -1,
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        );
        
        // Handle priority ordering
        if ($atts['orderby'] === 'meta_value_num') {
            $args['meta_key'] = 'team_priority';
            $args['orderby'] = array(
                'meta_value_num' => $atts['order'],
                'title' => 'ASC'
            );
        }
        
        if (!empty($atts['department'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'team_department',
                    'field' => 'slug',
                    'terms' => explode(',', $atts['department']),
                ),
            );
        }
        
        $query = new WP_Query($args);
        
        if (!$query->have_posts()) {
            return '<p>' . __('No team members found.', 'lf-team-profiles') . '</p>';
        }
        
        $output = '<div class="lf-team-profiles-grid" data-columns="' . esc_attr($atts['columns']) . '">';
        
        while ($query->have_posts()) {
            $query->the_post();
            
            $member_id = get_the_ID();
            $name = get_the_title();
            $photo = get_field('team_photo');
            $job_title = get_field('team_job_title');
            $team = get_field('team_team');
            $bio = get_field('team_bio');
            $linkedin = get_field('team_linkedin');
            
            // Get photo URL
            $photo_url = '';
            if ($photo) {
                $photo_url = isset($photo['sizes']['medium']) ? $photo['sizes']['medium'] : $photo['url'];
            } else {
                // Default placeholder image
                $photo_url = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgZmlsbD0iI2VlZSIvPjx0ZXh0IHRleHQtYW5jaG9yPSJtaWRkbGUiIHg9IjE1MCIgeT0iMTUwIiBzdHlsZT0iZmlsbDojYWFhO2ZvbnQtd2VpZ2h0OmJvbGQ7Zm9udC1zaXplOjE5cHg7Zm9udC1mYW1pbHk6QXJpYWwsSGVsdmV0aWNhLHNhbnMtc2VyaWY7ZG9taW5hbnQtYmFzZWxpbmU6Y2VudHJhbCI+Tm8gUGhvdG88L3RleHQ+PC9zdmc+';
            }
            
            // Team member card
            $output .= '<div class="lf-team-member">';
            $output .= '<button class="lf-team-member-button" popovertarget="team-popover-' . $member_id . '">';
            $output .= '<div class="lf-team-photo-wrapper">';
            $output .= '<img src="' . esc_url($photo_url) . '" alt="' . esc_attr($name) . '" class="lf-team-photo">';
            $output .= '</div>';
            $output .= '<div class="lf-team-name-wrapper">';
            $output .= '<h3 class="lf-team-name">' . esc_html($name);
            if ($linkedin) {
                $output .= ' <a href="' . esc_url($linkedin) . '" target="_blank" rel="noopener noreferrer nofollow" class="lf-team-linkedin-icon" onclick="event.stopPropagation();">';
                $output .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>';
                $output .= '</a>';
            }
            $output .= '</h3>';
            if ($job_title) {
                $output .= '<p class="lf-team-job-title">' . esc_html($job_title) . '</p>';
            }

            if ($team) {
                $output .= '<p class="lf-team-team">' . esc_html($team) . '</p>';
            }
            $output .= '</div>';
            $output .= '</button>';
            $output .= '</div>';
            
            // Popover modal content
            $output .= '<div id="team-popover-' . $member_id . '" popover class="lf-team-popover">';
            $output .= '<div class="lf-team-popover-inner">';
            $output .= '<button class="lf-team-popover-close" popovertarget="team-popover-' . $member_id . '" popovertargetaction="hide">&times;</button>';
            $output .= '<img src="' . esc_url($photo_url) . '" alt="' . esc_attr($name) . '" class="lf-team-popover-photo">';
            $output .= '<h3>' . esc_html($name) . '</h3>';
            if ($job_title) {
                $output .= '<p class="lf-team-popover-job-title">' . esc_html($job_title) . '</p>';
            }

            if ($team) {
                $output .= '<p class="lf-team-popover-team">' . esc_html($team) . '</p>';
            }
            
            if ($bio) {
                $output .= '<div class="lf-team-bio">' . wp_kses_post($bio) . '</div>';
            }
            
            if ($linkedin) {
                $output .= '<a href="' . esc_url($linkedin) . '" target="_blank" rel="noopener noreferrer nofollow" class="lf-team-linkedin">';
                $output .= '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>';
                $output .= ' ' . sprintf(__('Connect with %s on LinkedIn', 'lf-team-profiles'), esc_html($name));
                $output .= '</a>';
            }
            
            $output .= '</div>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        wp_reset_postdata();
        
        return $output;
    }
}

// Initialize plugin
LF_Team_Profiles::get_instance();

// Plugin activation/deactivation hooks
register_activation_hook(__FILE__, function() {
    LF_Team_Profiles::get_instance()->init();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

/**
 * USAGE:
 * 
 * 1. SHORTCODE: [team_profiles]
 * 
 * Parameters:
 * - department: Filter by department slug(s), comma-separated
 * - columns: Number of columns (2-6, default: 4)
 * - orderby: Order by field (title, date, menu_order, meta_value_num for priority)
 * - order: ASC or DESC
 * 
 * Examples:
 * [team_profiles]
 * [team_profiles department="marketing"]
 * [team_profiles department="marketing,sales" columns="3"]
 * [team_profiles orderby="meta_value_num" order="ASC"] // Sort by priority
 * 
 * 2. GUTENBERG BLOCK: Team Profiles
 * 
 * Available in the block editor under Widgets category.
 * Configure department, columns, and sorting options in the block settings.
 * 
 * 3. PRIORITY SORTING:
 * 
 * Each team member has a "Priority" field (integer).
 * Lower numbers appear first when sorting by priority.
 * Use orderby="meta_value_num" to sort by priority.
 */
