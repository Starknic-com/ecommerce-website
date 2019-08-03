jQuery(window).scroll(function() {

    if (jQuery(this).scrollTop()>0)
     {
        jQuery('.header').fadeOut();
     }
    else
     {
      jQuery('.header').fadeIn();
     }
 });
(function(mgsjQuery){
	"use strict";

	mgsjQuery(function(){

		var Core = {

			initialized : false,

			initialize : function(){

				if(this.initialized) return;
				this.initialized = true;

				this.build();

			},

			build : function(){
				this.animations();	
				this.plugins();
				if(mgsjQuery('.progress-indicator').length) this.events.progressBars();
			},

			sliders : function(){

				// collection of the slider elements

				var sliderCollection = ['.r_slider','#layerslider','#layerslider_video','.flexslider','.iosslider'],
					haveSlider = false;
				
				for(var i = 0; i < sliderCollection.length;i++){
					if(mgsjQuery(sliderCollection[i]).length) haveSlider = true;
				}
				if(!haveSlider) return false;

				// revolution

				if(mgsjQuery(sliderCollection[0]).length){
			    	var api = mgsjQuery(sliderCollection[0]).revolution({
				        delay:5000,
						startwidth:1170,
						startheight:500,
						hideThumbs:0,
						fullWidth:"on",
			     		hideTimerBar:"on",
			     		soloArrowRightHOffset:30,
			     		soloArrowLeftHOffset:30,
			     		shadow:0
			      	});

			      	api.bind('revolution.slide.onloaded',function(){
		      		mgsjQuery(sliderCollection[0]).parent().find('.tp-leftarrow,.tp-rightarrow')
		      			.addClass('color_light icon icon_wrap_size_3 circle tr_all');
		      		});
		      		mgsjQuery(sliderCollection[0]).parent().find('.tp-bullets').remove();
			     }

			   		
			},

			plugins : function(){

				// plugins collection

				var pluginsCollection = ['.tabs','.accordion','#calendar','.jackbox[data-group]','.tweets','#countdown','#dribbble_feed','#price','.thumbnails_carousel','#img_zoom'],
					havePlugin = false;
				
				for(var i = 0; i < pluginsCollection.length;i++){
					if(mgsjQuery(pluginsCollection[i]).length) havePlugin = true;
				}

				this.owlCarousel();
				this.isotope();

				if(!havePlugin) return false;
				
				if(mgsjQuery(pluginsCollection[3]).length){
					mgsjQuery(pluginsCollection[3]).jackBox("init",{
						    showInfoByDefault: false,
						    preloadGraphics: true, 
						    fullscreenScalesContent: true,
						    autoPlayVideo: true,
						    flashVideoFirst: false,
						    defaultVideoWidth: 960,
						    defaultVideoHeight: 540,
						    baseName: "plugins/jackbox",
						    className: ".jackbox",
						    useThumbs: true,
						    thumbsStartHidden: false,
						    thumbnailWidth: 75,
						    thumbnailHeight: 50,
						    useThumbTooltips: true,
						    showPageScrollbar: false,
						    useKeyboardControls: true 
					});
				}
			
			},

			animations : function(){
				// appear animatuion
				mgsjQuery("[data-appear-animation]").each(function() {

					var self = mgsjQuery(this);

					self.addClass("appear-animation");

					if(mgsjQuery(window).width() > 767) {
						self.appear(function() {

							var delay = (self.attr("data-appear-animation-delay") ? self.attr("data-appear-animation-delay") : 1);

							if(delay > 1) self.css("animation-delay", delay + "ms");
							self.addClass(self.attr("data-appear-animation"));
                                                        self.addClass("animated");

							setTimeout(function() {
								self.addClass("appear-animation-visible");
							}, delay);

						}, {accX: 0, accY: -150});
					} else {
						self.addClass("appear-animation-visible");
					}
				});
			},

			owlCarousel: function(options) {

				var total = mgsjQuery("div.owl-carousel").length;

				mgsjQuery("div.owl-carousel").each(function() {

					var slider = mgsjQuery(this),
						buttonClass = slider.data('nav');

					var defaults = {
						 // Most important owl features
						items : 5,
						itemsCustom : false,
						itemsDesktop : [1199,4],
						itemsDesktopSmall : [980,3],
						itemsTablet: [768,2],
						itemsTabletSmall: false,
						itemsMobile : [479,1],
						singleItem : true,
						itemsScaleUp : false,

						//Basic Speeds
						slideSpeed : 500,
						paginationSpeed : 800,
						rewindSpeed : 1000,

						//Autoplay
						autoPlay : false,
						stopOnHover : false,

						// Navigation
						navigation : false,
						navigationText : ["<i class=\"icon icon-chevron-left\"></i>","<i class=\"icon icon-chevron-right\"></i>"],
						rewindNav : true,
						scrollPerPage : false,

						//Pagination
						pagination : false,
						paginationNumbers: false,

						// Responsive
						responsive: true,
						responsiveRefreshRate : 200,
						responsiveBaseWidth: window,

						// CSS Styles
						baseClass : "owl-carousel",
						theme : "owl-theme",

						//Lazy load
						lazyLoad : false,
						lazyFollow : true,
						lazyEffect : "fade",

						//Auto height
						autoHeight : false,

						//JSON
						jsonPath : false,
						jsonSuccess : false,

						//Mouse Events
						dragBeforeAnimFinish : true,
						mouseDrag : true,
						touchDrag : true,

						//Transitions
						transitionStyle : false,

						// Other
						addClassActive : false,

						//Callbacks
						beforeUpdate : false,
						afterUpdate : false,
						beforeInit: false,
						afterInit: false,
						beforeMove: false,
						afterMove: false,
						afterAction: false,
						startDragging : false,
						afterLazyLoad : false,
						//right to left
						rtl: false
					}	
					var config = mgsjQuery.extend({}, defaults, options, slider.data("plugin-options"));
					// Initialize Slider
					slider.owlCarousel(config).addClass("owl-carousel-init");

					// subscribe filter event
					if(slider.hasClass('wfilter_carousel')) Core.events.filterCarousel(slider,mgsjQuery('[data-carousel-filter]'));

					mgsjQuery('.'+buttonClass+'next').on('click',function(){
						slider.trigger('owl.next');
					});

					mgsjQuery('.'+buttonClass+'prev').on('click',function(){
						slider.trigger('owl.prev');
					});

					if(slider.data('plugin-options') != undefined && slider.data('plugin-options').pagination){
						if(slider.hasClass('brands')){
							slider.find('.owl-controls').addClass('d_inline_b');
							return;
						}
						slider.find('.owl-controls')
							.appendTo(slider.next().find('.clients_pags_container'));
					}
					if(slider.hasClass('banners_carousel')) slider.find('.owl-controls').addClass('wrapper d_inline_b m_top_10');


				});
			},
			
			isotope : function(){
					var cthis = this;
					mgsjQuery('[data-isotope-options]').each(function(){

						var self = mgsjQuery(this),
							options = self.data('isotope-options');

						self.isotope(options);

						cthis.events.sortIsotope(self);
						cthis.events.loadMoreIsotope(self,options.itemSelector);

					});
			},
									
			events : {
				filterCarousel : function(carousel, filterUL){

					var elements = [],
					item = carousel.find('.wfcarousel_item'),
					len = item.length,
					counter = 0;

					for(var i = 0; i < len; i++){
			 			elements.push(item.eq(i)[0].outerHTML);
			 		}

				 	filterUL.on('click','a',function(e){

				 		e.preventDefault();
				 		counter++;

				 		var	self = mgsjQuery(this),
			 			activeElem = self.data('filter-c-item');

				 		carousel.addClass('changed').find('.owl-wrapper').animate({
				 			opacity : 0
				 		},function(){

				 			var s = mgsjQuery(this);
				 			carousel.children().remove();

				 			if(activeElem == "*"){
				 				mgsjQuery.each(elements,function(i,v){
				 					carousel.append(v);
					 			});
				 			}else{
					 			mgsjQuery.each(elements,function(i,v){
					 				if(v.indexOf(activeElem) !== -1){
					 					carousel.append(v);
					 				}
					 			});
				 			}

				 			carousel.data('owlCarousel').destroy();
				 			carousel.owlCarousel({
				 				itemsCustom: [[992,4],[768,3],[450,2],[10,1]],
								pagination:false,
								slideSpeed:500,
						 		afterInit: function(){
						 			carousel.addClass('no_children_animate');
						 		}
				 			});
				 			carousel.find("[data-group]").attr("data-group","filter_group" + counter).jackBox("newItem");
				 		});

			 			self.closest('li').addClass('active').siblings().removeClass('active');

					});
				},
				
				loadMoreIsotope : function(container,item){
					var loadMore = mgsjQuery('#load-more'),	
						// filter classes array
						sortItem = mgsjQuery('.sort').find('[data-filter]'),
						sortClasses = [];

					for(var i = 1; i < sortItem.length;i++){
						sortClasses.push(sortItem.eq(i).data('filter').slice(1));
					}

					loadMore.on('click',function(e){
						
						var elems = [];
					
						for(var i = 0,l = Core.helpers.getRandom(2,5);i < l;i++){
							elems.push(Core.helpers.getNewRandomElement(sortClasses,container,item.slice(1)));
						}

						container.append(elems).isotope('appended', container.find(item+':not([style])').addClass( container.hasClass('home') ? 'added type_2' : 'added'));
						setTimeout(function(){
							container.isotope('layout');
							Core.simpleSlideshow();
						},100);

						jQuery.jackBox.available(function() {
		     
						    jQuery(".added").find("[data-category]").jackBox("newItem");
						     
						});

						e.preventDefault();
					});
				},

				selectButtons : function(){

					var sButton = mgsjQuery('[class*="select_button"]');

					sButton.on('click',function(e){
						e.preventDefault();

						var self = mgsjQuery(this),
							container = self.attr('href'),
							offset = mgsjQuery(container).offset().top;

						mgsjQuery('html,body').animate({
							scrollTop : offset - 58
						},1000,'easeInOutCirc');

					});

				},
				
				progressBars : function(){

			    	var item = mgsjQuery('.progress-indicator'),
			    		container = item.closest('ul');
			    	if(!item.length) return;

			    	function scrollPage(){
			    		var offset = container.offset().top;
			    		if(mgsjQuery(window).scrollTop() > (offset - 700) && !(container.hasClass('counted'))){
				    		item.each(function(i){
				    			container.addClass('counted');

				    			var self = mgsjQuery(this),
				    				value = +self.data('value'),
				    				indicator = self.children('div');

				    			setTimeout(function(){
				    				indicator.animate({
					    				'width': value + '%'
					    			},1000,'easeInCubic');
				    			},i * 100);

				    		});
			    		}
			    	}
			    	scrollPage();

			    	mgsjQuery(window).on('scroll',scrollPage);
				},
			}
		}

		Core.initialize();
	});
})(jQuery);