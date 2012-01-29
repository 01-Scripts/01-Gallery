/*!
	Slimbox v1.7 - The ultimate lightweight Lightbox clone
	(c) 2007-2009 Christophe Beyls <http://www.digitalia.be>
	MIT-style license.
*/

function basename(path, suffix) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Ash Searle (http://hexmen.com/blog/)
    // +   improved by: Lincoln Ramsay
    // +   improved by: djmix
    // *     example 1: basename('/www/site/home.htm', '.htm');
    // *     returns 1: 'home'
 
    var b = path.replace(/^.*[\/\\]/g, '');
    
    if (typeof(suffix) == 'string' && b.substr(b.length-suffix.length) == suffix) {
        b = b.substr(0, b.length-suffix.length);
    }
    
    return b;
}

function urldecode( str ) {
    // Decodes URL-encoded string  
    // 
    // version: 905.412
    // discuss at: http://phpjs.org/functions/urldecode
    // +   original by: Philip Peterson
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: AJ
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Brett Zamir (http://brettz9.blogspot.com)
    // +      input by: travc
    // +      input by: Brett Zamir (http://brettz9.blogspot.com)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Lars Fischer
    // %          note 1: info on what encoding functions to use from: http://xkr.us/articles/javascript/encode-compare/
    // *     example 1: urldecode('Kevin+van+Zonneveld%21');
    // *     returns 1: 'Kevin van Zonneveld!'
    // *     example 2: urldecode('http%3A%2F%2Fkevin.vanzonneveld.net%2F');
    // *     returns 2: 'http://kevin.vanzonneveld.net/'
    // *     example 3: urldecode('http%3A%2F%2Fwww.google.nl%2Fsearch%3Fq%3Dphp.js%26ie%3Dutf-8%26oe%3Dutf-8%26aq%3Dt%26rls%3Dcom.ubuntu%3Aen-US%3Aunofficial%26client%3Dfirefox-a');
    // *     returns 3: 'http://www.google.nl/search?q=php.js&ie=utf-8&oe=utf-8&aq=t&rls=com.ubuntu:en-US:unofficial&client=firefox-a'
    
    var histogram = {}, ret = str.toString(), unicodeStr='', hexEscStr='';
    
    var replacer = function(search, replace, str) {
        var tmp_arr = [];
        tmp_arr = str.split(search);
        return tmp_arr.join(replace);
    };
    
    // The histogram is identical to the one in urlencode.
    histogram["'"]   = '%27';
    histogram['(']   = '%28';
    histogram[')']   = '%29';
    histogram['*']   = '%2A';
    histogram['~']   = '%7E';
    histogram['!']   = '%21';
    histogram['%20'] = '+';
    histogram['\u00DC'] = '%DC';
    histogram['\u00FC'] = '%FC';
    histogram['\u00C4'] = '%D4';
    histogram['\u00E4'] = '%E4';
    histogram['\u00D6'] = '%D6';
    histogram['\u00F6'] = '%F6';
    histogram['\u00DF'] = '%DF'; 
    histogram['\u20AC'] = '%80';
    histogram['\u0081'] = '%81';
    histogram['\u201A'] = '%82';
    histogram['\u0192'] = '%83';
    histogram['\u201E'] = '%84';
    histogram['\u2026'] = '%85';
    histogram['\u2020'] = '%86';
    histogram['\u2021'] = '%87';
    histogram['\u02C6'] = '%88';
    histogram['\u2030'] = '%89';
    histogram['\u0160'] = '%8A';
    histogram['\u2039'] = '%8B';
    histogram['\u0152'] = '%8C';
    histogram['\u008D'] = '%8D';
    histogram['\u017D'] = '%8E';
    histogram['\u008F'] = '%8F';
    histogram['\u0090'] = '%90';
    histogram['\u2018'] = '%91';
    histogram['\u2019'] = '%92';
    histogram['\u201C'] = '%93';
    histogram['\u201D'] = '%94';
    histogram['\u2022'] = '%95';
    histogram['\u2013'] = '%96';
    histogram['\u2014'] = '%97';
    histogram['\u02DC'] = '%98';
    histogram['\u2122'] = '%99';
    histogram['\u0161'] = '%9A';
    histogram['\u203A'] = '%9B';
    histogram['\u0153'] = '%9C';
    histogram['\u009D'] = '%9D';
    histogram['\u017E'] = '%9E';
    histogram['\u0178'] = '%9F';

    for (unicodeStr in histogram) {
        hexEscStr = histogram[unicodeStr]; // Switch order when decoding
        ret = replacer(hexEscStr, unicodeStr, ret); // Custom replace. No regexing
    }
    
    // End with decodeURIComponent, which most resembles PHP's encoding functions
    ret = decodeURIComponent(ret);

    return ret;
}

function parse_str(str, array){
    // Parses GET/POST/COOKIE data and sets global variables  
    // 
    // version: 905.412
    // discuss at: http://phpjs.org/functions/parse_str
    // +   original by: Cagri Ekin
    // +   improved by: Michael White (http://getsprink.com)
    // +    tweaked by: Jack
    // +   bugfixed by: Onno Marsman
    // +   reimplemented by: stag019
    // +   bugfixed by: Brett Zamir (http://brettz9.blogspot.com)
    // +   bugfixed by: stag019
	// -    depends on: urldecode
    // %        note 1: When no argument is specified, will put variables in global scope.
    // *     example 1: var arr = {};
    // *     example 1: parse_str('first=foo&second=bar', arr);
    // *     results 1: arr == { first: 'foo', second: 'bar' }
    // *     example 2: var arr = {};
    // *     example 2: parse_str('str_a=Jack+and+Jill+didn%27t+see+the+well.', arr);
    // *     results 2: arr == { str_a: "Jack and Jill didn't see the well." }
	var glue1 = '=', glue2 = '&', array2 = String(str).split(glue2),
	i, j, chr, tmp, key, value, bracket, keys, evalStr,
	fixStr = function(str)
	{
		return urldecode(str).replace(/([\\"'])/g, '\\$1').replace(/\n/g, '\\n').replace(/\r/g, '\\r');
	};

	if(!array)
	{
		array = window;
	}

	for(i = 0; i < array2.length; i++)
	{
		tmp = array2[i].split(glue1);
		if(tmp.length < 2)
		{
			tmp = [tmp, ''];
		}
		key   = fixStr(tmp[0]);
		value = fixStr(tmp[1]);
		while(key.charAt(0) === ' ')
		{
			key = key.substr(1);
		}
        if(key.indexOf('\0') !== -1)
        {
            key = key.substr(0, key.indexOf('\0'));
        }
		if(key && key.charAt(0) !== '[')
		{
			keys    = [];
			bracket = 0;
			for(j = 0; j < key.length; j++)
			{
				if(key.charAt(j) === '[' && !bracket)
				{
					bracket = j + 1;
				}
				else if(key.charAt(j) === ']')
				{
					if(bracket)
					{
						if(!keys.length)
						{
							keys.push(key.substr(0, bracket - 1));
						}
						keys.push(key.substr(bracket, j - bracket));
						bracket = 0;
						if(key.charAt(j + 1) !== '[')
						{
							break;
						}
					}
				}
			}
			if(!keys.length)
			{
				keys = [key];
			}
			for(j = 0; j < keys[0].length; j++)
			{
				chr = keys[0].charAt(j);
				if(chr === ' ' || chr === '.' || chr === '[')
				{
					keys[0] = keys[0].substr(0, j) + '_' + keys[0].substr(j + 1);
				}
				if(chr === '[')
				{
					break;
				}
			}
			evalStr = 'array';
			for(j = 0; j < keys.length; j++)
			{
				key = keys[j];
				if((key !== '' && key !== ' ') || j === 0)
				{
					key = "'" + key + "'";
				}
				else
				{
					key = eval(evalStr + '.push([]);') - 1;
				}
				evalStr += '[' + key + ']';
				if(j !== keys.length - 1 && eval('typeof ' + evalStr) === 'undefined')
				{
					eval(evalStr + ' = [];');
				}
			}
			evalStr += " = '" + value + "';\n";
			eval(evalStr);
		}
	}
}

function substr( f_string, f_start, f_length ) {
    // Returns part of a string  
    // 
    // version: 810.1317
    // discuss at: http://phpjs.org/functions/substr
    // +     original by: Martijn Wieringa
    // +     bugfixed by: T.Wild
    // +      tweaked by: Onno Marsman
    // *       example 1: substr('abcdef', 0, -1);
    // *       returns 1: 'abcde'
    // *       example 2: substr(2, 0, -6);
    // *       returns 2: ''
    f_string += '';

    if(f_start < 0) {
        f_start += f_string.length;
    }

    if(f_length == undefined) {
        f_length = f_string.length;
    } else if(f_length < 0){
        f_length += f_string.length;
    } else {
        f_length += f_start;
    }

    if(f_length < f_start) {
        f_length = f_start;
    }

    return f_string.substring(f_start, f_length);
}

var Slimbox = (function() {

	// Global variables, accessible to Slimbox only
	var win = window, ie6 = Browser.Engine.trident4, options, images, activeImage = -1, activeURL, prevImage, nextImage, compatibleOverlay, middle, centerWidth, centerHeight, clicked = 0,

	// Preload images
	preload = {}, preloadPrev = new Image(), preloadNext = new Image(),

	// DOM elements
	overlay, center, image, sizer, prevLink, nextLink, bottomContainer, bottom, caption, number,

	// Effects
	fxOverlay, fxResize, fxImage, fxBottom;

	/*
		Initialization
	*/

	win.addEvent("domready", function() {
		// Append the Slimbox HTML code at the bottom of the document
		$(document.body).adopt(
			$$(
				overlay = new Element("div", {id: "lbOverlay", events: {click: close}}),
				center = new Element("div", {id: "lbCenter"}),
				bottomContainer = new Element("div", {id: "lbBottomContainer"})
			).setStyle("display", "none")
		);

		image = new Element("div", {id: "lbImage"}).injectInside(center).adopt(
			sizer = new Element("div", {styles: {position: "relative"}}).adopt(
				prevLink = new Element("a", {id: "lbPrevLink", href: "#", events: {click: previous}}),
				nextLink = new Element("a", {id: "lbNextLink", href: "#", events: {click: next}})
			)
		);

		bottom = new Element("div", {id: "lbBottom"}).injectInside(bottomContainer).adopt(
			new Element("a", {id: "lbCloseLink", href: "#", events: {click: close}}),
			caption = new Element("div", {id: "lbCaption"}),
			number = new Element("div", {id: "lbNumber"}),
			new Element("div", {styles: {clear: "both"}})
		);
	});


	/*
		Internal functions
	*/

	function position() {
		var scroll = win.getScroll(), size = win.getSize();
		$$(center, bottomContainer).setStyle("left", scroll.x + (size.x / 2));
		if (compatibleOverlay) overlay.setStyles({left: scroll.x, top: scroll.y, width: size.x, height: size.y});
	}

	function setup(open) {
		["object", ie6 ? "select" : "embed"].forEach(function(tag) {
			Array.forEach(document.getElementsByTagName(tag), function(el) {
				if (open) el._slimbox = el.style.visibility;
				el.style.visibility = open ? "hidden" : el._slimbox;
			});
		});

		overlay.style.display = open ? "" : "none";

		var fn = open ? "addEvent" : "removeEvent";
		win[fn]("scroll", position)[fn]("resize", position);
		document[fn]("keydown", keyDown);
	}

	function keyDown(event) {
		var code = event.code;
		// Prevent default keyboard action (like navigating inside the page)
		return options.closeKeys.contains(code) ? close()
			: options.nextKeys.contains(code) ? next()
			: options.previousKeys.contains(code) ? previous()
			: false;
	}

	function previous() {
		clicked = 1;
		return changeImage(prevImage);
	}

	function next() {
		clicked = 1;
		return changeImage(nextImage);
	}

	function changeImage(imageIndex) {
		if (imageIndex >= 0) {
			activeImage = imageIndex;
			activeURL = images[imageIndex][0];
			prevImage = (activeImage || (options.loop ? images.length : 0)) - 1;
			nextImage = ((activeImage + 1) % images.length) || (options.loop ? 0 : -1);

			stop();
			center.className = "lbLoading";

			preload = new Image();
			preload.onload = animateBox;
			preload.src = activeURL;
		}

		return false;
	}

	function animateBox() {
		center.className = "";
		fxImage.set(0);
		image.setStyles({backgroundImage: "url(" + activeURL + ")", display: ""});
		sizer.setStyle("width", preload.width);
		$$(sizer, prevLink, nextLink).setStyle("height", preload.height);

		caption.set("html", images[activeImage][1] || "");
		number.set("html", (((images.length > 1) && options.counterText) || "").replace(/{x}/, activeImage + 1).replace(/{y}/, images.length));

		if (prevImage >= 0) preloadPrev.src = images[prevImage][0];
		if (nextImage >= 0) preloadNext.src = images[nextImage][0];

		centerWidth = image.offsetWidth;
		centerHeight = image.offsetHeight;
		var top = Math.max(0, middle - (centerHeight / 2)), check = 0, fn;
		if (center.offsetHeight != centerHeight) {
			check = fxResize.start({height: centerHeight, top: top});
		}
		if (center.offsetWidth != centerWidth) {
			check = fxResize.start({width: centerWidth, marginLeft: -centerWidth/2});
		}
		fn = function() {
			bottomContainer.setStyles({width: centerWidth, top: top + centerHeight, marginLeft: -centerWidth/2, visibility: "hidden", display: ""});
			fxImage.start(1);
		};
		if (check) {
			fxResize.chain(fn);
		}
		else {
			fn();
		}
	}

	function animateCaption() {
		if (prevImage >= 0) prevLink.style.display = "";
		if (nextImage >= 0) nextLink.style.display = "";
		fxBottom.set(-bottom.offsetHeight).start(0);
		bottomContainer.style.visibility = "";
	}

	function stop() {
		preload.onload = $empty;
		preload.src = preloadPrev.src = preloadNext.src = activeURL;
		fxResize.cancel();
		fxImage.cancel();
		fxBottom.cancel();
		$$(prevLink, nextLink, image, bottomContainer).setStyle("display", "none");
	}

	function close() {
		if (activeImage >= 0) {
			stop();
			activeImage = prevImage = nextImage = -1;
			center.style.display = "none";
			fxOverlay.cancel().chain(setup).start(0);
		}
		
		if (options.changeLocation && clicked == 1){
			var urlarr = {};
			parse_str(substr(this.document.URL, 1), urlarr);
			document.location = options.link_url+options.name_galid+'='+urlarr[options.name_galid]+'&'+options.name_picfilename+'='+basename(activeURL);
			}
		return false;
	}


	/*
		API
	*/

	Element.implement({
		slimbox: function(_options, linkMapper) {
			// The processing of a single element is similar to the processing of a collection with a single element
			$$(this).slimbox(_options, linkMapper);

			return this;
		}
	});

	Elements.implement({
		/*
			options:	Optional options object, see Slimbox.open()
			linkMapper:	Optional function taking a link DOM element and an index as arguments and returning an array containing 2 elements:
					the image URL and the image caption (may contain HTML)
			linksFilter:	Optional function taking a link DOM element and an index as arguments and returning true if the element is part of
					the image collection that will be shown on click, false if not. "this" refers to the element that was clicked.
					This function must always return true when the DOM element argument is "this".
		*/
		slimbox: function(_options, linkMapper, linksFilter) {
			linkMapper = linkMapper || function(el) {
				return [el.href, el.title];
			};

			linksFilter = linksFilter || function() {
				return true;
			};

			var links = this;

			links.removeEvents("click").addEvent("click", function() {
				// Build the list of images that will be displayed
				var filteredLinks = links.filter(linksFilter, this);
				return Slimbox.open(filteredLinks.map(linkMapper), filteredLinks.indexOf(this), _options);
			});

			return links;
		}
	});

	return {
		open: function(_images, startImage, _options) {
			options = $extend({
				loop: false,				// Allows to navigate between first and last images
				overlayOpacity: 0.8,			// 1 is opaque, 0 is completely transparent (change the color in the CSS file)
				overlayFadeDuration: 400,		// Duration of the overlay fade-in and fade-out animations (in milliseconds)
				resizeDuration: 400,			// Duration of each of the box resize animations (in milliseconds)
				resizeTransition: false,		// false uses the mootools default transition
				initialWidth: 250,			// Initial width of the box (in pixels)
				initialHeight: 250,			// Initial height of the box (in pixels)
				imageFadeDuration: 400,			// Duration of the image fade-in animation (in milliseconds)
				captionAnimationDuration: 400,		// Duration of the caption animation (in milliseconds)
				counterText: "Image {x} of {y}",	// Translate or change as you wish, or set it to false to disable counter text for image groups
				closeKeys: [27, 88, 67],		// Array of keycodes to close Slimbox, default: Esc (27), 'x' (88), 'c' (67)
				previousKeys: [37, 80],			// Array of keycodes to navigate to the previous image, default: Left arrow (37), 'p' (80)
				nextKeys: [39, 78],			// Array of keycodes to navigate to the next image, default: Right arrow (39), 'n' (78)
				name_galid: "galid",
				name_picfilename: "picfilename",
				link_url: "",
				changeLocation: false
			}, _options || {});

			// Setup effects
			fxOverlay = new Fx.Tween(overlay, {property: "opacity", duration: options.overlayFadeDuration});
			fxResize = new Fx.Morph(center, $extend({duration: options.resizeDuration, link: "chain"}, options.resizeTransition ? {transition: options.resizeTransition} : {}));
			fxImage = new Fx.Tween(image, {property: "opacity", duration: options.imageFadeDuration, onComplete: animateCaption});
			fxBottom = new Fx.Tween(bottom, {property: "margin-top", duration: options.captionAnimationDuration});

			// The function is called for a single image, with URL and Title as first two arguments
			if (typeof _images == "string") {
				_images = [[_images, startImage]];
				startImage = 0;
			}

			middle = win.getScrollTop() + (win.getHeight() / 2);
			centerWidth = options.initialWidth;
			centerHeight = options.initialHeight;
			center.setStyles({top: Math.max(0, middle - (centerHeight / 2)), width: centerWidth, height: centerHeight, marginLeft: -centerWidth/2, display: ""});
			compatibleOverlay = ie6 || (overlay.currentStyle && (overlay.currentStyle.position != "fixed"));
			if (compatibleOverlay) overlay.style.position = "absolute";
			fxOverlay.set(0).start(options.overlayOpacity);
			position();
			setup(1);

			images = _images;
			options.loop = options.loop && (images.length > 1);
			return changeImage(startImage);
		}
	};

})();
