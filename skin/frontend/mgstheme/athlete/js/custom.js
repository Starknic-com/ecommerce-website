function parallaxInit() {
	mgsjQuery('.parallax').parallax("30%", 0.1);
};

function initSlider(el,number,aplay,stophv,nav,pag){
	mgsjQuery("#"+el).owlCarousel({
		items : number,
		lazyLoad : true,
		navigation : nav,
		pagination : pag,
		autoPlay: aplay,
		stopOnHover: stophv,
		navigationText: ["<i class='fa fa-chevron-left'></i>","<i class='fa fa-chevron-right'></i>"],
		itemsCustom : false,
		itemsDesktop : [1199,number],
		itemsDesktopSmall : [980,number],
		itemsTablet: [768,number],
		itemsTabletSmall: false,
		itemsMobile : [479,1],
		rtl:RLT_VAR,
	});
};

function toggleEl(el){
	//mgsjQuery('.toggle-el').hide();
	mgsjQuery('#'+el).slideToggle('fast');
};

function initThemeJs(){
	// init tooltip
	mgsjQuery('.tooltip-links').tooltip({
		selector: "[data-toggle=tooltip]",
		container: "body"
	});
	
	// init height for product info box
	/* if(mgsjQuery(window).width() > 991) {
		mgsjQuery(".product-info-box").css("min-height", "auto");
		mgsjQuery(".products-grid").each(function() {
			var wrapper = $(this);
			var minBoxHeight = 0;
			mgsjQuery(".product-info-box", wrapper).each(function() {
				if(mgsjQuery(this).height() > minBoxHeight)
					minBoxHeight = mgsjQuery(this).height();
			});
			mgsjQuery(".product-info-box", wrapper).height(minBoxHeight);
		});
	} else {
		mgsjQuery(".product-info-box").css("min-height", "auto");
	} */
};

mgsjQuery(window).load(function() {
	mgsjQuery(window).bind('body', function() {
		parallaxInit();
	});
	
	var $container = mgsjQuery('.masonry-grid');
	// initialize
	$container.masonry({
	  itemSelector: '.item'
	});
	
	initThemeJs();
	
	if(mgsjQuery('.back-to-top').length){
		mgsjQuery('.back-to-top').click(function(){
			mgsjQuery('html, body').animate({scrollTop: '0px'}, 800);
			return false;
		});	
			
	};
	mgsjQuery('.scroll-down').click(function() {
		mgsjQuery('html, body').animate({scrollTop: '1000px'}, 800);
				return false;
	});
});
// init gmap
function initGmap(address, html, image){
	mgsjQuery.ajax({
		type: "GET",
		dataType: "json",
		url: "http://maps.googleapis.com/maps/api/geocode/json",
		data: {'address': address,'sensor':false},
		success: function(data){
			if(data.results.length){
				latitude = data.results[0].geometry.location.lat;
				longitude = data.results[0].geometry.location.lng;
				
				var locations = [
			[html, latitude, longitude, 2]
			];
		
			var map = new google.maps.Map(document.getElementById('map'), {
			  zoom: 14,
				scrollwheel: false,
				navigationControl: true,
				mapTypeControl: false,
				scaleControl: false,
				draggable: true,
				center: new google.maps.LatLng(latitude, longitude),
			  mapTypeId: google.maps.MapTypeId.ROADMAP
			});
		
			var infowindow = new google.maps.InfoWindow();
		
			var marker, i;
		
			for (i = 0; i < locations.length; i++) {  
		  
				marker = new google.maps.Marker({ 
				position: new google.maps.LatLng(locations[i][1], locations[i][2]), 
				map: map ,
				icon: image
				});
		
		
			  google.maps.event.addListener(marker, 'click', (function(marker, i) {
				return function() {
				  infowindow.setContent(locations[i][0]);
				  infowindow.open(map, marker);
				}
			  })(marker, i));
			}
			}
		}
	});
};

var newCount = 2;
var hotCount = 2;
var featuredCount = 2;

// load more products
function loadMore(count, type, productCount, perRow){
	mgsjQuery('#'+type+'_loadmore_button .loading').show();
	var request = new Ajax.Request(WEB_URL+'mpanel/loadmore/'+type+'?perrow='+perRow+'&p='+count+'&limit='+productCount, {
		onSuccess: function(response) {
			result = response.responseText;
			mgsjQuery('#'+type+'_product_container').append(result);
			mgsjQuery('#'+type+'_loadmore_button .loading').hide();
			initThemeJs();
		}
	});
};

// open overlay popup
function openOverlay(){
	mgsjQuery('#theme-popup').show();
};

// close overlay popup
function closeOverlay(){
	mgsjQuery('#theme-popup').hide();
};

var active = false;
var data = "";

