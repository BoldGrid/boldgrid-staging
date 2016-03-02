<?php

// Prevent direct calls
if ( ! defined( 'WPINC' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

?>

<div class='wrap'>

	<div class="plugin-card">
		<div class="plugin-card-top">
			<h1>Deploy Staging</h1>
			<p>
				It appears you do not have any active pages. To make your <b>Staging
					Site</b> your <b>Active Site</b>, click <em>Launch staging!</em>
				below.
			</p>
		</div>
		<div class="plugin-card-bottom">
			<div class="column-updated">
				<form method='post'>
			<?php wp_nonce_field( 'launch_staging' ); ?>
			<input type='hidden' name='action' id='action' value='launch_staging' />
					<input type='hidden' name='scenario' id='scenario'
						value='yes-staged-pages-no-active-pages' /> <input type="submit"
						name="submit" id="submit" class="button button-primary"
						value="Launch Staging!">
				</form>
			</div>
		</div>
	</div>

</div>
