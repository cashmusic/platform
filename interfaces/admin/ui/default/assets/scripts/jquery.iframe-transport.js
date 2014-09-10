/*
 * jQuery Iframe Transport Plugin 1.5
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2011, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

/*jslint unparam: true, nomen: true */
/*global define, window, document */

(function(b){"function"===typeof define&&define.amd?define(["jquery"],b):b(window.jQuery)})(function(b){var f=0;b.ajaxTransport("iframe",function(a){if(a.async&&("POST"===a.type||"GET"===a.type)){var c,d;return{send:function(h,g){c=b('<form style="display:none;"></form>');c.attr("accept-charset",a.formAcceptCharset);d=b('<iframe src="javascript:false;" name="iframe-transport-'+(f+=1)+'"></iframe>').bind("load",function(){var e,f=b.isArray(a.paramName)?a.paramName:[a.paramName];d.unbind("load").bind("load",
function(){var a;try{if(a=d.contents(),!a.length||!a[0].firstChild)throw Error();}catch(k){a=void 0}g(200,"success",{iframe:a});b('<iframe src="javascript:false;"></iframe>').appendTo(c);c.remove()});c.prop("target",d.prop("name")).prop("action",a.url).prop("method",a.type);a.formData&&b.each(a.formData,function(a,d){b('<input type="hidden"/>').prop("name",d.name).val(d.value).appendTo(c)});a.fileInput&&a.fileInput.length&&"POST"===a.type&&(e=a.fileInput.clone(),a.fileInput.after(function(a){return e[a]}),
a.paramName&&a.fileInput.each(function(c){b(this).prop("name",f[c]||a.paramName)}),c.append(a.fileInput).prop("enctype","multipart/form-data").prop("encoding","multipart/form-data"));c.submit();e&&e.length&&a.fileInput.each(function(a,c){var d=b(e[a]);b(c).prop("name",d.prop("name"));d.replaceWith(c)})});c.append(d).appendTo(document.body)},abort:function(){d&&d.unbind("load").prop("src","javascript".concat(":false;"));c&&c.remove()}}}});b.ajaxSetup({converters:{"iframe text":function(a){return b(a[0].body).text()},
"iframe json":function(a){return b.parseJSON(b(a[0].body).text())},"iframe html":function(a){return b(a[0].body).html()},"iframe script":function(a){return b.globalEval(b(a[0].body).text())}}})});