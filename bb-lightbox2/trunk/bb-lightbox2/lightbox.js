// -----------------------------------------------------------------------------------
//
//	Lightbox v2.02
//	by Lokesh Dhakar - http://www.huddletogether.com
//	3/31/06
//
//	For more information on this script, visit:
//	http://huddletogether.com/projects/lightbox2/
//
//	Licensed under the Creative Commons Attribution 2.5 License - http://creativecommons.org/licenses/by/2.5/
//	
//	Credit also due to those who have helped, inspired, and made their code available to the public.
//	Including: Scott Upton(uptonic.com), Peter-Paul Koch(quirksmode.org), Thomas Fuchs(mir.aculo.us), and others.
//
//
// -----------------------------------------------------------------------------------
/*

	Table of Contents
	-----------------
	Configuration
	Global Variables

	Extending Built-in Objects	
	- Object.extend(Element)
	- Array.prototype.removeDuplicates()
	- Array.prototype.empty()

	Lightbox Class Declaration
	- initialize()
	- start()
	- changeImage()
	- resizeImageContainer()
	- showImage()
	- updateDetails()
	- updateNav()
	- enableKeyboardNav()
	- disableKeyboardNav()
	- keyboardAction()
	- preloadNeighborImages()
	- end()
	
	Miscellaneous Functions
	- getPageScroll()
	- getPageSize()
	- getKey()
	- listenKey()
	- showSelectBoxes()
	- hideSelectBoxes()
	- pause()
	- initLightbox()
	
	Function Calls
	- addLoadEvent(initLightbox)
	
*/
// -----------------------------------------------------------------------------------

//
//	Configuration
//
//var fileLoadingImage = "images/loading.gif";		
//var fileBottomNavCloseImage = "images/closelabel.gif";

var resizeSpeed = 8;	// controls the speed of the image resizing (1=slowest and 10=fastest)

var borderSize = 10;	//if you adjust the padding in the CSS, you will need to update this variable

// -----------------------------------------------------------------------------------

//
//	Global Variables
//
var imageArray = new Array;
var activeImage;

if(resizeSpeed > 10){ resizeSpeed = 10;}
if(resizeSpeed < 1){ resizeSpeed = 1;}
resizeDuration = (11 - resizeSpeed) * 0.15;

// -----------------------------------------------------------------------------------

//
//	Additional methods for Element added by SU, Couloir
//	- further additions by Lokesh Dhakar (huddletogether.com)
//
Object.extend(Element, {
	getWidth: function(element) {
	   	element = $(element);
	   	return element.offsetWidth; 
	},
	setWidth: function(element,w) {
	   	element = $(element);
    	element.style.width = w +"px";
	},
	setHeight: function(element,h) {
   		element = $(element);
    	element.style.height = h +"px";
	},
	setTop: function(element,t) {
	   	element = $(element);
    	element.style.top = t +"px";
	},
	setSrc: function(element,src) {
    	element = $(element);
    	element.src = src; 
	},
	setHref: function(element,href) {
    	element = $(element);
    	element.href = href; 
	},
	setInnerHTML: function(element,content) {
		element = $(element);
		element.innerHTML = content;
	}
});

// -----------------------------------------------------------------------------------

//
//	Extending built-in Array object
//	- array.removeDuplicates()
//	- array.empty()
//
Array.prototype.removeDuplicates = function () {
	for(i = 1; i < this.length; i++){
		if(this[i][0] == this[i-1][0]){
			this.splice(i,1);
		}
	}
}

// -----------------------------------------------------------------------------------

Array.prototype.empty = function () {
	for(i = 0; i <= this.length; i++){
		this.shift();
	}
}

// -----------------------------------------------------------------------------------

//
//	Lightbox Class Declaration
//	- initialize()
//	- start()
//	- changeImage()
//	- resizeImageContainer()
//	- showImage()
//	- updateDetails()
//	- updateNav()
//	- enableKeyboardNav()
//	- disableKeyboardNav()
//	- keyboardNavAction()
//	- preloadNeighborImages()
//	- end()
//
//	Structuring of code inspired by Scott Upton (http://www.uptonic.com/)
//
var Lightbox = Class.create();

