<?php
namespace xarta\syntaxhighlighter;
/**
 * Plugin Name: Xarta Syntaxhighlighter
 * Plugin URI: https://blog.xarta.co.uk
 * Description: Simple WordPress ajax implementation of https://github.com/syntaxhighlighter/syntaxhighlighter/wiki
 * Version: 0.0.1
 * Author: David Bayliss
 * Author URI: https://blog.xarta.co.uk
 * License: MIT
 */







    /**             *********\
    *               * WARNING *         * THIS IS MY FIRST PLUG-IN *
    *               ***********
    *
    *               &&,  not got around to learning PHP yet
    *               ||,  JavaScript, (properly). So very basic.
    */

    /**             *********\
    *               * PURPOSE *         * (PRIMARY) SYNTAX HIGHLIGHT RAW GITHUB CODE *
    *               ***********
    * 
    * -------------------------------------------------------------------------------------------
    * 1.) provide shortcodes to process and turn into <pre blah blah></pre> syntax
    *     to be picked-up by the Syntaxhighlighter javascript/css client
    *
    *     ... I've modified the build-output of syntaxhighlighter.js to provide
    *     ... a simple custom event bind to re-execute the highlighting
    *     ... to simplify ajax loading of code from GitHub
    *     ... - not using WordPress Ajax as I thought had to use admin stuff - but not sure
    *     ... - so anyway - using own ajax
    *
    * -------------------------------------------------------------------------------------------
    * 2.) fetch raw file code from GitHub (or use other code in an attribute*)
    *
    * -------------------------------------------------------------------------------------------
    * 3.) wrap output with other DIVS for use with other plug-ins
    *     Requires two other plug-ins: 
    *
    *         https://noorsplugin.com/2014/01/11/wordpress-colorbox-plugin/
    *
    *         https://en-gb.wordpress.org/plugins/forget-about-shortcode-buttons/
    *
    *         ... and my own JavaScript related to those plug-ins
    *
    */

    /**             *********\
    *               * TODO'S  *         * OVER-TIME *
    *               ***********
    *
    *   check limits of associative array member for holding raw GitHub data
    *   look into ABSPATH
    *   look into doing OOP properly, and autoloading
    *   NOTE TO SELF: Use A: drive in Xarta8
    */


if ( ! defined( 'WPINC' ) ) {
    die;
}

require 'xarta-syntaxhighlighter-attribute-checks.php';




    /**             *********\
    *               * ALIASES *         * ALIAS ARRAY FOR SHORTCODES *
    *               ***********
    *
    * c++ causes a problem in the admin section with tiny mce & add media button
    * (so use cpp instead ... but c# seems ok so far)
    * Only include aliases that the JavaScript SyntaxHighlighter knows about!
    * Programmatically add shortcodes based on this array later
    *
    ****************************************************************************************/
    $xartaLangs = array('code', 'bash', 'cpp', 'c#', 'php', 'sql', 'js', 'css', 'xml');
    /** ************************************************************************************/



    /**             *********\
    *               * ENQUEUE *         * ENQUEUE JS, CSS, & CHECK AJAX TEMPLATE EXISTS *
    *               ***********
    *
    * Split the enqueing as not everything needed at 'wp_enqueue_scripts' time
    * Generate a page template for my own ajax function, if it doesn't exist or is old expired version'
    *
    */

class Enqueue
{
    public function __construct()
    {
        // enqueue for every page
        add_action('wp_enqueue_scripts', array($this, 'enqueueAssetsEveryTime'));

        // e.g. only enqueue / do_action('x_enqueue_syntax_scripts'); in:
        //      xgithub_ajax_shortcode
        //      xarta_highlight
        add_action('x_enqueue_syntax_scripts', array($this, 'enqueueAssetsShortCode'));
        add_action('x_enqueue_syntax_scripts', array($this, 'xarta_setup_syntax_ajax_single_template'));
        // add_action('x_enqueue_syntax_scripts', array($this, 'xarta_setup_syntax_ajax_post'));  // TODO
    }

