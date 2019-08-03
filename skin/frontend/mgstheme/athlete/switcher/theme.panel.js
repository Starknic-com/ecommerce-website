/** PANEL FUNCTION **/
	var colorSetting = '';
	var defaultSetting = '';
	var timeout = 0;
	mgsjQuery(document).ready(function () {
		if (mgsjQuery('.wrapper').hasClass('welcome') || mgsjQuery('.wrapper').hasClass('coming-soon'))
			return;
		mgsjQuery.ajax({
			type : "GET",
			url : WEB_URL+'skin/frontend/mgstheme/athlete/switcher/css/color.css',
			dataType : "html",
			success : function (result) {
				colorSetting = result;
			}
		});
		mgsjQuery.ajax({
			type : "GET",
			url : WEB_URL+'skin/frontend/mgstheme/athlete/switcher/setting.html',
			dataType : "html",
			success : function (result) {
				mgsjQuery('body').append(result);
				if (colorSetting) {
					panelSetting();
				} else {
					timeout = setInterval(function () {
							if (colorSetting) {
								panelSetting();
								clearInterval(timeout);
							}
						}, 200);
				}
			}
		});
	});
	function panelSetting() {
		mgsjQuery('.color-setting button').each(function () {
			if (this.value[0] == '#') {
				mgsjQuery(this).css('background-color', this.value);
			} else {
				bgUrl = WEB_URL + this.value;
				mgsjQuery(this).css('background', 'url(' + bgUrl + ')');
			}
		});
		mgsjQuery('body').append('<style type="text/css" id="color-setting"></style>');
		panelAddOverlay();
		panelBindEvents();
		panelLoadSetting();

	}
	function panelBindEvents() {
		var clickOutSite = true;
		mgsjQuery('.panel-button').click(function () {
			if (!mgsjQuery(this).hasClass('active')) {
				mgsjQuery(this).addClass('active');
				mgsjQuery('.panel-content').show().animate({
					'margin-left' : 0
				}, 400, 'easeInOutExpo');
			} else {
				mgsjQuery(this).removeClass('active');
				mgsjQuery('.panel-content').animate({
					'margin-left' : '-240px'
				}, 400, 'easeInOutExpo', function () {
					mgsjQuery('.panel-content').hide()
				});
			}
			clickOutSite = false;
			setTimeout(function () {
				clickOutSite = true;
			}, 100);
		});
		mgsjQuery('.panel-content').click(function () {
			clickOutSite = false;
			setTimeout(function () {
				clickOutSite = true;
			}, 100);
		});
		mgsjQuery(document).click(function () {
			if (clickOutSite && mgsjQuery('.panel-button').hasClass('active')) {
				mgsjQuery('.panel-button').trigger('click');
			}
		});

		mgsjQuery('.layout-setting button').click(function () {
			if (!mgsjQuery(this).hasClass('active')) {
				mgsjQuery('.layout-setting button').removeClass('active');
				mgsjQuery(this).addClass('active');
				panelAddOverlay();
				panelWriteSetting();
				mgsjQuery(window).resize();
			}
		});
		mgsjQuery('.background-setting button').click(function () {
			if(mgsjQuery('.layout-setting button.active').val()=='wide'){
				return;
			}
			if (!mgsjQuery(this).hasClass('active')) {
				mgsjQuery('.background-setting button').removeClass('active');
				mgsjQuery(this).addClass('active');
				if (this.value[0] == '#') {
					mgsjQuery('body').attr('style', 'background: '+this.value+' !important');
				} else {
					bgUrl = WEB_URL + this.value;
					mgsjQuery('body').attr('style', 'background: url(' + bgUrl + ') !important');
				}
				panelWriteSetting();
			}
		});
		mgsjQuery('.sample-setting button').click(function () {
			if (!mgsjQuery(this).hasClass('active')) {
				mgsjQuery('.sample-setting button').removeClass('active');
				mgsjQuery(this).addClass('active');
				var newColorSetting = colorSetting.replace(/#ec3642/g, this.value);
				mgsjQuery('#color-setting').html(newColorSetting);
				panelWriteSetting();
			}
		});
		mgsjQuery('.reset-button button').click(function () {
			panelApplySetting(defaultSetting);
			setCookie('layoutsetting', '');
		});
		
		
		mgsjQuery('.my-cart').click(function () {
			if (!mgsjQuery(this).hasClass('active')) {
				mgsjQuery(this).addClass('active');
				mgsjQuery('.icon-cart .carts-store').show().animate({
					'margin-right' : 0
				}, 400, 'easeInOutExpo');
			} else {
				mgsjQuery(this).removeClass('active');
				mgsjQuery('.icon-cart .carts-store').animate({
					'margin-right' : '-301px'
				}, 400, 'easeInOutExpo', function () {
					mgsjQuery('.icon-cart .carts-store').hide()
				});
			}
			clickOutSite = false;
			setTimeout(function () {
				clickOutSite = true;
			}, 100);
		});
		mgsjQuery('.icon-cart .carts-store').click(function () {
			clickOutSite = false;
			setTimeout(function () {
				clickOutSite = true;
			}, 100);
		});
		mgsjQuery(document).click(function () {
			if (clickOutSite && mgsjQuery('.my-cart').hasClass('active')) {
				mgsjQuery('.my-cart').trigger('click');
			}
		});
				
		mgsjQuery('.my-wishlist').click(function () {
			if (!mgsjQuery(this).hasClass('active')) {
				mgsjQuery(this).addClass('active');
				mgsjQuery('.icon-wishlist .wishlists-store').show().animate({
					'margin-right' : 0
				}, 400, 'easeInOutExpo');
			} else {
				mgsjQuery(this).removeClass('active');
				mgsjQuery('.icon-wishlist .wishlists-store').animate({
					'margin-right' : '-301px'
				}, 400, 'easeInOutExpo', function () {
					mgsjQuery('.icon-wishlist .wishlists-store').hide()
				});
			}
			clickOutSite = false;
			setTimeout(function () {
				clickOutSite = true;
			}, 100);
		});
		mgsjQuery('.icon-wishlist .wishlists-store').click(function () {
			clickOutSite = false;
			setTimeout(function () {
				clickOutSite = true;
			}, 100);
		});
		mgsjQuery(document).click(function () {
			if (clickOutSite && mgsjQuery('.my-wishlist').hasClass('active')) {
				mgsjQuery('.my-wishlist').trigger('click');
			}
		});
		
	}
	function panelAddOverlay() {
		if (mgsjQuery('.layout-setting .active').hasClass('boxed')) {
			mgsjQuery('.overlay-setting').removeClass('disabled');
			mgsjQuery('body').addClass('boxed');
		} else {
			mgsjQuery('.overlay-setting').addClass('disabled');
			mgsjQuery('body').removeClass('boxed');
			mgsjQuery('body').attr('style', 'background-image: none');
		}

	}
	
	function panelLoadSetting() {
		// remember default setting
		defaultSetting = {
			layout : mgsjQuery('.layout-setting button.active').val(),
			mainColor : mgsjQuery('.sample-setting button.active').val(),
			bgColor : mgsjQuery('.background-setting button.active').val()
		}
		// apply activated setting
		var activeSetting = getCookie('layoutsetting');
		if (activeSetting) {
			activeSetting = JSON.parse(activeSetting);
			panelApplySetting(activeSetting);
		}
	}
	function panelApplySetting(setting) {
		mgsjQuery('.layout-setting button').each(function () {
			if (setting.layout == this.value) {
				mgsjQuery(this).trigger('click');
			}
		});
		mgsjQuery('.sample-setting button').each(function () {
			if (setting.mainColor == this.value) {
				mgsjQuery(this).trigger('click');
			}
		});
		mgsjQuery('.background-setting button').each(function () {
			if (setting.bgColor == this.value) {
				mgsjQuery(this).trigger('click');
			}
		});
	}
	function panelWriteSetting() {
		var activeSetting = {
			layout : mgsjQuery('.layout-setting button.active').val(),
			mainColor : mgsjQuery('.sample-setting button.active').val(),
			bgColor : mgsjQuery('.background-setting button.active').val()
		}
		setCookie('layoutsetting', JSON.stringify(activeSetting), 0);
	}
	
	/** COOKIE FUNCTION */
	function setCookie(cname, cvalue, exdays) {
		var expires = "";
		if(exdays){
			var d = new Date();
			d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
			expires = " expires=" + d.toUTCString();	
		}
		document.cookie = cname + "=" + cvalue + ";" + expires;
	}
	function getCookie(cname) {
		var name = cname + "=";
		var ca = document.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ')
				c = c.substring(1);
			if (c.indexOf(name) == 0)
				return c.substring(name.length, c.length);
		}
		return "";
	}