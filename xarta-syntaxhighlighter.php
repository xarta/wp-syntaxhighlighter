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
  * ... and not got around to learning PHP yet (or JavaScript, properly) so very basic.
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


        // ****************************************************
        // TODO -- URGENT                                     *
        //         How much "data" i.e. code e.g. from        *
        //           GitHub, can an associative array member  *
        //           hold?  Are there other limits?          *
        //           Should I set limits to $_POST & data?  *
        //         WordPress standard plugin stuff ...      *
        //         ABSPATH thingy?                          *
        //         Encapsulate somehow ... OOP / Class?     *
        //         *** CURRENTLY GLOBAL SPACE ***           *
        //         Standard security practice?              *
        // **************************************************


require 'xarta-syntaxhighlighter-attribute-checks.php';
require 'xarta-syntaxhighlighter-helper-functions.php';


function xarta_load_scripts($hook) {
 
    // create my own version codes
    $xarta_global_js_ver  = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'xarta-global-functions.js' ));
    $syntax_theme_css_ver = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'theme.css' ));
    $x_syntax_theme_css_ver = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'x_syntaxhighlighter_css' ));
     
    // 
    wp_register_script( 'xarta_global_js', plugins_url( 'xarta-global-functions.js', __FILE__ ), array(), $xarta_global_js_ver );
    wp_register_script('xarta_syntaxhighlighter_site_footer', plugins_url('xarta-syntaxhighlighter-site-footer.js', __FILE__ ), array('syntaxhighlighter'), true );
    wp_register_script('syntaxhighlighter', plugins_url( 'syntaxhighlighter.js', __FILE__ ), array('xarta_global_js'));

    wp_register_style( 'syntaxhighlighter_css',    plugins_url( 'theme.css',    __FILE__ ), array(),   $syntax_theme_css_ver, 'all' );
    wp_register_style( 'x_syntaxhighlighter_css',    plugins_url( 'xarta-syntaxhighlighter-site-footer.css',    __FILE__ ), array(syntaxhighlighter_css),   $x_syntax_theme_css_ver, 'all' );
    
    wp_enqueue_script( 'xarta_global_js' );   

    // make sure single-post-page thingy template exists that only provides 'the_content'
    // for me to run the ajax shortcode on to handle $_POST
    xarta_setup_syntax_ajax_single_template();

    //xarta_setup_syntax_ajax_post();

}
add_action('wp_enqueue_scripts', 'xarta_load_scripts');



function xarta_enqueue_syntax_scripts()
{
    wp_enqueue_style ( 'syntaxhighlighter_css' );
    wp_enqueue_style ( 'x_syntaxhighlighter_css' );
    wp_enqueue_script( 'xarta_syntaxhighlighter_site_footer' );
    wp_enqueue_script( 'syntaxhighlighter' );
}
add_action('x_enqueue_syntax_scripts', xarta_enqueue_syntax_scripts);


$xartaLangs = array('code', 'bash', 'c++', 'c#', 'php', 'sql', 'js', 'css', 'xml');
/**
 * In site header-code (JavaScript):
 * <script>syntaxhighlighterConfig = { className: 'xarta-big-code' };</script>
 */


