<?php
/**
WP Post Template: xgithub
*/

/*
    So long as this file is in my WordPress theme
    (active) folder e.g. wp-content/themes/simone
    at the moment, and so long as I'm using the 
    WP Custom Post Template plug-in to match the
    comment above, then WordPress will include 
    this template in the dropdown of custom post
    templates.  I'm pairing it with my "xgithub"
    shortcode.  I plan on using it asynchronously
    to load syntaxhighlighted code from GitHub in
    target DIVS etc. (will need the suitable JS to
    be called after ajax-loaded).
*/

// To help debugging filters:
function print_filters_for( $hook = '' ) 
{
    global $wp_filter;
    if( empty( $hook ) || !isset( $wp_filter[$hook] ) )
        return;

    print '<pre>';
    print_r( $wp_filter[$hook] );
    print '</pre>';
}
function x_remove_eager_filter($file)
{
  	// remove printfriendly plugin filter (gets overzealous)

    global $printfriendly;
    remove_filter( 'the_content', array($printfriendly, 'show_link') );
    remove_filter( 'the_content', array($printfriendly, 'add_pf_content_class_around_content_hook') );
  	
  	return $file;
}
while ( have_posts() ) : the_post();
	add_filter('the_content', 'x_remove_eager_filter', 0);
	the_content();
	//print_filters_for( 'the_content' ); // for debug only
endwhile; // end of the loop.
?>