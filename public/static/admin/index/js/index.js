
$(function(){

height=$(window).height()-64;
//console.log(height);
$(".recontent").height(height);//直接设置元素的高
//	抽屉效果;
$(".UCleft-fixed").children("dl").on("click","dt",function(){
//	alert(111);
	if($(this).parent("dl").hasClass("curr")){
		$(this).parent("dl").removeClass("curr");
	}
	else{
		$(this).parent("dl").addClass("curr");
    	$(this).parent("dl").siblings().removeClass("curr");
	}

  });

  $(".UCleft-fixed").children("dl").children("dd").on("click","p",function(){
  	
  $(this).css('font-weight','bold');
  $(this).siblings().css('font-weight','500');
  $(this).parent("dd").parent("dl").siblings().children("dd").children("p").css('font-weight','500');
   var url=$(this).data("url");

   var dk = $(window.parent.document).find("#add").attr("src"); 
			$.ajax({
				type : "get",
				url: url,
				cache: true,
				success: function(html){
					$('#add').attr('src',url);
				}

				
			});
  });

    
    
	

});