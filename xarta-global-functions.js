// once I understand how, I'll have to do this as some
// kind of a module, and import etc. ?


// this is effectively over-ridden in xarta-syntaxhighlighter.php
syntaxhighlighterConfig = {
  className: 'xarta-big-code'
};


// COMMON GLOBAL CONVENIENT CONSOLE-LOG OUTPUT WITH ERROR-LEVEL -------------------
// I normally define / set in the site header. TODO - deployment script that strips
// all this debug stuff and any other duplication out.
console.log("xarta-global-functions.js");

if(typeof extraDebug !== "undefined" && 
    extraDebug !== null)
{
    console.log("typeof extraDebug="+typeof extraDebug);
    console.log("extraDebug variable already defined, value:"+extraDebug);
}
else
{
    console.log("extraDebug variable undefined, defining and default to 1");
    var extraDebug = 1;
}

if(typeof clog !== "undefined" && 
    clog !== null)
{
    console.log("clog function already created");
}
else
{
    console.log("clog function not created: creating");
    // create global function
    window.clog = function(message, level)
    {
        if (extraDebug>=level)
	    {
		    console.log(message);
	    }
    }
}
// ------------------------------------------------------------------------------

// My other global functions for my site:
// TODO Factor-out to "global" script so no more duplication
//      e.g. just raise an error if script not present, with instructions for inclusion

if(typeof xarta_ajax !== "undefined" && 
    xarta_ajax !== null)
{
    clog("xarta_ajax function already created", 1);
}
else
{
    clog("xarta_ajax function not created: creating", 1);
    window.xarta_ajax =function (url, responseFunction, postString)
    {
        // TODO: ERROR HANDLING (assumption that successful)

        clog("xarta_ajax",1);

        var xhttp;

        if (window.XMLHttpRequest)
        {
            xhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5 (old example! leaving in for fun)
            xhttp = new ActiveXObject("Microsoft.XMLHTTP"); 
        }

        xhttp.onreadystatechange = function() 
        {
            if (this.readyState == 4 && this.status == 200) 
            {
                responseFunction(this);
            }
            else
            {
                // What? Yeah? What you gonna do about it?
            }
        };
        clog("url="+url+"?random="+Math.random(),1);
        clog("postString="+postString,1);
        xhttp.open("POST", url, true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send(postString);
    }
}
// ------------------------------------------------------------------------------