Lightbox.prototype = {
	
	// initialize()
	// Constructor runs on completion of the DOM loading. Loops through anchor tags looking for 
	// 'lightbox' references and applies onclick events to appropriate links. The 2nd section of
	// the function inserts html at the bottom of the page which is used to display the shadow 
	// overlay and the image container.
	//
	initialize: function() {	
		if (!document.getElementsByTagName){ return; }
		var anchors = document.getElementsByTagName('a');

		// loop through all anchor tags
		for (var i=0; i<anchors.length; i++){
			var anchor = anchors[i];
			
			var relAttribute = String(anchor.getAttribute('rel'));
			
			// use the string.match() method to catch 'lightbox' references in the rel attribute
			if (anchor.getAttribute('href') && (relAttribute.toLowerCase().match('lightbox'))){
				anchor.onclick = function () {myLightbox.start(this); return false;}
			}
		}

		// The rest of this code inserts html at the bottom of the page that looks similar to this:
		//
		//	<div id="overlay"></div>
		//	<div id="lightbox">
		//		<div id="outerImageContainer">
		//			<div id="imageContainer">
		//				<img id="lightboxImage">
		//				<div style="" id="hoverNav">
		//					<a href="#" id="prevLink"></a>
		//					<a href="#" id="nextLink"></a>
		//				</div>
		//				<div id="loading">
		//					<a href="#" id="loadingLink">
		//						<img src="images/loading.gif">
		//					</a>
		//				</div>
		//			</div>
		//		</div>
		//		<div id="imageDataContainer">
		//			<div id="imageData">
		//				<div id="imageDetails">
		//					<span id="caption"></span>
		//					<span id="numberDisplay"></span>
		//				</div>
		//				<div id="bottomNav">
		//					<a href="#" id="bottomNavClose">
		//						<img src="images/close.gif">
		//					</a>
		//				</div>
		//			</div>
		//		</div>
		//	</div>


		var objBody = document.getElementsByTagName("body").item(0);
		
		var objOverlay = document.createElement("div");
		objOverlay.setAttribute('id','stimuli_overlay');
		objOverlay.style.display = 'none';
		objOverlay.onclick = function() { myLightbox.end(); return false; }
		objBody.appendChild(objOverlay);
		
		var objLightbox = document.createElement("div");
		objLightbox.setAttribute('id','stimuli_lightbox');
		objLightbox.style.display = 'none';
		objLightbox.onclick = function(e) {	// close Lightbox is user clicks shadow overlay
			if (!e) var e = window.event;
			var clickObj = Event.element(e).id;
			if ( clickObj == 'stimuli_lightbox') {
				myLightbox.end();
			}
		};
		objBody.appendChild(objLightbox);
	
		var objOuterImageContainer = document.createElement("div");
		objOuterImageContainer.setAttribute('id','stimuli_outerImageContainer');
		objLightbox.appendChild(objOuterImageContainer);

		var objImageContainer = document.createElement("div");
		objImageContainer.setAttribute('id','stimuli_imageContainer');
		objOuterImageContainer.appendChild(objImageContainer);
	
		var objLightboxImage = document.createElement("img");
		objLightboxImage.setAttribute('id','stimuli_lightboxImage');
		objImageContainer.appendChild(objLightboxImage);
	
		var objHoverNav = document.createElement("div");
		objHoverNav.setAttribute('id','stimuli_hoverNav');
		objImageContainer.appendChild(objHoverNav);
	
		var objPrevLink = document.createElement("a");
		objPrevLink.setAttribute('id','stimuli_prevLink');
		objPrevLink.setAttribute('href','#');
		objHoverNav.appendChild(objPrevLink);
		
		var objNextLink = document.createElement("a");
		objNextLink.setAttribute('id','stimuli_nextLink');
		objNextLink.setAttribute('href','#');
		objHoverNav.appendChild(objNextLink);
	
		var objLoading = document.createElement("div");
		objLoading.setAttribute('id','stimuli_loading');
		objImageContainer.appendChild(objLoading);
	
		var objLoadingLink = document.createElement("a");
		objLoadingLink.setAttribute('id','stimuli_loadingLink');
		objLoadingLink.setAttribute('href','#');
		objLoadingLink.onclick = function() { myLightbox.end(); return false; }
		objLoading.appendChild(objLoadingLink);
	
		//var objLoadingImage = document.createElement("img");
		//objLoadingImage.setAttribute('src', fileLoadingImage);
		//objLoadingLink.appendChild(objLoadingImage);

		var objImageDataContainer = document.createElement("div");
		objImageDataContainer.setAttribute('id','stimuli_imageDataContainer');
		objImageDataContainer.className = 'clearfix';
		objLightbox.appendChild(objImageDataContainer);

		var objImageData = document.createElement("div");
		objImageData.setAttribute('id','stimuli_imageData');
		objImageDataContainer.appendChild(objImageData);
	
		var objImageDetails = document.createElement("div");
		objImageDetails.setAttribute('id','stimuli_imageDetails');
		objImageData.appendChild(objImageDetails);
	
		var objCaption = document.createElement("span");
		objCaption.setAttribute('id','stimuli_caption');
		objImageDetails.appendChild(objCaption);
	
		var objNumberDisplay = document.createElement("span");
		objNumberDisplay.setAttribute('id','stimuli_numberDisplay');
		objImageDetails.appendChild(objNumberDisplay);
		
		var objBottomNav = document.createElement("div");
		objBottomNav.setAttribute('id','stimuli_bottomNav');
		objImageData.appendChild(objBottomNav);
	
		var objBottomNavCloseLink = document.createElement("a");
		objBottomNavCloseLink.setAttribute('id','stimuli_bottomNavClose');
		objBottomNavCloseLink.setAttribute('href','#');
		objBottomNavCloseLink.onclick = function() { myLightbox.end(); return false; }
		objBottomNav.appendChild(objBottomNavCloseLink);
	
		//var objBottomNavCloseImage = document.createElement("img");
		//objBottomNavCloseImage.setAttribute('src', fileBottomNavCloseImage);
		//objBottomNavCloseLink.appendChild(objBottomNavCloseImage);
	},
	
	//
	//	start()
	//	Display overlay and lightbox. If image is part of a set, add siblings to imageArray.
	//
	start: function(imageLink) {	

		hideSelectBoxes();

		// stretch overlay to fill page and fade in
		var arrayPageSize = getPageSize();
		Element.setHeight('stimuli_overlay', arrayPageSize[1]);
		new Effect.Appear('stimuli_overlay', { duration: 0.2, from: 0.0, to: 0.8 });

		imageArray = [];
		imageNum = 0;		

		if (!document.getElementsByTagName){ return; }
		var anchors = document.getElementsByTagName('a');

		// if image is NOT part of a set..
		if((imageLink.getAttribute('rel') == 'lightbox')){
			// add single image to imageArray
			imageArray.push(new Array(imageLink.getAttribute('href'), imageLink.getAttribute('title')));			
		} else {
		// if image is part of a set..

			// loop through anchors, find other images in set, and add them to imageArray
			for (var i=0; i<anchors.length; i++){
				var anchor = anchors[i];
				if (anchor.getAttribute('href') && (anchor.getAttribute('rel') == imageLink.getAttribute('rel'))){
					imageArray.push(new Array(anchor.getAttribute('href'), anchor.getAttribute('title')));
				}
			}
			imageArray.removeDuplicates();
			while(imageArray[imageNum][0] != imageLink.getAttribute('href')) { imageNum++;}
		}

		// calculate top offset for the lightbox and display 
		var arrayPageSize = getPageSize();
		var arrayPageScroll = getPageScroll();
		var lightboxTop = arrayPageScroll[1] + (arrayPageSize[3] / 15);

		Element.setTop('stimuli_lightbox', lightboxTop);
		Element.show('stimuli_lightbox');
		
		this.changeImage(imageNum);
	},

	//
	//	changeImage()
	//	Hide most elements and preload image in preparation for resizing image container.
	//
	changeImage: function(imageNum) {	
		
		activeImage = imageNum;	// update global var

		// hide elements during transition
		Element.show('stimuli_loading');
		Element.hide('stimuli_lightboxImage');
		Element.hide('stimuli_hoverNav');
		Element.hide('stimuli_prevLink');
		Element.hide('stimuli_nextLink');
		Element.hide('stimuli_imageDataContainer');
		Element.hide('stimuli_numberDisplay');		
		
		imgPreloader = new Image();
		
		// once image is preloaded, resize image container
		imgPreloader.onload=function(){
			Element.setSrc('stimuli_lightboxImage', imageArray[activeImage][0]);
			myLightbox.resizeImageContainer(imgPreloader.width, imgPreloader.height);
		}
		imgPreloader.src = imageArray[activeImage][0];
	},

	//
	//	resizeImageContainer()
	//
	resizeImageContainer: function( imgWidth, imgHeight) {

		// get current height and width
		this.wCur = Element.getWidth('stimuli_outerImageContainer');
		this.hCur = Element.getHeight('stimuli_outerImageContainer');

		// scalars based on change from old to new
		this.xScale = ((imgWidth  + (borderSize * 2)) / this.wCur) * 100;
		this.yScale = ((imgHeight  + (borderSize * 2)) / this.hCur) * 100;

		// calculate size difference between new and old image, and resize if necessary
		wDiff = (this.wCur - borderSize * 2) - imgWidth;
		hDiff = (this.hCur - borderSize * 2) - imgHeight;

		if(!( hDiff == 0)){ new Effect.Scale('stimuli_outerImageContainer', this.yScale, {scaleX: false, duration: resizeDuration, queue: 'front'}); }
		if(!( wDiff == 0)){ new Effect.Scale('stimuli_outerImageContainer', this.xScale, {scaleY: false, delay: resizeDuration, duration: resizeDuration}); }

		// if new and old image are same size and no scaling transition is necessary, 
		// do a quick pause to prevent image flicker.
		if((hDiff == 0) && (wDiff == 0)){
			if (navigator.appVersion.indexOf("MSIE")!=-1){ pause(250); } else { pause(100);} 
		}

		Element.setHeight('stimuli_prevLink', imgHeight);
		Element.setHeight('stimuli_nextLink', imgHeight);
		Element.setWidth( 'stimuli_imageDataContainer', imgWidth + (borderSize * 2));

		this.showImage();
	},
	
	//
	//	showImage()
	//	Display image and begin preloading neighbors.
	//
	showImage: function(){
		Element.hide('stimuli_loading');
		new Effect.Appear('stimuli_lightboxImage', { duration: 0.5, queue: 'end', afterFinish: function(){	myLightbox.updateDetails(); } });
		this.preloadNeighborImages();
	},

	//
	//	updateDetails()
	//	Display caption, image number, and bottom nav.
	//
	updateDetails: function() {
	
		Element.show('stimuli_caption');
		Element.setInnerHTML( 'stimuli_caption', imageArray[activeImage][1]);
		
		// if image is part of set display 'Image x of x' 
		if(imageArray.length > 1){
			Element.show('stimuli_numberDisplay');
			Element.setInnerHTML( 'stimuli_numberDisplay', "Image " + eval(activeImage + 1) + " of " + imageArray.length);
		}

		new Effect.Parallel(
			[ new Effect.SlideDown( 'stimuli_imageDataContainer', { sync: true, duration: resizeDuration + 0.25, from: 0.0, to: 1.0 }), 
			  new Effect.Appear('stimuli_imageDataContainer', { sync: true, duration: 1.0 }) ], 
			{ duration: 0.65, afterFinish: function() { myLightbox.updateNav();} } 
		);
	},

	//
	//	updateNav()
	//	Display appropriate previous and next hover navigation.
	//
	updateNav: function() {

		Element.show('stimuli_hoverNav');				

		// if not first image in set, display prev image button
		if(activeImage != 0){
			Element.show('stimuli_prevLink');
			document.getElementById('stimuli_prevLink').onclick = function() {
				myLightbox.changeImage(activeImage - 1); return false;
			}
		}

		// if not last image in set, display next image button
		if(activeImage != (imageArray.length - 1)){
			Element.show('stimuli_nextLink');
			document.getElementById('stimuli_nextLink').onclick = function() {
				myLightbox.changeImage(activeImage + 1); return false;
			}
		}
		
		this.enableKeyboardNav();
	},

	//
	//	enableKeyboardNav()
	//
	enableKeyboardNav: function() {
		document.onkeydown = this.keyboardAction; 
	},

	//
	//	disableKeyboardNav()
	//
	disableKeyboardNav: function() {
		document.onkeydown = '';
	},

	//
	//	keyboardAction()
	//
	keyboardAction: function(e) {
		if (e == null) { // ie
			keycode = event.keyCode;
		} else { // mozilla
			keycode = e.which;
		}

		key = String.fromCharCode(keycode).toLowerCase();
		// 27 = esc, 37 = left arrow, 39 = right arow
		if((keycode == 27) || (key == 'x') || (key == 'o') || (key == 'c')){	// close lightbox
			myLightbox.end();
		} else if((keycode == 37) || (key == 'p')){	// display previous image
			if(activeImage != 0){
				myLightbox.disableKeyboardNav();
				myLightbox.changeImage(activeImage - 1);
			}
		} else if((keycode == 39) || (key == 'n')){	// display next image
			if(activeImage != (imageArray.length - 1)){
				myLightbox.disableKeyboardNav();
				myLightbox.changeImage(activeImage + 1);
			}
		}
	},

	//
	//	preloadNeighborImages()
	//	Preload previous and next images.
	//
	preloadNeighborImages: function(){

		if((imageArray.length - 1) > activeImage){
			preloadNextImage = new Image();
			preloadNextImage.src = imageArray[activeImage + 1][0];
		}
		if(activeImage > 0){
			preloadPrevImage = new Image();
			preloadPrevImage.src = imageArray[activeImage - 1][0];
		}
	
	},

	//
	//	end()
	//
	end: function() {
		this.disableKeyboardNav();
		Element.hide('stimuli_lightbox');
		new Effect.Fade('stimuli_overlay', { duration: 0.2});
		showSelectBoxes();
	}
}

