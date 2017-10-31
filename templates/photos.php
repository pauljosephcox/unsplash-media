<?php
/**
 * Unsplash Photo Feed
 *
 */
 ?>
<div class="wrap unsplash-media-library">

    <h2><?php _e('Unsplash Media'); ?></h2>
    <h3 class="lead">Free stock photos for use in your website. Select photos below to import them to your media library.</h3>

	<div>

		<div id="post-body" class="columns-3">

			<!-- main content -->
			<div id="post-body-content">

				<div class="meta-box">

					<hr>

					<div class="wp-filter">

						<div class="media-toolbar-secondary">

							<div class="media-toolbar-primary search-form">

								<form class="unsplash-search" action="/">

									<label for="media-search-input" class="screen-reader-text">Search Media</label>

									<input name="search" type="search" placeholder="Search stock photos..." id="media-search-input" class="unsplash-search-text">

									<input name="submit" type="submit" class="button button-primary button-large search-button" id="unsplash-search-button" value="Search Photos">

									<?php wp_nonce_field( 'unsplash_media' ); ?>

								</form>

							</div>

						</div>

					</div>

					<div class="content">

						<h3 class="unplash-message">Start by searching.</h3>

						<ul tabindex="-1" class="unsplash-photos"></ul>

						<a href="#" class="button load-more" style="display:none;">Load More</a>

					</div>

				</div><!-- .meta-box -->

			</div><!-- post-body-content -->


		</div><!-- #post-body .metabox-holder .columns-2 -->

		<br class="clear">

	</div><!-- #poststuff -->

</div> <!-- .wrap -->