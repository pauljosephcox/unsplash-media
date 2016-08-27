<?php
/**
 * Plugin Options Page
 *
 */
 ?>
<div class="wrap">

    <h2><?php _e('Unsplash Media'); ?></h2>

	<div>

		<div id="post-body" class="columns-3">

			<!-- main content -->
			<div id="post-body-content">

				<div class="meta-box">

					<form method="post" action="options.php">

    					<?php settings_fields('unsplash_media_options'); ?>
    					<?php do_settings_sections('unsplash_media_options'); ?>

    					<h2><?php _e('Settings'); ?></h2>

    					<table class="form-table">
    						<tbody>
    							<tr valign="top">
    								<th scope="row"><label for="unsplash_media_application_id">Application ID</label></th>
    								<td>
										<input type="text" class="regular-text" id="unsplash_media_application_id" name="unsplash_media_application_id" value="<?php echo esc_attr( get_option('unsplash_media_application_id') ); ?>">
									</td>
    							</tr>
    						</tbody>
    					</table>

    					<?php submit_button(); ?>

					</form>

				</div><!-- .meta-box -->

			</div><!-- post-body-content -->


		</div><!-- #post-body .metabox-holder .columns-2 -->

		<br class="clear">

	</div><!-- #poststuff -->

</div> <!-- .wrap -->