// -----------------------------------------------------------------------------------

//
// getPageScroll()
// Returns array with x,y page scroll values.
// Core code from - quirksmode.org
//
function getPageScroll(){

	var yScroll;

	if (self.pageYOffset) {
		yScroll = self.pageYOffset;
	} else if (document.documentElement && document.documentElement.scrollTop){	 // Explorer 6 Strict
		yScroll = document.documentElement.scrollTop;
	} else if (document.body) {// all other Explorers
		yScroll = document.body.scrollTop;
	}

	arrayPageScroll = new Array('',yScroll) 
	return arrayPageScroll;
}

// -----------------------------------------------------------------------------------

//
// getPageSize()
// Returns array with page width, height and window width, height
// Core code from - quirksmode.org
// Edit for Firefox by pHaez
//
function getPageSize(){
	
	var xScroll, yScroll;
	
	if (window.innerHeight && window.scrollMaxY) {	
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}
	
	var windowWidth, windowHeight;
	if (self.innerHeight) {	// all except Explorer
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}	
	
	// for small pages with total height less then height of the viewport
	if(yScroll < windowHeight){
		pageHeight = windowHeight;
	} else { 
		pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if(xScroll < windowWidth){	
		pageWidth = windowWidth;
	} else {
		pageWidth = xScroll;
	}


	arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight) 
	return arrayPageSize;
}

