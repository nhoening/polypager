/*
    --------------------------------------------------------------------------
    Code for link-hover text boxes
    By Nicolas Hoening (http://nicolashoening.de/?twocents&nr=8)
    usage: <a onmouseover="popup('popup content', width)">a link</a>
     (width is optional - default is in CSS: #pup {width: x;},
      escape " in content with &quot;)
    --------------------------------------------------------------------------
*/

// create the popup box - remember to give it some width in your styling 
document.write('<div id="pup" style="position:abolute; display:none; z-index:200;"></div>');

var minMargin = 15; // set how much minimal space there should be to
                            // between the popup and everything else (borders, mouse)
var ready = false; // we are ready when the mouse event is set up
var default_width = '200px'; // will be set to width from css in document.ready

jQuery(document).ready(function(){
    $('#pup').hide();
    default_width = $('#pup').width();
    // set dynamic coords when the mouse moves
    $(document).mousemove(function(e){ 
        var x,y;
      
        x = $(document).scrollLeft() + e.clientX;
        y = $(document).scrollTop() + e.clientY;

        x += 10; // important: if the popup is where the mouse is, the hoverOver/hoverOut events flicker
      
        var x_y = nudge(x,y); // avoids edge overflow
      
        // remember: the popup is still hidden
        $('#pup').css('top', x_y[1] + 'px');
        $('#pup').css('left', x_y[0] + 'px');
    });

    ready = true;
});

// avoid edge overflow
function nudge(x,y)
{
    var win = $(window);
    
    // When the mouse is too far on the right, put window to the left
    var xtreme = $(document).scrollLeft() + win.width() - $('#pup').width() - minMargin;
    if(x > xtreme) {
        x -= $('#pup').width() + 2 * minMargin;
    }
    x = max(x, 0);

    // When the mouse is too far down, move window up
    if((y + $('#pup').height()) > (win.height() +  $(document).scrollTop())) {
        y -= $('#pup').height() + minMargin;
    }

    return [ x, y ];
}

function popup(msg, width)
{
    if (typeof width === "undefined"){
        width = default_width;
    }
    // write content and display
    if (ready) {
        $('#pup').width(width).html(msg).show();
    }
    // make sure popup goes away on mouse out
    $(this).mouseout(function(e){
        $('#pup').hide().width(default_width);
    });
}


function max(a,b){
    if (a>b) return a;
    else return b;
}


