<?php

/**
 *  BEFORE looking at $atts array generally, the classname is scrutinised
 *         as it might need modifying. If supplied, it overrides the default.
 *         The default is also a custom name set in syntaxhighlighter config.
 *         If empty in $atts, then the default is used.
 *  HOWEVER ... still a xarta requirement to provide a unique ID per "instance"
 */
function css_classname_and_instance_id ($atts)
{
    $instanceID = xarta_get_instance_id();  // unique id for every "instance"
    $customClassName = 'xarta-big-code';    // also set in JavaScript header - 
                                            // syntaxhighlighter config

    $atts = array_change_key_case( (array)$atts, CASE_LOWER);
    if(!array_key_exists('classname', $atts)){ $atts['classname'] = ''; }

    if(!empty($atts['classname']))
    {
        // over-ride custom classname with what was provided in shortcode attributes
        // will override JavaScript-set classname (syntaxhighlighter config) too
        $atts['classname'] = $atts['classname'] . " $instanceID";
    }
    else
    {
        $atts['classname'] = "$customClassName $instanceID";
    }

    $atts['instanceid'] = $instanceID; // appending to array
 
    return $atts;   // only possibly added/modified "classname", and added "instanceid"
                    // not touched anything else yet in the array
                    // except made all keys lower-case
}
// *****************************


const THIS_IS_MY_ATT = true;
const NOT_MY_ATT = false;

const SYNTAX_DEFAULT_TRUE = true;
const SYNTAX_DEFAULT_FALSE = false;

function attribute_massage ($atts)
{
    $raw_default = 'my-wp-code-snippets/master/default.php';
    $github_user_default = constrain_github_user('tell-me-default');
    $outputcode_default = '';
    $lang_default = 'code';
    $light_default = '0';
    $caption_default = '';
    $title_default = '';
    $autolinks_default = 'true';
    $classname_default = '';
    $firstline_default = '1';
    $gutter_default = 'true';
    $highlight_default = '';
    $htmlscript_default = 'false';
    $smarttabs_default = 'true';
    $tabsize_default = '4';
    $escapelt_default = 'true';
    $buttons_default = 'true';
    $lightbox_default = 'true';
    $testmode_default = 'false';


    // for xarta_highlight function
    // WARNING WARNING:  will change semantics of some attributes
    // e.g. 'autolinks' => 'true' will become 'auto-links: 'true' ready for output insertion
    $atts_default = array(
            'raw' => $raw_default,
            'github_user' => $github_user_default,
            'outputcode' => $outputcode_default, // empty if using xgithub shortcode (which will populate it)
            'lang' => $lang_default,
            'light' => $light_default,
            'caption' => $caption_default,
            'title' => $title_default,
            'autolinks' => $autolinks_default,
            'classname' => $classname_default,
            'firstline' => $firstline_default,
            'gutter' => $gutter_default,
            'highlight' => $highlight_default,
            'htmlscript' => $htmlscript_default,
            'smarttabs' => $smarttabs_default,
            'tabsize' => $tabsize_default,
            'escapelt' => $escapelt_default,
            'buttons' => $buttons_default,
            'lightbox' => $lightbox_default,
            'testmode' => $testmode_default
    );

    // normalize attribute keys, lowercase
    // $atts = array_change_key_case( (array)$atts, CASE_LOWER); // already done

    // supply missing attributes from $atts_default (and limit to atts_default)
    $atts = shortcode_atts( $atts_default, $atts );
  	
    $atts = true_false_sanitization($atts, 'gutter', 'gutter', $gutter_default, SYNTAX_DEFAULT_TRUE, NOT_MY_ATT);
    $atts = true_false_sanitization($atts, 'autolinks', 'auto-links', $autolinks_default, SYNTAX_DEFAULT_TRUE, NOT_MY_ATT);
    $atts = true_false_sanitization($atts, 'htmlscript', 'html-script', $htmlscript_default, SYNTAX_DEFAULT_FALSE, NOT_MY_ATT);
    $atts = true_false_sanitization($atts, 'smarttabs', 'smart-tabs', $smarttabs_default, SYNTAX_DEFAULT_TRUE, NOT_MY_ATT);
    $atts = true_false_sanitization($atts, 'escapelt', 'escape-lt', $escapelt_default, SYNTAX_DEFAULT_FALSE, THIS_IS_MY_ATT);
    $atts = true_false_sanitization($atts, 'buttons', 'buttons', $buttons_default, SYNTAX_DEFAULT_FALSE, THIS_IS_MY_ATT);
    $atts = true_false_sanitization($atts, 'lightbox', 'lightbox', $lightbox_default, SYNTAX_DEFAULT_FALSE, THIS_IS_MY_ATT);
    $atts = true_false_sanitization($atts, 'testmode', 'testmode', $testmode_default, SYNTAX_DEFAULT_FALSE, THIS_IS_MY_ATT);


    if (!accept_lang($atts['lang']))
    {
        $atts['lang'] = $lang_default; 
    }


    // compatible with standard WordPress Syntaxhighlighter plug-in attribute "light"
    // override "gutter"
    if ($atts['light'] === '1')
    {
        $atts['gutter'] ='gutter: \'false\'; '; 
    }
    else if ($atts['light'] === '0')
    {
        $atts['gutter'] = 'gutter: \'true\'; '; 
    }
    else
    {
        $atts['light'] = $light_default;
    }

    if(!empty($atts['caption']))
    {
        // TODO (trim etc. maybe), check length etc.
        // ... escape?
        // TODO Any injection possibilities here?
        $atts['caption'] = '<div class="xcaption">'.$atts['caption'].'</div>';
    }
    else
    {
        // I know, I know ... css vs content ... just seems
        // convenient to do this here.  If no caption, then
        // I want a line-break between the bottom of the code
        // and succeeding content that's not stripped-out or mangled
        // ... I think it's good to see the <br /> in source too;
        // ... I think there's some semantic meaning of sorts
        $atts['caption'] = '<br />';
    }

    if(!empty($atts['title']))
    {
        // TODO (trim etc. maybe), check length etc.
        // TODO ... escape?
        // TODO Any injection possibilities here?
        $atts['title'] = 'title: \''.$atts['title'].'\'; ';
    }
    else
    {
        $atts['title'] = $title_default;
    }


    if(!empty($atts['classname']))
    {
        // TODO (check legal/valid sanitisation)
        $atts['classname'] = 'class-name: \''.$atts['classname'].'\'; ';
    }
    else
    {
        // default set anyway in site header with JavaScript
        // syntaxhighlighterConfig ... className, so leave empty
        // $atts['classname'] = $classname_default;
    }





    // TODO - FIND OUT IF CONDITIONAL SHORTCUTS IN PHP - PERSONALLY TEST
    // TODO - FACTOR OUT THIS FUNCTION FOR BOTH firstline AND tabsize
    if( is_numeric($atts['firstline']) && is_int($atts['firstline']))
    {
        $atts['firstline'] = 'first-line: \''.$atts['firstline'].'\'; ';
    }
    else
    {
        $atts['firstline'] = '';    // default is 1 anyway!
                                    // I mean, on the client side
        // $atts['firstline'] = 'first-line: \''.$firstline_default.'\'; ';;
    }

    if( is_numeric($atts['tabsize']) && is_int($atts['tabsize']))
    {
        // TODO check limits on tabsize e.g. 1 to 10 or something?
        $atts['tabsize'] = 'tab-size: \''.$atts['tabsize'].'\'; ';
    }
    else
    {
        $atts['tabsize'] = '';      // default is 4 anyway!
                                    // I mean, on the client side
        // $atts['firstline'] = 'first-line: \''.$firstline_default.'\'; ';;
    }

    if(!empty($atts['highlight']))
    {
        // TODO ... THIS ONE IS A BIT TRICKY
        $atts['highlight'] = 'highlight: \''.$atts['highlight'].'\'; ';
    }


    return $atts;

}

