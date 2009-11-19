jQuery(document).ready(function(){
   jQuery("#checkLink").click(function() {
        check();
   });
   jQuery("#postformsub").click(function () {
        var disableClick = function() { return false; };
        jQuery('#checkLink').click(disableClick);
        AtD.restoreTextArea('post_content');
   });
});
function check()
{
        AtD.checkTextAreaCrossAJAX('post_content', 'checkLink', 'Edit Text');
}

/*
 * AtD jQuery Section
 */
var AtD = 
{
   rpc : 'http://www.your_server_here/directory/proxy.php?url=',
   rpc_css : 'http://www.polishmywriting.com/atd_jquery/server/proxycss.php?data=',
   api_key : '',
   ignore_types : ['Bias Language', 'Cliches', 'Complex Expression', 'Diacritical Marks', 'Double Negatives', 'Hidden Verbs', 'Jargon Language', 'Passive voice', 'Phrases to Avoid', 'Redundant Expression'],
   ignore_strings : [],
   setIgnoreStrings : function(string)
   {
      AtD.ignore_strings = string.split(/,/g);
   },
   showTypes : function(string)
   {
      var show_these_types = string.split(/,/g);
      AtD.ignore_types = jQuery.grep(AtD.ignore_types, function(value)
      {
         return jQuery.inArray(value, show_these_types) == -1; 
      });
   },
   checkCrossAJAX : function(container_id, callback_f)
   {
      AtD.callback_f = callback_f;
      AtD.remove(container_id);
      var container = jQuery('#' + container_id);
      var html = container.html();
      text     = jQuery.trim(container.html());
      text     = encodeURIComponent( text.replace( /\%/g, '%25' ) );
      if ((text.length > 2000 && navigator.appName == 'Microsoft Internet Explorer') || text.length > 7800)
      {
         if (callback_f != undefined && callback_f.error != undefined)
            callback_f.error("Maximum text length for this browser exceeded");
         return;
      }
      CSSHttpRequest.get(AtD.rpc_css + text + "&nocache=" + (new Date().getTime()), function(response)
      {
            var xml;
            if (navigator.appName == 'Microsoft Internet Explorer') 
            {
               xml = new ActiveXObject("Microsoft.XMLDOM");
               xml.async = false;
               xml.loadXML(response);
            } 
            else 
            {
               xml = (new DOMParser()).parseFromString(response, 'text/xml');
            }
            AtD.container = container_id;
            var count = AtD.processXML(container_id, xml);
            if (AtD.callback_f != undefined && AtD.callback_f.ready != undefined)
		AtD.callback_f.ready(count);
            if (count == 0 && AtD.callback_f != undefined && AtD.callback_f.success != undefined)
                AtD.callback_f.success(count);
            AtD.counter = count;
            AtD.count   = count;
      });
   },
   check : function(container_id, callback_f)
   {
      AtD.callback_f = callback_f;
      AtD.remove(container_id);	
      var container = jQuery('#' + container_id);
      var html = container.html();
      text     = jQuery.trim(container.html());
      text     = encodeURIComponent( text );
      jQuery.ajax({
         type : "POST",
         url : AtD.rpc + '/checkDocument',
         data : 'key=' + AtD.api_key + '&data=' + text,
         format : 'raw', 
         dataType : (jQuery.browser.msie) ? "text" : "xml",
         error : function(XHR, status, error) 
         {
            if (AtD.callback_f != undefined && AtD.callback_f.error != undefined)
               AtD.callback_f.error(status + ": " + error);
         },
         success : function(data)
         {
            var xml;
            if (typeof data == "string") 
            {
               xml = new ActiveXObject("Microsoft.XMLDOM");
               xml.async = false;
               xml.loadXML(data);
            } 
            else 
            {
               xml = data;
            }
            AtD.container = container_id;
            var count = AtD.processXML(container_id, xml);
            if (AtD.callback_f != undefined && AtD.callback_f.ready != undefined)
		AtD.callback_f.ready(count);
            if (count == 0 && AtD.callback_f != undefined && AtD.callback_f.success != undefined)
               AtD.callback_f.success(count);
            AtD.counter = count;
            AtD.count   = count;
         }
      });
   },
   remove : function(container_id) 
   {
      AtD._removeWords(container_id, null);
   }
};
AtD.makeError = function(error_s, tokens, type, seps, pre)
{        
   var struct = new Object();
   struct.type = type;
   struct.string = error_s;
   struct.tokens = tokens;
   if (new RegExp(error_s + '\b').test(error_s))
   {
      struct.regexp = new RegExp("(?!"+error_s+"<)" + error_s.replace(/\s+/g, seps) + "\\b");
   }
   else
   {
      struct.regexp = new RegExp("(?!"+error_s+"<)" + error_s.replace(/\s+/g, seps));
   }
   struct.used   = false;
   return struct;
};
AtD.addToErrorStructure = function(errors, list, type, seps)                 
{
   var parent = this;                  
   jQuery.map(list, function(error)
   {
      var tokens = error["word"].split(/\s+/);
      var pre    = error["pre"];
      var first  = tokens[0];
      if (errors['__' + first] == undefined)
      {      
         errors['__' + first] = new Object();
         errors['__' + first].pretoks  = {};
         errors['__' + first].defaults = new Array();
      }
      if (pre == "")  
      {               
         errors['__' + first].defaults.push(parent.makeError(error["word"], tokens, type, seps, pre));
      }
      else         
      {
         if (errors['__' + first].pretoks['__' + pre] == undefined)
         {
            errors['__' + first].pretoks['__' + pre] = new Array();
         }         
         errors['__' + first].pretoks['__' + pre].push(parent.makeError(error["word"], tokens, type, seps, pre));
      }
   });
};
AtD.buildErrorStructure = function(spellingList, enrichmentList, grammarList)
{
   var seps   = this._getSeparators();
   var errors = {};
   this.addToErrorStructure(errors, spellingList, "hiddenSpellError", seps);            
   this.addToErrorStructure(errors, grammarList, "hiddenGrammarError", seps);
   this.addToErrorStructure(errors, enrichmentList, "hiddenSuggestion", seps);
   return errors;
};
AtD._getSeparators = function()
{
   var re = '', i;
   var str = '"s!#$%&()*+,./:;<=>?@[\]^_{|}';
   for (i=0; i<str.length; i++)
   {
      re += '\\' + str.charAt(i);
   }
   return "(?:(?:[\xa0" + re  + "])|(?:\\-\\-))+";
};        
AtD.processXML = function(container_id, responseXML)
{
   var ignore = {};
   jQuery.map(AtD.ignore_strings, function(string)
   {
     ignore[string] = 1;
   }); 
   var types = {};
   jQuery.map(AtD.ignore_types, function(type)
   {
      types[type] = 1;
   });
   AtD.suggestions = [];
   var errors = responseXML.getElementsByTagName('error');
   var grammarErrors    = [];
   var spellingErrors   = [];
   var enrichment       = [];
   for (i = 0; i < errors.length; i++)
   {
      if (errors[i].getElementsByTagName('string').item(0).firstChild != null)
      {
         var errorString      = errors[i].getElementsByTagName('string').item(0).firstChild.data;
         var errorType        = errors[i].getElementsByTagName('type').item(0).firstChild.data;
         var errorDescription = errors[i].getElementsByTagName('description').item(0).firstChild.data;
         var errorContext;
         if (errors[i].getElementsByTagName('precontext').item(0).firstChild != null)
         {
            errorContext = errors[i].getElementsByTagName('precontext').item(0).firstChild.data;   
         }
         else
         {
            errorContext = "";
         }
         if (ignore[errorString] == undefined)
         {
            var suggestion = {};
            suggestion["description"] = errorDescription;
            suggestion["suggestions"] = [];
            suggestion["matcher"]     = new RegExp('^' + errorString.replace(/\s+/, AtD._getSeparators()) + '$');
            suggestion["context"]     = errorContext;
            suggestion["string"]      = errorString;
            suggestion["type"]        = errorType;
            AtD.suggestions.push(suggestion);
            if (errors[i].getElementsByTagName('suggestions').item(0) != undefined)
            {
               var suggestions = errors[i].getElementsByTagName('suggestions').item(0).getElementsByTagName('option');
               for (j = 0; j < suggestions.length; j++)
               {
                  suggestion["suggestions"].push(suggestions[j].firstChild.data);
               }
            }
            if (errors[i].getElementsByTagName('url').item(0) != undefined)
            {
               var errorUrl = errors[i].getElementsByTagName('url').item(0).firstChild.data;
               suggestion["moreinfo"] = errorUrl + '&theme=tinymce';
            }
            if (types[errorDescription] == undefined)
            {
               if (errorType == "suggestion")
                  enrichment.push({ word: errorString, pre: errorContext });
               if (errorType == "grammar")
                  grammarErrors.push({ word: errorString, pre: errorContext });
            }
            if (errorType == "spelling" || errorDescription == "Homophone")
               spellingErrors.push({ word: errorString, pre: errorContext });
            if (errorDescription == 'Cliches')
               suggestion["description"] = 'Clich&eacute;s';
         }
      }
   }
   var ecount = spellingErrors.length + grammarErrors.length + enrichment.length;
   if (ecount > 0)
   {
      var errorStruct = AtD.buildErrorStructure(spellingErrors, enrichment, grammarErrors);
      ecount = AtD.markMyWords(container_id, errorStruct);
   }
   return ecount;
};
AtD.tokenIterate =
{
     init: function(tokens)
     {
        this.tokens = tokens;
        this.index  = 0;
        this.count  = 0;
        this.last   = 0;
     },
     next: function()
     {
        var current = this.tokens[this.index];
        this.count = this.last;
        this.last += current.length + 1;
        this.index++;
        return current;
     },
     hasNext: function()
     {
        return this.index < this.tokens.length;
     },
     hasNextN: function(n)
     {
        return (this.index + n) < this.tokens.length;
     },
     skip: function(m, n)
     {
        this.index += m;
        this.last += n;
        if (this.index < this.tokens.length)
        {
           this.count = this.last - this.tokens[this.index].length;
        }
     },
     getCount: function()
     {
        return this.count;
     },
     peek: function(n)
     {
        var peepers = new Array();
        var end = this.index + n;
        for (var x = this.index; x < end; x++)
        {
           peepers.push(this.tokens[x]);
        }
        return peepers;
     }
};
AtD.useSuggestion = function(word)
{
   AtD.errorElement.text(word);
   AtD.errorElement.replaceWith(AtD.errorElement.html());
   AtD.counter --;
   if (AtD.counter == 0 && AtD.callback_f != undefined && AtD.callback_f.success != undefined)
     AtD.callback_f.success(AtD.count);
};
AtD.editSelection = function()
{
   if (AtD.callback_f != undefined && AtD.callback_f.editSelection != undefined)
      AtD.callback_f.editSelection(AtD.errorElement);
};
AtD.ignoreSuggestion = function()
{
   AtD.errorElement.replaceWith(AtD.errorElement.html());
   AtD.counter --;
   if (AtD.counter == 0 && AtD.callback_f != undefined && AtD.callback_f.success != undefined)
      AtD.callback_f.success(AtD.count);
};
AtD.ignoreAll = function(container_id)
{
   var target = AtD.errorElement.text();
   var removed = AtD._removeWords(container_id, target);
   AtD.counter -= removed;
   if (AtD.counter == 0 && AtD.callback_f != undefined && AtD.callback_f.success != undefined)
      AtD.callback_f.success(AtD.count);
   if (AtD.callback_f != undefined && AtD.callback_f.ignore != undefined) 
   {
      AtD.callback_f.ignore(target);
      AtD.ignore_strings.push(target);
   }
};
AtD.explainError = function()
{
   if (AtD.callback_f != undefined && AtD.callback_f.explain != undefined)
      AtD.callback_f.explain(AtD.explainURL);
};
AtD.suggest = function(element)
{
   if (jQuery('#suggestmenu').length == 0)
   {
      var suggest = jQuery('<div id="suggestmenu"></div>');
      suggest.prependTo('body');
   }
   else
   {
      var suggest = jQuery('#suggestmenu');
      suggest.hide();
   }
   var text = jQuery(element).text();
   var context = jQuery.trim(jQuery(element).attr('pre')).replace(/[\\,!\\?\\."]/g, '');
   var errorDescription;
   var len = AtD.suggestions.length;
   for (var i = 0; i < len; i++)
   {
      var key = AtD.suggestions[i]["string"];           
      if ((context == "" || context == AtD.suggestions[i]["context"]) && AtD.suggestions[i]["matcher"].test(text))
      {
         errorDescription = AtD.suggestions[i];
         break;
      }
   }
   AtD.errorElement = jQuery(element);
   suggest.empty();
   if (errorDescription == undefined)
   {
      suggest.append('<strong>No suggestions</strong>');
   }
   else if (errorDescription["suggestions"].length == 0)
   {
      suggest.append('<strong>' + errorDescription['description'] + '</strong>');
   }
   else
   {
      suggest.append('<strong>' + errorDescription['description'] + '</strong>');
      for (var i = 0; i < errorDescription["suggestions"].length; i++)
      {
         (function(sugg)
         {
            suggest.append('<a onclick="AtD.useSuggestion(\'' + sugg + '\');">' + sugg + '</a>');
         })(errorDescription["suggestions"][i]);
      }
   }
   if (AtD.callback_f != undefined && AtD.callback_f.explain != undefined && errorDescription['moreinfo'] != undefined)
   {
      suggest.append('<a onclick="AtD.explainError();" class="spell_sep_top">Explain...</a>');
      AtD.explainURL = errorDescription['moreinfo'];
   }
   suggest.append('<a onclick="AtD.ignoreSuggestion();" class="spell_sep_top">Ignore suggestion</a>');
   if (AtD.callback_f != undefined && AtD.callback_f.editSelection != undefined)
   {
      if (AtD.callback_f != undefined && AtD.callback_f.ignore != undefined)
         suggest.append('<a onclick="AtD.ignoreAll(\'' + AtD.container + '\');">Ignore always</a>');
      else
         suggest.append('<a onclick="AtD.ignoreAll(\'' + AtD.container + '\');">Ignore all</a>');
      suggest.append('<a onclick="AtD.editSelection(\'' + AtD.container + '\');" class="spell_sep_bottom spell_sep_top">Edit Selection...</a>');
   }
   else
   {
      if (AtD.callback_f != undefined && AtD.callback_f.ignore != undefined)
         suggest.append('<a onclick="AtD.ignoreAll(\'' + AtD.container + '\')" class="spell_sep_bottom">Ignore always</a>');
      else
         suggest.append('<a onclick="AtD.ignoreAll(\'' + AtD.container + '\')" class="spell_sep_bottom">Ignore all</a>');
   }
   var pos = jQuery(element).offset();
   var width = jQuery(element).width();
   jQuery(suggest).css({ left: (pos.left + width) + 'px', top: pos.top + 'px' });
   jQuery(suggest).fadeIn(200);
   AtD.suggestShow = true;
   setTimeout(function()
   {
      jQuery("body").bind("click", function()
      {
         if (!AtD.suggestShow)
         {
            jQuery('#suggestmenu').fadeOut(200);      
         }
      });
   }, 1);
   setTimeout(function()
   {
      AtD.suggestShow = false;
   }, 2); 
}
AtD.markMyWords = function(container_id, errors)
{
   var seps  = new RegExp(this._getSeparators());
   var nl = new Array();
   var ecount = 0;
   this._walk(container_id, function(n)
   {   
      if (n.nodeType == 3 && !jQuery(n).hasClass("hiddenSpellError") && !jQuery(n).hasClass("hiddenGrammarError") && !jQuery(n).hasClass("hiddenSuggestion"))
      {
         nl.push(n);  
      }
   });
   var tokenIterate = this.tokenIterate;
   jQuery.map(nl, function(n)
   {
      var v;
      if (n.nodeType == 3)
      {
         v = n.nodeValue;
         var tokens = n.nodeValue.split(seps);
         var previous = "";
         var doReplaces = [];
         tokenIterate.init(tokens);
         while (tokenIterate.hasNext())
         {
            var token = tokenIterate.next();
            var current  = errors['__' + token];
            var defaults;
            if (current != undefined && current.pretoks != undefined)
            {
               defaults = current.defaults;
               current = current.pretoks['__' + previous];
               var done = false;
               var prev, curr;
               prev = v.substr(0, tokenIterate.getCount());
               curr = v.substr(prev.length, v.length);
               var checkErrors = function(error)
               {
                  if (!done && error != undefined && !error.used && error.regexp.test(curr))
                  {
                     var oldlen = curr.length;
                     doReplaces.push([error.regexp, '<span class="'+error.type+'" pre="'+previous+'" onClick=\"AtD.suggest(this);\">$&</span>']);
                     error.used = true;
                     done = true;
                     tokenIterate.skip(error.tokens.length - 1, 0);
                     token = error.tokens[error.tokens.length - 1];
                  }
               };
               if (current != undefined)
               {
                  previous = previous + ' ';
                  jQuery.map(current, checkErrors);
               }
               if (!done)
               {
                  previous = '';
                  jQuery.map(defaults, checkErrors);
               }
            }
            previous = token;
         }
         if (doReplaces.length > 0)
         {
            newNode = n;
            for (var x = 0; x < doReplaces.length; x++)
            {
               var regexp = doReplaces[x][0], result = doReplaces[x][1];
               var bringTheHurt = function(node)
               {
                  if (node.nodeType == 3)
                  {
                     ecount++;
                     if (navigator.appName == 'Microsoft Internet Explorer' && node.nodeValue.length > 0 && node.nodeValue.substr(0, 1) == ' ')
                     {
                      	return jQuery('<span class="mceItemHidden"><span class="mceItemHidden">&nbsp;</span>' + node.nodeValue.substr(1, node.nodeValue.length - 1).replace(regexp, result) + '</span>');
                     }
                     else
                     {
                        return jQuery('<span class="mceItemHidden">' + node.nodeValue.replace(regexp, result) + '</span>');
                     }
                  }
                  else
                  {
                     var contents = jQuery(node).contents();
                     for (var y = 0; y < contents.length; y++)
                     {
                        if (contents[y].nodeType == 3 && regexp.test(contents[y].nodeValue))
                        {
                           var nnode = contents[y].nodeValue.replace(regexp, result);
                           jQuery(contents[y]).replaceWith(nnode);
                           ecount++;
                           return node;
                        }
                     }
                     return node;
                  }
               };
               newNode = bringTheHurt(newNode);            
            }
            jQuery(n).replaceWith(newNode);
         }
      }
   });
   return ecount;
};
AtD._walk = function(container_id, f)
{
   var elements = jQuery('#' + container_id).contents();
   AtD.__walk(elements, f);
};
AtD.__walk = function(elements, f)
{
   var i;
   for (i = 0; i < elements.length; i++)
   {
      f.call(f, elements[i]);   
      AtD.__walk(jQuery(elements[i]).contents(), f);
   }
};
AtD._removeWords = function(container_id, w)     
{    
   var count = 0;  
   var elements = jQuery('#' + container_id).find('span');
   jQuery.map(jQuery.makeArray(elements).reverse(), function(n)
   {
      if (n && (jQuery(n).hasClass('hiddenGrammarError') || jQuery(n).hasClass('hiddenSpellError') || jQuery(n).hasClass('hiddenSuggestion') || jQuery(n).hasClass('mceItemHidden')))
      {
         if (n.innerHTML == '&nbsp;')
         {
            jQuery(n).replaceWith(' ');
         }
         else if (!w || n.innerHTML == w)
         {
            jQuery(n).replaceWith(jQuery(n).html());
            count++;
         }            
      }
   });    
   return count;
};
/* 
CSSHttpRequest Section
Copyright 2008 nb.io - http://nb.io/
*/
(function(){
    var chr = window.CSSHttpRequest = {};
    chr.id = 0;
    chr.requests = {};
    chr.MATCH_ORDINAL = /#c(\d+)/;
    chr.MATCH_URL = /url\("?data\:[^,]*,([^")]+)"?\)/;
    chr.get = function(url, callback) {
        var id = ++chr.id;
        var iframe = document.createElement( "iframe" );
        iframe.style.position = "absolute";
        iframe.style.left = iframe.style.top = "-1000px";
        iframe.style.width = iframe.style.height = 0;
        document.documentElement.appendChild(iframe);
        var r = chr.requests[id] = {
            id: id,
            iframe: iframe,
            document: iframe.contentDocument || iframe.contentWindow.document,
            callback: callback
        };
        r.document.open("text/html", false);
        r.document.write("<html><head>");
        r.document.write("<link rel='stylesheet' type='text/css' media='print, csshttprequest' href='" + chr.escapeHTML(url) + "' />");
        r.document.write("</head><body>");
        r.document.write("<script type='text/javascript'>");
        r.document.write("(function(){var w = window; var p = w.parent; p.CSSHttpRequest.sandbox(w); w.onload = function(){p.CSSHttpRequest.callback('" + id + "');};})();");
        r.document.write("</script>");
        r.document.write("</body></html>");
        r.document.close();
    };
    chr.sandbox = function(w) {
    };
    chr.callback = function(id) {
        var r = chr.requests[id];
        var data = chr.parse(r);
        r.callback(data);
        window.setTimeout(function() {
            var r = chr.requests[id];
            try { r.iframe.parentElement.removeChild(r.iframe); } catch(e) {};
            delete chr.requests[id];
        }, 0);
    };
    chr.parse = function(r) {
        var data = [];
        try {
            var rules = r.document.styleSheets[0].cssRules || r.document.styleSheets[0].rules;
            for(var i = 0; i < rules.length; i++) {
                try {
                    var r2 = rules.item ? rules.item(i) : rules[i];
                    var ord = r2.selectorText.match(chr.MATCH_ORDINAL)[1];
                    var val = r2.style.backgroundImage.match(chr.MATCH_URL)[1];
                    data[ord] = val;
                } catch(e) {}
            }
        }
        catch(e) {
            r.document.getElementsByTagName("link")[0].setAttribute("media", "screen");
            var x = r.document.createElement("div");
            x.innerHTML = "foo";
            r.document.body.appendChild(x);
            var ord = 0;
            try {
                while(1) {
                    x.id = "c" + ord;
                    var style = r.document.defaultView.getComputedStyle(x, null);
                    var bg = style["background-image"] || style.backgroundImage || style.getPropertyValue("background-image");
                    var val = bg.match(chr.MATCH_URL)[1];
                    data[ord] = val;
                    ord++;
                }
            } catch(e) {}
        }
        return decodeURIComponent(data.join(""));
    };
    chr.escapeHTML = function(s) {
        return s.replace(/([<>&""''])/g,
            function(m, c) {
                switch(c) {
                    case "<": return "&lt;";
                    case ">": return "&gt;";
                    case "&": return "&amp;";
                    case '"': return "&quot;";
                    case "'": return "&apos;";
                }
                return c;
            });
    };
})();
/*
 jQuery Alert Dialogs Section
*/
(function($) {
	$.alerts = {
		verticalOffset: -75,
		horizontalOffset: 0,
		repositionOnResize: true,
		overlayOpacity: .01,
		overlayColor: '#FFF',
		draggable: false,
		okButton: '&nbsp;OK&nbsp;',
		cancelButton: '&nbsp;Cancel&nbsp;',
		dialogClass: null,
		alert: function(message, title, callback) {
			if( title == null ) title = 'Alert';
			$.alerts._show(title, message, null, 'alert', function(result) {
				if( callback ) callback(result);
			});
		},
		confirm: function(message, title, callback) {
			if( title == null ) title = 'Confirm';
			$.alerts._show(title, message, null, 'confirm', function(result) {
				if( callback ) callback(result);
			});
		},
		prompt: function(message, value, title, callback) {
			if( title == null ) title = 'Prompt';
			$.alerts._show(title, message, value, 'prompt', function(result) {
				if( callback ) callback(result);
			});
		},
		_show: function(title, msg, value, type, callback) {
			$.alerts._hide();
			$.alerts._overlay('show');
			$("BODY").append(
			  '<div id="popup_container">' +
			    '<h1 id="popup_title"></h1>' +
			    '<div id="popup_content">' +
			      '<div id="popup_message"></div>' +
				'</div>' +
			  '</div>');
			if( $.alerts.dialogClass ) $("#popup_container").addClass($.alerts.dialogClass);
			var pos = ($.browser.msie && parseInt($.browser.version) <= 6 ) ? 'absolute' : 'fixed'; 
			$("#popup_container").css({
				position: pos,
				zIndex: 99999,
				padding: 0,
				margin: 0
			});
			$("#popup_title").text(title);
			$("#popup_content").addClass(type);
			$("#popup_message").text(msg);
			$("#popup_message").html( $("#popup_message").text().replace(/\n/g, '<br />') );
			$("#popup_container").css({
				minWidth: $("#popup_container").outerWidth(),
				maxWidth: $("#popup_container").outerWidth()
			});
			$.alerts._reposition();
			$.alerts._maintainPosition(true);
			switch( type ) {
				case 'alert':
					$("#popup_message").after('<div id="popup_panel"><input type="button" value="' + $.alerts.okButton + '" id="popup_ok" /></div>');
					$("#popup_ok").click( function() {
						$.alerts._hide();
						callback(true);
					});
					$("#popup_ok").focus().keypress( function(e) {
						if( e.keyCode == 13 || e.keyCode == 27 ) $("#popup_ok").trigger('click');
					});
				break;
				case 'confirm':
					$("#popup_message").after('<div id="popup_panel"><input type="button" value="' + $.alerts.okButton + '" id="popup_ok" /> <input type="button" value="' + $.alerts.cancelButton + '" id="popup_cancel" /></div>');
					$("#popup_ok").click( function() {
						$.alerts._hide();
						if( callback ) callback(true);
					});
					$("#popup_cancel").click( function() {
						$.alerts._hide();
						if( callback ) callback(false);
					});
					$("#popup_ok").focus();
					$("#popup_ok, #popup_cancel").keypress( function(e) {
						if( e.keyCode == 13 ) $("#popup_ok").trigger('click');
						if( e.keyCode == 27 ) $("#popup_cancel").trigger('click');
					});
				break;
				case 'prompt':
					$("#popup_message").append('<br /><input type="text" size="30" id="popup_prompt" />').after('<div id="popup_panel"><input type="button" value="' + $.alerts.okButton + '" id="popup_ok" /> <input type="button" value="' + $.alerts.cancelButton + '" id="popup_cancel" /></div>');
					$("#popup_prompt").width( $("#popup_message").width() );
					$("#popup_ok").click( function() {
						var val = $("#popup_prompt").val();
						$.alerts._hide();
						if( callback ) callback( val );
					});
					$("#popup_cancel").click( function() {
						$.alerts._hide();
						if( callback ) callback( null );
					});
					$("#popup_prompt, #popup_ok, #popup_cancel").keypress( function(e) {
						if( e.keyCode == 13 ) $("#popup_ok").trigger('click');
						if( e.keyCode == 27 ) $("#popup_cancel").trigger('click');
					});
					if( value ) $("#popup_prompt").val(value);
					$("#popup_prompt").focus().select();
				break;
			}
			if( $.alerts.draggable ) {
				try {
					$("#popup_container").draggable({ handle: $("#popup_title") });
					$("#popup_title").css({ cursor: 'move' });
				} catch(e) { }
			}
		},
		_hide: function() {
			$("#popup_container").remove();
			$.alerts._overlay('hide');
			$.alerts._maintainPosition(false);
		},
		_overlay: function(status) {
			switch( status ) {
				case 'show':
					$.alerts._overlay('hide');
					$("BODY").append('<div id="popup_overlay"></div>');
					$("#popup_overlay").css({
						position: 'absolute',
						zIndex: 99998,
						top: '0px',
						left: '0px',
						width: '100%',
						height: $(document).height(),
						background: $.alerts.overlayColor,
						opacity: $.alerts.overlayOpacity
					});
				break;
				case 'hide':
					$("#popup_overlay").remove();
				break;
			}
		},
		_reposition: function() {
			var top = (($(window).height() / 2) - ($("#popup_container").outerHeight() / 2)) + $.alerts.verticalOffset;
			var left = (($(window).width() / 2) - ($("#popup_container").outerWidth() / 2)) + $.alerts.horizontalOffset;
			if( top < 0 ) top = 0;
			if( left < 0 ) left = 0;
			if( $.browser.msie && parseInt($.browser.version) <= 6 ) top = top + $(window).scrollTop();
			$("#popup_container").css({
				top: top + 'px',
				left: left + 'px'
			});
			$("#popup_overlay").height( $(document).height() );
		},
		_maintainPosition: function(status) {
			if( $.alerts.repositionOnResize ) {
				switch(status) {
					case true:
						$(window).bind('resize', $.alerts._reposition);
					break;
					case false:
						$(window).unbind('resize', $.alerts._reposition);
					break;
				}
			}
		}
	}
	jAlert = function(message, title, callback) {
		$.alerts.alert(message, title, callback);
	}
	jConfirm = function(message, title, callback) {
		$.alerts.confirm(message, title, callback);
	};
	jPrompt = function(message, value, title, callback) {
		$.alerts.prompt(message, value, title, callback);
	};
})(jQuery);

