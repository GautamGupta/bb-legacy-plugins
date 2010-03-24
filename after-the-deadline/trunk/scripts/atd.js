var a,EXPORTED_SYMBOLS=["AtDCore"];function AtDCore(){this.ignore_types=["Bias Language","Cliches","Complex Expression","Diacritical Marks","Double Negatives","Hidden Verbs","Jargon Language","Passive voice","Phrases to Avoid","Redundant Expression"];this.ignore_strings={};this.i18n=AtD}a=AtDCore.prototype;a.getLang=function(b,c){if(AtD[b]==undefined)return c;return AtD[b]};a.addI18n=function(b){this.i18n=b}; a.setIgnoreStrings=function(b){var c=this;this.map(b.split(/,\s*/g),function(d){c.ignore_strings[d]=1})}; a.showTypes=function(b){b=b.split(/,\s*/g);var c={};c["Double Negatives"]=1;c["Hidden Verbs"]=1;c["Passive voice"]=1;c["Bias Language"]=1;c.Cliches=1;c["Complex Expression"]=1;c["Diacritical Marks"]=1;c["Jargon Language"]=1;c["Phrases to Avoid"]=1;c["Redundant Expression"]=1;var d=[];this.map(b,function(e){c[e]=undefined});this.map(this.ignore_types,function(e){c[e]!=undefined&&d.push(e)});this.ignore_types=d}; a.makeError=function(b,c,d,e){var f={};f.type=d;f.string=b;f.tokens=c;f.regexp=(new RegExp("\\b"+b+"\\b")).test(b)?new RegExp("(?!"+b+"<)\\b"+b.replace(/\s+/g,e)+"\\b"):(new RegExp(b+"\\b")).test(b)?new RegExp("(?!"+b+"<)"+b.replace(/\s+/g,e)+"\\b"):(new RegExp("\\b"+b)).test(b)?new RegExp("(?!"+b+"<)\\b"+b.replace(/\s+/g,e)):new RegExp("(?!"+b+"<)"+b.replace(/\s+/g,e));f.used=false;return f}; a.addToErrorStructure=function(b,c,d,e){var f=this;this.map(c,function(g){var n=g.word.split(/\s+/),k=g.pre,h=n[0];if(b["__"+h]==undefined){b["__"+h]={};b["__"+h].pretoks={};b["__"+h].defaults=[]}if(k=="")b["__"+h].defaults.push(f.makeError(g.word,n,d,e,k));else{if(b["__"+h].pretoks["__"+k]==undefined)b["__"+h].pretoks["__"+k]=[];b["__"+h].pretoks["__"+k].push(f.makeError(g.word,n,d,e,k))}})}; a.buildErrorStructure=function(b,c,d){var e=this._getSeparators(),f={};this.addToErrorStructure(f,b,"hiddenSpellError",e);this.addToErrorStructure(f,d,"hiddenGrammarError",e);this.addToErrorStructure(f,c,"hiddenSuggestion",e);return f};a._getSeparators=function(){var b="",c;for(c=0;c<28;c++)b+="\\"+'"s!#$%&()*+,./:;<=>?@[]^_{|}'.charAt(c);return"(?:(?:[\u00a0"+b+"])|(?:\\-\\-))+"}; a.processXML=function(b){var c={};this.map(this.ignore_types,function(t){c[t]=1});this.suggestions=[];var d=b.getElementsByTagName("error");b=[];for(var e=[],f=[],g=0;g<d.length;g++)if(d[g].getElementsByTagName("string").item(0).firstChild!=null){var n=d[g].getElementsByTagName("string").item(0).firstChild.data,k=d[g].getElementsByTagName("type").item(0).firstChild.data,h=d[g].getElementsByTagName("description").item(0).firstChild.data,j;j=d[g].getElementsByTagName("precontext").item(0).firstChild!= null?d[g].getElementsByTagName("precontext").item(0).firstChild.data:"";if(this.ignore_strings[n]==undefined){var m={};m.description=h;m.suggestions=[];m.matcher=new RegExp("^"+n.replace(/\s+/,this._getSeparators())+"$");m.context=j;m.string=n;m.type=k;this.suggestions.push(m);if(d[g].getElementsByTagName("suggestions").item(0)!=undefined)for(var i=d[g].getElementsByTagName("suggestions").item(0).getElementsByTagName("option"),p=0;p<i.length;p++)m.suggestions.push(i[p].firstChild.data);if(d[g].getElementsByTagName("url").item(0)!= undefined){i=d[g].getElementsByTagName("url").item(0).firstChild.data;m.moreinfo=i+"&theme=tinymce"}if(c[h]==undefined){k=="suggestion"&&f.push({word:n,pre:j});k=="grammar"&&b.push({word:n,pre:j})}if(k=="spelling"||h=="Homophone")e.push({word:n,pre:j});if(h=="Cliches")m.description="Clich&eacute;s";if(h=="Spelling")m.description=this.getLang("menu_title_spelling","Spelling");if(h=="Repeated Word")m.description=this.getLang("menu_title_repeated_word","Repeated Word");if(h=="Did you mean...")m.description= this.getLang("menu_title_confused_word","Did you mean...")}}d=e.length+b.length+f.length;return{errors:d>0?this.buildErrorStructure(e,f,b):undefined,count:d,suggestions:this.suggestions}}; a.findSuggestion=function(b){var c=b.innerHTML,d=(this.getAttrib(b,"pre")+"").replace(/[\\,!\\?\\."\s]/g,"");this.getAttrib(b,"pre")==undefined&&alert(b.innerHTML);b=undefined;for(var e=this.suggestions.length,f=0;f<e;f++)if((d==""||d==this.suggestions[f].context)&&this.suggestions[f].matcher.test(c)){b=this.suggestions[f];break}return b};function TokenIterator(b){this.tokens=b;this.last=this.count=this.index=0}a=TokenIterator.prototype; a.next=function(){var b=this.tokens[this.index];this.count=this.last;this.last+=b.length+1;this.index++;if(b!=""){if(b[0]=="'")b=b.substring(1,b.length);if(b[b.length-1]=="'")b=b.substring(0,b.length-1)}return b};a.hasNext=function(){return this.index<this.tokens.length};a.hasNextN=function(b){return this.index+b<this.tokens.length};a.skip=function(b,c){this.index+=b;this.last+=c;if(this.index<this.tokens.length)this.count=this.last-this.tokens[this.index].length};a.getCount=function(){return this.count}; a.peek=function(b){var c=[];b=this.index+b;for(var d=this.index;d<b;d++)c.push(this.tokens[d]);return c};a=AtDCore.prototype; a.markMyWords=function(b,c){var d=new RegExp(this._getSeparators()),e=[],f=0,g=this;this._walk(b,function(k){k.nodeType==3&&!g.isMarkedNode(k)&&e.push(k)});var n;this.map(e,function(k){var h;if(k.nodeType==3){h=k.nodeValue;var j=k.nodeValue.split(d),m="",i=[];for(n=new TokenIterator(j);n.hasNext();){j=n.next();var p=c["__"+j],t;if(p!=undefined&&p.pretoks!=undefined){t=p.defaults;p=p.pretoks["__"+m];var l=false,q,u;q=h.substr(0,n.getCount());u=h.substr(q.length,h.length);q=function(o){if(o!=undefined&& !o.used&&w["__"+o.string]==undefined&&o.regexp.test(u)){w["__"+o.string]=1;i.push([o.regexp,'<span class="'+o.type+'" pre="'+m+'">$&</span>']);l=o.used=true}};var w={};if(p!=undefined){m+=" ";g.map(p,q)}if(!l){m="";g.map(t,q)}}m=j}if(i.length>0){newNode=k;for(h=0;h<i.length;h++){var v=i[h][0],x=i[h][1];newNode=function(o){if(o.nodeType==3){f++;return g.isIE()&&o.nodeValue.length>0&&o.nodeValue.substr(0,1)==" "?g.create('<span class="mceItemHidden">&nbsp;</span>'+o.nodeValue.substr(1,o.nodeValue.length- 1).replace(v,x),false):g.create(o.nodeValue.replace(v,x),false)}else{for(var s=g.contents(o),r=0;r<s.length;r++)if(s[r].nodeType==3&&v.test(s[r].nodeValue)){var y;y=g.isIE()&&s[r].nodeValue.length>0&&s[r].nodeValue.substr(0,1)==" "?g.create('<span class="mceItemHidden">&nbsp;</span>'+s[r].nodeValue.substr(1,s[r].nodeValue.length-1).replace(v,x),true):g.create(s[r].nodeValue.replace(v,x),true);g.replaceWith(s[r],y);g.removeParent(y);f++;return o}return o}}(newNode)}g.replaceWith(k,newNode)}}});return f}; a._walk=function(b,c){var d;for(d=0;d<b.length;d++){c.call(c,b[d]);this._walk(this.contents(b[d]),c)}};a.removeWords=function(b,c){var d=0,e=this;this.map(this.findSpans(b).reverse(),function(f){if(f&&(e.isMarkedNode(f)||e.hasClass(f,"mceItemHidden")||e.isEmptySpan(f)))if(f.innerHTML=="&nbsp;"){var g=document.createTextNode(" ");e.replaceWith(f,g)}else if(!c||f.innerHTML==c){e.removeParent(f);d++}});return d}; a.isEmptySpan=function(b){return this.getAttrib(b,"class")==""&&this.getAttrib(b,"style")==""&&this.getAttrib(b,"id")==""&&!this.hasClass(b,"Apple-style-span")&&this.getAttrib(b,"mce_name")==""};a.isMarkedNode=function(b){return this.hasClass(b,"hiddenGrammarError")||this.hasClass(b,"hiddenSpellError")||this.hasClass(b,"hiddenSuggestion")};a.applySuggestion=function(b,c){if(c=="(omit)")this.remove(b);else{c=this.create(c);this.replaceWith(b,c);this.removeParent(c)}}; a.hasErrorMessage=function(b){return b!=undefined&&b.getElementsByTagName("message").item(0)!=null};a.getErrorMessage=function(b){return b.getElementsByTagName("message").item(0)};a.isIE=function(){return navigator.appName=="Microsoft Internet Explorer"};var AtD=jQuery.extend({proofread_click_count:0,i18n:{},rpc_css:"http://www.polishmywriting.com/atd_jquery/server/proxycss.php?data=",listener:{}},AtD||{api_key:"",rpc:"",lang:"en"});AtD.getLang=function(b,c){if(AtD[b]==undefined)return c;return AtD[b]}; AtD.addI18n=function(b){AtD.i18n=b;AtD.core.addI18n(b)};AtD.setIgnoreStrings=function(b){AtD.core.setIgnoreStrings(b)};AtD.showTypes=function(b){AtD.core.showTypes(b)}; AtD.check=function(b,c){typeof AtD.proofread_click_count!="undefined"&&AtD.proofread_click_count++;AtD.callback_f=c;AtD.remove(b);c=jQuery("#"+b);c.html();text=jQuery.trim(c.html());text=encodeURIComponent(text);jQuery.ajax({type:"POST",url:AtD.rpc+"?url=/checkDocument&lang="+AtD.lang,data:"key="+AtD.api_key+"&data="+text,format:"raw",dataType:jQuery.browser.msie?"text":"xml",error:function(d,e,f){AtD.callback_f!=undefined&&AtD.callback_f.error!=undefined&&AtD.callback_f.error(e+": "+f)},success:function(d){var e; if(typeof d=="string"){e=new ActiveXObject("Microsoft.XMLDOM");e.async=false;e.loadXML(d)}else e=d;if(AtD.core.hasErrorMessage(e))AtD.callback_f!=undefined&&AtD.callback_f.error!=undefined&&AtD.callback_f.error(AtD.core.getErrorMessage(e));else{AtD.container=b;d=AtD.processXML(b,e);AtD.callback_f!=undefined&&AtD.callback_f.ready!=undefined&&AtD.callback_f.ready(d);d==0&&AtD.callback_f!=undefined&&AtD.callback_f.success!=undefined&&AtD.callback_f.success(d);AtD.counter=d;AtD.count=d}}})}; AtD.remove=function(b){AtD._removeWords(b,null)};AtD.clickListener=function(b){AtD.core.isMarkedNode(b.target)&&AtD.suggest(b.target)};AtD.processXML=function(b,c){c=AtD.core.processXML(c);if(c.count>0)c.count=AtD.core.markMyWords(jQuery("#"+b).contents(),c.errors);jQuery("#"+b).unbind("click",AtD.clickListener);jQuery("#"+b).click(AtD.clickListener);return c.count}; AtD.useSuggestion=function(b){this.core.applySuggestion(AtD.errorElement,b);AtD.counter--;AtD.counter==0&&AtD.callback_f!=undefined&&AtD.callback_f.success!=undefined&&AtD.callback_f.success(AtD.count)};AtD.editSelection=function(){AtD.errorElement.parent();AtD.callback_f!=undefined&&AtD.callback_f.editSelection!=undefined&&AtD.callback_f.editSelection(AtD.errorElement)}; AtD.ignoreSuggestion=function(){AtD.core.removeParent(AtD.errorElement);AtD.counter--;AtD.counter==0&&AtD.callback_f!=undefined&&AtD.callback_f.success!=undefined&&AtD.callback_f.success(AtD.count)}; AtD.ignoreAll=function(b){var c=AtD.errorElement.text();b=AtD._removeWords(b,c);AtD.counter-=b;AtD.counter==0&&AtD.callback_f!=undefined&&AtD.callback_f.success!=undefined&&AtD.callback_f.success(AtD.count);if(AtD.callback_f!=undefined&&AtD.callback_f.ignore!=undefined&&AtD.rpc_ignore!=undefined){AtD.callback_f.ignore(c);AtD.core.setIgnoreStrings(c)}};AtD.explainError=function(){AtD.callback_f!=undefined&&AtD.callback_f.explain!=undefined&&AtD.callback_f.explain(AtD.explainURL)}; AtD.suggest=function(b){if(jQuery("#suggestmenu").length==0){var c=jQuery('<div id="suggestmenu"></div>');c.prependTo("body")}else{c=jQuery("#suggestmenu");c.hide()}errorDescription=AtD.core.findSuggestion(b);AtD.errorElement=jQuery(b);c.empty();if(errorDescription==undefined)c.append("<strong>"+AtD.getLang("menu_title_no_suggestions","No suggestions")+"</strong>");else if(errorDescription.suggestions.length==0)c.append("<strong>"+errorDescription.description+"</strong>");else{c.append("<strong>"+ errorDescription.description+"</strong>");for(var d=0;d<errorDescription.suggestions.length;d++)(function(e){c.append("<a onclick=\"AtD.useSuggestion('"+e.replace(/'/,"\\'")+"');\">"+e+"</a>")})(errorDescription.suggestions[d])}if(AtD.callback_f!=undefined&&AtD.callback_f.explain!=undefined&&errorDescription.moreinfo!=undefined){c.append('<a onclick="AtD.explainError();" class="spell_sep_top">'+AtD.getLang("menu_option_explain","Explain...")+"</a>");AtD.explainURL=errorDescription.moreinfo}c.append('<a onclick="AtD.ignoreSuggestion();" class="spell_sep_top">'+ AtD.getLang("menu_option_ignore_once","Ignore suggestion")+"</a>");if(AtD.callback_f!=undefined&&AtD.callback_f.editSelection!=undefined){AtD.callback_f!=undefined&&AtD.callback_f.ignore!=undefined&&AtD.rpc_ignore!=undefined?c.append("<a onclick=\"AtD.ignoreAll('"+AtD.container+"');\">"+AtD.getLang("menu_option_ignore_always","Ignore always")+"</a>"):c.append("<a onclick=\"AtD.ignoreAll('"+AtD.container+"');\">"+AtD.getLang("menu_option_ignore_all","Ignore all")+"</a>");c.append("<a onclick=\"AtD.editSelection('"+ AtD.container+'\');" class="spell_sep_bottom spell_sep_top">'+AtD.getLang("menu_option_edit_selection","Edit Selection...")+"</a>")}else AtD.callback_f!=undefined&&AtD.callback_f.ignore!=undefined&&AtD.rpc_ignore!=undefined?c.append("<a onclick=\"AtD.ignoreAll('"+AtD.container+'\');" class="spell_sep_bottom">'+AtD.getLang("menu_option_ignore_always","Ignore always")+"</a>"):c.append("<a onclick=\"AtD.ignoreAll('"+AtD.container+'\');" class="spell_sep_bottom">'+AtD.getLang("menu_option_ignore_all", "Ignore all")+"</a>");d=jQuery(b).offset();b=jQuery(b).width();jQuery(c).css({left:d.left+b+"px",top:d.top+"px"});jQuery(c).fadeIn(200);AtD.suggestShow=true;setTimeout(function(){jQuery("body").bind("click",function(){AtD.suggestShow||jQuery("#suggestmenu").fadeOut(200)})},1);setTimeout(function(){AtD.suggestShow=false},2)};AtD._removeWords=function(b,c){return this.core.removeWords(jQuery("#"+b),c)}; AtD.initCoreModule=function(){var b=new AtDCore;b.hasClass=function(c,d){return jQuery(c).hasClass(d)};b.map=jQuery.map;b.contents=function(c){return jQuery(c).contents()};b.replaceWith=function(c,d){return jQuery(c).replaceWith(d)};b.findSpans=function(c){return jQuery.makeArray(c.find("span"))};b.create=function(c){return jQuery('<span class="mceItemHidden">'+c+"</span>")};b.remove=function(c){return jQuery(c).remove()};b.removeParent=function(c){return jQuery(c).unwrap?jQuery(c).contents().unwrap(): jQuery(c).replaceWith(jQuery(c).html())};b.getAttrib=function(c,d){return jQuery(c).attr(d)};return b};AtD.core=AtD.initCoreModule(); (function(b){b.alerts={verticalOffset:-75,horizontalOffset:0,repositionOnResize:true,overlayOpacity:0.01,overlayColor:"#FFF",draggable:false,okButton:"&nbsp;"+AtD.getLang("button_ok","OK")+"&nbsp;",cancelButton:"&nbsp;"+AtD.getLang("button_cancel","Cancel")+"&nbsp;",dialogClass:null,alert:function(c,d,e){if(d==null)d="Alert";b.alerts._show(d,c,null,"alert",function(f){e&&e(f)})},confirm:function(c,d,e){if(d==null)d="Confirm";b.alerts._show(d,c,null,"confirm",function(f){e&&e(f)})},prompt:function(c, d,e,f){if(e==null)e="Prompt";b.alerts._show(e,c,d,"prompt",function(g){f&&f(g)})},_show:function(c,d,e,f,g){b.alerts._hide();b.alerts._overlay("show");b("BODY").append('<div id="popup_container"><h1 id="popup_title"></h1><div id="popup_content"><div id="popup_message"></div></div></div>');b.alerts.dialogClass&&b("#popup_container").addClass(b.alerts.dialogClass);var n=b.browser.msie&&parseInt(b.browser.version)<=6?"absolute":"fixed";b("#popup_container").css({position:n,zIndex:99999,padding:0,margin:0}); b("#popup_title").text(c);b("#popup_content").addClass(f);b("#popup_message").text(d);b("#popup_message").html(b("#popup_message").text().replace(/\n/g,"<br />"));b("#popup_container").css({minWidth:b("#popup_container").outerWidth(),maxWidth:b("#popup_container").outerWidth()});b.alerts._reposition();b.alerts._maintainPosition(true);switch(f){case "alert":b("#popup_message").after('<div id="popup_panel"><input type="button" value="'+b.alerts.okButton+'" id="popup_ok" /></div>');b("#popup_ok").click(function(){b.alerts._hide(); g(true)});b("#popup_ok").focus().keypress(function(h){if(h.keyCode==13||h.keyCode==27)b("#popup_ok").trigger("click")});break;case "confirm":b("#popup_message").after('<div id="popup_panel"><input type="button" value="'+b.alerts.okButton+'" id="popup_ok" /> <input type="button" value="'+b.alerts.cancelButton+'" id="popup_cancel" /></div>');b("#popup_ok").click(function(){b.alerts._hide();g&&g(true)});b("#popup_cancel").click(function(){b.alerts._hide();g&&g(false)});b("#popup_ok").focus();b("#popup_ok, #popup_cancel").keypress(function(h){h.keyCode== 13&&b("#popup_ok").trigger("click");h.keyCode==27&&b("#popup_cancel").trigger("click")});break;case "prompt":b("#popup_message").append('<br /><input type="text" size="30" id="popup_prompt" />').after('<div id="popup_panel"><input type="button" value="'+b.alerts.okButton+'" id="popup_ok" /> <input type="button" value="'+b.alerts.cancelButton+'" id="popup_cancel" /></div>');b("#popup_prompt").width(b("#popup_message").width());b("#popup_ok").click(function(){var h=b("#popup_prompt").val();b.alerts._hide(); g&&g(h)});b("#popup_cancel").click(function(){b.alerts._hide();g&&g(null)});b("#popup_prompt, #popup_ok, #popup_cancel").keypress(function(h){h.keyCode==13&&b("#popup_ok").trigger("click");h.keyCode==27&&b("#popup_cancel").trigger("click")});e&&b("#popup_prompt").val(e);b("#popup_prompt").focus().select();break}if(b.alerts.draggable)try{b("#popup_container").draggable({handle:b("#popup_title")});b("#popup_title").css({cursor:"move"})}catch(k){}},_hide:function(){b("#popup_container").remove();b.alerts._overlay("hide"); b.alerts._maintainPosition(false)},_overlay:function(c){switch(c){case "show":b.alerts._overlay("hide");b("BODY").append('<div id="popup_overlay"></div>');b("#popup_overlay").css({position:"absolute",zIndex:99998,top:"0px",left:"0px",width:"100%",height:b(document).height(),background:b.alerts.overlayColor,opacity:b.alerts.overlayOpacity});break;case "hide":b("#popup_overlay").remove();break}},_reposition:function(){var c=b(window).height()/2-b("#popup_container").outerHeight()/2+b.alerts.verticalOffset, d=b(window).width()/2-b("#popup_container").outerWidth()/2+b.alerts.horizontalOffset;if(c<0)c=0;if(d<0)d=0;if(b.browser.msie&&parseInt(b.browser.version)<=6)c+=b(window).scrollTop();b("#popup_container").css({top:c+"px",left:d+"px"});b("#popup_overlay").height(b(document).height())},_maintainPosition:function(c){if(b.alerts.repositionOnResize)switch(c){case true:b(window).bind("resize",b.alerts._reposition);break;case false:b(window).unbind("resize",b.alerts._reposition);break}}};jAlert=function(c, d,e){b.alerts.alert(c,d,e)};jConfirm=function(c,d,e){b.alerts.confirm(c,d,e)};jPrompt=function(c,d,e,f){b.alerts.prompt(c,d,e,f)}})(jQuery);AtD.textareas={}; AtD.restoreTextArea=function(b){AtD_ajax_load("hide");var c=AtD.textareas[b];if(!(c==undefined||c.before==c.link.html())){AtD.remove(b);jQuery("#AtD_sync_").remove();var d;d=navigator.appName=="Microsoft Internet Explorer"?jQuery("#"+b).html().replace(/<BR.*?class.*?atd_remove_me.*?>/gi,"\n"):jQuery("#"+b).html();jQuery("#"+b).replaceWith(c.node);jQuery("#"+b).val(d.replace(/\&lt\;/g,"<").replace(/\&gt\;/,">").replace(/\&amp;/g,"&"));jQuery("#"+b).height(c.height);c.link.html(c.before)}}; AtD.checkTextArea=function(b,c,d){AtD._checkTextArea(b,AtD.check,c,d)}; AtD._checkTextArea=function(b,c,d,e){var f=jQuery("#"+b);if(AtD.textareas[b]==undefined){for(var g={},n=function(l,q){if(q.css(l)!="")g[l]=q.css(l)},k=["background-color","color","font-size","font-family","border-top-width","border-bottom-width","border-left-width","border-right-width","border-top-style","border-bottom-style","border-left-style","border-right-style","border-top-color","border-bottom-color","border-left-color","border-right-color","text-align","margin-top","margin-bottom","margin-left", "margin-right","width","line-height","letter-spacing","left","right","top","bottom","position","padding-left","padding-right","padding-top","padding-bottom"],h=0;h<k.length;h++)n(k[h],f);AtD.textareas[b]={node:f,height:f.height(),link:jQuery("#"+d),before:jQuery("#"+d).html(),after:e,style:g}}var j=AtD.textareas[b];if(j.link.html()!=j.before)AtD.restoreTextArea(b);else{j.link.html(j.after);var m=function(){return false};j.link.click(m);var i,p=jQuery('<input type="hidden" />');p.attr("id","AtD_sync_"); p.val(f.val());d=f.attr("name");if(navigator.appName=="Microsoft Internet Explorer"){f.replaceWith('<div id="'+b+'">'+f.val().replace(/\&/g,"&amp;").replace(/[\n\r\f]/gm,'<BR class="atd_remove_me">')+"</div>");i=jQuery("#"+b);i.attr("style",j.node.attr("style"));i.attr("class",j.node.attr("class"));i.css({overflow:"auto"});j.style["font-size"]=undefined;j.style["font-family"]=undefined}else{f.replaceWith('<div id="'+b+'">'+f.val().replace(/\&/g,"&amp;")+"</div>");i=jQuery("#"+b);i.attr("style",j.node.attr("style")); i.attr("class",j.node.attr("class"));i.css({overflow:"auto","white-space":"pre-wrap"});i.attr("contenteditable","true");i.attr("spellcheck",false);i.css({outline:"none"})}i.keypress(function(l){return l.keyCode!=13});p.attr("name",d);i.after(p);var t=false;f=function(){if(!t){t=true;setTimeout(function(){var l;l=navigator.appName=="Microsoft Internet Explorer"?i.html().replace(/<BR.*?class.*?atd_remove_me.*?>/gi,"\n"):i.html();var q=jQuery("<div></div>");q.html(l);AtD.core.removeWords(q);p.val(q.html().replace(/\&lt\;/g, "<").replace(/\&gt\;/,">").replace(/\&amp;/g,"&"));t=false},1500)}};i.keypress(f);i.mousemove(f);i.mouseout(f);i.css(j.style);i.height(j.height);AtD_ajax_load("show");c(b,{ready:function(){j.link.unbind("click",m);AtD_ajax_load("hide")},explain:function(l){window.open(l,"","width=480,height=380,toolbar=0,status=0,resizable=0,location=0,menuBar=0,left="+(screen.width/2-240)+",top="+(screen.height/2-190)).focus()},success:function(l){l==0&&jAlert(AtD.getLang("message_no_errors_found","No writing errors were found!"), AtD.getLang("message_no_errors","No errors!"));AtD.restoreTextArea(b)},error:function(l){j.link.unbind("click",m);l==undefined?jAlert(AtD.getLang("message_server_error_short","There was an error communicating with the spell checking service."),AtD.getLang("message_error","Error!")):jAlert(AtD.getLang("message_server_error_short","There was an error communicating with the spell checking service.")+"\n\n"+l,AtD.getLang("message_error","Error!"));AtD.restoreTextArea(b)},editSelection:function(l){var q= l.text();jPrompt(AtD.getLang("dialog_replace_selection","Replace selection with:"),l.text(),AtD.getLang("dialog_replace","Replace"),function(u){if(u!=null&&q!=u){jQuery(l).html(u);AtD.core.removeParent(l);AtD.counter--;AtD.counter==0&&AtD.restoreTextArea(b)}})},ignore:function(l){AtD_ajax_load("show");jQuery.ajax({type:"POST",url:AtD.rpc_ignore+encodeURI(l).replace(/&/g,"%26"),data:"action=atd_ignore",format:"raw",timeout:5E3,error:function(q,u,w){AtD.callback_f!=undefined&&AtD.callback_f.error!= undefined&&AtD.callback_f.error(u+": "+w)},success:function(){AtD_ajax_load("hide")}})}})}}; function AtD_check(b,c){AtD_ajax_load("show");jQuery.ajax({type:"POST",url:AtD.rpc+"?url=/checkDocument&lang="+AtD.lang,data:"key="+AtD.api_key+"&data="+jQuery("#"+b).val(),format:"raw",dataType:jQuery.browser.msie?"text":"xml",error:function(){AtD_update_post(b)},success:function(d){var e;if(typeof d=="string"){e=new ActiveXObject("Microsoft.XMLDOM");e.async=false;e.loadXML(d)}else e=d;AtD_ajax_load("hide");AtD.core.hasErrorMessage(e)&&AtD_update_post(b);if(AtD.core.processXML(e).count>0){d=AtD.getLang("dialog_confirm_post", "The proofreader has suggestions for your reply. Are you sure you want to post it?")+"\n\n"+AtD.getLang("dialog_confirm_post2","Press OK to post your reply, or Cancel to view the suggestions and edit your reply.");jConfirm(d,"",function(f){f==true?AtD_update_post(b):AtD.checkTextArea(b,c.attr("id"),jQuery.fn.addProofreader.defaults.edit_text_content)})}else AtD_update_post(b)}})}function AtD_update_post(b){AtD.proofread_click_count=2;jQuery("#"+b).parents("form").submit()} function AtD_ajax_load(b){if(b==undefined)jQuery(".atd-ajax-load").css("height")=="16px"?jQuery(".atd-ajax-load").css("height","0"):jQuery(".atd-ajax-load").css("height","16px");else b=="show"?jQuery(".atd-ajax-load").css("height","16px"):jQuery(".atd-ajax-load").css("height","0")} jQuery.fn.addProofreader=function(b){this.id=0;var c=this,d=jQuery.extend({},jQuery.fn.addProofreader.defaults,b);return this.each(function(){$this=jQuery(this);if($this.css("display")!="none"){$this.attr("id").length==0&&$this.attr("id","AtD_"+c.id++);var e=$this.attr("id"),f=jQuery("<span></span>");f.attr("id","AtD_"+c.id++);f.html(d.proofread_content);f.click(function(){AtD.current_id!=undefined&&AtD.current_id!=e&&AtD.restoreTextArea(AtD.current_id);if($this.val()!=""){AtD.checkTextArea(e,f.attr("id"), d.edit_text_content);AtD.current_id=e}else jAlert(AtD.getLang("message_error_no_text","Please enter some text in the post textbox to be checked!"),AtD.getLang("message_error","Error!"))});$this.wrap("<div></div>");$this.before('<span class="atd-ajax-load"></span>');$this.parents("form").submit(function(g){AtD.restoreTextArea(e);if(AtD.autoproofread!=undefined&&AtD.autoproofread==1&&AtD.proofread_click_count<=0&&$this.val()!=""){g.preventDefault();AtD_check(e,f)}});$this.before(f)}})}; jQuery.fn.addProofreader.defaults={edit_text_content:'<span class="atd_container"><a class="checkLink">'+AtD.getLang("button_edit_text","Edit Text")+"</a></span>",proofread_content:'<span class="atd_container"><a class="checkLink">'+AtD.getLang("button_proofread","Proofread")+"</a></span>"};jQuery(function(){jQuery("textarea").addProofreader();AtD.ignoreStrings!=undefined&&AtD.setIgnoreStrings(AtD.ignoreStrings);AtD.ignoreTypes!=undefined&&AtD.showTypes(AtD.ignoreTypes)});