# wp-syntaxhighlighter
My first WordPress plug-in attempt; a mash-up and extension for ajax-github-file syntax highlighting

## WARNING       ... THIS IS STILL ALPHA - UNDER DEVELOPMENT
### *just* motivated by wanting functionality on my [own site](https://blog.xarta.co.uk)  

## MAJOR ISSUES: 

* still in global scope (in WordPress) ... at least affects the "add media" button  
* still have to manually add a post with a custom template for the ajax response shortcode, and a path to it   
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
 
 
 
As well as the https://github.com/syntaxhighlighter/syntaxhighlighter options, I add:
* buttons="true"
* lightbox="true" 	

Buttons tell my JavaScript to prepend some CSS buttons for width and font-size.  "lightbox" wraps the hightlighted output so that a magnifying glass button will open that output in a lightbox  (I just use the a colorbox plugin: https://noorsplugin.com/2014/01/11/wordpress-colorbox-plugin/ ).  I also keep the ' light="1" ' option as the existing WordPress syntaxhighlighter evolved plug-in uses it.

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
