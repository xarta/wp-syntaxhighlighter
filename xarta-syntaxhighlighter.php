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
  * ... and not got around to learning PHP yet so very basic.
  *
  * Purpose:
  * 1.) provide shortcodes to process and turn into <pre blah blah></pre> syntax
  *     to be picked-up by the Syntaxhighlighter javascript/css client
  *
  *     ... I've modified the build-output of syntaxhighlighter.js to provide
  *     ... a simple custom event bind to re-execute the highlighting
  *     ... to simplify ajax loading of code from GitHub
  *
  * 2.) fetch raw file code from GitHub (or use other code in an attribute*)
  *
  * 3.) wrap output with other DIVS for use with other plug-ins
  *     Requires two other plug-ins: 
  *         https://noorsplugin.com/2014/01/11/wordpress-colorbox-plugin/
  *         https://en-gb.wordpress.org/plugins/forget-about-shortcode-buttons/
  *         ... and my own JavaScript related to those plug-ins
  *
  */

require 'xarta-syntaxhighlighter-attribute-checks.php';
require 'xarta-syntaxhighlighter-helper-functions.php';
/**
 * In site header-code (JavaScript):
 * <script>syntaxhighlighterConfig = { className: 'xarta-big-code' };</script>
 */

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

// GitHub shortcodes - get GitHub raw file contents
function github_shortcode( $atts = []) 
{
    // TODO
    //  - local caching

    return file_get_contents(github_get_url($atts));
}
add_shortcode('github', 'github_shortcode');


function cgithub_shortcode( $atts = [] ) 
{
    return  '<pre><div class="notjq">'. 
                htmlspecialchars(github_shortcode($atts)) . 
            '</div></pre>';
}
add_shortcode('cgithub', 'cgithub_shortcode');


function xgithub_shortcode( $atts = [])
{
    $atts['outputcode'] = github_shortcode($atts);
    $atts['title'] = github_get_url($atts);

    return xarta_highlight( $atts );
}
add_shortcode('xgithub', 'xgithub_shortcode');


function xsyntax_shortcode( $atts = [], $content = '' )
{
    $atts['outputcode'] = $content;
    return xarta_highlight( $atts );
}
add_shortcode('xsyntax', 'xsyntax_shortcode');



function xarta_highlight( $atts ) 
{
    // unique id for every "instance"
    $instance_id = 'xarta-id-'.trim(strval(uniqid())); // just a bit overcautious about trimmed string lol
    $customClassName = 'xarta-big-code'; // also set in JavaScript header - syntaxhighlighter config

    $atts_default = array('classname' => '');
    $atts_classname = array_change_key_case( (array)$atts, CASE_LOWER);
    $atts_classname = shortcode_atts( $atts_default, $atts_classname );

    if(!empty($atts['classname']))
    {
        // over-ride custom classname with what was provided in shortcode attributes
        // will override JavaScript-set classname (syntaxhighlighter config) too
        $atts['classname'] = $atts['classname'] . " $instance_id";
    }
    else
    {
        $atts['classname'] = "$customClassName $instance_id";
    }
    
    // $options below requires that $atts have been massaged
    extract( attribute_massage( $atts ));





    // options - either empty strings or key: 'value' pairs with dash-case keys
    $options = $classname.$title.$firstline.$gutter.$autolinks.$highlight.$htmlscript.$smarttabs.$tabsize;
      
    $outputcode = x_squarebrackets_to_guid($outputcode); // prevent shortcodes in code
                                                         // from being evaluated in do_shortcode()
    $outputcode = fix_reference_issue($outputcode);      // TODO check if still necessary

    // my attribute $escapelt
    if($escapelt === 'true')
    {
        $outputcode = x_escape_lt($outputcode);
    }

    // my attribute $buttons
    if($buttons === 'true')
    {
        $buttons = ' xarta-code-buttons ' . $instance_id;
    }
    else
    {
        $buttons = '';
    }

    // xarta-syntaxhighlighter-site-footer.js will look for this special <pre>
    $syntax = '<pre class="brush: \''.$lang.'\'; '.$options.' ">'.$outputcode.'</pre>';
    
    // nb: xarta-big-code class set in JavaScript syntaxhighlighterConfig default className
    //     xarta-syntaxhighlighter-site-footer.js will include as a class in output div
    $wrap_classes = "xarta-syntax-highlight $buttons"; // my wrapping div

    // my attribute $lightbox
    if($lightbox === 'true')
    {
        $colorboxID = trim(strval(uniqid()));
        $wrap = '<div class="'.$wrap_classes.'"><div id="wp_colorbox_'.$colorboxID.'">'.$syntax.'</div></div>';
        $start = '<p style="clear:both;">...</p><p><span style="float:right;"> ';
        $codeoutput = do_shortcode($start . ' [wp_colorbox_media url="#wp_colorbox_'.$colorboxID.'" type="inline" hyperlink="" alt="CODE ZOOM"] ' . "</span></p>$wrap");
    }
    else
    {
        $codeoutput = '<div class"'.$wrap_classes.'">'.$syntax.'</div>';
    }
  
    return x_guid_to_squarebrackets($codeoutput).$caption;
}



