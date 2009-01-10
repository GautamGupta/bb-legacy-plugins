if (window.attachEvent) {window.attachEvent("onload", BBcodeButtonsToolbar);} 
else if (window.addEventListener) {window.addEventListener("load", BBcodeButtonsToolbar, false);} 
else {document.addEventListener("load", BBcodeButtonsToolbar, false);}

function BBcodeButtonsToolbar() {
if (typeof bbField == 'undefined') {bbField = document.getElementsByTagName('textarea')[0];}
if (bbField) { 
smilieCount=0, BBcodeButtons = new Array(), edOpenTags = new Array(); 
BBcodeButtons_init(); var buttonhtml=""; 
for (i = 0; i < BBcodeButtons.length; i++) {
if (BBcodeButtons[i].access) {var accesskey = ' accesskey = "' + BBcodeButtons[i].access + '"'} else {var accesskey = '';}
buttonhtml+='<div title="' + BBcodeButtons[i].title + '" id="' + BBcodeButtons[i].id + '" style="' + BBcodeButtons[i].style + '" ' + accesskey + ' class="ed_button" ';	
buttonhtml+='onmouseover="edOver(this);" onmouseout="edOut(this);" onclick="edClick('+ i+');">' + BBcodeButtons[i].display + '</div>';
}

var CSS='#bbcode_buttons div {float:left; border: 1px outset; color: black; background: buttonface; padding: 0px 6px 1px 6px; margin: 1px 7px 2px 0;'
	+'font: 1.2em times, serif; word-spacing: -1px; height: 16px; vertical-align:middle; line-height:16px;'
	+'text-align:center; position:relative; cursor: pointer; cursor: hand;}';

var stylesheet = document.createElement('style'); stylesheet.type = 'text/css';
if (stylesheet.styleSheet){stylesheet.styleSheet.cssText = CSS;} 
else {stylesheet.appendChild(document.createTextNode(CSS));}
var head = document.getElementsByTagName('head')[0];
head.appendChild(stylesheet);

bbField.setAttribute("style", "clear:both;"); 	// fix textarea to clear toolbar
bbcode_buttons = document.createElement("div");
bbcode_buttons.setAttribute("id", "bbcode_buttons"); 
// bbcode_buttons.setAttribute("style", "background: buttonface;"); 
// bbcode_buttons.style.backgroundColor="buttonface";
bbcode_buttons.innerHTML=buttonhtml;
bbField.parentNode.insertBefore(bbcode_buttons,bbField);
}
}

function edOver(element) {element.style.color="#000080"; element.style.backgroundColor="#ddd";}
function edOut(element)   {element.style.color="#000000"; element.style.backgroundColor="buttonface";}

function BBcodeButton(id, display, tagStart, tagEnd, access, style,title) {
	this.id = id;			// used to name the toolbar button
	this.display = display;	// label on button
	this.tagStart = tagStart; 	// open tag
	this.tagEnd = tagEnd;	// close tag
	this.access = access;	// set to -1 if tag does not need to be closed	
	this.style = style;
	this.title = title;
}

function edClick(button) {
	switch (BBcodeButtons[button].id) {	
		case 'ed_close': edCloseAllTags(button); 	break;		
		case 'ed_link':  edInsertLink(button); break;
		case 'ed_img': edInsertImage(button); break;			 
		default:  edInsertTag(button); break;
	}
}

function edAddTag(button) {
	if (BBcodeButtons[button].tagEnd != '') {edOpenTags[edOpenTags.length] = button; BBcodeButtonText(button,"/ ");}
}

function edRemoveTag(button) {
	for (i = 0; i < edOpenTags.length; i++) {if (edOpenTags[i] == button) {edOpenTags.splice(i, 1); BBcodeButtonText(button,"");}}
}


function BBcodeButtonText(button,someHtml) {
var oldDiv = document.getElementById(BBcodeButtons[button].id);
var newDiv = oldDiv.cloneNode(false);
newDiv.innerHTML = someHtml+BBcodeButtons[button].display; 
oldDiv.parentNode.replaceChild(newDiv, oldDiv);
}


function edCheckOpenTags(button) {
	var tag = 0; for (i = 0; i < edOpenTags.length; i++) {if (edOpenTags[i] == button) {tag++;}}
	if (tag > 0) {return true; } else {return false;} 
}	