    public function enqueueAssetsEveryTime()
    {
        // create my own version codes
        $xarta_global_js_ver  = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'xarta-global-functions.js' ));
        $syntax_theme_css_ver = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'theme.css' ));
        $x_syntax_theme_css_ver = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'xarta-syntaxhighlighter-site-footer.css' ));
            
        // 
        wp_register_script( 'xarta_global_js', plugins_url( 'xarta-global-functions.js', __FILE__ ), array(), $xarta_global_js_ver );
        wp_register_script('xarta_syntaxhighlighter_site_footer', plugins_url('xarta-syntaxhighlighter-site-footer.js', __FILE__ ), 
            array('syntaxhighlighter'), true );

        wp_register_script('syntaxhighlighter', plugins_url( 'syntaxhighlighter.js', __FILE__ ), array('xarta_global_js'));

        wp_register_style( 'syntaxhighlighter_css',    plugins_url( 'theme.css',    __FILE__ ), array(),   $syntax_theme_css_ver, 'all' );
        wp_register_style( 'x_syntaxhighlighter_css',    plugins_url( 'xarta-syntaxhighlighter-site-footer.css',    __FILE__ ), 
            array(syntaxhighlighter_css),   $x_syntax_theme_css_ver, 'all' );

        // Only script I enqueue straight-away
        wp_enqueue_script( 'xarta_global_js' );   
    }

    public function enqueueAssetsShortCode()
    {
        wp_enqueue_style ( 'syntaxhighlighter_css' );
        wp_enqueue_style ( 'x_syntaxhighlighter_css' );
        wp_enqueue_script( 'xarta_syntaxhighlighter_site_footer' );
        wp_enqueue_script( 'syntaxhighlighter' );
    }

    public function xarta_setup_syntax_ajax_single_template()
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
            // TODO ... this without the leading white-space from the indentation somehow
            //      ... maybe an external file reference (require maybe)
            $ajax_post_template_for_my_xgithub_ajax_shortcode = 
            '<?php
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
            // END FILE
            $success = file_put_contents ( $ajax_post_template_file_name, 
                $ajax_post_template_for_my_xgithub_ajax_shortcode);
            // echo $success;
        }
    }

    public function xarta_setup_syntax_ajax_post()
    {
        // UNDER DEVELOPMENT / TODO / don't use yet!!!
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
} 

    /**             *************\
    *               * THE_CONTENT *         * PROTECT IT FROM BEING MASHED-UP *
    *               ***************
    *
    * TWO TASKS FOR the_content, AND ONE FOR SYNTAX HIGHLIGHTING:
    * <1> ----------------------------------
    *       I want to be be able to type in code, including shortcodes e.g. [js]
    *       or [js some-attributes]content[/js] etc. in <pre><code> whole-thing </code></pre>
    *       tags (sometimes), without being processed as shortcodes.  So to do that,
    *       I want to replace "<" and "[" in just those instances with "&lt;" and "&#91;"
    * 
    *       So, I mean, just the stuff between <pre><code> sandwich-filling </code></pre>
    *
    * <2> ----------------------------------
    *       WordPress by default does things like remove <p> with wpauto etc. during the_content.
    *       And, it won't know that [js some-attibutes]some-JavaScript[/js] should be immune.
    *
    *       So, in this task, every [js some-attributes]some-JavaScript[/js] (for example) gets
    *       wrapped like this:
    *
    *       [js some-attributes ]<pre class="xprotect">some-JavaScript</pre><!-- end xprotect -->[/js]
    *
    *       ... for example (same for any alias etc.)
    *
    *
    *         ****************************************************************************
    *       **** TASKS ONE AND TWO ARE COMBINED, AND ADDED TO WORDPRESS'S "the_content" ****
    *         ****************************************************************************
    *
    *
    * <3> ----------------------------------
    *       Later, as part of do_action ... in the shortcode that syntax-highlights the xprotect content,
    *       the <pre></pre> can be both easily identified by the class xprotect and the html comment,
    *       for easy removal before the response is delivered for the JavaScript syntaxhighlighter to do
    *       its thing.
    *
    */

class TheContent
{

    private $xartaCodesToCheck; // NULL TODO: ERROR CHECKING IN CONSTRUCTOR

    public function __construct($xartaLangs)
    {
            $this->xartaCodesToCheck = $xartaLangs;
            add_filter('the_content', array($this, 'xarta_before_the_content_normal_filters'), 4); // higher priority
    }

