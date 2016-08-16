<?php

// Prevent direct calls
if ( ! defined( 'WPINC' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

?>

<script type="text/javascript">
	$stop_and_explain = jQuery(".stop-and-explain");
	$title_deploy = jQuery("h1.deploying");
	var ruler = "<hr class='clear' />";

	// Hide the spinner.
	$spinner.remove();

	// Move "stop and explain" to the top of the page.
	$stop_and_explain.insertBefore($title_deploy).slideToggle(1000);

	// Move the separator into place.
	jQuery(ruler).insertBefore($title_deploy);

	// Update the title of the page.
	$title_deploy.html("Staging deployment complete!").prepend("<span class='dashicons dashicons-yes'></span>");

	// Scroll the user to the top of the page.
	jQuery("html").animate({ scrollTop: 0 }, "slow");
</script>
