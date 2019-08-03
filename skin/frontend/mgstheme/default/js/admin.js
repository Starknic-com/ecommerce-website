function checkBuilder(){
	if($('homesetting')){
		if($('homesetting').checked == true){
			mgsjQuery('#layout-button').show();
			$('configstatus').value = 1;
		}
		else{
			mgsjQuery('#layout-button').hide();
			$('configstatus').value = 0;
			mgsjQuery('#layout-setting').hide();
			//$('panel-form').submit();
		}
	}
}

function switchBuilder(){
    if($('homesetting')){
	if($('homesetting').checked != true){
		$('panel-form').submit();
	}
    }
}

function setLayout(layout){
	if(mgsjQuery('#'+layout).hasClass('active')){
		mgsjQuery('#'+layout).removeClass('active');
		mgsjQuery('#layout-input').val('');
	}
	else{
		mgsjQuery('#layouts .item img').removeClass('active');
		mgsjQuery('#'+layout).addClass('active');
		mgsjQuery('#layout-input').val(layout);
	}
}

mgsjQuery(document).ready(function() {
	var magnificPopup = mgsjQuery('.popup-link').magnificPopup({
		type: 'iframe',
		iframe: {
			markup: '<div class="mfp-iframe-scaler builder-iframe">'+
					'<div class="mfp-close"></div>'+
					'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
					'</div>'
		}, 
		mainClass: 'mfp-fade',
		removalDelay: 160,
		preloader: false,
		fixedContentPos: false
	});
	checkBuilder(mgsjQuery('#homepageconfig').val());
	
	 mgsjQuery("#font, #h1, #h2, #h3, #price").trigger("change");
		
	// set max height for all section
	setMaxHeightforAll();
});

function initSortable(el, url){
	mgsjQuery(function() {
		mgsjQuery("#"+el).sortable({
			handle: '.sort-handle',
			update: function (event, ui) {
				var data = mgsjQuery(this).sortable('serialize');

				// POST to server using $.post or $.ajax
				mgsjQuery.ajax({
					data: data,
					type: 'POST',
					url: url
				});
			}
		});
		mgsjQuery("#"+el).disableSelection();
	});
}

function setMaxHeightforAll(){
	if(mgsjQuery('.section1').length && mgsjQuery('.section2').length){
		var parentTag = mgsjQuery('.section1').parent().get(0);
		mgsjQuery('.section1, .section2').height(mgsjQuery(parentTag).height());
	}
	
	if((mgsjQuery('.section4').length && mgsjQuery('.section5').length) || (mgsjQuery('.section4').length && mgsjQuery('.section6').length) || (mgsjQuery('.section5').length && mgsjQuery('.section6').length)){
		if(mgsjQuery('.section4').length){
			var parentTag = mgsjQuery('.section4').parent().get(0);
		}
		else{
			if(mgsjQuery('.section5').length){
				var parentTag = mgsjQuery('.section5').parent().get(0);
			}
			else{
				var parentTag = mgsjQuery('.section6').parent().get(0);
			}
		}
		
		if(mgsjQuery('.section4').length){
			mgsjQuery('.section4').height(mgsjQuery(parentTag).height());
		}
		
		if(mgsjQuery('.section5').length){
			mgsjQuery('.section5').height(mgsjQuery(parentTag).height());
		}
		
		if(mgsjQuery('.section6').length){
			mgsjQuery('.section6').height(mgsjQuery(parentTag).height());
		}
	}
}

// Load homepage content after change settings of a block or child
function loadHomepageContent(){
	openOverlay();
	var request = new Ajax.Request(WEB_URL+'mpanel/index/ajax', {
		onSuccess: function(response) {
			result = response.responseText.evalJSON();
			$('homepage-content-container').update(result.html);
			
			var magnificPopup = mgsjQuery('.popup-link').magnificPopup({
				type: 'iframe',
				iframe: {
					markup: '<div class="mfp-iframe-scaler builder-iframe">'+
							'<div class="mfp-close"></div>'+
							'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
							'</div>'
				},
				mainClass: 'mfp-fade',
				removalDelay: 160,
				preloader: true,
				fixedContentPos: false
			});
			
			setMaxHeightforAll();
			closeOverlay();
			var $container = mgsjQuery('.masonry-grid');
			$container.masonry({
			  itemSelector: '.item'
			});
		}
	});
}

function loadRightContent(category_id){
	var request = new Ajax.Request(WEB_URL+'mpanel/index/right/category_id/'+category_id+'/', {
		onSuccess: function(response) {
			result = response.responseText.evalJSON();
			$('right-content-container').update(result.html);
			
			var magnificPopup = mgsjQuery('.popup-link').magnificPopup({
				type: 'iframe',
				iframe: {
					markup: '<div class="mfp-iframe-scaler builder-iframe">'+
							'<div class="mfp-close"></div>'+
							'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
							'</div>'
				},
				mainClass: 'mfp-fade',
				removalDelay: 160,
				preloader: true,
				fixedContentPos: false
			});
		}
	});
}

function loadLeftContent(category_id){
	var request = new Ajax.Request(WEB_URL+'mpanel/index/left/category_id/'+category_id+'/', {
		onSuccess: function(response) {
			result = response.responseText.evalJSON();
			$('left-content-container').update(result.html);
			
			var magnificPopup = mgsjQuery('.popup-link').magnificPopup({
				type: 'iframe',
				iframe: {
					markup: '<div class="mfp-iframe-scaler builder-iframe">'+
							'<div class="mfp-close"></div>'+
							'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
							'</div>'
				},
				mainClass: 'mfp-fade',
				removalDelay: 160,
				preloader: true,
				fixedContentPos: false
			});
		}
	});
}

// change font of element
function changeFont(el){
	mgsjQuery("#mgs_theme_fonts_"+el+"_view").css({ fontFamily: mgsjQuery("#"+el).val().replace("+"," ") });
	mgsjQuery("<link />",{href:"http://fonts.googleapis.com/css?family="+mgsjQuery("#"+el).val(),rel:"stylesheet",type:"text/css"}).appendTo("head");
}

// Save store config
function saveStoreConfig(el,saveText,savingText){
	mgsjQuery('#'+el+' .btn-save-config').attr('disabled', 'disabled');
	mgsjQuery('#'+el+' .btn-save-config').html(savingText);
	var val = mgsjQuery('#'+el+'-input').val();
	var request = new Ajax.Request(WEB_URL+'mpanel/edit/config?path='+el+'&val='+val, {
		onSuccess: function(response) {
			mgsjQuery('#'+el+' .btn-save-config').removeAttr('disabled');
			mgsjQuery('#'+el+' .btn-save-config').html(saveText);
			if(response.responseText=='success'){
				mgsjQuery('#'+el+'-text').html(val);
				mgsjQuery('#'+el).slideToggle('fast');
			}else{
				alert('Problem saving data, please try again later.');
			}
		}
	});
}

function changeBlockCol(link){
	openOverlay();
	var request = new Ajax.Request(link, {
		onSuccess: function(response) {
			loadHomepageContent();
		}
	});
}