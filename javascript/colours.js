
function test(itemID, change, greyingFactor) {
	var lighterShade = getShade(document.getElementById(itemID), change, greyingFactor);
	jQuery("#" + itemID).css("background-color", lighterShade);
}

var colourCalculator={}//, colourShader=function(a){return document.getElementById(a)}
colourCalculator.color={
	shade:function(a,b,c){
		var v=[],i
		var avg = (a[0]+a[1]+a[2])/3;
		for(i=0;i<3;i++){
			//alert("was: " + a[i]);
			var greyedColour = (a[i] * (1-c)) + (avg * c);
			//alert("step 1: " + greyedColour);
			v[i]=Math.round(greyedColour * b);
			//alert("step 2: " + v[i]);
			if(v[i]>240)v[i]=240
			if(v[i]<0)v[i]=0
		}
		return v
	},
	rgb:function(a){
		return "rgb(" +a[0]+","+a[1]+","+a[2]+")";
	},
	hex:function(a){
		var f = colourCalculator.color._hex;
		return "#" + f(a[0])+f(a[1])+f(a[2])
	},
	_hex:function(a){
		return ('0'+a.toString(16)).slice(-2)
	}
}

function getShade(el, change, greyingFactor){
	var baseColor = jQuery.getRealColour(el, "backgroundColor");
	var newColour = colourCalculator.color.shade(baseColor,change, greyingFactor);
	var newHexColour =  colourCalculator.color.hex(newColour);
	return newHexColour;
}


/*
 * jQuery Color Animations
 * Copyright 2007 John Resig
 * Released under the MIT and GPL licenses.
 */


jQuery.getRealColour = function (elem, attr) {
	var color;
	do {
		color = jQuery.curCSS(elem, attr);
		// Keep going until we find an element that has color, or we hit the body
		if ( color != '' && color != 'transparent' || jQuery.nodeName(elem, "body") )
			break;
		attr = "backgroundColor";
	} while ( elem = elem.parentNode );
	return jQuery.getRGB(color);
}


	// Color Conversion functions from highlightFade
	// By Blair Mitchelmore
	// http://jquery.offput.ca/highlightFade/

	// Parse strings looking for color tuples [255,255,255]
jQuery.getRGB = function (color) {
	var result;
	// Check if we're already dealing with an array of colors
	if ( color && color.constructor == Array && color.length == 3 )
		return color;
	// Look for rgb(num,num,num)
	if (result = /rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(color))
		return [parseInt(result[1]), parseInt(result[2]), parseInt(result[3])];
	// Look for rgb(num%,num%,num%)
	if (result = /rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(color))
		return [parseFloat(result[1])*2.55, parseFloat(result[2])*2.55, parseFloat(result[3])*2.55];
	// Look for #a0b1c2
	if (result = /#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(color))
		return [parseInt(result[1],16), parseInt(result[2],16), parseInt(result[3],16)];
	// Look for #fff
	if (result = /#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(color))
		return [parseInt(result[1]+result[1],16), parseInt(result[2]+result[2],16), parseInt(result[3]+result[3],16)];
	// Otherwise, we're most likely dealing with a named color
	return colors[jQuery.trim(color).toLowerCase()];
}


	// Some named colors to work with
	// From Interface by Stefan Petre
	// http://interface.eyecon.ro/

var colors = {
aqua:[0,255,255],
azure:[240,255,255],
beige:[245,245,220],
black:[0,0,0],
blue:[0,0,255],
brown:[165,42,42],
cyan:[0,255,255],
darkblue:[0,0,139],
darkcyan:[0,139,139],
darkgrey:[169,169,169],
darkgreen:[0,100,0],
darkkhaki:[189,183,107],
darkmagenta:[139,0,139],
darkolivegreen:[85,107,47],
darkorange:[255,140,0],
darkorchid:[153,50,204],
darkred:[139,0,0],
darksalmon:[233,150,122],
darkviolet:[148,0,211],
fuchsia:[255,0,255],
gold:[255,215,0],
green:[0,128,0],
indigo:[75,0,130],
khaki:[240,230,140],
lightblue:[173,216,230],
lightcyan:[224,255,255],
lightgreen:[144,238,144],
lightgrey:[211,211,211],
lightpink:[255,182,193],
lightyellow:[255,255,224],
lime:[0,255,0],
magenta:[255,0,255],
maroon:[128,0,0],
navy:[0,0,128],
olive:[128,128,0],
orange:[255,165,0],
pink:[255,192,203],
purple:[128,0,128],
violet:[128,0,128],
red:[255,0,0],
silver:[192,192,192],
white:[255,255,255],
yellow:[255,255,0]
};
