<?php


function fix_reference_issue($input) {
    if (strpos($input,'/// <reference',0)===0) {
	  return preg_replace('/^\/\/\/ <reference.+" \/>/', 
        "/* Dave has auto-removed visual studio code typings reference \n   as it upsets syntax highlighter evolved */ ", $input); }
    else {
	  return $input; } 
}

// square bracket functions to prevent other shortcodes in the source file from being evaluated!
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


?>