    public function xarta_before_the_content_normal_filters($content)
    {

        // TASK ONE (see notes above class)

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
                // nb this precludes nesting
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

        // TASK TWO (see notes above class)
        $potentialShortcode = strpos($content, '[');
        if($potentialShortcode !== FALSE)
        {

            array_push($this->xartaCodesToCheck, "xsyntax"); // additional shortcode to check (not language)

            foreach ($this->xartaCodesToCheck as $searchLang)
            {
                //$searchLang = 'code';
                if(strpos($content,'['.$searchLang, $potentialShortcode) !== FALSE)
                {

                    $searchString = '/\['.$searchLang.'(.*)\]/'; // https://regex101.com/
                    $replaceString = "[$searchLang $1 ]<pre class=\"xprotect\">";

                    // using preg_replace to cope with attributes (no wild card in str_pos)
                    // can't easily do the whole [shortcode atts]code-to-highlight[/shortcode]
                    // in one go though as it get's complicated when the shortcode appears
                    // more than once, successively (have to look at occurances etc.)
                    // and computationally gets expensive.  This is a compromise.
                    $content = preg_replace( $searchString, $replaceString , $content );
                    $content = str_replace('[/'.$searchLang.']', '</pre><!-- end xprotect -->[/'.$searchLang.']', $content);
                }
                //break;
            }
        }

        return $content;
    }

    // TASK THREE (see notes above class) KEEPING THIS ONE STATIC !!! (no dynamic refs)
    public static function xarta_remove_xprotect_pre_tags($code_content)
    {

        /*
        OLD METHOD ... using preg_replace: too expensive if large $code_content
        ... discovered it broke without increasing pcre.backtrack_limit
        ... e.g. ... ini_set('pcre.backtrack_limit', 99999999999);

        // https://regex101.com/

        // / (<pre class="xprotect">)((?s:.)*)(<\/pre>) /g etc.
        // ... group 1 = '<pre class="xprotect">'
        // ... group 2 = the code we want to restore without <pre> tags

        $searchString = '/((?:<\/p>\s)<pre class="xprotect">)((?s:.)*)(<\/pre>)(?:\s<p>)/';
        $code_content = preg_replace( $searchString, "$2", $code_content );
        */



        // new method ... changing xarta_before_the_content_normal_filters
        //                to make </pre> more identifable ... and just replace
        //                xprotect class <pre>, and identifiable </pre> here with ''

        
        if (strpos($code_content, "</p>\n<pre class=\"xprotect\">") !== FALSE)
        {
            // wpautop() on:
            $code_content = str_replace("</p>\n<pre class=\"xprotect\">", '', $code_content);
            $code_content = str_replace("</pre>\n<p><!-- end xprotect -->", '', $code_content);
        }
        else
        {
            // wpautop() off:
            $code_content = str_replace("<pre class=\"xprotect\">", '', $code_content);
            $code_content = str_replace("</pre><!-- end xprotect -->", '', $code_content);
        }

        return $code_content;
    }
}

    /**             ************\
    *               * SHORTCODES *         * add_shortcode *
    *               **************
    *
    * shortcodes for inline syntax highlighting, github raw file highlighting,
    * and github raw file retrieved by ajax calls (multiple within a post for example)
    * highlighting.  All the aliases use xsyntax_shortcode
    *
    */

class Shortcodes
{
    private $xartaLangs; // NULL TODO: ERROR CHECKING IN CONSTRUCTOR
    private $xartaSyntaxHLoutput;

    public function __construct($xartaLangs)
    {
        $this->xartaLangs = $xartaLangs;
        $this->xartaSyntaxHLoutput = new Output();

        add_shortcode('github',                 array($this, 'github_shortcode'));
        add_shortcode('cgithub',                array($this, 'cgithub_shortcode'));
        add_shortcode('xgithub',                array($this, 'xgithub_shortcode'));
        add_shortcode('xgithub_ajax',           array($this, 'xgithub_ajax_shortcode'));
        add_shortcode('xgithub_ajax_response',  array($this, 'xgithub_ajax_response_shortcode'));
        add_shortcode('xsyntax',                array($this, 'xsyntax_shortcode'));

        foreach ($this->xartaLangs as $searchLang)
        {
            add_shortcode($searchLang, function( $atts = [], $content = '') use ($searchLang)
            {
                $atts['lang'] = "$searchLang";
                return $this->xsyntax_shortcode( $atts, $content);
            });
        }
    }

    private function github_get_url($atts)
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