// protect [js attributes]content-code[/js] style shortcodes by adding <pre></pre>
// tags around the code inbetween the shortcode tags, and remove them later during
// actual shortcode do_shortcode etc. (Protect from things like wpauto filter).
function xarta_before_the_content_normal_filters($content)
{
    // first of all though, this is a convenience for me so that in tiny mce
    // (for example) I can type code in that's not going to be syntax highlighted,
    // inbetween <pre><code> tags without having to worry about escaping
    // ... and without having to worry about picking-up false positives for
    // ... shortcodes in the second half of this function

    $start = 0; // 0 from start of $content ... use as index (head)
    $end = 0;   // 0 from start of $content ... use as index (tail)

    $contentLength = strlen($content);

    while ( $end < $contentLength)
    {   
        // look in $content for <pre><code> from $end-value
        // to end of file
        $start = strpos($content, '<pre><code>', $end);
        if ($start !== FALSE)
        {
            // leap-frog $end, looking for </code></pre> after
            // $start i.e. after where <pre><code> was found
            $end = strpos($content, '</code></pre>', $start);

            if ($end !== FALSE)
            {
                $start = $start + 13;   // i.e. + characters for <pre><code>
                                        // $end is already at beginning of </code></pre>
                                        // so - like the filling of the sandwich ...
                $pre_code_code_pre = substr($content, $start, $end-$start);
                $pre_code_code_pre = str_replace('<', '&lt;', $pre_code_code_pre);
                $pre_code_code_pre = str_replace('[', '&#91;', $pre_code_code_pre);
                $content = substr_replace ($content, $pre_code_code_pre, $start, $end-$start);

                // if more $content to look at, go round and round
            }
            else
            {
                // broken <pre><code> tags (none closing)
                break;
            }
        }
        else
        {
            // no <pre><code> (or run out of them to process)
            break;
        }
    }



    global $xartaLangs;
    $xartaCodesToCheck = $xartaLangs;
    array_push($xartaCodesToCheck, "xsyntax"); // additional shortcode to check (not language)

    foreach ($xartaCodesToCheck as $searchLang)
    {
        //$searchLang = 'code';
        if(strpos($content,'['.$searchLang) !== FALSE)
        {

            $searchString = '/\['.$searchLang.'(.*)\]/'; // https://regex101.com/
            $replaceString = "[$searchLang $1 ]<pre class=\"xprotect\">";

            // using preg_replace to cope with attributes (no wild card in str_pos)
            // can't easily do the whole [shortcode atts]code-to-highlight[/shortcode]
            // in one go though as it get's complicated when the shortcode appears
            // more than once, successively (have to look at occurances etc.)
            // and computationally gets expensive.  This is a compromise.
            $content = preg_replace( $searchString, $replaceString , $content );
            $content = str_replace('[/'.$searchLang.']', '</pre>[/'.$searchLang.']', $content);
        }
        //break;
    }



    return $content;
}
add_filter('the_content', 'xarta_before_the_content_normal_filters', 4); // higher priority




function xarta_remove_xprotect_pre_tags($code_content)
{
    // https://regex101.com/

    // / (<pre class="xprotect">)((?s:.)*)(<\/pre>) /g etc.
    // ... group 1 = '<pre class="xprotect">'
    // ... group 2 = the code we want to restore without <pre> tags

    $searchString = '/((?:<\/p>\s)<pre class="xprotect">)((?s:.)*)(<\/pre>)(?:\s<p>)/';
    $code_content = preg_replace( $searchString, "$2", $code_content );

    return $code_content;
}


/* OOPS - MAKES THINGS WORSE ... TODO INVESTIGATE ------------------------------
*/
// including this here as seems relevant to doing WordPress posts on Development
// where additional control over output is important:
// https://ikreativ.com/stop-wordpress-removing-html/
function ikreativ_tinymce_fix( $init )
{
    // html elements being stripped
    $init['extended_valid_elements'] = 'div[*], article[*]';

    // don't remove line breaks
    $init['remove_linebreaks'] = false;

    // convert newline characters to BR
    $init['convert_newlines_to_brs'] = false;

    // don't remove redundant BR
    $init['remove_redundant_brs'] = false;

    // pass back to wordpress
    return $init;
}
add_filter('tiny_mce_before_init', 'ikreativ_tinymce_fix');
/*
*/ // ------------------------------------------------------------------------


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

/**
 * MY SYNTAXHIGHLIGHTER WORDPRESS SHORTCODES INCL. GET FROM GITHUB
 *
 */

function github_shortcode( $atts = []) 
{
    // TODO
    //  - set-up own Git server as fallback

    return file_get_contents(github_get_url($atts));
}
add_shortcode('github', 'github_shortcode');   //              <<<===== github


function cgithub_shortcode( $atts = [] ) 
{
    return  '<pre><div class="notjq">'. 
                htmlspecialchars(github_shortcode($atts)) . 
            '</div></pre>';
}
add_shortcode('cgithub', 'cgithub_shortcode'); //              <<<====== cgithub


function xgithub_shortcode( $atts )
{
    $atts['outputcode'] = github_shortcode($atts);
    $atts['title'] = github_get_url($atts);

    //echo "xgithub_shortcode<br /><br />";
    //printArray($atts);
    
    return xarta_highlight( $atts );     
}
add_shortcode('xgithub', 'xgithub_shortcode'); //               <<<====== xgithub


