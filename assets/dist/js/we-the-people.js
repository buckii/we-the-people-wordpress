jQuery(function(a){"use strict";var b=a("body");b.find(".wtp-petition blockquote").each(function(){var b=a(this);b.find("p").length>1&&(b.find("p:not(:first)").wrapAll('<div class="extended" />'),b.append('<p class="toggle-btn"><a href="#" class="toggle more" role="button">'+WeThePeople.i18n.more+"</a></p>").addClass("collapsed"))}),b.on("click",".wtp-petition a.toggle",function(b){var c=a(this),d=c.parents("blockquote");return b.preventDefault(),d.find(".extended").slideToggle(200,function(){d.toggleClass("collapsed expanded"),c.text(c.hasClass("more")?WeThePeople.i18n.less:WeThePeople.i18n.more).toggleClass("more less")}),!1}),b.on("submit","form.wtp-petitions-signature",function(b){var c=a(this),d=c.serializeArray();b.preventDefault(),d.push({name:"actually_ajax",value:!0}),a.post(WeThePeople.ajaxurl,d,function(a){a===WeThePeople.signatureStatus.success?c.fadeOut(200,function(){c.text(WeThePeople.i18n.signatureSuccess).fadeIn(200)}):c.before('<div class="wtp-signature-error"><p>'+WeThePeople.i18n.signatureError+"</p></div>")})})});