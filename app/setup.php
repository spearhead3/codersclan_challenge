<?php

namespace App;

use Roots\Sage\Assets\JsonManifest;
use Roots\Sage\Container;
use Roots\Sage\Template\Blade;
use Roots\Sage\Template\BladeProvider;

/**
 * Theme version string.
 * Tries to read a "version.json" file in the "dist" folder - a JSON file with a "version" property.
 * If this file is not available it returns the WordPress version.
 *
 * @return string
 */
function theme_version()
{
    $version = get_bloginfo('version');
    $version_json_path = get_template_directory() . '/../dist/version.json';

    if (file_exists($version_json_path)) {
        $version_json = file_get_contents($version_json_path);

        if (!empty($version_json)) {
            $version_json_data = json_decode($version_json);

            if (!empty($version_json_data)) {
                if (!empty($version_json_data->version)) {
                    $version = $version_json_data->version;
                }
            }
        }
    }

    return $version;
}

/**
 * Theme assets
 */
add_action('wp_enqueue_scripts', function () {
    $version = theme_version();
    wp_enqueue_style('font-awesome', asset_path('static/font-awesome.min.css'), false, $version);
    wp_enqueue_style('sage/main.css', asset_path('styles/main.css'), false, $version);
    wp_enqueue_script('sage/main.js', asset_path('scripts/main.js'), ['jquery'], $version, true);

    if (is_single() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}, 100);

/**
 * Theme setup
 */
add_action('after_setup_theme', function () {
    /**
     * Enable features from Soil when plugin is activated
     * @link https://roots.io/plugins/soil/
     */
    add_theme_support('soil-clean-up');
    add_theme_support('soil-jquery-cdn');
    add_theme_support('soil-nav-walker');
    add_theme_support('soil-nice-search');
    add_theme_support('soil-relative-urls');

    /**
     * Enable plugins to manage the document title
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Register navigation menus
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
    ]);

    /**
     * Enable post thumbnails
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable HTML5 markup support
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', ['caption', 'comment-form', 'comment-list', 'gallery', 'search-form']);

    /**
     * Enable selective refresh for widgets in customizer
     * @link https://developer.wordpress.org/themes/advanced-topics/customizer-api/#theme-support-in-sidebars
     */
    add_theme_support('customize-selective-refresh-widgets');

    /**
     * Use main stylesheet for visual editor
     * @see resources/assets/styles/layouts/_tinymce.scss
     */
    add_editor_style(asset_path('styles/main.css'));
}, 20);

/**
 * Register sidebars
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];
    register_sidebar([
        'name' => __('Primary', 'sage'),
        'id' => 'sidebar-primary',
    ] + $config);
    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id' => 'sidebar-footer',
    ] + $config);
});

/**
 * Updates the `$post` variable on each iteration of the loop.
 * Note: updated value is only available for subsequently loaded views, such as partials
 */
add_action('the_post', function ($post) {
    sage('blade')->share('post', $post);
});

/**
 * Setup Sage options
 */
add_action('after_setup_theme', function () {
    /**
     * Add JsonManifest to Sage container
     */
    sage()->singleton('sage.assets', function () {
        return new JsonManifest(config('assets.manifest'), config('assets.uri'));
    });

    /**
     * Add Blade to Sage container
     */
    sage()->singleton('sage.blade', function (Container $app) {
        $cachePath = config('view.compiled');
        if (!file_exists($cachePath)) {
            wp_mkdir_p($cachePath);
        }
        (new BladeProvider($app))->register();
        return new Blade($app['view']);
    });

    /**
     * Create @asset() Blade directive
     */
    sage('blade')->compiler()->directive('asset', function ($asset) {
        return "<?= " . __NAMESPACE__ . "\\asset_path({$asset}); ?>";
    });
});

/**
 * Allow SVG uploads.
 */
add_filter('upload_mimes', function ($mimes) {
    $mimes['svg'] = 'image/svg+xml';

    return $mimes;
});

/**
 * Add option pages.
 */
add_action('acf/init', function () {
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page(array(
            'page_title' => __('CodersClan
             Theme Settings', 'CodersClan'),
            'menu_title' => __('CodersClan Settings', 'codersclan'),
            'menu_slug' => 'codersclan-settings',
        ));

        acf_add_options_sub_page(array(
            'page_title' => __('General Settings', 'codersclan'),
            'menu_title' => __('General', 'codersclan'),
            'parent_slug' => 'codersclan-settings',
        ));
    }
});

/**
 * Only show Custom Fields admin page on ".local" address.
 */
add_filter('acf/settings/show_admin', function () {
    $site_url = get_bloginfo('url');

    if (string_ends_with($site_url, '.local')) {
        return true;
    } else {
        return false;
    }
});



function create_posttype() {
 
    // Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'NPosts', 'Post Type General Name'),
        'singular_name'       => _x( 'NPost', 'Post Type Singular Name'),
        'menu_name'           => __( 'NPosts'),
        'parent_item_colon'   => __( 'Parent NPost'),
        'all_items'           => __( 'All NPosts'),
        'view_item'           => __( 'View NPost'),
        'add_new_item'        => __( 'Add New NPost'),
        'add_new'             => __( 'Add New'),
        'edit_item'           => __( 'Edit NPost'),
        'update_item'         => __( 'Update NPost'),
        'search_items'        => __( 'Search NPost'),
        'not_found'           => __( 'Not Found'),
        'not_found_in_trash'  => __( 'Not found in Trash'),
    );
     
// Set other options for Custom Post Type
     
    $args = array(
        'label'               => __( 'nposts'),
        'description'         => __( 'NPost news and reviews'),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
        // You can associate this CPT with a taxonomy or custom taxonomy. 
        // 'taxonomies'          => array( 'genres' ),
        /* A hierarchical CPT is like Pages and can have
        * Parent and child items. A non-hierarchical CPT
        * is like Posts.
        */ 
        'hierarchical'        => true,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
    );
     
    // Registering your Custom Post Type
    register_post_type( 'npost', $args );
 
}

