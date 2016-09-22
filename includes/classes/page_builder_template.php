<?php

/*
 * Puzzle Page Builder
 * Page Builder Template Class
 */

class PuzzlePageBuilderTemplate {

    /*
     * A unique identifier
     */
    protected $plugin_slug;

    /*
     * A reference to an instance of this class.
     */
    private static $instance;

    /*
     * The array of templates that this plugin tracks.
     */
    protected $templates;
    
    /*
     * Returns an instance of this class.
     */
    public static function get_instance() {
        if (null == self::$instance) {
            self::$instance = new PuzzlePageBuilderTemplate();
        }

        return self::$instance;
    }

    /*
     * Initializes the class by setting filters and administration functions.
     */
    private function __construct() {
        $this->templates = array();

        /*
         * Add a filter to the attributes metabox to inject template into
         * the cache.
         */
        add_filter('page_attributes_dropdown_pages_args', array($this, 'register_puzzle_page_builder_template'));
        
        /*
         * Add a filter to the save post to inject out template into the
         * page cache
         */
        add_filter('wp_insert_post_data', array($this, 'register_puzzle_page_builder_template'));
        
        /*
         * Add a filter to the template include to determine if the page has our
         * template assigned and return its path
         */
        add_filter('template_include', array($this, 'view_puzzle_page_builder_template'));


        /* Add templates to the array. */
        $this->templates = array(
            'template_page_builder.php' => 'Page Builder'
        );
    }
    
    /*
     * Adds our template to the pages cache in order to trick WordPress
     * into thinking the template file exists where it doesn't really exist.
     */
    public function register_puzzle_page_builder_template($atts) {
        /* Create the key used for the themes cache */
        $cache_key = 'page_templates-' . md5(get_theme_root() . '/' . get_stylesheet());
        
        /*
         * Retrieve the cache list.
         * If it doesn't exist, or if it's empty, prepare an array.
         */
        $templates = wp_get_theme()->get_page_templates();
        if (empty($templates)) {
            $templates = array();
        }

        /* New cache, therefore remove the old one */
        wp_cache_delete($cache_key , 'themes');
        
        /*
         * Now add our template to the list of templates by merging our templates
         * with the existing templates array from the cache.
         */
        $templates = array_merge($templates, $this->templates);
        
        /*
         * Add the modified cache to allow WordPress to pick it up for listing
         * available templates
         */
        wp_cache_add($cache_key, $templates, 'themes', 1800);

        return $atts;
    }
    
    /*
     * Checks if the template is assigned to the page
     */
    public function view_puzzle_page_builder_template($template) {
        global $post;
        
        if (!is_singular()) return $template;
        
        /* Get the name of the post's template */
        if ($post->post_type == 'page') {
            $active_template = get_post_meta($post->ID, '_wp_page_template', true);
        } else {
            $active_template = get_post_meta($post->ID, '_puzzle_custom_template', true);
        }
        
        /*
         * Check if this template is one of our plugin's templates. If it
         * isn't, return the template as-is.
         */
        if (!isset($this->templates[$active_template])) return $template;
        
        /* Check that the template file exists. If it does, use it. */
        $file = plugin_dir_path(dirname(dirname(__FILE__))) . 'views/' . $active_template;
        if (file_exists($file)) return $file;
        
        /* If all else fails, return the template without altering it. */
        return $template;
    }
}

add_action('plugins_loaded', array('PuzzlePageBuilderTemplate', 'get_instance'));

?>