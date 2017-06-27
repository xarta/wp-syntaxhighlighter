
/**
 *  Started 2017, February. https://blog.xarta.co.uk   TODO - PARTICULAR POST
 *  Licence MIT - see: https://github.com  TODO - LINK LICENCE ON GITHUB
 *  Part of: https://github.com  TODO - LINK TO REPO ON GITHUB
 * 
 *  @version
 *  TODO: use above as placeholder for inserting version here during build process
 *  @copyright
 *  Copyright (C) 2017 David Bayliss.
 */

/**
 * xarta-syntaxhighlighter-site-footer.js
 * part of WordPress plugin I'm making that uses a few plug-ins to work together
 * along with a slightly "hacked" build of the classic syntaxhighlighter browser-JavaScript:
 * https://github.com/syntaxhighlighter/syntaxhighlighter
 * 
 * Goals:
 *  WordPress shortcodes incl. shortcodes for GitHub raw files
 *  Attributes/defaults for lightbox (colorbox)
 *  Controls for Width, Font-size, and eventually line-number toggeling
 *  (per highlighted instance)
 *  Eventually, a strip-and-collapse comments button
 *  Eventually, a strip-and-collapse lines-starting-with button (e.g. my clog function)
 *  Shortcodes for safe ajax loading of raw GitHub files to be highlighted
 */

console.log("**************************************************");
console.log("* SCRIPT: xarta-syntaxhighlighter-site-footer.js *");
console.log("**************************************************");

// GLOBAL VARS/FUNCTIONS IN "xarta-global-functions.js"
// VARS:
//      extraDebug
//
// FUNCTIONS:
//      clog(msg_str, extra_debug_level);
//      xarta_ajax(url, responseFunction, postString);


// IMPORTANT: although using document-ready here ... remember that class names / divs
//            are later set-up by the syntaxhighlighter.js script ... so can't pre-pend buttons
//            for example to divs/classes that don't exist yet!


var oldOrientation;