add_action( 'init', 'App\\create_posttype' );

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
    'key' => 'group_5cbe2790d6b30',
    'title' => 'Custom Button',
    'fields' => array(
        array(
            'key' => 'field_5cbe757b3f6d1',
            'label' => 'Button Text',
            'name' => 'button_text',
            'type' => 'text',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => 'Click Me',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        array(
            'key' => 'field_5cbe28943f6d0',
            'label' => 'Use Default Background',
            'name' => 'use_default_bg',
            'type' => 'select',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                'default' => 'Default',
                'custom' => 'Custom',
            ),
            'default_value' => array(
            ),
            'allow_null' => 0,
            'multiple' => 0,
            'ui' => 0,
            'return_format' => 'value',
            'ajax' => 0,
            'placeholder' => '',
        ),
        array(
            'key' => 'field_5cbe77615b35f',
            'label' => 'Background Color',
            'name' => 'bg_color',
            'type' => 'color_picker',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'field_5cbe28943f6d0',
                        'operator' => '==',
                        'value' => 'custom',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
        ),
    ),
    'location' => array(
        array(
            array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'page',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
));

acf_add_local_field_group(array(
    'key' => 'group_5cbe101aeb3e0',
    'title' => 'Like or Not',
    'fields' => array(
        array(
            'key' => 'field_5cbe10de0e1be',
            'label' => 'Like or Not',
            'name' => 'like_or_not',
            'type' => 'radio',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                'Yes' => 'Yes',
                'No' => 'No',
            ),
            'allow_null' => 0,
            'other_choice' => 0,
            'default_value' => '',
            'layout' => 'vertical',
            'return_format' => 'value',
            'save_other_choice' => 0,
        ),
        array(
            'key' => 'field_5cbe17a403252',
            'label' => 'Message',
            'name' => 'message',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'field_5cbe10de0e1be',
                        'operator' => '==',
                        'value' => 'No',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        array(
            'key' => 'field_5cbe1a68c80ec',
            'label' => 'Level',
            'name' => 'level',
            'type' => 'select',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'field_5cbe10de0e1be',
                        'operator' => '==',
                        'value' => 'No',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                'info' => 'Normal',
                'warning' => 'Warning',
                'danger' => 'Critical',
            ),
            'default_value' => array(
            ),
            'allow_null' => 0,
            'multiple' => 0,
            'ui' => 0,
            'return_format' => 'value',
            'ajax' => 0,
            'placeholder' => '',
        ),
    ),
    'location' => array(
        array(
            array(
                'param' => 'page_template',
                'operator' => '==',
                'value' => 'views/template-dynamic.blade.php',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
));

acf_add_local_field_group(array(
    'key' => 'group_5cbf67477757f',
    'title' => 'Logo Carousel',
    'fields' => array(
        array(
            'key' => 'field_5cbf6776c8361',
            'label' => 'Logo1',
            'name' => 'logo1',
            'type' => 'image',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'return_format' => 'url',
            'preview_size' => 'full',
            'library' => 'all',
            'min_width' => 195,
            'min_height' => 115,
            'min_size' => '',
            'max_width' => 195,
            'max_height' => 115,
            'max_size' => '',
            'mime_types' => '',
        ),
        array(
            'key' => 'field_5cbf6809c8362',
            'label' => 'Logo 1 Text',
            'name' => 'logo_1_text',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => 'Logo 1',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        array(
            'key' => 'field_5cbf680fc8363',
            'label' => 'Logo2',
            'name' => 'logo2',
            'type' => 'image',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'field_5cbf6776c8361',
                        'operator' => '!=empty',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'return_format' => 'url',
            'preview_size' => 'full',
            'library' => 'all',
            'min_width' => 195,
            'min_height' => 115,
            'min_size' => '',
            'max_width' => 195,
            'max_height' => 115,
            'max_size' => '',
            'mime_types' => '',
        ),
        array(
            'key' => 'field_5cbf68c0c8365',
            'label' => 'Logo 2 Text',
            'name' => 'logo_2_text',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'field_5cbf6776c8361',
                        'operator' => '!=empty',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => 'Logo 2',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        array(
            'key' => 'field_5cbf681ec8364',
            'label' => 'Logo3',
            'name' => 'logo3',
            'type' => 'image',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'field_5cbf680fc8363',
                        'operator' => '!=empty',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'return_format' => 'url',
            'preview_size' => 'full',
            'library' => 'all',
            'min_width' => 195,
            'min_height' => 115,
            'min_size' => '',
            'max_width' => 195,
            'max_height' => 115,
            'max_size' => '',
            'mime_types' => '',
        ),
        array(
            'key' => 'field_5cbf691bc8366',
            'label' => 'Logo 3 Text',
            'name' => 'logo_3_text',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'field_5cbf680fc8363',
                        'operator' => '!=empty',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => 'Logo 3',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
    ),
    'location' => array(
        array(
            array(
                'param' => 'page',
                'operator' => '==',
                'value' => '5',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
));

endif;
