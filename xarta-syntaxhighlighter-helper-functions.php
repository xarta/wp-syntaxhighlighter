<?php

// TODO: test if this is still the case (no longer using original WordPress plugin)
function fix_reference_issue($input) 
{
    if (strpos($input,'/// <reference',0)===0) 
    {
	  return preg_replace('/^\/\/\/ <reference.+" \/>/', 
        "/* Dave has auto-removed visual studio code typings reference \n   as it upsets syntax highlighter evolved */ ", $input); 
    }
    else 
    {
	  return $input; 
    } 
}

function x_escape_lt( $input)
{
    return str_replace('<', '&lt;', $input);
}

// square bracket functions to prevent other shortcodes in 
// the source file from being evaluated!
function x_squarebrackets_to_guid( $input)
{
	$guid1 = 'f4cd1bfaa3fa49b28'.'984c326ab9b36d9';
  	$guid2 = '984c326ab9b36d9'.'f4cd1bfaa3fa49b28';
	$step1 = str_replace('[',$guid1, $input);
	$step2 = str_replace(']',$guid2, $step1);
						   
	return $step2;
}

function x_guid_to_squarebrackets( $step2)
{
  	$guid1 = 'f4cd1bfaa3fa49b28'.'984c326ab9b36d9';
	$guid2 = '984c326ab9b36d9'.'f4cd1bfaa3fa49b28';
 	$step3 = str_replace($guid1,'[', $step2);
	$step4 = str_replace($guid2,']', $step3);
  
	return $step4;
}

function xarta_get_instance_id()
{
    // just a bit overcautious about trimmed string lol
    return 'xarta-id-'.trim(strval(uniqid())); 
}

function xarta_setup_syntax_ajax_post()
{
    // UNDER DEVELOPMENT
    // DEVELOPING THIS - PROBLEM ON MY SITE AND "post_type"
    // (have to use meta tables instead)

    $post_arr = array(
        'post_title'    =>  'random', 
        'post_content'  =>  'demo text',
        'post_type'     =>  'xgithub',
        'post_status'   =>  'publish',
        'post_author'   =>   1,
        'post_category' =>  'uncategorised'
    );

    $id = wp_insert_post($post_arr, true);

    echo $id;

}

function xarta_setup_syntax_ajax_single_template()
{
    // a WordPress post template, that just returns "content"
    // where I can put my ajax shortcode to process/respond to $_POST
    // data thrown at it

    // if not already there, copy to the theme template directory


    $timestamp = date("Y-m-d h:i:sa");
    // *****************************************************
    $ajax_post_template_file_current_version = 5;      // **
    // ************************************************** **
    $feedback = '';
    $ajax_post_template_file_name = get_stylesheet_directory().'/single-xgithub.php';
    $ajax_post_template_file_create = TRUE;


    if (file_exists($ajax_post_template_file_name) !== FALSE)
    {
        $ajax_post_template_file_create = FALSE;

        // BUT ...

        // I'M STILL READING THIS EVERY SINGLE TIME THE TEMPLATE IS USED
        // ... TODO ... MAKE MORE EFFICIENT FOR VERSION CHECKING
        // ... ONCE PER SESSION OR SOMETHING??? OR ...
        // ... IF FILE DOESN'T EXIST, CREATE ... BUT THEN INSTEAD OF
        // ... READFILE ... GET THE FILE TO CALL THIS FUNCTION WITH ITS
        // ... VERSION NUMBER (IF THIS FUNCTION REMAINS ACCESSIBLE?)
        $temp_read_file = file_get_contents($ajax_post_template_file_name);
        $file_length = strlen($temp_read_file);

        $lets_check_file_version = substr($temp_read_file,$file_length-5,3);
        if ( intval($lets_check_file_version ) < $ajax_post_template_file_current_version)
        {
            $feedback = "lets_check_file_version = " . $lets_check_file_version;
            $feedback .= "// intval(lets_check_file_version) = ". intval($lets_check_file_version);
            $ajax_post_template_file_create = TRUE;
        }
    }


    if($ajax_post_template_file_create === TRUE)
    {
        $ajax_post_template_for_my_xgithub_ajax_shortcode = 
'
<?php
/**
WP Post Template: xgithub
*/
// '.$feedback.'
// To help debugging filters:
function xarta_print_filters_for( $hook = "" ) {
    global $wp_filter;
    if( empty( $hook ) || !isset( $wp_filter[$hook] ) )
        return;

    print "<pre>";
    print_r( $wp_filter[$hook] );
    print "</pre>";
}
function x_remove_eager_filter($file)
{
    // remove printfriendly plugin filter (gets overzealous)

    global $printfriendly;
    remove_filter( "the_content", array($printfriendly, "show_link") );
    remove_filter( "the_content", array($printfriendly, "add_pf_content_class_around_content_hook") );
    
    return $file;
}
while ( have_posts() ) : the_post();
    add_filter("the_content", "x_remove_eager_filter", 0);
    the_content();
    // xarta/print_filters_for( "the_content" ); // for debug only
endwhile; // end of the loop.
// this file created by xarta-syntaxhighlighter plugin: '.$timestamp.'
// VERSION:   '.$ajax_post_template_file_current_version.'?>';

        $success = file_put_contents ( $ajax_post_template_file_name, 
            $ajax_post_template_for_my_xgithub_ajax_shortcode);
       // echo $success;
    }
 
   
}



/*
 * http://stackoverflow.com/questions/3489387/print-post-variable-name-along-with-value
 * $pad='' gives $pad a default value, meaning we don't have 
 * to pass printArray a value for it if we don't want to if we're
 * happy with the given default value (no padding)
 */
function printArray($array, $pad='')
{
     foreach ($array as $key => $value)
     {
        echo $pad . "$key => $value<br />";
        if(is_array($value))
        {
            printArray($value, $pad.' ');
        }  
    } 
}








?>