/*
 * jquery.atd.textarea.js - jQuery powered writing check for textarea elements
 */
AtD.textareas = {};
AtD.restoreTextArea = function(id)
{
   var options = AtD.textareas[id];
   if (options == undefined || options['before'] == options['link'].html())
      return;
   AtD.remove(id);
   var content;
   if (navigator.appName == 'Microsoft Internet Explorer')
      content = jQuery('#' + id).html().replace(/<BR.*?class.*?atd_remove_me.*?>/gi, "\n");
   else
      content = jQuery('#' + id).html();
   jQuery('#' + id).replaceWith( options['node'] );
   jQuery('#' + id).val( content );
   jQuery('#' + id).height( options['height'] );
   options['link'].html( options['before'] );
};
AtD.checkTextAreaCrossAJAX = function(id, linkId, after)
{
   AtD._checkTextArea(id, AtD.checkCrossAJAX, linkId, after);
}
AtD.checkTextArea = function(id, linkId, after)
{
   AtD._checkTextArea(id, AtD.check, linkId, after);
}
AtD._checkTextArea = function(id, commChannel, linkId, after)
{
   if (AtD.textareas[id] == undefined)
   {
      AtD.textareas[id] = { 'node': jQuery('#' + id), 
                            'height': jQuery('#' + id).height(), 
                            'link': jQuery('#' + linkId), 
                            'before': jQuery('#' + linkId).html(), 
                            'after': after 
                          };
   }
   var options = AtD.textareas[id];
   if (options['link'].html() != options['before'])
   {
       AtD.restoreTextArea(id);
   }          
   else
   {
       options['link'].html( options['after'] );
       var disableClick = function() { return false; };
       options['link'].click(disableClick);
       if (navigator.appName == 'Microsoft Internet Explorer') 
       {
          jQuery('#' + id).replaceWith( '<div id="' + id + '">' + jQuery('#' + id).val().replace(/[\n\r\f]/gm, '<BR class="atd_remove_me">') + '</div>' );
          jQuery('#' + id).attr('style', options['node'].attr('style') );
          jQuery('#' + id).attr('class', options['node'].attr('class') );
          jQuery('#' + id).css( { 'overflow' : 'auto' } );
       } 
       else 
       {
          jQuery('#' + id).replaceWith( '<div id="' + id + '">' + jQuery('#' + id).val() + '</div>' );
          jQuery('#' + id).attr('style', options['node'].attr('style') );
          jQuery('#' + id).attr('class', options['node'].attr('class') );
          jQuery('#' + id).css( { 'overflow' : 'auto', 'white-space' : 'pre-wrap' } );
       }
       jQuery('#' + id).height( options['height'] );
       commChannel(id,
       {
          ready: function(errorCount)
          {
             options['link'].unbind('click', disableClick);
          },
          explain: function(url) 
          {
             var left = (screen.width / 2) - (480 / 2);
             var top = (screen.height / 2) - (380 / 2);
             window.open( url, '', 'width=480,height=380,toolbar=0,status=0,resizable=0,location=0,menuBar=0,left=' + left + ',top=' + top).focus();
          },
          success: function(errorCount)                                  
          {
             if (errorCount == 0)
             {
                jAlert("No writing errors were found!", "No errors!");
             }
             AtD.restoreTextArea( id );
          },
          error: function(reason)
          {
             options['link'].unbind('click', disableClick);
             jAlert("There was an error communicating with the spell checking service.\n\n" + reason, "Error!");
             AtD.restoreTextArea( id );
          },
          editSelection : function(element)
          {
            var oldtext = element.text();
               jPrompt('Replace selection with:', element.text(), 'Replace', function(text) {
                    if (oldtext != text)
                    {
                        if( text ) element.replaceWith( text );
                        AtD.counter --;
                        if (AtD.counter == 0 && AtD.callback_f != undefined && AtD.callback_f.success != undefined)
                            AtD.callback_f.success(AtD.count);
                    }
               });
          }
      });
   }
}