// Price slider
function sliderAjax(url) {
	if (!active) {
		active = true;
		openOverlay();		
		oldUrl = url;
		try {
			mgsjQuery.ajax({
				url: url,
				dataType: 'json',
				type: 'post',
				data: data,
				success: function(data) {
					if (data.leftcontent) {
						if (mgsjQuery('.block-layered-nav')) {
							mgsjQuery('.block-layered-nav').empty();
							mgsjQuery('.block-layered-nav').append(data.leftcontent);
						}
					}
					if (data.maincontent) {
						mgsjQuery('#product-list-container').empty();
						mgsjQuery('#product-list-container').append(data.maincontent);
					}
					var hist = url.split('?');
					if(window.history && window.history.pushState){
						window.history.pushState('GET', data.title, url);
					}
					initThemeJs();
					closeOverlay();
				}
			});
		} catch (e) {}

		active = false;
	}
	return false;
};


// Ajax catalog load
function shopMore(url) {
	oldHtml = mgsjQuery('.category-products ul.products-grid').html();
	openOverlay();
	oldUrl = url;
	try {
		mgsjQuery.ajax({
			url: url,
			dataType: 'json',
			type: 'post',
			data: data,
			success: function(data) {
				if (data.leftcontent) {
					if (mgsjQuery('.block-layered-nav')) {
						mgsjQuery('.block-layered-nav').empty();
						mgsjQuery('.block-layered-nav').append(data.leftcontent);
					}
				}
				if (data.maincontent) {
					mgsjQuery('#product-list-container').empty();
					mgsjQuery('#product-list-container').append(data.maincontent);
					mgsjQuery('.category-products ul.products-grid').prepend(oldHtml);
				}
				initThemeJs();
				closeOverlay();
			}
		});
	} catch (e) {}
};

mgsjQuery(document).ready(function() {    
	var TopFixMenu = mgsjQuery(".sticky-menu .content-h");
	mgsjQuery(window).scroll(function(){
        if(mgsjQuery(this).scrollTop()>39 && mgsjQuery(this).width() > 990){
        	mgsjQuery('.top-panel').css({"z-index":"300"});
        	TopFixMenu.addClass("sticky_menu");
        }else{
        	mgsjQuery('.top-panel').css({"z-index":"9999"});
        	TopFixMenu.removeClass("sticky_menu");
        }    
    });
	if(mgsjQuery('.crumb .titles').children('.page-title').length == 0) {
		mgsjQuery('.crumb .titles').css("display", "none");
		mgsjQuery('.crumb').addClass('notitle');
	};
	mgsjQuery('#open-button').click(function(){
		mgsjQuery('.content-wrapper').addClass('show-menu');
		mgsjQuery('.cms-home').addClass('show-m');
	});
	mgsjQuery('#close-button').click(function(){
		mgsjQuery('.content-wrapper').removeClass('show-menu');
		mgsjQuery('.cms-home').removeClass('show-m');
	});
	mgsjQuery('#open-button').click(function(){
		mgsjQuery(this).toggleClass('run');
	});
	mgsjQuery('#content-wrapper').toggleClass('content-wrapper');
	mgsjQuery('nav ul.nav-menu > li.dropdown').each(function(){
		mgsjQuery(this).children('a.dropdown-toggle').click(function(){
			mgsjQuery(this).siblings('ul.dropdown-menu').toggleClass('drop');
			mgsjQuery(this).children('.icon-arrow').toggleClass('rotate');
			mgsjQuery(this).parent('.dropdown').siblings('li.dropdown').children('a.dropdown-toggle').siblings('ul.dropdown-menu').removeClass('drop');
		});
	});
	
	
});
mgsjQuery(document).ready(function() {
	mgsjQuery('#mainMenu li.level0').each(function() {
		var w = mgsjQuery(this).width();
		mgsjQuery(this).children('a.level0').append('<span class="arrow-nav"></span>')
		mgsjQuery(this).children().children('span.arrow-nav').css('border-left',w/2+'px solid transparent');
		mgsjQuery(this).children().children('span.arrow-nav').css('border-right',w/2+'px solid transparent');
	});
	
	mgsjQuery('#footer h4 span.collapse_button').click(function() {
		if (!mgsjQuery(this).parent().parent().hasClass('current')){
			mgsjQuery(this).html('&#8211;');
			mgsjQuery(this).parent().parent().children('ul').show(300);
			mgsjQuery(this).parent().parent().children('.footer-left').show(300);
			mgsjQuery(this).parent().parent().find('.static-can-edit > div > ul').show(300);
			mgsjQuery(this).parent().parent().addClass('current');
		}	else 	{
			mgsjQuery(this).html('+');
			mgsjQuery(this).parent().parent().find('ul').hide(300);
			mgsjQuery(this).parent().parent().find('.footer-left').hide(300);
			mgsjQuery(this).parent().parent().find('.static-can-edit > div > ul').hide(300);
			mgsjQuery(this).parent().parent().removeClass('current');
			mgsjQuery(this).parent().parent().find('li').removeClass('current');
			mgsjQuery(this).parent().parent().find('span.collapse_button').html('+');
		}
	});
});

