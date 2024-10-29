<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://cba-records.com
 * @since      1.0.0
 *
 * @package    Api_Fetch_Twitter
 * @subpackage Api_Fetch_Twitter/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<h2>API Fetch Twitter</h2>
	<form action="options.php" method="post">
		<?php
		settings_fields( 'api_fetch_twitter_options' );
		do_settings_sections( 'api_fetch_twitter' );
		submit_button();
		?>
	</form>
 </div>
