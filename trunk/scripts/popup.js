/*
	PolyPager - a lean, mean web publishing system
    Copyright (C) 2006 Nicolas Hšning
	polypager.nicolashoening.de
	
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA' .
*/
    
/*  --------------------------------------------------------------------------
	Code for link-hover text boxes
	Original: Mike McGrath (Web Site: http://website.lineone.net/~mike_mcgrath) 
	modified by Nicolas Hoening (Web Site: http://nicolashoening.de)
*/

var iex=(document.all);	//this can also be Opera!
var nav=(document.layers);
var old=(navigator.appName=="Netscape" && !document.layers && !document.getElementById);
var n_6=(window.sidebar);
var opera = (name.indexOf("opera")> -1) //this is just Opera

// create the popup box - inline so everyone, including Opera, will tell the width
document.write('<div id="pup" style="visibility:hidden;display:inline;"></div>');

var skin = "nothing";		//this is the style of our popup we'll modify

var minMarginToBorder = 15;	//set how much minimal space there should be to
							//the next border (horizontally)
var popwidth = "nothing";	//this is how wide your popup is, we'll read it
							//from the stylesheet later, so keep this as-is

// initialize the capture pointer
if(nav) document.captureEvents(Event.MOUSEMOVE);
if(n_6) document.addEventListener("mousemove", get_mouse, true);
if(nav || iex) document.onmousemove = get_mouse;

// set dynamic coords when the mouse moves
function get_mouse(e)
{
  var x,y;
  
  //get X
  if (iex) x = document.body.scrollLeft + event.clientX;
  if (nav || n_6) x = e.pageX;

  //get Y
  if (iex) y = document.body.scrollTop + event.clientY;
  if (nav || n_6) y = e.pageY;
  
  // assign style object when not already known
  if (skin == "nothing") {
  	if(nav) skin=document.pup;
  	if(iex) skin=pup.style;
  	if(n_6) skin=document.getElementById("pup").style;
  }
  
  //getting the popwidth - we'll get this only once, too! 
  //Then it will always have the stylesheet value
  if (popwidth == "nothing") {
    popwidth = 0;
  	if (iex && !opera) {
		popwidth = parseInt(pup.currentStyle.width);
	} 
	if (opera) {
		popwidth = parseInt(document.defaultView.getComputedStyle(pup,null).width);
	} 
	if (n_6) {
		popwidth = parseInt(document.defaultView.getComputedStyle(document.getElementById("pup"),null).getPropertyValue('width'));
	}
	skin.display = "none";	//turn "inline" off now, it widens the page horizontally
							//when the parked popup is positioned
  }


  //now set coordinates for our popup - n_6 wants "px", the others not
  //remember: the popup is still hidden
  if(iex || nav)
  {
    skin.top = y;
    skin.left = x;
  }
  if(n_6)
  {
    skin.top = y + "px";
    skin.left = x + "px";
  }
  
  nudge(x,y); // avoids edge overflow
   
  //uncomment this to test your coordinates (Moz. FF has disabled changing 
  //the status bar - go to options to enable it)
  //showCoordinatesInStatusBar(x,y);
}

// avoid edge overflow
function nudge(x,y)
{
  var extreme, overflow, temp;
  
  if(iex) extreme = window.document.body.clientWidth - popwidth;
  if(n_6 || nav) extreme = window.innerWidth - popwidth - 25;
  extreme -= minMarginToBorder;
  
  // let's operate on these instead of the real skin
  newtop = parseInt(skin.top);
  newleft = parseInt(skin.left);
  
  // right
  if(newleft>extreme)
  {
    //overflow = newleft - extreme;	// this behavior caused flickering ...
    //newleft -= overflow;			//  when both right and bottom were adjusted
	newleft -= (parseInt(popwidth) + minMarginToBorder + 20 );
  }

  // left - should almost never be a problem - we're drawing the window 
  // to the right of the mouse per default (maybe corrected by the code above
  // but then this has the last horizontal word)
  if(newleft<1)
  {
    overflow = newleft - 1;
    newleft -= overflow;
  }

  //down: when I am close to the bottom, move it up
  //I estimate the lines that fit in the width, assuming a char width of 10 pixels
  // and a (little pessimistic) line height of 20 (That should work for most cases)
  est_lines = parseInt(get("pup").innerHTML.length / (parseInt(skin.width)/10) );
  if((newtop + parseInt(est_lines * 20)) > window.innerHeight) {
  	newtop = parseInt(window.innerHeight) - parseInt(est_lines * 20); //correct
  }
  
  if(nav || iex) {
	  skin.left = newleft;
	  skin.top = newtop;
  }else if(n_6){
	  skin.left = newleft + "px";
	  skin.top = newtop + "px";
  }
}

// write content and display
function popup(msg,bak)
{
	
  if(old) {	//display plain message box for old browsers
    alert(msg);
    return;
  }
   if(iex || nav)
  {
    skin.width = popwidth;
  }
  if(n_6)
  {
    skin.width = popwidth + "px";
  }
				
  //write the message in
  if(nav) { 
    skin.document.open();
    skin.document.write(msg);
    skin.document.close();
  }
  if(iex){ pup.innerHTML = msg;}  
  if(n_6){ document.getElementById("pup").innerHTML = msg;}
  
  //make the popup visible
  skin.visibility ="visible";
  skin.display = "inline";
}

// make content box invisible
function kill()
{
  if(!old) {
    skin.visibility = "hidden";	//invisible
	skin.display = "none";	//invisible
  }
}

function showCoordinatesInStatusBar(theX, theY) {
  var browser;
  if(iex){browser="iex"};
  if(nav){browser="nav"};
  if(n_6){browser="n_6"};
  window.status="browser is " + browser 
		+ ", window.innerHeight is " + window.innerHeight
		+ ", window.outerHeight is " + window.outerHeight
		+ ", popwidth is " + popwidth
		+ ", skin.left is " + skin.left
		+ ", skin.top is " + skin.top
		+ ", our x=" + theX + ", our y=" + theY;
}
/* -------------- end of hover box code --------------------- */

