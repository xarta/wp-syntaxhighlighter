<?php
/**
 * Plugin Name: Xarta Syntaxhighlighter
 * Plugin URI: https://blog.xarta.co.uk
 * Description: Simple WordPress ajax implementation of https://github.com/syntaxhighlighter/syntaxhighlighter/wiki
 * Version: 0.0.0
 * Author: David Bayliss
 * Author URI: https://blog.xarta.co.uk
 * License: MIT
 */


 /**
  * WARNING: THIS IS MY FIRST PLUG-IN ... VERY RUDIMENTARY/CRUDE; UNDER DEVELOPMENT
  *
  * Purpose:
  * 1.) provide shortcodes to process and turn into <pre blah blah></pre> syntax
  *     to be picked-up by the Syntaxhighlighter javascript/css client
  *
  *     ... I've modified the build-output of syntaxhighlighter.js to provide
  *     ... a simple custom event bind to re-execute the highlighting
  *     ... to simplify ajax loading of code from GitHub
  *
  * 2.) fetch raw file code from GitHub
  *
  * 3.) wrap output with other DIVS for use with other plug-ins
  *     Dependency: 
  *         https://noorsplugin.com/2014/01/11/wordpress-colorbox-plugin/
  *         https://en-gb.wordpress.org/plugins/forget-about-shortcode-buttons/
  *         ... and my own JavaScript related to those plug-ins
  *
  */

require 'xarta-syntaxhighlighter-attribute-checks.php';
require 'xarta-syntaxhighlighter-helper-functions.php';


function github_get_url($atts)
{
    // TODO'S
    //  - $raw ... check well-formed, no http:// etc. and 200 response?

    // hard-code here as not sure of security risk of php file_get_contents 
    // ... available to shortcode
    $github_base_url = 'https://raw.githubusercontent.com/';
    $github_user = constrain_github_user($atts['github_user']);
    $repo_raw_file = $atts['raw'];
    return  "$github_base_url$github_user/$repo_raw_file";
}

// GitHub shortcodes - load raw file to syntax highlight
function github_shortcode( $atts ) 
{
    // TODO
    //  - local caching

    return file_get_contents(github_get_url($atts));
}
add_shortcode('github', 'github_shortcode');


function cgithub_shortcode( $atts ) 
{
    return  '<pre><div class="notjq">'. 
                htmlspecialchars(github_shortcode($atts)) . 
            '</div></pre>';
}
add_shortcode('cgithub', 'cgithub_shortcode');


function xgithub_shortcode( $atts )
{
    $atts['outputcode'] = github_shortcode($atts);
    $atts['title'] = github_get_url($atts);

    return xarta_highlight( $atts );
}
add_shortcode('xgithub', 'xgithub_shortcode');


function xxgithub_shortcode( $atts )
{
    $atts['lightbox'] = 'false';
    return xgithub_shortcode( $atts );
}
add_shortcode('xxgithub', 'xxgithub_shortcode');



function xarta_highlight( $atts ) 
{
    extract( attribute_massage( $atts ));
    
    $colorboxID = trim(strval(uniqid()));

    $options = $classname.$firstline.$gutter.$autolinks;
    
    $outputcode = x_squarebrackets_to_guid($outputcode);
    $outputcode = fix_reference_issue($outputcode);

    $syntax = '<pre class="brush: \''.$lang.'\'; '.$options.' title: \''.$title.'\'; ">'.$outputcode.'</pre>';
    $wrap = '<div class="xarta-code-style xarta-code-width xarta-code-buttons"><div id="wp_colorbox_'.$colorboxID.'">'.$syntax.'</div></div>';
    $start = '<p style="clear:both;">...</p><p><span style="float:right;"> ';
    $colorbox = do_shortcode($start . ' [wp_colorbox_media url="#wp_colorbox_'.$colorboxID.'" type="inline" hyperlink="" alt="CODE ZOOM"] ' . "</span></p>$wrap");
  
    $codeoutput = x_guid_to_squarebrackets($colorbox);
    if(!empty($caption))
    {
        return $codeoutput.'<div class="xcaption">'.$caption.'</div>';
    }
    else
    {
        return $codeoutput;
    }
}



