function showDialogIfMobVerReq() {
	jQuery.ajax({
		url: 'http://starknic.com/index.php/checkout/cart/isMobileVerReq',
		dataType: 'json',
		cache: false,
		type: "GET",
		contentType: 'application/json',
		processData: true,
		success: function( data, textStatus, jQxhr ){
			if(data.type=="required") {
				if(document.getElementById("phoneVerifyDialog"))
				    document.getElementById("phoneVerifyDialog").showModal();
			}
		},
		error: function( jqXhr, textStatus, errorThrown ){
			console.log( errorThrown );
		}
	});
}
function sendOTP() {
	$(".error").html("").hide();
	$(".mobSubmit").hide();
	$(".mobSubmited").show();
	var number = $("#mobile").val();
	if (number.length == 10 && number !== null) {
		jQuery.ajax({
			url: 'http://starknic.com/index.php/checkout/cart/mobileVerify?' + 'mobNo=' + number,
			dataType: 'json',
			cache: false,
			type: "GET",
			contentType: 'application/json',
			processData: true,
			success: function( data, textStatus, jQxhr ){
				if(data.type=="success") {
					document.getElementById("phoneVerifyDialog").close();
					document.getElementById("verifyOTP").showModal();
				} else {
					document.getElementById("phoneVerifyDialog").close();
				}
			},
			error: function( jqXhr, textStatus, errorThrown ){
				console.log( errorThrown );
				document.getElementById("phoneVerifyDialog").close();
			}
		});
	}  else {
		$(".error").html('Please enter a valid number!')
		$(".error").show();
	}
}
function verifyOTP() {
	$(".error").html("").hide();
	$(".success").html("").hide();
	$(".otpVerify").hide();
	$(".otpVerifying").show();
	var otp = $("#mobileOtp").val();
	if (otp.length == 6 && otp !== null) {
		jQuery.ajax({
			url: 'http://starknic.com/index.php/checkout/cart/verifyOTP?' + 'otp=' + otp,
			dataType: 'json',
			cache: false,
			type: "GET",
			contentType: 'application/json',
			processData: true,
			success: function( response, textStatus, jQxhr ){
				$("." + response.type).html(response.message)
			    $("." + response.type).show();
			    setTimeout(function(){ 
                    document.getElementById("verifyOTP").close();
                }, 1000); 
			},
			error: function( jqXhr, textStatus, errorThrown ){
				document.getElementById("verifyOTP").close();
				console.log( errorThrown );
			}
		});
	}  else {
		$(".error").html('Please enter a valid number!')
		$(".error").show();
	}
}
/*function verifyOTP() {
	$(".error").html("").hide();
	$(".success").html("").hide();
	var otp = $("#mobileOtp").val();
	var input = {
		"otp" : otp,
		"action" : "verify_otp"
	};
	if (otp.length == 6 && otp != null) {
		$.ajax({
			url : 'controller.php',
			type : 'POST',
			dataType : "json",
			data : input,
			success : function(response) {
				$("." + response.type).html(response.message)
				$("." + response.type).show();
			},
			error : function() {
				alert("ss");
			}
		});
	} else {
		$(".error").html('You have entered wrong OTP.')
		$(".error").show();
	}
}*/