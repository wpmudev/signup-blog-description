<?php
/*
Plugin Name: Signup Blog Description
Plugin URI: 
Description:
Author: Andrew Billits
Version: 1.0.0
Author URI:
*/

/* 
Copyright 2007-2009 Incsub (http://incsub.com)

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

//------------------------------------------------------------------------//
//---Config---------------------------------------------------------------//
//------------------------------------------------------------------------//

$default_blog_description = ''; //optional

//------------------------------------------------------------------------//
//---Hook-----------------------------------------------------------------//
//------------------------------------------------------------------------//
add_action('wp_head', 'signup_blog_description_stylesheet');
add_filter('add_signup_meta', 'signup_blog_description_meta_filter',99);
add_action('wpmu_new_blog', 'signup_blog_description', 1, 1);
add_action('signup_blogform', 'signup_blog_description_signup_form');

//------------------------------------------------------------------------//
//---Functions------------------------------------------------------------//
//------------------------------------------------------------------------//

function signup_blog_description($blog_ID) {
	global $wpdb, $default_blog_description;
	if ( ! empty($_GET['key']) ) {
		$key = $_GET['key'];
	} else {
		$key = $_POST['key'];
	}
	if ( !empty($_POST['blog_description']) ) {
		$blog_description = $_POST['blog_description'];
	} else if ( !empty( $key ) ) {
		$signup = $wpdb->get_row("SELECT * FROM " . $wpdb->signups . " WHERE activation_key = '" . $key . "'");
		if ( empty($signup) || $signup->active ) {
			//bad key or already active
		} else {
			//check for password in signup meta
			$meta = unserialize($signup->meta);
			if ( !empty( $meta['blog_description'] ) ) {
				$blog_description = stripslashes($meta['blog_description']);
			}
		}		
	}
	
	if ( empty($blog_description) ) {
		$blog_description = $default_blog_description;
	}
	
	if ( $blog_description == 'empty' ) {
		$blog_description = '';
	}
	
	if ( !empty( $blog_description ) ) {
		switch_to_blog( $blog_ID );
		update_option('blogdescription', stripslashes($blog_description));
		restore_current_blog();
	}
}

function signup_blog_description_meta_filter($meta) {
	$blog_description = $_POST['blog_description'];
	if ( !empty( $blog_description ) ) {
		$add_meta = array('blog_description' => addslashes($blog_description));
		$meta = array_merge($add_meta, $meta);
	}
	return $meta;
}

//------------------------------------------------------------------------//
//---Output Functions-----------------------------------------------------//
//------------------------------------------------------------------------//

function signup_blog_description_stylesheet() {
?>
<style type="text/css">
	.mu_register #blog_description { width:100%; font-size: 24px; margin:5px 0; }
</style>
<?php
}

function signup_blog_description_signup_form($errors) {
	$error = $errors->get_error_message('blog_description');
	?>
    <label for="blog_description"><?php _e('Blog Tagline'); ?>:</label>
		<input name="blog_description" type="text" id="blog_description" value="" autocomplete="off" maxlength="50" /><br />
		<?php _e('In a few words, explain what this blog is about. Default will be used if left blank.') ?>
	<?php
}

?>
