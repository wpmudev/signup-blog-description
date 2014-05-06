<?php
/*
Plugin Name: Set Blog Description on Blog Creation
Plugin URI: http://premium.wpmudev.org/project/set-blog-description-on-blog-creation/
Description: Allows new bloggers to be able to set their tagline when they create a blog in Multisite
Version: 1.1
Author: WPMU DEV
Author URI: http://premium.wpmudev.org
Network: true
WDP ID: 104
Contributors: Umesh Kumar
*/

/*
Copyright 2007-2014 Incsub (http://incsub.com)
Author - Aaron Edwards & Andrew Billits (Incsub)
Contributors - Umesh Kumar
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class SignupBlogDescription {
    var $version = '1.1';
    var $language;
    var $location;
    var $plugin_dir;
    var $plugin_url;

    function __construct() {

        //Setup variables
        $this->init_vars();

        //install plugin
        register_activation_hook( __FILE__, array($this, 'install') );

        //localize
        add_action( 'plugins_loaded', array( &$this, 'localization' ) );

        add_action('wp_head', array( &$this, 'stylesheet' ) );
        add_filter('add_signup_meta', array( &$this, 'meta_filter' ) );
        add_filter('bp_signup_usermeta', array( &$this, 'meta_filter' ) );
        add_action('signup_blogform', array( &$this, 'signup_form' ) );
        add_action('bp_blog_details_fields', array( &$this, 'signup_form' ) );
        add_filter('blog_template_exclude_settings', array( &$this, 'nbt' ) );
        add_filter('wpmu_new_blog', array( &$this, 'nbt' ) );

        include_once( $this->plugin_dir . 'dash-notice/wpmudev-dash-notification.php' );
    }

    /**
     * Initiallize variables
     */
    function  init_vars(){
        //setup proper directories
        if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/signup-blog-description/' . basename( __FILE__ ) ) ) {
            $this->location = 'plugins';
            $this->plugin_dir = WP_PLUGIN_DIR . '/signup-blog-description/';
            $this->plugin_url = WP_PLUGIN_URL . '/signup-blog-description/';
        }
    }

    /**
     * Textdomain for plugin
     */
    function localization() {
        // Load up the localization
        load_plugin_textdomain( 'sbd', false, '/signup-blog-description/languages' );

    }

    /**
     * Checks if it is a mulitsite or not
     * @global type $wpdb
     * @global type $current_site
     */
    function install() {
        global $wpdb, $current_site;
	
        //check if multisite is installed
        if ( !is_multisite() ) {
            $this->trigger_activation_error(__('WordPress multisite is required to run this plugin. <a target="_blank" href="http://codex.wordpress.org/Create_A_Network">Create a network</a>.', 'psts'), E_USER_ERROR);
        }
    }
    
    function trigger_activation_error( $message, $errno) {
 
        if(isset($_GET['action']) && $_GET['action'] == 'error_scrape') {

            echo $message;

        } else {

            trigger_error($message, $errno);

        }
        exit;
    }
    /**
     * Save the blogdescription value in meta
     * @param type $meta
     * @return type $meta
     */
    function meta_filter($meta) {
        if ( !empty( $_POST['blog_description'] ) ) {
            $meta['blogdescription'] = $_POST['blog_description'];
        }
        return $meta;
    }

    /**
     * Exclude option from New Site Template plugin copy
     * @param string $and
     * @return string
     */
    function nbt( $and ) {
        $and .= " AND `option_name` != 'blogdescription'";
        return $and;
    }

    /**
     * Style for input field
     */
    function stylesheet() {
    ?>
        <style type="text/css">
            .mu_register #blog_description { width:100%; font-size: 24px; margin:5px 0; }
        </style>
      <?php
    }

    /**
     * Adds an additional field for Blog description,
     * on signup form for WordPress or Buddypress
     * @param type $errors
     */
    function signup_form($errors) {
        if(!empty( $errors ) ) {
            $error = $errors->get_error_message( 'blog_description' );
        }

        $desc = isset($_POST['blog_description']) ? esc_attr($_POST['blog_description']) : '';
        ?>

        <label for="blog_description"><?php _e('Site Tagline', 'sbd'); ?>:</label>
        <input name="blog_description" type="text" id="blog_description" value="<?php echo $desc; ?>" autocomplete="off" maxlength="50" /><br />
        <?php _e('In a few words, explain what this site is about. Default will be used if left blank.', 'sbd') ?>
        <?php
    }
}

//Initiallize Class
$sbd = new SignupBlogDescription();