jQuery(document).ready(function($) {

    
    function getOrientation()
    {
        if(window.innerHeight > window.innerWidth)
        {
            return 'portrait';
        }
        else
        {
            return 'landscape';
        }
    }

    
    oldOrientation = getOrientation();

    // this will overwrite and undo any manual width changes with width buttons
    function resizeSyntaxHighlightInstance(xartaWidthControl, xartaInstanceID)
    {

        clog("resizeSyntaxHighlightInstance(xartaWidthControl, xartaInstanceID): ", 1);

        var getCSSmediaQsize;
        var instanceWidth;
        getCSSmediaQsize = $().mediaqNum();
        if (getCSSmediaQsize <= 480)
        {
            clog("Trying to set syntaxhighlighter instance width wide", 1);
            instanceWidth = "110%";
            $(xartaWidthControl+xartaInstanceID).css('margin-left','-5%');
        }
        else
        {
            instanceWidth = "100%";
            $(xartaWidthControl+xartaInstanceID).css('margin-left','0');
        }

        $(xartaWidthControl+xartaInstanceID).width(instanceWidth);

        // doing this here because of convenience for blog.xarta.co.uk
        // using free version of menutab - which isn't responsive
        // this should fix the main issue on low res. screens for me

        if (window.innerWidth >= 360)
        {
            clog("Attempting to add tabs to menucool", 1);
            $("ul.xartaPlaceHolder01").addClass("tabs");
            $("ul.tabs").removeClass("xartaPlaceHolder01");
        }
        else
        {
            clog("Attempting to REMOVE tabs FROM menucool", 1);
            $("ul.tabs").addClass("xartaPlaceHolder01");
            $("ul.tabs").removeClass("tabs");
        }
    }

    // call codeButtonLabelChange from jQuery window resize event, and pass as a callback
    // to the function that pre-pends the buttons and sets-up the on-click events
    // ** labels: if tight fit, use "f" for "font", "w" for "width" etc. **
    function codeButtonLabelChange(xartaWidthControl, xartaInstanceID)
    {
 
        clog("codeButtonLabelChange,"+  " xartaWidthControl = "+xartaWidthControl+
                                        " xartaInstanceID = "+xartaInstanceID, 1);




        var curxartaWidthControlPx = parseInt($(xartaWidthControl+xartaInstanceID).width());
        var small = curxartaWidthControlPx < 500;
        var labelFont = small ? "f" : "font";
        var labelWidth = small ? "w" : "width";

        clog('curxartaWidthControlPx = '+curxartaWidthControlPx, 1);
        clog(xartaInstanceID+' button labels: '+labelFont+' , '+labelWidth, 1);

        $(xartaInstanceID+'.increase-font').html(labelFont); // classes target css buttons
        $(xartaInstanceID+'.decrease-font').html(labelFont);
        $(xartaInstanceID+'.inflate-code').html(labelWidth);
        $(xartaInstanceID+'.shrink-code').html(labelWidth); 
    };  


    // buttons only exist after prepended by JQuery
    function renderCodeButtons(
                                codeButtonLabelChange, 
                                xartaBigCodeTarget, 
                                xartaWidthControl, 
                                xartaCodeButtons,
                                xartaInstanceID)
    {    
        clog("renderCodeButtons,"+  " xartaBigCodeTarget = "+xartaBigCodeTarget+
                                    " xartaWidthControl = "+xartaWidthControl+
                                    " xartaCodeButtons = "+xartaCodeButtons+
                                    " xartaInstanceID = "+xartaInstanceID+
                                    " jQuery().mediaqNum() = "+jQuery().mediaqNum(), 1);
        

        resizeSyntaxHighlightInstance(xartaWidthControl, xartaInstanceID);

        var xartaInstanceIDnoDot = xartaInstanceID.substring(1);
        clog("xartaInstanceIDnoDot = "+xartaInstanceIDnoDot,1);

        // button to control font size, and button to control width of code displayed
        // include the xartaInstanceID for each of them, so they can be targted by css
        // later in codeButtonLabelChange function (e.g. f vs font, w vs width labels)
        var myprependFontBtns = ' <a class="fasc-button fasc-size-large fasc-type-glossy fasc-ico-before dashicons-plus' +
            ' '+xartaInstanceIDnoDot+' increase-font" style="background-color: #000; color: #ffffff;" data-fasc-style="background-color' + 
            ':#000;color:#ffffff;"></a> <a class="fasc-button fasc-size-large fasc-type-glossy fasc-ico-before' +
            ' dashicons-minus '+xartaInstanceIDnoDot+' decrease-font" style="background-color: #000; color: #ffffff;" ' +
            ' data-fasc-style="background-color:#000;color:#ffffff;"></a>';
   
        var myprependWidthBtns = '<a class="fasc-button fasc-size-large fasc-type-glossy fasc-ico-before dashicons-plus' +
            ' '+xartaInstanceIDnoDot+' inflate-code" style="background-color: #000; color: #ffffff;" data-fasc-style="background-color' +
            ':#000;color:#ffffff;"></a> <a class="fasc-button fasc-size-large fasc-type-glossy fasc-ico-before' +
            ' dashicons-minus '+xartaInstanceIDnoDot+' shrink-code" style="background-color: #000; color: #ffffff;" ' +
            ' data-fasc-style="background-color:#000;color:#ffffff;"></a>';

        // TODO another (single) button ... a toggle for line-numbering in the output
        //      it would involve reloading that instance's code & rehighlighting etc.

        if($(xartaInstanceID+'.increase-font').length<1)
        {
            $(xartaCodeButtons+xartaInstanceID).prepend(myprependFontBtns);
        }
        if($(xartaInstanceID+'.inflate-code').length<1)
        {
            $(xartaCodeButtons+xartaInstanceID).prepend(myprependWidthBtns);
        }
        
        $(xartaInstanceID).removeClass(xartaCodeButtons); // render once only

        
        codeButtonLabelChange(xartaWidthControl, xartaInstanceID); // do after render


        // Set-up the on-click events for the buttons
        // initial css style sheet uses @media queries ...
        // ems as a fallback, and vws if supported
        // but once manually changing here, px should be fine

        $("a"+xartaInstanceID+".decrease-font").on( "click", function() 
        {
            var curSize = parseInt($(xartaBigCodeTarget+xartaInstanceID).css('font-size'));
            clog("curSize: "+curSize,1);
            curSize = curSize - 1;
            // alternative way of doing this - keep for reference
            $( xartaBigCodeTarget+xartaInstanceID ).attr('style', 'font-size:'+ curSize +'px !important' );
            clog("Decrease font - "+xartaBigCodeTarget+xartaInstanceID, 1);           
        });

        $("a"+xartaInstanceID+".increase-font").on( "click", function() 
        {
            var curSize = parseInt($(xartaBigCodeTarget+xartaInstanceID).css('font-size'));
            curSize = curSize + 1;
            // alternative way of doing this - keep for reference
            $( xartaBigCodeTarget+xartaInstanceID ).each(function () 
            {
                this.style.setProperty( 'font-size', curSize +'px', 'important' );
            });
            clog("Increase font - "+xartaBigCodeTarget+xartaInstanceID, 1);
        });


        // on-click events for changing the width of the code-presentation area
        // this will also change the labels of the buttons if required
        // (codeButtonLabelChange was passed to renderCodeButtons to include in closure)

        /**
         * TODO: This way of detecting menutab or code-extracts container is SILLY
         *       Need to look at using DOM parents for current syntaxhighlighter instance thingy
         *       REMEMBER: do twice ... shrinking and expanding code!  
         *       AS-IS ... if I use both a menutab and/or a code-extracts div and/or just in 
         *      .entry-content div then I'm going to have a problem.
         */

        $("a"+xartaInstanceID+".shrink-code").on( "click", function() 
        {
            var probableContainerForPercentCalcs; // using WordPress menutab plugin sometimes

            if($(".tabcontents").length)
            {
                clog("Ok: seem to be in a menutab", 1);
                probableContainerForPercentCalcs = ".tabcontents";
                // so that new width % doesn't end-up less than current width %    
            }
            else if($(".code-extracts").length)
            {
                clog("Ok: seem to be in a code-extracts div", 1);
                probableContainerForPercentCalcs = ".code-extracts";
                // so that new width % doesn't end-up less than current width % 
            }
            else
            {
                // .entry-content class particular to this WordPress theme (maybe) TODO CHECK?
                probableContainerForPercentCalcs = ".entry-content";    
            }

            var curContainerWidthPx = parseInt($(probableContainerForPercentCalcs).width());
            //var curWinWidthPx = parseInt($(window).width()+"px");

            // in this case, xartaWidthControl same DIV as xarta-code-buttons class
            // so WILL have xartaInstanceID
            var curWidth = parseInt($(xartaWidthControl+xartaInstanceID).width());
            var fivePercent = curContainerWidthPx/20;
            var newWidthPx = curWidth-fivePercent;
         
            // IMPORTANT: keep as percentage e.g. orientation on mobile device
            var newWidthPercent = newWidthPx/curContainerWidthPx*100;

            $(xartaWidthControl+xartaInstanceID).width(newWidthPercent+"%");
            codeButtonLabelChange(xartaWidthControl, xartaInstanceID); // in click event closure
        });


        $("a"+xartaInstanceID+".inflate-code").on( "click", function() 
        {
            var probableContainerForPercentCalcs;

            if($(".tabcontents").length)
            {
                clog("Ok: seem to be in a menutab",1);
                probableContainerForPercentCalcs = ".tabcontents";
                // so that new width % doesn't end-up less than current width %    
            }
            else if($(".code-extracts").length)
            {
                clog("Ok: seem to be in a code-extracts div", 1);
                probableContainerForPercentCalcs = ".code-extracts";
                // so that new width % doesn't end-up less than current width % 
            }
            else
            {
                probableContainerForPercentCalcs = ".entry-content";    
            }

            var curContainerWidthPx = parseInt($(probableContainerForPercentCalcs).width());
            clog("curContainerWidthPx: "+curContainerWidthPx,1);
            //var curWinWidthPx = parseInt($(window).width()+"px");
            //clog("curWinWidthPx: "+curWinWidthPx, 1);
            var curWidth = parseInt($(xartaWidthControl+xartaInstanceID).width());
            clog("curWidth: "+curWidth,1);
            var fivePercent = curContainerWidthPx/20;
            clog("fivePercent: "+fivePercent,1);
            var newWidthPx = curWidth+fivePercent;
            clog("newWidthPx: "+newWidthPx,1);
            var oldWidthPercent = curWidth/curContainerWidthPx*100;
            clog("oldWidthPercent: "+oldWidthPercent,1);
            var newWidthPercent = newWidthPx/curContainerWidthPx*100;
            clog("newWidthPercent: "+newWidthPercent, 1);

            $(xartaWidthControl+xartaInstanceID).width(newWidthPercent+"%");
            codeButtonLabelChange(xartaWidthControl, xartaInstanceID); // in click event closure
        });
    
    }; // end renderCodeButtons()

    // generic css classes from xarta-syntaxhighlighter.php (defaults)
    // TODO generate JavaScript script from the plugin php script to include default vars from there?

    var xartaBigCodeTarget = ".xarta-big-code";         // custom syntaxhighlighter class, optional
    var xartaWidthControl = ".xarta-syntax-highlight";  // containing DIV
    var xartaCodeButtons = ".xarta-code-buttons";       // if present, will render buttons
                                                        // (css class with css attr "xarta-syntax-highlight")

    var xartaAjaxTarget = '.xarta-target-ajax';         // ONLY needed for automated ajax DIVS         
    
    // this is default (have to set dynamically per instance)
    var xartaInstanceID = '';                           // css class set in xarta-syntaxhighlighter.php
                                                        // per instance, of form:
                                                        // xarta-id-58c00bfabbfcd (random number)

                        // e.g. outer-DIV: (if buttons required)
                        // xarta-syntax-highlight xarta-code-buttons xarta-id-58c00bfabbfcd

                        // e.g. outer-DIV: (if no buttons required)
                        // xarta-syntax-highlight

                        // e.g. inner-DIV: (default classname)
                        // syntaxhighlighter xarta-big-code xarta-id-58c00bfabbfcd

                        // jQuery intersect selections (no space inbetween)
                        // e.g. $(xartaBigCodeTarget+xartaInstanceID)
                        // &    $(xartaCodeButtons+xartaInstanceID)
                        // ... generic if no xartaInstanceID (select all instances)


    // => and if an ajax shortcode is used, to load a GitHub raw file ...

        // <div class="xarta-target-ajax xarta-id-58c00bfabbfcd" 
        //          data-url="URL-TO-MY-WORDPRESS-POST-WITH-AJAX-SHORTCODE-TO-PROVIDE-RESPONSE"
        //          data-post="BASE-64-URL-FRIENDLY-ENCODED-IN-PHP--DETAILS-">
        //          LOADING CODE FROM GITHUB VIA AJAX </div>

    // => this will be replaced by ajax response, to be dealt with like any other instance
    


    // trigger this event in syntaxhighlighter.js when highlighting done
    //   (script in syntaxhighlighter.js is self-invoking etc. - actually a "module")
    //   (this is where I've hacked the build a little: TODO - change source)
    // nb on first page load, will be little bit after document ready
    // TODO THINK-ABOUT: if no xartaInstanceID's do I want all instances to render?
    // Currently: none will.
    $( "body" ).on( "moreCodeEventEnded", function( event, eventData ) 
    {
        clog("moreCodeEventEnded: "+eventData, 1);

        loopInstanceIDs(function(xartaInstanceID)
        {
            // (inner) callback passing xartaInstanceID
            // that loopInstanceIDs finds, per loop:
            renderCodeButtons(  codeButtonLabelChange, 
                                xartaBigCodeTarget, 
                                xartaWidthControl, 
                                xartaCodeButtons,
                                xartaInstanceID);
            
        }, xartaCodeButtons);


        // public function already in colorbox plug-in for WordPress (extends jQuery).
        // it has the added benefit of binding click events to the wp-colorbox-inline
        // elements, using the unqiue value generated by the php wp shortcode, 
        // and storing it as settings in jQuery data.  Adds the class attribute "cboxElement".
        // PLUS: override the default already in (in the plugin): wp-colorbox.js
        $(".wp-colorbox-inline").colorbox(
            {inline:true, width:"99%", minWidth:"99%",maxWidth:"99%"});
    });


    // http://stackoverflow.com/questions/3458553/javascript-passing-parameters-to-a-callback-function
    function loopInstanceIDs (callback, targetClass)
    {
        $(targetClass).each(function()
        {
            var stringWithClassNames = $(this).attr('class');

            // this could set xartaInstanceID to null, or if found, returns an array
            xartaInstanceID = stringWithClassNames.match(/(xarta-id-)([0-9a-f]){13}/g);

            if(typeof xartaInstanceID !== "undefined" && 
                xartaInstanceID !== null)
            {
                if(xartaInstanceID[0].length === 22)
                {
                    xartaInstanceID = '.'+xartaInstanceID[0];

                    // rendering new buttons
                    if(targetClass === xartaCodeButtons)
                    {
                        // xartaInstanceID was the "missing" parameter
                        
                        /*
                        renderCodeButtons(  codeButtonLabelChange, 
                                xartaBigCodeTarget, 
                                xartaWidthControl, 
                                xartaCodeButtons,
                                xartaInstanceID);
                        ) 
                        */
                        callback(xartaInstanceID);
                    }

                    // window resize ... re-do all the buttons
                    // if necessary (f vs font, w vs width etc.)
                    if(targetClass === xartaWidthControl)
                    {
                        // xartaInstanceID was the "missing" parameter
                        // codeButtonLabelChange(xartaWidthControl, xartaInstanceID);
                        callback(xartaInstanceID);
                    }

                    // get all Ajax DIVS going
                    if(targetClass === xartaAjaxTarget)
                    {
                        var targetDiv = xartaAjaxTarget+xartaInstanceID;

                        var url = $(this).attr('data-url');
                        // TODO Error handling - no url?  no attribute?
                        // expecting url to be a custom template post with
                        // shortcode to run custom php to look at $_POST[] values etc.
                        
                        var postString = $(this).attr('data-post');

                        // three "missing" parameters
                        // xgithub_ajax_post_load_in(targetDiv, url, postString )
                        callback(targetDiv, url, postString);
                    }

                    // TODO Error handling - targetClass not yet accounted for?
                }
                else
                {
                    // TODO Error handling (not well-formed xartaInstanceID)
                }
            }
            else
            {
                xartaInstanceID = '';
            }

        });
    }




    $(window).resize(function() 
    {
        clog("xarta-syntaxhighlighter-site-footer.js: resize event", 3);

        var newOrientation = getOrientation();
        if (newOrientation !== oldOrientation)
        {
            oldOrientation = newOrientation;
            loopInstanceIDs(function(xartaInstanceID)
            {
                // (inner) callback passing xartaInstanceID
                // that loopInstanceIDs finds, per loop
                // (and xartaWidthControl as another param):
                resizeSyntaxHighlightInstance(xartaWidthControl, xartaInstanceID);
            }, xartaWidthControl);
        }

        loopInstanceIDs(function(xartaInstanceID)
        {
            // (inner) callback passing xartaInstanceID
            // that loopInstanceIDs finds, per loop
            // (and xartaWidthControl as another param):
            codeButtonLabelChange(xartaWidthControl, xartaInstanceID);
        }, xartaWidthControl);



        
    }).resize();

    

    /**   
     * Ajax functions (specific to syntax highlighting)
     * 
     *   The concept - for an ajax syntax-highlight raw github file    
     * 
     *   Principally: get the GitHub raw file contents in php, avoiding
     *                site origin issues, and have better control of source 
     *
     *   Separate shortcode in php/WordPress for Ajax ... all it does
     *   is output placeholder div with Ajax class name, and instnce ID
     *   and, base-64-url-friendly-encoding of url for GitHub file, and
     *   attributes for the syntax highlighting. JavaScript has no need to
     *   decode this ... it's just going to be sent straight back to the
     *   server, but this time as an asynchronous Ajax request. No hurry
     *   for the GitHub file.  The domain/user for GitHub files are 
     *   validated/constrained in php - server-side.
     * 
     * 
     */

    // look for xarta-target-ajax DIVS, and get them loading with ajax
    function get_ajax_going_in_target_divs()
    {
        clog("get_ajax_going_in_target_divs()", 1);

        loopInstanceIDs(function(targetDiv, url, postString)
        {
            // loopInstanceIDs looks at every xartaAjaxTarget DIV
            // and, for every div with a "xarta-id-XXXXXXXXXXXXX" in it,
            // will run xgithub_ajax_post_load_in 
            // ... so targetDiv = xartaAjaxTarget+xartaInstanceID
            // ... and url & postString are extracted using jQuery attr

            // *** IMPORTANT *** "xarta-id-XXXXXXXXXXXXX" used for targetDiv
            // is DIFFERENT to a new unique id that will be part of the responseText
            // that will REPLACE any trace of the unique id in targetDiv ...
            // targetDiv html is replaced (not innerHTML).

            // (inner) callback passing xartaAjaxTarget+xartaInstanceID, url, postString
            // that loopInstanceIDs finds, per loop:
            xgithub_ajax_post_load_in(targetDiv, url, postString);
        }, xartaAjaxTarget);
    }

    // might as well get that going ...
    get_ajax_going_in_target_divs();  // TODO ... make self invoking instead? TEST?


    // could call this anytime ... e.g. thinking of menu-tabs for posts in general
    // TODO change name?  More generic than xgithub? For highlighting?
    function xgithub_ajax_post_load_in(targetDiv, url, postString )
    {
        // only allow site origin for extra safety / simplicity
        clog ("xgithub_ajax_post_load_in("+targetDiv+", "+url+", "+postString+")",1);

        // assign "this" as "passthis" in my global xarta_ajax() function
        function responseFunction(passthis)
        {
            var tmpResponseText = passthis.responseText;
            $(targetDiv).html(tmpResponseText);
            // THIS WILL BRING IN NEW INSTANCEID - DIFFERENT TO THE ONE IN TARGETDIV
            
            // little "hack" ...
            // added event in built syntaxhighlighter.js to start highlighting
            // ... although really should be "requiring"'ing the module and using
            // ... the API ... but quite like this simple "hack" with even decoupling
            $(function() {
                // Handler for .ready() called.
                clog("tmpResponseText="+tmpResponseText,3);
                // FIND THE NEW INSTANCEID OF THE (AJAX) ELEMENT TO BE HIGHLIGHTED
                var startID = tmpResponseText.indexOf("xarta-id-");
                clog("startID="+startID,1);
                var id = '#'+ tmpResponseText.substr(startID,22);
                clog("ID="+id,1);

                clog($(id).get(),1);
                $( "body" ).trigger( "moreCodeEventStarted", [ $(id).get() ] );           
            });
            

            // and bind the body "moreCodeEventEnded" event to continue
            // The process:
            // Add & bind click events for new control buttons 
            // (if using the button attribute or default for control buttons)
            // And, if the colorbox classes were output, 
            // then run the colorbox jQuery extension to bind the
            // magnifying class to the click event with stored settings data
        }
        
        // my global xarta_ajax function
        xarta_ajax(url, responseFunction, postString);
    }


}); // end jQuery document ready



