
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

jQuery(document).ready(function($) {
    
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
                                    " xartaInstanceID = "+xartaInstanceID, 1);

        $(xartaWidthControl+xartaInstanceID).width("100%");

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

        $("a"+xartaInstanceID+".shrink-code").on( "click", function() 
        {
            var probableContainerForPercentCalcs; // using WordPress menutab plugin sometimes

            if($(".tabcontents").length)
            {
                clog("Ok: seem to be in a menutab", 1);
                probableContainerForPercentCalcs = ".tabcontents";
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