function true_false_sanitization($atts, $att_key, $att_dash_case, $my_default, $syntax_default, $my_att)
{

    // https://github.com/syntaxhighlighter/syntaxhighlighter/wiki/Configuration     
    // I also have my own attributes to sanitize and pass along.

    if( !($atts[$att_key] === 'true') && !($atts[$att_key] === 'false'))
    {
        if( ($my_default === $syntax_default) && !$my_att)
        {
            $atts[$att_key] = ''; // let front-end syntax client assume default
        }
        else if( !$my_att)
        {
            $atts[$att_key] = $att_dash_case.': \''.$my_default.'\'; ';
        }  
        else
        {
            $atts[$att_key] = $my_default;
        }
    }
    else if( !$my_att)
    {
        $atts[$att_key] = $att_dash_case.': \''.$atts[$att_key].'\'; ';
    } 
    else
    {
        // my attribute already set to true or false
    }  

    return $atts;

}


function accept_lang( $lang )
{
  	// limit available brushes to these aliases, default to "code" 
    // $xartaLangs is global  (at least to plugin)
    global $xartaLangs;
 	
  	$lang_found = false;
	  
	for($i = 0; $i < count($xartaLangs); $i++)
	{
	  	if ( $xartaLangs[$i] === $lang)
		{
		 	$lang_found = true; 
		  	break;
		}
	}
  
  	return $lang_found;
}


function constrain_github_user( $github_user)
{
    $github_users = array('davros1973', 'xarta');
    $github_user_default = 'davros1973';

 	for($i = 0; $i < count($github_users); $i++)
	{
	  	if ( $github_users[$i] === $github_user)
		{
		 	return $github_user;
		}
	}   
    
    return $github_user_default;
}

?>