mgsjQuery(document).ready(function() {
	var dropdown = mgsjQuery("#mainMenu li.dropdown");
	if(mgsjQuery(this).width() < 990) {
		dropdown.each(function(){
			if(mgsjQuery(this).children("ul").hasClass("active")){
				mgsjQuery(this).addClass("e-active");
			}
			else {
				mgsjQuery(this).removeClass("e-active");
			}
		});
	}
});
mgsjQuery(document).ready(function() {    
	var slide2 = mgsjQuery(".righttop");
	var slide3 = mgsjQuery(".form_search #search_mini_form");
	var slide4 = mgsjQuery(".cart_top .sidebar1 .block-content");
	var slide5 = mgsjQuery(".lefttop");
	var slide6 = mgsjQuery(".righttop .toplinks .dropdown-menu");
	var slide7 = mgsjQuery(".cart_top .sidebar1 .dropdown-menu");
	slide2.children("div.btn-group").each(function(){
		mgsjQuery(this).click(function(){
			mgsjQuery(this).children("ul.dropdown-menu").slideToggle("fast");
		});
	});
	slide5.children("div.btn-group").each(function(){
		mgsjQuery(this).click(function(){
			mgsjQuery(this).children("ul.dropdown-menu").slideToggle("fast");
		});
	});
	mgsjQuery(".form_search .search").click(function(){
		slide3.slideToggle("fast");
	});
	mgsjQuery(".cart_top .sidebar1 .icon-cart").click(function(){
		slide4.slideToggle("fast");
	});
	mgsjQuery(".cart3").click(function(){
		slide7.slideToggle("fast");
	});
	mgsjQuery("#header2 .toplinks > a").click(function(){
		slide6.slideToggle('fast');
	});
});

mgsjQuery(window).load(function(){
				
				mgsjQuery(".quickview-product,body.ajaxcart-index-options").mCustomScrollbar({		
					setHeight: 450,
					theme:"minimal-dark"
				});
				
	});
mgsjQuery(document).ready(function(){
	mgsjQuery('.alert').slideDown(400);
	mgsjQuery('i').click(function () {
            mgsjQuery('.alert').slideUp(400);
        });
	 mgsjQuery('.alert').slideDown('400', function () {
            setTimeout(function () {
                mgsjQuery('.alert').slideUp('400', function () {
                    mgsjQuery(this).slideUp(400, function(){ mgsjQuery(this).detach(); })
                });
            }, 7000)
        });
});
/* add class to the open accordion title */
mgsjQuery(function(){
	mgsjQuery('#accordion-product-questions').on('show.bs.collapse', function(e){
		mgsjQuery(e.target).prev('.panel-heading').find('.panel-content-heading').addClass('active');
	});
	mgsjQuery('#accordion-product-questions').on('hide.bs.collapse', function(e){ 
		mgsjQuery(this).find('.panel-content-heading').not(mgsjQuery(e.target)).removeClass('active');
	});
});

