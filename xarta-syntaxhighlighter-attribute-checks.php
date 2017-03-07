<?php

function attribute_massage ($atts)
{
    $raw_default = 'my-wp-code-snippets/master/default.php';
    $github_user_default = constrain_github_user('tell-me-default');
    $outputcode_default = '';
    $lang_default = 'code';
    $light_default = '0';

    $autolinks_default = 'true';


    $gutter_default = 'true';


    // for xarta_highlight function
    // WARNING WARNING:  will change semantics of some attributes
    // e.g. 'autolinks' => 'true' will become 'auto-links: 'true' ready for output insertion
    $atts_default = array(
            'raw' => $raw_default,
            'github_user' => $github_user_default,
            'outputcode' => $outputcode_default, // empty if using xgithub shortcode (which will populate it)
            'lang' => $lang_default,
            'light' => $light_default,
            'caption' => '',
            'title' => '',
            'autolinks' => $autolinks_default,
            'classname' => '',
            'firstline' => '1',
            'gutter' => $gutter_default,
            'highlight' => '',
            'htmlscript' => 'false',
            'smarttabs' => 'true',
            'tabsize' => '4',
            'escapelt' => 'true',
            'buttons' => 'true',
            'lightbox' => 'true'
    );

    // normalize attribute keys, lowercase
    $atts = array_change_key_case( (array)$atts, CASE_LOWER);
    // supply missing attributes from $atts_default
    $atts = shortcode_atts( $atts_default, $atts );

    if (!accept_lang($atts['lang']))
    {
        $atts['lang'] = $lang_default; 
    }
  	
    if ( !($atts['gutter'] === 'false') && !($atts['gutter'] === 'true') )
    {
        $atts['gutter'] = 'gutter: \''.$gutter_default.'\'; ';
    }
    else
    {
        $atts['gutter'] = 'gutter: \''.$atts['gutter'].'\'; ';
    }

		
    // compatible with WordPress attribute "light"
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
        // TO DO (trim etc. maybe)
    }

    if(!empty($atts['title']))
    {
        // TO DO (trim etc. maybe)
    }
    else
    {
        
    }

    if( !($atts['autolinks'] === 'true') && !($atts['autolinks'] === 'false'))
    {
        $atts['autolinks'] = 'auto-links: \''.$autolinks_default.'\'; ';
    }
    else
    {
        $atts['autolinks'] = 'auto-links: \''.$atts['autolinks'].'\'; ';
    }

    if(!empty($atts['classname']))
    {
        // TO DO (check legal/valid sanitisation)
        $atts['classname'] = 'class-name: \''.$atts['classname'].'\'; ';
    }
    else
    {
        $atts['classname'] = '';
    }

    // TODO - FIND OUT IF CONDITIONAL SHORTCUTS IN PHP - PERSONALLY TEST
    if( is_numeric($atts['firstline']) && is_int($atts['firstline']))
    {
        $atts['firstline'] = 'first-line: \''.$atts['firstline'].'\'; ';
    }
    else
    {
        $atts['firstline'] = '';

    }


    return $atts;

}



function accept_lang( $lang )
{
  	// limit available brushes to these aliases, default to "code" 
 	$langs = array('code', 'bash', 'c++', 'c#', 'php', 'sql', 'js', 'css');
  	$lang_found = false;
	  
	for($i = 0; $i < count($langs); $i++)
	{
	  	if ( $langs[$i] === $lang)
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