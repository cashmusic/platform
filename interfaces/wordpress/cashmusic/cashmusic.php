<?php
/*
Plugin Name: CASH Music
Plugin URI: https://github.com/cashmusic/platform/tree/master/interfaces/wordpress
Description: Works with the CASH Music platform to allow element inserts via WP short-code.
Version: 1.0
Author: CASH Music
Author URI: http://cashmusic.org/
License: BSD


distributed under a BSD license, terms:
Copyright (c) 2012, CASH Music
All rights reserved.
Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:
Redistributions of source code must retain the above copyright notice, this list
of conditions and the following disclaimer. Redistributions in binary form must
reproduce the above copyright notice, this list of conditions and the following
disclaimer in the documentation and/or other materials provided with the
distribution. Neither the name of CASH Music nor the names of its contributors
may be used to endorse or promote products derived from this software without
specific prior written permission.
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.
*/

class CASHMusicPlatformWPPlugin {

	function __construct() {
		require_once(ABSPATH . '/wp-admin/includes/plugin.php');
		require_once(ABSPATH . WPINC . '/pluggable.php');

		// handle settings/options
		register_setting( 'cashmusic_platform_options', 'cashmusic_platform' );
		$this->options = get_option('cashmusic_platform');
		$this->plugin_path = dirname(__FILE__);
		
		// include CASH init script
		if (!empty($this->options['location'])) {
			if (file_exists($this->options['location'])) {
				include_once($this->options['location']);
			} 
		}

		// menu stuff
		add_action('admin_menu', array(&$this, 'add_options_page'));

		// register cashmusic short-code
		// ex: [cashmusic element="1" name="element name"]
		add_shortcode('cashmusic', array($this, 'cashmusic_shortcode'));
	}

	// add menu page
	function add_options_page() {
		add_options_page('CASH Music options', 'CASH Music', 'manage_options', 'cashmusic_platform_options', array($this, 'render_options_page'));
	}

	// render the menu page
	function render_options_page() {
		echo '<div class="wrap">' .
			 '<h2>CASH Music platform details</h2>';

		if (!file_exists($this->options['location'])) {
			echo '<div class="error"><p><strong>The cashmusic.php location seems to be wrong. Please check your CASH admin in "System settings" for the correct location.</strong><p/></div>';
		}

		echo '<form method="post" action="options.php">';
			 		settings_fields('cashmusic_platform_options');
				
		echo '  <table class="form-table"><tbody><tr>' .
			 '	   <th><label for="cashmusic_platform[location]">Full cashmusic.php location</label></th>' .
			 '			<td> <input name="cashmusic_platform[location]" value="' . $this->options['location'] . '" class="regular-text code" type="text"></td>';

		echo '	</tr><tr>' .
			 '	   <th><label for="cashmusic_platform[address]">CASH Music admin email address</label></th>' .
			 '	   <td><input name="cashmusic_platform[address]" value="' . $this->options['address'] . '" class="regular-text code" type="text"></td>' .
			 '		</tr></tbody></table>';

		echo '	<p><br /><input type="submit" class="button-primary" value="Save changes" /></p>' .
			 '	</form>' .
			 '</div>';
	}

	function cashmusic_shortcode($attributes) {
		extract(shortcode_atts(
			array(
				'element' => false,
				'name'    => false
			), 
			$attributes
		));

		if ($element) {
			CASHSystem::embedElement($element);
		} else {
			echo '<!-- CASH Music error: no such element found -->';
		}
	}
}

$cashmusic_wp_platform = new CASHMusicPlatformWPPlugin();

?>