// -----------------------------------------------------------------------------------

//
// getKey(key)
// Gets keycode. If 'x' is pressed then it hides the lightbox.
//
function getKey(e){
	if (e == null) { // ie
		keycode = event.keyCode;
	} else { // mozilla
		keycode = e.which;
	}
	key = String.fromCharCode(keycode).toLowerCase();
	
	if(key == 'x'){
	}
}

// -----------------------------------------------------------------------------------

//
// listenKey()
//
function listenKey () {	document.onkeypress = getKey; }
	
// ---------------------------------------------------

function showSelectBoxes(){
	selects = document.getElementsByTagName("select");
	for (i = 0; i != selects.length; i++) {
		selects[i].style.visibility = "visible";
	}
}

// ---------------------------------------------------

function hideSelectBoxes(){
	selects = document.getElementsByTagName("select");
	for (i = 0; i != selects.length; i++) {
		selects[i].style.visibility = "hidden";
	}
}

// ---------------------------------------------------

//
// pause(numberMillis)
// Pauses code execution for specified time. Uses busy code, not good.
// Code from http://www.faqts.com/knowledge_base/view.phtml/aid/1602
//
function pause(numberMillis) {
	var now = new Date();
	var exitTime = now.getTime() + numberMillis;
	while (true) {
		now = new Date();
		if (now.getTime() > exitTime)
			return;
	}
}

// ---------------------------------------------------



function initLightbox() { myLightbox = new Lightbox(); }
Event.observe(window, 'load', initLightbox, false);