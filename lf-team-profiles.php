<?php
/**
 * Plugin Name: LF Team Profiles
 * Plugin URI: https://github.com/yourusername/lf-team-profiles
 * Description: Display team members with ACF, department filtering, and native HTML popovers
 * Version: 2.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: lf-team-profiles
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LF_TEAM_PROFILES_VERSION', '2.0.0');
define('LF_TEAM_PROFILES_URL', plugin_dir_url(__FILE__));
define('LF_TEAM_PROFILES_PATH', plugin_dir_path(__FILE__));

/**
 * Main plugin class
 */
class LF_Team_Profiles {
    
    private static $instance = null;
    private $shortcode_used = false;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_shortcode('team_profiles', array($this, 'render_shortcode'));
        
        // Check if shortcode is used before rendering
        add_filter('the_content', array($this, 'check_for_shortcode'), 5);
        add_action('wp_footer', array($this, 'maybe_print_inline_assets'));
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
            'supports'            => array('title', 'thumbnail'),
            'has_archive'         => false,
            'publicly_queryable'  => false,
            'capability_type'     => 'post',
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
    
    public function check_for_shortcode($content) {
        if (has_shortcode($content, 'team_profiles')) {
            $this->shortcode_used = true;
        }
        return $content;
    }
    
    public function render_shortcode($atts) {
        $this->shortcode_used = true;
        
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
            $output .= '<h3 class="lf-team-name">' . esc_html($name) . '</h3>';
            if ($job_title) {
                $output .= '<p class="lf-team-job-title">' . esc_html($job_title) . '</p>';
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
            
            if ($bio) {
                $output .= '<div class="lf-team-bio">' . wp_kses_post($bio) . '</div>';
            }
            
            if ($linkedin) {
                $output .= '<a href="' . esc_url($linkedin) . '" target="_blank" rel="noopener noreferrer nofollow" class="lf-team-linkedin">';
                $output .= '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>';
                $output .= ' Connect with '. esc_html($name) . ' on LinkedIn';
                $output .= '</a>';
            }
            
            $output .= '</div>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        wp_reset_postdata();
        
        return $output;
    }
    
    public function maybe_print_inline_assets() {
        if (!$this->shortcode_used) {
            return;
        }
        
        ?>
        <style>
        .lf-team-profiles-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin: 40px 0;
        }
        .lf-team-profiles-grid[data-columns="2"] { grid-template-columns: repeat(2, 1fr); }
        .lf-team-profiles-grid[data-columns="3"] { grid-template-columns: repeat(3, 1fr); }
        .lf-team-profiles-grid[data-columns="5"] { grid-template-columns: repeat(5, 1fr); }
        .lf-team-profiles-grid[data-columns="6"] { grid-template-columns: repeat(6, 1fr); }
        @media (max-width: 768px) {
            .lf-team-profiles-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
        }
        @media (max-width: 480px) {
            .lf-team-profiles-grid { grid-template-columns: 1fr; }
        }
        .lf-team-member {
            text-align: center;
        }
        .lf-team-member-button {
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            width: 100%;
            transition: transform 0.3s ease;
        }
        .lf-team-member-button:hover { 
            transform: translateY(-5px); 
        }
        .lf-team-photo-wrapper {
            position: relative;
            overflow: hidden;
            border-radius: 50%;
            margin: 0 auto 15px;
            width: 150px;
            height: 150px;
            background: #f0f0f0;
        }
        .lf-team-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .lf-team-member-button:hover .lf-team-photo { 
            transform: scale(1.1); 
        }
        .lf-team-name-wrapper {
            margin-top: 10px;
        }
        .lf-team-name {
            font-size: 18px;
            margin: 0 0 5px 0;
            color: #333;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .lf-team-linkedin-icon {
            display: inline-flex;
            align-items: center;
            color: #0077b5;
            transition: color 0.3s ease;
        }
        .lf-team-linkedin-icon:hover {
            color: #005885;
        }
        .lf-team-job-title {
            font-size: 14px;
            color: #666;
            margin: 0;
            font-weight: normal;
        }
        
        /* Popover styles */
        .lf-team-popover {
            position: fixed;
            inset: 0;
            width: fit-content;
            height: fit-content;
            margin: auto;
            border: none;
            padding: 0;
            overflow: visible;
            background: transparent;
            max-width: 90vw;
            max-height: 90vh;
        }
        
        .lf-team-popover::backdrop {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(3px);
        }
        
        .lf-team-popover-inner {
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 30px;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
            text-align: center;
        }
        
        .lf-team-popover-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
        }
        .lf-team-popover-job-title {
            font-size: 16px;
            color: #666;
            margin: -10px 0 20px 0;
            font-weight: normal;
        }
        .lf-team-bio {
            text-align: left;
            margin: 20px 0;
            line-height: 1.6;
        }
        .lf-team-linkedin {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #0077b5;
            text-decoration: none;
            font-weight: 500;
            margin-top: 20px;
            transition: color 0.3s ease;
        }
        .lf-team-linkedin:hover { 
            color: #005885; 
        }
        .lf-team-popover-close {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 30px;
            height: 30px;
            background: #f0f0f0;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            line-height: 1;
            transition: background 0.2s ease;
        }
        .lf-team-popover-close:hover { 
            background: #e0e0e0; 
        }
        
        /* Popover animation */
        .lf-team-popover {
            animation: fadeIn 0.2s ease-out;
        }
        
        .lf-team-popover:popover-open {
            display: block;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* Fallback for browsers without popover support */
        @supports not (selector(:popover-open)) {
            .lf-team-popover {
                display: none;
            }
        }
        </style>
        
        <script>
        // Minimal JavaScript for browsers that don't support popover API
        (function() {
            // Check if popover API is supported
            if (!HTMLElement.prototype.hasOwnProperty('popover')) {
                console.log('Popover API not supported, implementing fallback');
                
                document.addEventListener('DOMContentLoaded', function() {
                    // Simple fallback implementation
                    var buttons = document.querySelectorAll('[popovertarget]');
                    
                    buttons.forEach(function(button) {
                        button.addEventListener('click', function(e) {
                            e.preventDefault();
                            var targetId = button.getAttribute('popovertarget');
                            var popover = document.getElementById(targetId);
                            
                            if (popover) {
                                // Toggle visibility
                                if (popover.style.display === 'block') {
                                    popover.style.display = 'none';
                                } else {
                                    // Hide all other popovers
                                    document.querySelectorAll('.lf-team-popover').forEach(function(p) {
                                        p.style.display = 'none';
                                    });
                                    popover.style.display = 'block';
                                }
                            }
                        });
                    });
                    
                    // Close on backdrop click
                    document.addEventListener('click', function(e) {
                        if (e.target.classList.contains('lf-team-popover')) {
                            e.target.style.display = 'none';
                        }
                    });
                    
                    // Close on escape key
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape') {
                            document.querySelectorAll('.lf-team-popover').forEach(function(p) {
                                p.style.display = 'none';
                            });
                        }
                    });
                });
            }
        })();
        </script>
        <?php
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
 * Shortcode: [team_profiles]
 * 
 * Parameters:
 * - department: Filter by department slug(s)
 * - columns: Number of columns (2-6, default: 4)
 * - orderby: Order by field (title, date, menu_order)
 * - order: ASC or DESC
 * 
 * Examples:
 * [team_profiles]
 * [team_profiles department="marketing"]
 * [team_profiles department="marketing,sales" columns="3"]
 */