function xgithub_ajax_shortcode( $atts = [])
{
    //echo "Debug. This is \$atts array before encoding:<br /><br />";
    //printArray($atts);

    do_action('x_enqueue_syntax_scripts');

    $instance_id = xarta_get_instance_id();
    $xartaAjaxCssClass = 'xarta-target-ajax';

    $ajaxurl = "https://blog.xarta.co.uk/2017/03/test-ajax/";

    $step1 = json_encode($atts);               // input
    $step2 = base64_encode($step1);
    $step3 = strtr($step2, '+/=', '-_,');      // url friendly
    $ajaxpost = "atts=$step3";                 // output

    return "<div class=\"$xartaAjaxCssClass $instance_id\" ".
        "data-url=\"$ajaxurl\" data-post=\"$ajaxpost\">".
        "LOADING CODE FROM GITHUB VIA AJAX...</div>";

}
add_shortcode('xgithub_ajax', 'xgithub_ajax_shortcode'); //    <<<====== xgithub_ajax


function xgithub_ajax_response_shortcode()
{
    // TODO - ERROR HANDLING!!!

    // reverse of xgithub_ajax_shortcode
    $ajaxpost = $_POST['atts'];                     // input
    $step3 = strtr($ajaxpost, '-_,', '+/=');        // was url friendly
    $step2 = base64_decode($step3);
    $step1 = json_decode($step2,true);              // output
                                                    // nb: "true" for associative array
    
    $atts = $step1;

    // echo "xgithub_ajax_reponse_shortcode";
    // printArray($atts);

    return xgithub_shortcode ($atts);
}
add_shortcode(  'xgithub_ajax_response', 
                'xgithub_ajax_response_shortcode'); //       <<<====== xgithub_ajax_response




function xsyntax_shortcode( $atts = [], $content = '' )
{
    // $atts is likely small here, so lower cost doing this
    $atts = array_change_key_case( (array)$atts, CASE_LOWER);

    // for this shortcode, likely less inline code ... probably
    // don't want my buttons or lightbox, so check if they exist,
    // and if not, then set to them to default to false, rather
    // than be defaulted later to true
    if(!array_key_exists('buttons', $atts)){ $atts['buttons'] = 'false'; }
    if(!array_key_exists('lightbox', $atts)){ $atts['lightbox'] = 'false'; }
    if(!array_key_exists('light', $atts)){ $atts['light'] = '1'; }

    // for this shortcode (and aliases), we get the code inbetween shortcode tags
    // e.g. $content.  But xarta_highlight looks for array member 'outputcode'
    $atts['outputcode'] = xarta_remove_xprotect_pre_tags($content);
    return xarta_highlight( $atts );
}                                              //               *****************
add_shortcode('xsyntax', 'xsyntax_shortcode'); //            <<<====== xsyntax *
                                               //               ***************
function xarta_add_aliases()                   // ... and programmatically add aliases
{
    global $xartaLangs;
    foreach ($xartaLangs as $searchLang)
    {
        add_shortcode($searchLang, function( $atts = [], $content = '') use ($searchLang)
        {
            $atts['lang'] = "$searchLang";
            return xsyntax_shortcode( $atts, $content);
        });

    }
}
xarta_add_aliases();







function xarta_highlight( $atts ) 
{
    //echo "Debug, xarta_highlight, \$atts array:<br /><br />";
    //printArray($atts);

    do_action('x_enqueue_syntax_scripts');

    $atts = css_classname_and_instance_id($atts);
    $instanceID = $atts['instanceid'];

    // $options below requires that $atts have been massaged
    extract( attribute_massage( $atts )); // e.g. $outputcode is extracted, $options etc.

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
        $buttons = ' xarta-code-buttons ' . $instanceID;
    }
    else
    {
        $buttons = '';
    }

    // xarta-syntaxhighlighter-site-footer.js will look for this special <pre>
    $syntax = '<pre id="'.$instanceID.'" class="brush: \''.$lang.'\'; '.$options.' ">'.$outputcode.'</pre>';
    
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
        $codeoutput = '<div class="'.$wrap_classes.'">'.$syntax.'</div>';
    }
  


    return x_guid_to_squarebrackets($codeoutput).$caption;
}