function edCloseAllTags() {
	var count = edOpenTags.length; for (i = 0; i < count; i++) {edInsertTag(edOpenTags[edOpenTags.length - 1]);}
}

// insertion code

function edInsertTag(i) {
	//IE support
	if (document.selection) {
		bbField.focus();
		sel = document.selection.createRange();
		if (sel.text.length > 0) {
			sel.text = BBcodeButtons[i].tagStart + sel.text + BBcodeButtons[i].tagEnd;
		}
		else {
			if (!edCheckOpenTags(i) || BBcodeButtons[i].tagEnd == '') {
				sel.text = BBcodeButtons[i].tagStart;
				edAddTag(i);
			}
			else {
				sel.text = BBcodeButtons[i].tagEnd;
				edRemoveTag(i);
			}
		}
		bbField.focus();
	}
	//MOZILLA/NETSCAPE support
	else if (bbField.selectionStart || bbField.selectionStart == '0') {
		var startPos = bbField.selectionStart;
		var endPos = bbField.selectionEnd;
		var cursorPos = endPos;
		var scrollTop = bbField.scrollTop;
		if (startPos != endPos) {
			bbField.value = bbField.value.substring(0, startPos)
			              + BBcodeButtons[i].tagStart
			              + bbField.value.substring(startPos, endPos) 
			              + BBcodeButtons[i].tagEnd
			              + bbField.value.substring(endPos, bbField.value.length);
			cursorPos += BBcodeButtons[i].tagStart.length + BBcodeButtons[i].tagEnd.length;
		}
		else {
			if (!edCheckOpenTags(i) || BBcodeButtons[i].tagEnd == '') {
				bbField.value = bbField.value.substring(0, startPos) 
				              + BBcodeButtons[i].tagStart
				              + bbField.value.substring(endPos, bbField.value.length);
				edAddTag(i);
				cursorPos = startPos + BBcodeButtons[i].tagStart.length;
			}
			else {
				bbField.value = bbField.value.substring(0, startPos) 
				              + BBcodeButtons[i].tagEnd
				              + bbField.value.substring(endPos, bbField.value.length);
				edRemoveTag(i);
				cursorPos = startPos + BBcodeButtons[i].tagEnd.length;
			}
		}
		bbField.focus();
		bbField.selectionStart = cursorPos;
		bbField.selectionEnd = cursorPos;
		bbField.scrollTop = scrollTop;
	}
	else {
		if (!edCheckOpenTags(i) || BBcodeButtons[i].tagEnd == '') {bbField.value += BBcodeButtons[i].tagStart; edAddTag(i);}
		else {bbField.value += BBcodeButtons[i].tagEnd; edRemoveTag(i);}
		bbField.focus();
	}
}

function edInsertContent(myValue) {
	//IE support
	if (document.selection) {bbField.focus(); sel = document.selection.createRange(); sel.text = myValue; bbField.focus();}
	//MOZILLA/NETSCAPE support
	else if (bbField.selectionStart || bbField.selectionStart == '0') {
		var startPos = bbField.selectionStart;
		var endPos = bbField.selectionEnd;
		var scrollTop = bbField.scrollTop;
		bbField.value = bbField.value.substring(0, startPos) + myValue + bbField.value.substring(endPos, bbField.value.length);
		bbField.focus();
		bbField.selectionStart = startPos + myValue.length;
		bbField.selectionEnd = startPos + myValue.length;
		bbField.scrollTop = scrollTop;
	} else {
		bbField.value += myValue;
		bbField.focus();
	}
}

function edInsertLink(i, defaultValue) {
	if (!defaultValue) {defaultValue = 'http://';}
	if (!edCheckOpenTags(i)) {
		var URL = prompt('Enter the URL' ,defaultValue);
		if (URL) {BBcodeButtons[i].tagStart = '[url=' + URL + ']'; edInsertTag(i);}
		if (edCheckOpenTags(i)) {var myValue = prompt('Enter the text for the link', ''); edInsertContent(myValue); edInsertTag(i);}
	}
	else {edInsertTag(i);}
}

function edInsertImage() {
	var myValue = prompt('Enter the URL of the image', 'http://');
	if (myValue) {
		myValue = '[img]'+ myValue+'[/img]';
		edInsertContent(myValue);
	}
}

function edSmilies(text) {
	smilieCount=smilieCount+1; if (smilieCount<6) {edInsertContent(" "+text+" ");} else {alert("Please use only a few smilies at a time.");}
}
