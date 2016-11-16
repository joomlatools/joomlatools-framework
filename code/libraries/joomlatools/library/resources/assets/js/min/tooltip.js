var globalCacheForjQueryReplacement=window.jQuery;window.jQuery=window.kQuery,!function(a){"use strict";var b=function(a,b){this.init("ktooltip",a,b)};b.prototype={constructor:b,init:function(b,c,d){var e,f,g,h,i;for(this.type=b,this.$element=a(c),this.options=this.getOptions(d),this.enabled=!0,g=this.options.trigger.split(" "),i=g.length;i--;)h=g[i],"click"==h?this.$element.on("click."+this.type,this.options.selector,a.proxy(this.toggle,this)):"manual"!=h&&(e="hover"==h?"mouseenter":"focus",f="hover"==h?"mouseleave":"blur",this.$element.on(e+"."+this.type,this.options.selector,a.proxy(this.enter,this)),this.$element.on(f+"."+this.type,this.options.selector,a.proxy(this.leave,this)));this.options.selector?this._options=a.extend({},this.options,{trigger:"manual",selector:""}):this.fixTitle()},getOptions:function(b){return b=a.extend({},a.fn[this.type].defaults,this.$element.data(),b),b.delay&&"number"==typeof b.delay&&(b.delay={show:b.delay,hide:b.delay}),b},enter:function(b){var c,d=a.fn[this.type].defaults,e={};return this._options&&a.each(this._options,function(a,b){d[a]!=b&&(e[a]=b)},this),c=a(b.currentTarget)[this.type](e).data(this.type),c.options.delay&&c.options.delay.show?(clearTimeout(this.timeout),c.hoverState="in",void(this.timeout=setTimeout(function(){"in"==c.hoverState&&c.show()},c.options.delay.show))):c.show()},leave:function(b){var c=a(b.currentTarget)[this.type](this._options).data(this.type);return this.timeout&&clearTimeout(this.timeout),c.options.delay&&c.options.delay.hide?(c.hoverState="out",void(this.timeout=setTimeout(function(){"out"==c.hoverState&&c.hide()},c.options.delay.hide))):c.hide()},show:function(){var b,c,d,e,f,g,h=a.Event("show");if(this.hasContent()&&this.enabled){if(this.$element.trigger(h),h.isDefaultPrevented())return;switch(b=this.tip(),this.setContent(),this.options.animation&&b.addClass("fade"),f="function"==typeof this.options.placement?this.options.placement.call(this,b[0],this.$element[0]):this.options.placement,b.detach().css({top:0,left:0,display:"block"}),this.options.container?b.appendTo(this.options.container):b.insertAfter(this.$element),c=this.getPosition(),d=b[0].offsetWidth,e=b[0].offsetHeight,f){case"bottom":g={top:c.top+c.height,left:c.left+c.width/2-d/2};break;case"top":g={top:c.top-e,left:c.left+c.width/2-d/2};break;case"left":g={top:c.top+c.height/2-e/2,left:c.left-d};break;case"right":g={top:c.top+c.height/2-e/2,left:c.left+c.width}}this.applyPlacement(g,f),this.$element.trigger("shown")}},applyPlacement:function(a,b){var c,d,e,f,g=this.tip(),h=g[0].offsetWidth,i=g[0].offsetHeight;g.offset(a).addClass(b).addClass("in"),c=g[0].offsetWidth,d=g[0].offsetHeight,"top"==b&&d!=i&&(a.top=a.top+i-d,f=!0),"bottom"==b||"top"==b?(e=0,a.left<0&&(e=a.left*-2,a.left=0,g.offset(a),c=g[0].offsetWidth,d=g[0].offsetHeight),this.replaceArrow(e-h+c,c,"left")):this.replaceArrow(d-i,d,"top"),f&&g.offset(a)},replaceArrow:function(a,b,c){this.arrow().css(c,a?50*(1-a/b)+"%":"")},setContent:function(){var a=this.tip(),b=this.getTitle();a.find(".k-tooltip__inner")[this.options.html?"html":"text"](b),a.removeClass("fade in top bottom left right")},hide:function(){function b(){var b=setTimeout(function(){c.off(a.support.transition.end).detach()},500);c.one(a.support.transition.end,function(){clearTimeout(b),c.detach()})}var c=this.tip(),d=a.Event("hide");if("undefined"!=typeof window.MooTools&&!this.mootools_compatible){var e=window.Element.prototype.hide;window.Element.implement({hide:function(){return a(this).data("ktooltip")?this:void e.apply(this,arguments)}}),this.mootools_compatible=!0}if(this.$element.trigger(d),!d.isDefaultPrevented())return c.removeClass("in"),a.support.transition&&this.$tip.hasClass("fade")?b():c.detach(),this.$element.trigger("hidden"),this},fixTitle:function(){var a=this.$element;(a.attr("title")||"string"!=typeof a.attr("data-original-title"))&&a.attr("data-original-title",a.attr("title")||"").attr("title","")},hasContent:function(){return this.getTitle()},getPosition:function(){var b=this.$element[0];return a.extend({},"function"==typeof b.getBoundingClientRect?b.getBoundingClientRect():{width:b.offsetWidth,height:b.offsetHeight},this.$element.offset())},getTitle:function(){var a,b=this.$element,c=this.options;return a=b.attr("data-original-title")||("function"==typeof c.title?c.title.call(b[0]):c.title)},tip:function(){return this.$tip=this.$tip||a(this.options.template)},arrow:function(){return this.$arrow=this.$arrow||this.tip().find(".k-tooltip__arrow")},validate:function(){this.$element[0].parentNode||(this.hide(),this.$element=null,this.options=null)},enable:function(){this.enabled=!0},disable:function(){this.enabled=!1},toggleEnabled:function(){this.enabled=!this.enabled},toggle:function(b){var c=b?a(b.currentTarget)[this.type](this._options).data(this.type):this;c.tip().hasClass("in")?c.hide():c.show()},destroy:function(){this.hide().$element.off("."+this.type).removeData(this.type)}};a.fn.tooltip;a.fn.ktooltip=function(c){return this.each(function(){var d=a(this),e=d.data("ktooltip"),f="object"==typeof c&&c;e||d.data("ktooltip",e=new b(this,f)),"string"==typeof c&&e[c]()})},a.fn.ktooltip.Constructor=b,a.fn.ktooltip.defaults={animation:!0,placement:"top",selector:!1,template:'<div class="k-tooltip"><div class="k-tooltip__arrow"></div><div class="k-tooltip__inner"></div></div>',trigger:"hover focus",title:"",delay:0,html:!1,container:!1}}(window.jQuery),window.jQuery=globalCacheForjQueryReplacement,globalCacheForjQueryReplacement=void 0;
//# sourceMappingURL=tooltip.js.map