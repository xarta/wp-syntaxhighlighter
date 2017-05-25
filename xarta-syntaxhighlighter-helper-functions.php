<?php
namespace xarta\syntaxhighlighter;



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