    public function github_shortcode( $atts = []) 
    {
        // TODO
        //  - set-up own Git server as fallback

        return file_get_contents($this->github_get_url($atts));
    }

    public function cgithub_shortcode( $atts = [] ) 
    {
        return  '<pre><div class="notjq">'. 
                    htmlspecialchars($this->github_shortcode($atts)) . 
                '</div></pre>';
    }

    public function xgithub_shortcode( $atts )
    {
        //global $xartaSyntaxHLshortcodes;
        $atts['outputcode'] = $this->github_shortcode($atts);
        $atts['title'] = $this->github_get_url($atts);

        //echo "xgithub_shortcode<br /><br />";
        //printArray($atts);
        

        // TODO TODO TODO HERE HERE HERE WILL BECOME INTERNAL REFERENCE?
        return $this->xartaSyntaxHLoutput->xarta_highlight( $atts );     
    }
    

    public function xgithub_ajax_shortcode( $atts = [])
    {
        //echo "Debug. This is \$atts array before encoding:<br /><br />";
        //printArray($atts);

        do_action('x_enqueue_syntax_scripts');

        $instance_id = 'xarta-id-'.trim(strval(uniqid()));
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

    public function xgithub_ajax_response_shortcode()
    {
        //global $xartaSyntaxHLshortcodes;
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

        return $this->xgithub_shortcode ($atts);
    }

    public function xsyntax_shortcode( $atts = [], $content = '' )
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
        $atts['outputcode'] = TheContent::xarta_remove_xprotect_pre_tags($content);
        //$atts['outputcode'] = $content;
        return $this->xartaSyntaxHLoutput->xarta_highlight( $atts );
    }
}

    /**             *********\
    *               * OUTPUT *         * GENERATE THE RESPONSE *
    *               **********
    *
    *   although any of the shortcodes could be used individually, 
    *   they mostly resolve to or get passed to:
    *
    *           __________________________________________________
    *           * xsyntax_shortcode( $atts = [], $content = '' )
    *           * xgithub_shortcode( $atts )
    *           --------------------------------------------------
    *
    *   ... they both eventually call xarta_hightlight ( $atts )
    *
    *   This is where syntax highlighting, colorbox/lightbox, and width/font buttons etc.
    *   get put together for output.
    *
    */

class Output
{
    public function __construct()
    {

    }

    // square bracket functions to prevent other shortcodes in 
    // the source file from being evaluated!
    private function x_squarebrackets_to_guid( $input)
    {
        $guid1 = 'f4cd1bfaa3fa49b28'.'984c326ab9b36d9';
        $guid2 = '984c326ab9b36d9'.'f4cd1bfaa3fa49b28';
        $step1 = str_replace('[',$guid1, $input);
        $step2 = str_replace(']',$guid2, $step1);
                            
        return $step2;
    }

    private function x_guid_to_squarebrackets( $step2)
    {
        $guid1 = 'f4cd1bfaa3fa49b28'.'984c326ab9b36d9';
        $guid2 = '984c326ab9b36d9'.'f4cd1bfaa3fa49b28';
        $step3 = str_replace($guid1,'[', $step2);
        $step4 = str_replace($guid2,']', $step3);
    
        return $step4;
    }

    // TODO: test if this is still the case (no longer using original WordPress plugin)
    private function fix_reference_issue($input) 
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

    public function xarta_highlight( $atts ) 
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
        
        $testoutput = $outputcode; // capture before anything done to it

        $outputcode = $this->x_squarebrackets_to_guid($outputcode);     // prevent shortcodes in code
                                                                        // from being evaluated in do_shortcode()
        $outputcode = $this->fix_reference_issue($outputcode);                 // TODO check if still necessary

        // my attribute $escapelt
        if($escapelt === 'true')
        {
            $outputcode = str_replace('<', '&lt;', $outputcode);
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
        else if($testmode === 'true')
        {
            $codeoutput = '<pre>'.$testoutput.'</pre>';
        }
        else
        {
            $codeoutput = '<div class="'.$wrap_classes.'">'.$syntax.'</div>';
        }
    


        return $this->x_guid_to_squarebrackets($codeoutput).$caption;
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




$xartaSyntaxHLenqueue =     new Enqueue();
$xartaSyntaxHLthecontent =  new TheContent($xartaLangs);
$xartaSyntaxHLshortcodes =  new Shortcodes($xartaLangs);