function setTabBackground(url){
	mgsjQuery('tab-background').setStyle({backgroundImage: 'url('+url+')'});
};
//Add custom js theme athlete 
mgsjQuery(document).ready(function(){
	
	 	mgsjQuery('body:not(.bt-other-shortcodes-loaded) .our-team-nav span').on('click',  function (e) {

 		var $tab = mgsjQuery(this),

 			index = $tab.index(),

 			is_disabled = $tab.hasClass('bt-tabs-disabled'),

 			$tabs = $tab.parent('.our-team-nav').children('span'),

 			$panes = $tab.parents('.our-team-tabs').find('.our-team-pane'),

 			$gmaps = $panes.eq(index).find('.bt-gmap:not(.bt-gmap-reloaded)');

 		// Check tab is not disabled

 		if (is_disabled) return false;

 		// Hide all panes, show selected pane

 		$panes.hide().removeClass('active').eq(index).show().addClass('active');

 		// Disable all tabs, enable selected tab

 		$tabs.removeClass('our-team-current').eq(index).addClass('our-team-current');

 		// Reload gmaps

 		if ($gmaps.length > 0) $gmaps.each(function () {

 			var $iframe = mgsjQuery(this).find('iframe:first');

 			mgsjQuery(this).addClass('bt-gmap-reloaded');

 			$iframe.attr('src', $iframe.attr('src'));

 		});

 		// Set height for vertical tabs

 		tabs_height();

 		e.preventDefault();

 	});

 	// Activate tabs

 	mgsjQuery('.our-team-tabs').each(function () {

 		var active = parseInt(mgsjQuery(this).data('active')) - 1;

 		mgsjQuery(this).children('.our-team-nav').children('span').eq(active).trigger('click');

 		tabs_height();

 	});

 	function tabs_height() {

 		mgsjQuery('.bt-tabs-vertical, .bt-tabs-vertical-right').each(function () {

 			var $tabs = mgsjQuery(this),

 				$nav = $tabs.children('.our-team-nav'),

 				$panes = $tabs.find('.our-team-pane'),

 				height = 0;

 			$panes.css('min-height', $nav.outerHeight(true));

 		});

 	}	
	
	mgsjQuery('.owl-page').click(function () {
		var owl1 = mgsjQuery('#carousel-text').data('owlCarousel');
		var owl2 = mgsjQuery('#carousel-image').data('owlCarousel');
		mgsjQuery('.owl-page').removeClass('active');
		mgsjQuery(this).addClass('active');
		owl1.goTo(mgsjQuery(this).attr('data-page'));
		owl2.goTo(mgsjQuery(this).attr('data-page'));
	});
	mgsjQuery('.fit-strong-left').waypoint(function () {
			mgsjQuery('.fit-strong-text, .fit-strong-sub, .fit-strong-bottom').each(function (index) {
				mgsjQuery(this).delay(600 * index).animate({
					width : "auto"
				}, 0, function () {
					mgsjQuery(this).addClass('move_to_fadein_title');
				});
			});
		}, {
			offset : '100%'
		});
	mgsjQuery('.fit-strong-right, .img-box-right').each(function (index) {
			mgsjQuery(this).waypoint(function () {
				mgsjQuery(this).delay(600 * index + 1000).animate({
					width : "auto"
				}, 0, function () {
					mgsjQuery(this).addClass('move_to_fadein_title');
				});
			}, {
				offset : '100%'
			});
		});

	mgsjQuery('.img-box-right').waypoint(function () {
			mgsjQuery('.img-box, .open-hour, .text-box').each(function (index) {
				mgsjQuery(this).delay(600 * index + 2000).animate({
					width : "auto"
				}, 0, function () {
					mgsjQuery(this).addClass('move_to_fadein_title');
				});
			});
		}, {
			offset : '100%'
		});
	
	mgsjQuery('.facts-page').waypoint(function () {
			mgsjQuery('.title-facts').each(function (index) {
				mgsjQuery(this).animate({
					width : "auto"
				}, 0, function () {
					mgsjQuery(this).addClass('move_to_fadein_title');
				});
			});
			
			var counter = 0;
			mgsjQuery('.facts-content .count span').each(function(){
				var el = this;
				counter++;
				var y = parseInt(mgsjQuery(el).html());
					setTimeout(function(){
					mgsjQuery({someValue: 0}).animate({someValue: y}, {
					  duration: 2000,
					  easing:'swing', // can be anything
					  step: function() { // called on every step
						mgsjQuery(el).html(Math.round(this.someValue));
					  },
					  complete:function(){
						mgsjQuery(el).html(y);
					  }				  
					});
				},1000 * counter);
			});
			
		}, {
			offset : '100%'
		});
	mgsjQuery('.facts-page').waypoint(function () {
			mgsjQuery('.facts-content').each(function (index) {
				mgsjQuery(this).delay(600 * index + 2000).animate({
					width : "auto"
				}, 0, function () {
					mgsjQuery(this).addClass('move_to_center');
				});
			});
		}, {
			offset : '100%'
		});
	mgsjQuery('.to-bottom').click(function () {
			mgsjQuery('html, body').animate({
				scrollTop : mgsjQuery(this).offset().top
			}, 'slow');
			return false;

		});
	mgsjQuery('.to-top-bottom').click(function () {
			mgsjQuery('html, body').animate({
				scrollTop : mgsjQuery(this).offset().top
			}, 'slow');
			return false;

		});
	
	var widthBox = mgsjQuery('#boxOpenTime').width();
	mgsjQuery('.img-box-right-border').css('border-left-width', widthBox + 'px');
	mgsjQuery(window).resize(function () {
			widthBox = mgsjQuery('#boxOpenTime').width();
			mgsjQuery('.img-box-right-border').css('border-left-width', widthBox + 'px');
		});
		
	mgsjQuery('.scrollcate_women').click(function(){		
		mgsjQuery('html,body').animate({
			scrollTop: mgsjQuery('#category-women').offset().top
		},'slow');
	});
	mgsjQuery('.scrollcate_man').click(function(){		
		mgsjQuery('html,body').animate({
			scrollTop: mgsjQuery('#category-man').offset().top
		},'slow');
	});
});
function closeMgs() {
	mgsjQuery.magnificPopup.close();
}