jQuery(function() 
{

        /**
         *  MEDIA QUERY CSS RESULTS AVAILABLE IN JAVASCRIPT
         *  ... and available for image preloading
         * 
         * ---
         * 
         *  This is an idea I started in timeouttherapy.co.uk
         *  I'm further developing it to be more general purpose.
         * 
         *  I wanted to use css (non-js-computed) @media widths, for
         *  various reasons, but including preloading / loading
         *  images according to responsive need.
         * 
         *  I decided to use a colour attribute in a hidden DIV.
         *  And then an array of permitted nearest-values, so I can
         *  limit available images for example without worrying about
         *  maintaining the @media query thingies too much.
         * 
         *  Eventually I'll extend jQuery with preload functions e.g.
         *  an original image, and responsively sized images etc. etc.
         */

        var mediaquery;
        var aMediaqNum;
        var aMediaqTxt;
        var aMediaqTxtDefault;
        var acceptableMediaq;

        aMediaqTxtDefault = '1320';
        mediaquery = '.jQueryMedia\r{ \r  color: #00' + aMediaqTxtDefault + '; \r} \r' ;
        acceptableMediaq = [240, 320, 400, 420, 480, 650, 780, 980, 1320]; // important: ordered ascending

        for (i = acceptableMediaq.length-1; i > 0; i--) 
        {
            aMediaqNum = acceptableMediaq[i];
            aMediaqTxt = ('0000' + aMediaqNum).slice(-4);

            mediaquery += '@media screen and (max-width: ' + aMediaqNum + 'px)\r{\r    .jQueryMedia\r    {\r        color: #00' + aMediaqTxt + '; \r    }\r}\r';
        }
        jQuery('head')
                .append(
            '<style type="text/css">' + mediaquery + '</style>' + "\r"
                )
        jQuery('body') 
                .prepend(
            '<div class="hidden jQueryMedia" style="opacity: 0">' +
                '<span style="font-family: Arial, Helvetica, sans-serif;"></span>' +
            '</div>');
        
        var hexDigits = new Array
            ("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"); 

        //Function to convert rgb color to hex format
        function rgb2hex(rgb) {
            rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
            return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
        }
    
        // last 4 characters of colour in hex - using as denary for @media query screen size
        function getMediaQuerySize (css) {
            return rgb2hex(css).slice(-4);
        }

        function hex(x) {
            return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
        }

        function getMediaq()
        {
            var mediaq;
            // var acceptableMediaq; // declared outside of function

            mediaq = getMediaQuerySize(jQuery('.jQueryMedia').css('color') );
            // acceptableMediaq = [240, 320, 480, 650, 780, 980, 1320];    // pre-sorted !!!
                                                                            // (using binary search)

            // still using "closest" despite setting @media queries based on same array
            // because want to reserve capability of manually adding more jQueryMedia stops
            // in the style sheet, but constrain them here to the accepable array,
            // (for premade / pre-existing images for example with the sizes in the file name)
            // or use unrestrained with getMediaQuerySize etc.
            //return mediaq;
            return ('0000' + closest (mediaq, acceptableMediaq)).slice(-4);
        }

        jQuery.fn.extend({
            mediaqTxt: function() {
                return getMediaq();
            },
            mediaqNum: function() {
                return parseInt(getMediaq(), 10);
            }

        });
        
        function closest (num, arr) {
            var mid;
            var lo = 0;
            var hi = arr.length - 1;
            while (hi - lo > 1) {
                mid = Math.floor ((lo + hi) / 2);
                if (arr[mid] < num) {
                    lo = mid;
                } else {
                    hi = mid;
                }
            }
            if (num - arr[lo] <= arr[hi] - num) {
                return arr[lo];
            }
            return arr[hi];
        }

        // resize
        jQuery(window).resize(function(){
            //preload();
            //alert("Temp test: mediaq = " +  getMediaq());
            //console.log("Temp test: mediaq = " +  getMediaq());
            console.log(jQuery().mediaqTxt());
        });

        // document ready - before images loaded etc.
        jQuery(function() {
            //preload(); // might add template-specific images to preload
        });

        // page load complete (including images)
        jQuery( window ).on( "load", function() {
            // preload(); // might add template-specific images to preload
        });

        function replaceimage()
        {
            jQuery(this.elm).find('img[src$="' + this.org + '"]').attr("srcset",this.src + " " + this.wdt);
            jQuery(this.elm).find('img[src$="' + this.org + '"]').attr("src",this.src);
        }

        function setimage()
        {
            console.log ("Element: " + this.elm + ", CSS: " + this.css + ", Path: " + this.pth + mediaq + "." + this.typ)
            jQuery(this.elm).css(this.css, 'url("' + this.pth + mediaq + '.' + this.typ + '")');
        }

        // use preload( array )  in footer (but consider only calling after low-res images loaded)
        // EXAMPLE:

        /*
        <script>
            preload(["https://timeouttherapy.co.uk/images-high/tree-high-", "#featured", "background-image", "png",
                "https://timeouttherapy.co.uk/images-low/tree-low-0240.jpg", "100w"], [ETC 1], [ETC 2], [ETC n]);            
        </script>
        */
        var images = new Array();
        function preload() 
        {
            // DECISION BASED ON TIMING WHETHER TO LOAD BIGGER ASSETS
            var loadTime = window.performance.timing.domContentLoadedEventEnd- window.performance.timing.navigationStart;
            console.log("loadTime = " + loadTime);

            if(loadTime > 2800)
            {
                console.log("Because of big Dom Content loadtime, aborting lazy loading of big assets");
                return;
            }

            // using color attr to provide non-computed @media query I'm using
            var mediaq = getMediaq();
            console.log(mediaq);

            // Access the parameter arrays
            for (i = 0; i < preload.arguments.length; i++) 
            {
                images[i] = new Image();
                images[i].pth = preload.arguments[i][0];    // used to create src
                images[i].elm = preload.arguments[i][1];    // e.g. ".class" or "#id" etc. (jQuery)
                images[i].css = preload.arguments[i][2];    // e.g. background-image
                images[i].typ = preload.arguments[i][3];    // e.g. png, jpg etc.

                images[i].org = preload.arguments[i][4];    // original image to find & replace e.g. maybe in a widget
                images[i].wdt = preload.arguments[i][5];    // if there's a srcset, overwriting - specify width (string)
                                                            // including units

                // create src uri (image should pre-exist at this uri !!!)
                images[i].src = preload.arguments[i][0] + mediaq + '.' + preload.arguments[i][3];
                images[i].mdq = mediaq;

                if (images[i].org === '')
                {
                    // just change image src for given element & css
                    images[i].onload = setimage;
                    console.log ("Preloading images for loop: path = " + images[i].pth + mediaq + "." + images[i].typ + ", element = " + images[i].elm);
                }
                else
                {
                    // find using element elm, and source src, then replace src, and overwrite srcset too if exists
                    images[i].onload = replaceimage;
                }

            }
        }
});


