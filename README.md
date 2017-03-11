# wp-syntaxhighlighter
My first **WordPress plug-in** attempt; a mash-up and extension for ajax-github-file syntax highlighting

## WARNING       ... THIS IS STILL ALPHA - UNDER DEVELOPMENT
### *just* motivated by wanting functionality on my [own site](https://blog.xarta.co.uk)

* I take the css and js dist files from (*the classic*) https://github.com/syntaxhighlighter/syntaxhighlighter and "hack" the js file a little
* I take a [colorbox WordPress plug-in](https://noorsplugin.com/2014/01/11/wordpress-colorbox-plugin/)
* I take a [CSS buttons plug-in](https://en-gb.wordpress.org/plugins/forget-about-shortcode-buttons/)
* I make my own plug-in
* Add my own JavaScript to the mix
* Make a custom template for a single post (just to return *the_content*)
* Add one of my shortcodes to a post based on that template (that responds to Ajax requests)

And, Bob's your Uncle ... syntax highlighting of GitHub raw files retrieved optionally via Ajax request, or Syntax highlighting of code pasted into open shortcodes in the WordPress post, and with optional Width size, Fontsize, or fullscreen potentially Lightbox effect for the highlighted code.  TODO: button to toggle line-numbering on/off.

## MAJOR ISSUES: 

* still in global scope (in WordPress) ... at least affects (*as in disables*) the "add media" button in the post-editor
* still have to manually add a post with a custom template for the ajax response shortcode, and a path to it *although the template's automated now*
* still have to manually set other variables (and still uses some global variables) etc.  
* no admin part yet    

## Adds shortcodes (in WordPress):

Shortcode                   | Enlosed | Description
--------------------------- | ------- | -----------
[github raw='path to GitHub raw file'] | enclosed | URL constrained in the plug-in.
[cgithub raw='ditto path'] | enclosed | Wrapper for github shortcode ... returns raw file escaped in &lt;pre&gt; tags.
[xgithub ... options ... ] | enclosed | Wrapper for github shortcode, combining the file returned with options intended <br /> for https://github.com/syntaxhighlighter/syntaxhighlighter. The output structured <br /> according to my addtional options, invoking the lightbox shortcode if required, and <br /> providing css classes & id in preparation for JavaScript client-side processing.
[xgithub_ajax ... options] | enclosed | Encodes and outputs info to make an ajax request, json/base64 encoding <br /> [xgithub ... options ...] shortcode.
[xgithub_ajax_response_shortcode] | enclosed | Checks &#36;&#95;POST and decodes [xgithub ... options ...] to <br /> recreate the shortcode for processing, for a response. nb: this shortcode must run <br /> on a custom post type that only responds with WordPress **the_content**
[xsyntax ... options ...]inline-code[/xsyntax] | open | exactly like the [xgithub] shortcode, except uses inline code rather <br /> than a GitHub URL for raw files.
[css ... options ...]inline-code[/css] | open | aliases e.g. css, c#, js, php and so on ... aliases for <br />[xsyntax ... options ...]inline-code[/xsyntax]
 
 
 
As well as the https://github.com/syntaxhighlighter/syntaxhighlighter/wiki/Configuration options that just get output for syntaxhighlighter.js to pick-up on, I add:
* buttons="true"  *or "false"*
* lightbox="true" *or "false"*
* caption="like an image caption"
* light = "1"&nbsp;&nbsp;*or "0"* This is for compatibility with the [existing WordPress Syntaxhighlighter Evolved plug-in](https://en-gb.wordpress.org/plugins/syntaxhighlighter/)

Buttons tell my JavaScript to prepend some [CSS buttons](https://en-gb.wordpress.org/plugins/forget-about-shortcode-buttons/) for width and font-size.  "lightbox" wraps the hightlighted output so that a magnifying glass button will open that output in a lightbox  [(I just use the a colorbox plugin.)](https://noorsplugin.com/2014/01/11/wordpress-colorbox-plugin/) If 'caption' is non-empty it get's output by the plug-in immediately after the highlighted code with a css class styled like a "caption".

**note** my shortcode options are all of the form *something='blah'* **where** *something* is **not** *dash-case*. However my plug-in code translates options as required to dash-case as expected by syntaxhighlighter.js.

## My superset of options *with examples*

option | Example value or default or explanation
------ | ------------------------
raw=   | 'my-wp-code-snippets/master/github.php'
github_user= | 'davros1973'
outputcode= | '' *not normally used by the end-user: either a raw GitHub file is <br /> loaded in there, or content in open shortcodes, normally*
lang= | 'code' *default* e.g. *js*, *css* ... *I only use a subset, decided in an array in the plug-in*
light= | '1'
caption= | '*html string styled under the highlighted code like an image caption*'
title= | '*github raw file URL* or *as assigned*'
autolinks= | 'true' [*see syntaxhighlighter config wiki*](https://github.com/syntaxhighlighter/syntaxhighlighter/wiki/Configuration)
classname= | '*over-rides default*' ... my default is *xarta-big-code*
firstline= | '1' [*see syntaxhighlighter config wiki*](https://github.com/syntaxhighlighter/syntaxhighlighter/wiki/Configuration)
gutter= | 'true' *e.g. line number column*
highlight= | '' [*see syntaxhighlighter config wiki*](https://github.com/syntaxhighlighter/syntaxhighlighter/wiki/Configuration)
htmlscript= | 'false' [*see syntaxhighlighter config wiki*](https://github.com/syntaxhighlighter/syntaxhighlighter/wiki/Configuration)
smarttabs= | 'true' [*see syntaxhighlighter config wiki*](https://github.com/syntaxhighlighter/syntaxhighlighter/wiki/Configuration)
tabsize= | '4' [*see syntaxhighlighter config wiki*](https://github.com/syntaxhighlighter/syntaxhighlighter/wiki/Configuration)
escapelt= | 'true' *my option - for escaping &lt'*
buttons= | 'true' (default) for GitHub raw files, 'false' for [xsyntax ... ]*code*[/xsyntax] <br /> code (or aliases)
lightbox= | 'true' *ditto*

## Example usage:
[xgithub lang='php' raw='my-wp-code-snippets/master/github.php' caption='&lt;em&gt;this is an example&lt;/em&gt;' ]

TODO: *examples of the ajax stuff and other shortcodes*

---
 
 => I've built the js and css as per https://github.com/syntaxhighlighter/syntaxhighlighter
    	... but just set-up a jQuery custom event in the build (for now) ...    
   
```javascript 
// **********************************************************************************
// Little addition to built js by David Bayliss March 2017; blog.xarta.co.uk
// I just wanted to be able to kick-off the highlighting stuff again at any time
// AFTER domready, so I can bring-in new code with ajax e.g. lots of different code
// samples pulled in from elsewhere e.g. github
// (I'm still green at JS and didn't want to get into CommonJS, Webpack and importing
// modules and all that at this time - I don't even use SASS or LESS etc. yet!!!
// - so ignoring the "default" export / API (for now), breaking encapsulation/portability
// - although introducing event decoupling

jQuery(document).ready(function($) 
{
//var codeDownloadTimeout;

/**
 * expecting eventData to be '', or eventData[0] to be native element(s)
			e.g. $( "body" ).trigger( "moreCodeEventStarted", [ $(id).get() ] ); 
... 	where id is of the form: '#xarta-id-xxxxxxxxxxxxx' - unique element id
... 	and $ is jQuery alias ... so can now use targeting an Ajax-loaded-element!
*/

$( "body" ).on( "moreCodeEventStarted", function( event, eventData ) 
{
			console.log("MORE CODE EVENT STARTED, eventData[0]="+eventData[0]);
		//clearTimeout(codeDownloadTimeout);
		//codeDownloadTimeout = setTimeout(function() 
		//{
			(0, _domready2.default)(function () 
			{
					var $highlightResult = _core2.default.highlight(dasherize.object(window.syntaxhighlighterConfig || {}), eventData[0]);
					$( "body" ).trigger( "moreCodeEventEnded", eventData[0] );
					return $highlightResult;
			});
		//}, 250);
});

// Once the DOM is loaded, trigger this event, setting element to null
// so highlight will do it's thing for ALL elements (with syntaxhighlighter class)
var dom_element = '';
$( "body" ).trigger( "moreCodeEventStarted", [ dom_element ] ); // do on domready etc.  
});
// ***********************************************************************************

// COMMENTED OUT BY David Bayliss March 2017 - SEE ABOVE
//(0, _domready2.default)(function () {
//  return _core2.default.highlight(dasherize.object(window.syntaxhighlighterConfig || {}));
//});
```
