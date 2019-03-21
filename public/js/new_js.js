$(document).ready(function(){
	$('#mhl').click(function(){
		$('.choose_style_nav .choose_style_nav_gou1').hide();
		$('#mhl .choose_style_nav_gou1').show();
		$('.choose_bgcolor').css('background-color','#4491F1');
		$('.choose_bgcolor2').css('background-color','#60A1F2');
		$('.choose_color').css('color','#FFFFFF');
		$('.choose_color2').css('color','#FFFFFF');
		$('.choose_color3').css('color','#4491F1');
		$('.zdydivbox').hide();
	})
	$('#jdh').click(function(){
		$('.choose_style_nav .choose_style_nav_gou1').hide();
		$('#jdh .choose_style_nav_gou1').show();
		$('.choose_bgcolor').css('background-color','#FF4444');
		$('.choose_bgcolor2').css('background-color','#FF6F6F');
		$('.choose_color').css('color','#FFFFFF');
		$('.choose_color2').css('color','#FFFFFF');
		$('.choose_color3').css('color','#FF4444');
		$('.zdydivbox').hide();
	})
	$('#qzj').click(function(){
		$('.choose_style_nav .choose_style_nav_gou1').hide();
		$('#qzj .choose_style_nav_gou1').show();
		$('.choose_bgcolor').css('background-color','#C3A769');
		$('.choose_bgcolor2').css('background-color','#F3EEE1');
		$('.choose_color').css('color','#FFFFFF');
		$('.choose_color2').css('color','#C3A769');
		$('.choose_color3').css('color','#C3A769');
		$('.zdydivbox').hide();
	})
	$('#qxl').click(function(){
		$('.choose_style_nav .choose_style_nav_gou1').hide();
		$('#qxl .choose_style_nav_gou1').show();
		$('.choose_bgcolor').css('background-color','#63BE72');
		$('.choose_bgcolor2').css('background-color','#E1F4E3');
		$('.choose_color').css('color','#FFFFFF');
		$('.choose_color2').css('color','#63BE72');
		$('.choose_color3').css('color','#63BE72');
		$('.zdydivbox').hide();
	})
	$('#hlh').click(function(){
		$('.choose_style_nav .choose_style_nav_gou1').hide();
		$('#hlh .choose_style_nav_gou1').show();
		$('.choose_bgcolor').css('background-color','#FCC600');
		$('.choose_bgcolor2').css('background-color','#1D262E');
		$('.choose_color').css('color','#FFFFFF');
		$('.choose_color2').css('color','#FFFFFF');
		$('.choose_color3').css('color','#FCC600');
		$('.zdydivbox').hide();
	})
	$('#gqh').click(function(){
		$('.choose_style_nav .choose_style_nav_gou1').hide();
		$('#gqh .choose_style_nav_gou1').show();
		$('.choose_bgcolor').css('background-color','#333333');
		$('.choose_bgcolor2').css('background-color','#FFFFFF');
		$('.choose_color').css('color','#FFFFFF');
		$('.choose_color2').css('color','#333333');
		$('.choose_color3').css('color','#333333');
		$('.zdydivbox').hide();
	})
	$('#qlf').click(function(){
		$('.choose_style_nav .choose_style_nav_gou1').hide();
		$('#qlf .choose_style_nav_gou1').show();
		$('.choose_bgcolor').css('background-color','#FF547B');
		$('.choose_bgcolor2').css('background-color','#FFE6E8');
		$('.choose_color').css('color','#FFFFFF');
		$('.choose_color2').css('color','#FF547B');
		$('.choose_color3').css('color','#FF547B');
		$('.zdydivbox').hide();
	})
	$('#yyz').click(function(){
		$('.choose_style_nav .choose_style_nav_gou1').hide();
		$('#yyz .choose_style_nav_gou1').show();
		$('.choose_bgcolor').css('background-color','#7783EA');
		$('.choose_bgcolor2').css('background-color','#E9EBFF');
		$('.choose_color').css('color','#FFFFFF');
		$('.choose_color2').css('color','#7783EA');
		$('.choose_color3').css('color','#7783EA');
		$('.zdydivbox').hide();
	})
	$('#zdy').click(function(){
		$('.choose_style_nav .choose_style_nav_gou1').hide();
		$('#zdy .choose_style_nav_gou1').show();
		$('.zdydivbox').show();
	})
	$('#colorSelector5').ColorPicker({
    color: '#ffffff',
    onShow: function (colpkr) {
        $(colpkr).fadeIn(500);
        return false;
    },
    onHide: function (colpkr) {
        $(colpkr).fadeOut(500);
        return false;
    },
    onChange: function (hsb, hex, rgb) {
        $('#colorSelector5 div').css('backgroundColor', '#' + hex);
        $('#tabbar_bg5').val("#"+hex);
        $('.choose_headbg').css('backgroundColor', '#' + hex)
    }
  });
  $('#colorSelector4').ColorPicker({
    color: '#ffffff',
    onShow: function (colpkr) {
        $(colpkr).fadeIn(500);
        return false;
    },
    onHide: function (colpkr) {
        $(colpkr).fadeOut(500);
        return false;
    },
    onChange: function (hsb, hex, rgb) {
        $('#colorSelector4 div').css('backgroundColor', '#' + hex);
        $('#tabbar_bg4').val("#"+hex);
        $('.choose_bgcolor').css('backgroundColor', '#' + hex);
		$('.choose_color3').css('color','#' + hex);
    }
  });
  $('#colorSelector3').ColorPicker({
    color: '#ffffff',
    onShow: function (colpkr) {
        $(colpkr).fadeIn(500);
        return false;
    },
    onHide: function (colpkr) {
        $(colpkr).fadeOut(500);
        return false;
    },
    onChange: function (hsb, hex, rgb) {
        $('#colorSelector3 div').css('backgroundColor', '#' + hex);
        $('#tabbar_bg3').val("#"+hex);
        $('.choose_bgcolor2').css('backgroundColor', '#' + hex);
    }
  });
  $('#colorSelector2').ColorPicker({
    color: '#ffffff',
    onShow: function (colpkr) {
        $(colpkr).fadeIn(500);
        return false;
    },
    onHide: function (colpkr) {
        $(colpkr).fadeOut(500);
        return false;
    },
    onChange: function (hsb, hex, rgb) {
        $('#colorSelector2 div').css('backgroundColor', '#' + hex);
        $('#tabbar_bg2').val("#"+hex);
        $('.choose_color').css('color', '#' + hex);
		
    }
  });
  $('#colorSelector1').ColorPicker({
    color: '#ffffff',
    onShow: function (colpkr) {
        $(colpkr).fadeIn(500);
        return false;
    },
    onHide: function (colpkr) {
        $(colpkr).fadeOut(500);
        return false;
    },
    onChange: function (hsb, hex, rgb) {
        $('#colorSelector1 div').css('backgroundColor', '#' + hex);
        $('#tabbar_bg1').val("#"+hex);
        $('.choose_color2').css('color','#' + hex);
    }
  });
  $('#choose_black').click(function(){
  	$('.choose_style_head_img1').show();
  	$('.choose_style_head_img2').hide();
  	$('.choose_style_head').css('color','#000000')
  })
  $('#choose_white').click(function(){
  	$('.choose_style_head_img2').show();
  	$('.choose_style_head_img1').hide();
  	$('.choose_style_head').css('color','#FFFFFF')
  })
})
