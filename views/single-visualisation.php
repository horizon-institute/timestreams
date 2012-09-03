<?php 

	add_filter( 'show_admin_bar', '__return_false');
	
	while ( have_posts() ) : the_post();
		the_content();
	endwhile;
	
	wp_footer();

?>