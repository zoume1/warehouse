var dhtml='';
dhtml+='<div class="list">';
dhtml+='<div class="list_title"> <input type="text" value="" placeholder="最多输入5个字"></div>';
dhtml+='<div class="liat_oprtion">';
dhtml+='<div class="delect">删除</div>';
dhtml+='<div >复制</div>';
dhtml+=' <div  class="">编辑</div>';
dhtml+='</div>';
dhtml+='</div>';
// 点击编辑页面的操作
$(".add_buton").click(function(){
    $(".edit_content").append(dhtml);
});
$(".delect_ico").click(function(){
	$(this).parent().remove();
});

$(".edit_content").on("click",".delect",function(){
     $(this).parent().parent().remove();
});
$(".files").change(function(){
	var id=$(this).attr("id");
	var src = getObjectURL(this.files[0]) ; 
    $(this).parent().siblings().children("img").attr("src", src);
});
//建立一個可存取到该file的url  
function getObjectURL(file) {  
    var url = null ;   
    if (window.createObjectURL!=undefined) { // basic  
        url = window.createObjectURL(file) ;  
    } else if (window.URL!=undefined) { // mozilla(firefox)  
        url = window.URL.createObjectURL(file) ;  
    } else if (window.webkitURL!=undefined) { // webkit or chrome  
        url = window.webkitURL.createObjectURL(file) ;  
    }  
    return url ;  
}
//  拖动条

	// $( "#slider" ).slider({
	// 		range: "min",
	// 		value: 10,
	// 		min: 1,
	// 		max: 10,
	// 	slide: function( event, ui ) {
	// 	   $( "#amount" ).html( ui.value/10 );
	// 	   $(this).parent().parent().siblings(".page_seach_edit_contentss").css("opacity",ui.value/10)
    //       }
	// 	});
	   $( "#slider1" ).slider({
			range: "min",
			value: 1,
			min: 1,
			max: 12,
		slide: function( event, ui ) {
           $( "#amount1" ).html( ui.value );
          }
		});
		$( "#slider2" ).slider({
			range: "min",
			value: 1,
			min: 1,
			max: 5,
		slide: function( event, ui ) {
           $( "#amount2" ).html( ui.value );
          }
		});
		$( "#slider3" ).slider({
			range: "min",
			value: 1,
			min: 1,
			max: 5,
		slide: function( event, ui ) {
           $( "#amount3" ).html( ui.value );
          }
		});
		$( "#slider4" ).slider({
			range: "min",
			value: 1,
			min: 1,
			max: 5,
		slide: function( event, ui ) {
           $( "#amount4" ).html( ui.value );
          }
		})
		$( "#slider5" ).slider({
			range: "min",
			value: 1,
			min: 1,
			max: 5,
		slide: function( event, ui ) {
           $( "#amount5" ).html( ui.value );
          }
		});
	
		$( "#slider6" ).slider({
			range: "min",
			value: 1,
			min: 1,
			max: 5,
		slide: function( event, ui ) {
           $( "#amount6" ).html( ui.value );
          }
		});
	
		$( "#slider7" ).slider({
			range: "min",
			value: 1,
			min: 1,
			max: 5,
		slide: function( event, ui ) {
           $( "#amount7" ).html( ui.value );
          }
		});
		$( "#slider8" ).slider({
			range: "min",
			value: 1,
			min: 1,
			max: 5,
		slide: function( event, ui ) {
           $( "#amount8" ).html( ui.value );
          }
		});
			
		$( ".style_boder").sortable();
		$( ".style_boder").disableSelection();
		var dhtml1="";
		dhtml1+='<div class="slideshow_box_list">';
		dhtml1+='<div class="slideshow_box_list_img"> <img src="http://teahouse.siring.com.cn/upload/20181128/31d02b855e58a946d5c8dddbb278376b.jpg" alt="" /></div>';
		dhtml1+='<div class="slideshow_box_input">';
		dhtml1+='<div class="slideshow_select_show">';
		dhtml1+='<input type="text" name="" id="" value="" />';
		dhtml1+='<span>选择图片</span>';
		dhtml1+='</div>';
		dhtml1+='<div class="link">';
	    dhtml1+='<select name="" >';
		dhtml1+='<option value="0">首页</option>';
		dhtml1+='</select>';
		dhtml1+='</div>';
		dhtml1+='</div>';
	    dhtml1+='</div>';
		$(".add_slideshow").click(function(){
	  	  $(".slideshow_box").append(dhtml1);
	  	   
	      });
		$(".recolor").click(function(){
			var id=$(this).siblings(".page_input_color").data("id");
			$(this).siblings(".page_input_color").val(id);
		});
		var dhtml2="";
		dhtml2+='<div class="navigation_box_list">';
		dhtml2+='<div class="navigation_box_list_img"> <img src="http://teahouse.siring.com.cn/upload/20181128/31d02b855e58a946d5c8dddbb278376b.jpg" alt="" /></div>';
		dhtml2+='<div class="navigation_box_input">';
		dhtml2+='<div class="wenzi">';
		dhtml2+='<span>文字</span>';
		dhtml2+='<input type="text" placeholder="最多5个字"/>';
		dhtml2+='<input type="color" placeholder="" class="page_input_color" value="#821006" data-id = "#821006"/>';
		dhtml2+='<span style="color: #30B6F8; cursor: pointer;" class="recolor">字体颜色重置</span>';
		dhtml2+='</div>';
		dhtml2+='<div class="link">';
		dhtml2+='<select name="" >';
		dhtml2+='<option value="0">首页</option>';
		dhtml2+='</select>';
		dhtml2+='</div> ';
		dhtml2+='</div>';
		dhtml2+='</div>';
		$(".add_navigationshow").click(function(){
	  	  $(".navigation_box").append(dhtml2);
	  	   
		  });

		  var seach_goods=[];
		  $("#seach_button").click(function() {
			  var seach_val=$("#seach_val").val();
			  $.ajax({
				  type: "POST",
				  url: "{:url('admin/Bonus/coupon_search')}",
				  data: {
					  "goods_number": seach_val,
				  },
				  success: function(data) {
					  var data=eval("("+data+")").data;
					  var num=0;
					  
					  var dhtml='';
					  for(var i=0;i<data.length;i++){
						  console.log(seach_goods.length);
						  for(var j=0;j<seach_goods.length;j++){
							  if(data[i].goods_number==seach_goods[j].goods_number){
								  num++;
							  }
						  }
						  if(num==0){
							  seach_goods.push(data[i]);
							  dhtml+='<tr>';
							  dhtml+='<td><input type="checkbox" sname="" lay-skin="primary" lay-filter="choose" data-id="" value="'+data[i].id+'" name="goods_id[]"></td>';
							  dhtml+='<td>'+data[i].goods_number+'</td>';
							  dhtml+='<td class="">';
							  dhtml+='<div class="tdimg"><img src="__UPLOADS__/'+data[i].goods_show_images+'" /></div>';
							  dhtml+='</td>';
				  
							  dhtml+='<td>'+data[i].goods_name+'</td>';
							  dhtml+='<td>'+data[i].goods_repertory+'</td>';
							  dhtml+='<td>';
							  dhtml+='<div class="layui-btn layui-btn-mini edit"><i class="iconfont icon-edit"></i> 参加活动</div>';
							  dhtml+='</td>';
							  dhtml+='</tr>';
							  $('.news_content').append(dhtml);
							  form.render('checkbox');
						  }
						  else{
							  layer.msg('列表中已有此商品，请勿重复添加');
						  }
					  
					  }
				  },
				  error: function(data) {
					  console.log("错误")
				  }
			  });
		  });
		  $(".popup_x").click(function(){
			  $(".popup_one").addClass("noshow")
		  })
		  	// 页面标题
		$(".style_boder").on("click",".title_tag",function(){
			$(this).children(".page_edit").removeClass("noshow");
		 });
		 $(".page_input_input").keyup(function(){
			 console.log();
			 var vals=$(this).data("value");
			 var id=$(this).data("id");
			 var val=$(this).val();
			 if(val==''){
				 $(this).parent().parent().siblings(".page_title").html(vals);
			 }
			 else{
				 $(this).parent().parent().siblings(".page_title").html(val);
			 }
		 })
		 $(".page_input_input").blur(function(){
			 var vals=$(this).data("value");
			 var id=$(this).data("id");
			 var val=$(this).val();
			 if(val==''){
				 $(this).parent().parent().siblings(".page_title").html(vals);
			 }
			 else{
				 $(this).parent().parent().siblings(".page_title").html(val);
			 }
			 
		 })
		 $(".lm_bg").change(function(){
			 var id=$(this).data("id");
			 var val=$(this).val();
			  $(this).parent().parent().parent().parent(".title_tag").css({'background':val});
		 })
		 $(".pg_bg").change(function(){
			 var id=$(this).data("id");
			 var val=$(this).val();
			 $(".style_boder").css({'background':val});
		 })
		 $(".wz_bg").change(function(){
			 var id=$(this).data("id");
			 var val=$(this).val();
			  $(this).parent().parent().parent().siblings(".page_title").css({'color':val});
		 })
		 // 搜索框
	    $(".seach_bg_color").change(function(){
			var val=$(this).val();
			$(this).parent().parent().parent().css("background-color",val);
		})
		$('input[type=radio][name=seach_right_button]').change(function() {
			if($(this).val()==0){
			   $(".seach_box_left").css("display","none"); 
			   console.log();
			   $(".seach_box_center").width($(".seach_box_center").outerWidth()+50);
			   $(".page_seach_edit_contentss").css("justify-content","center"); 
		   }
		   else{
			   $(".seach_box_left").css("display","block"); 
			   $(".seach_box_center").width($(".seach_box_center").outerWidth()-50);
			   $(".page_seach_edit_contentss").css("justify-content","space-between"); 
		   }
		});
		
		$('input[type=radio][name=right]').change(function() {
			if($(this).val()==0){
			   $(".seach_box_right").css("display","none"); 
			   console.log();
			   $(".seach_box_center").width($(".seach_box_center").outerWidth()+50);
			   $(".page_seach_edit_contentss").css("justify-content","center"); 
		   }
		   else{
			   $(".seach_box_right").css("display","block"); 
			   $(".seach_box_center").width($(".seach_box_center").outerWidth()-50);
			   $(".page_seach_edit_contentss").css("justify-content","space-between"); 
		   }
		});
		$(".slider_erae").click(function(){
			var slider=$(this).parent().children(".click_content").find(".slider");
			var amount=$(this).parent().children(".click_content").find(".amount");
			for(var i=0;i<slider.length;i++){
				var id=slider[i].id;
				var id1=amount[i].id
				var min=parseInt(slider[i].dataset.min);
				var max=parseInt(slider[i].dataset.max);
				var value=parseInt(slider[i].dataset.value);
				$( "#"+id ).slider({
							range: "min",
							value: value,
							min: min,
							max: max,
						slide: function( event, ui ) {
							$( "#"+id1 ).html( ui.value/10 );
						   $(this).parent().parent().siblings(".page_seach_edit_contentss").css("opacity",ui.value/10)
						  }
				});
			}
		})
		$(".click").click(function(){
			
			
			$(".click").css({'border':'0'}); 
			$(".delect_ico").addClass("noshow")
			$(".click_content").addClass("noshow")
		   $(this).children(".click_content").removeClass("noshow");
		   $(this).css({'border':'dashed 1px #00A0E9'}); 
		   $(this).children(".delect_ico").removeClass("noshow");
		})
		// 轮播图
		$('input[type=radio][name=weight]').change(function() {
		   
			   $(".swiper-container").height($(this).val());
		});
			// 图文导航
			$(".page_navigation_bg").change(function(){
				var val=$(this).val();
				$(this).parent().parent().siblings("ul").css("background-color",val)
				})
				$(".page_navigation_bg").change(function(){
				var val=$(this).val();
				$(this).parent().parent().siblings("ul").css("background-color",val)
				})
				$('input[type=radio][name=shape]').change(function() {
					if($(this).val()=="正方形"){
					  $(".classification_ico").css("border-radius","0")
				   }
				   else if($(this).val()=="圆角"){
					$(".classification_ico").css("border-radius","20%")
				   }
				   else{
					$(".classification_ico").css("border-radius","50%")
				   }
				});
				$('input[type=radio][name=num]').change(function() {
					if($(this).val()=="3个"){
				
					  $(".classification ul li").css("flex-basis","33.33%")
				   }
				   else if($(this).val()=="4个"){
					$(".classification ul li").css("flex-basis","25%")
				   }
				   else{
					
					$(".classification ul li").css("flex-basis","20%")
				   }
				});
				// 通知
		$(".announcement_bg").change(function(){
			var val=$(this).val();	
			$(this).parent().parent().parent().siblings().css("background-color",val);
		})
		$(".announcement_text_color").change(function(){
			var val=$(this).val();	
			$(this).parent().parent().parent().siblings().children(".new_text").css("color",val);
		})
		$(".announcement_text_content").blur(function(){
			var val=$(this).val();
			$(this).parent().parent().siblings().children(".new_text").html(val);
		})
	
	//  商品
	$('input[type=radio][name=list_style]').change(function() {
			if($(this).val()=="单列显示"){
				$(this).parent().parent().parent().parent().siblings(".good_content").children(".good_list").css({'flex-basis':'100%'});
			 }
		   else if($(this).val()=="双列显示"){
			$(this).parent().parent().parent().parent().siblings(".good_content").children(".good_list").css({'flex-basis':'48%'});
	
		   }
		   else{
			$(this).parent().parent().parent().parent().siblings(".good_content").children(".good_list").css({'flex-basis':'33%'});
	
		   }
		});
		$('input[type=checkbox][name=display_content]').change(function() {
			if($(this)[0].checked){
				if($(this).val()==0){
					$(this).parent().parent().parent().parent().siblings(".good_content").children(".good_list").children(".listnames").children(".good_name").css('display','block');;	
				}
				else if($(this).val()==1){
					$(this).parent().parent().parent().parent().siblings(".good_content").children(".good_list").children(".listnames").children(".good_inventory").css('display','block');;
				}
				else if($(this).val()==2){
					$(this).parent().parent().parent().parent().siblings(".good_content").children(".good_list").children(".sellpoint").css('display','block');;
				}
				else if($(this).val()==3){
					$(this).parent().parent().parent().parent().siblings(".good_content").children(".good_list").children(".price_box").children(".level_ico").css('display','block');;
				}
				else if($(this).val()==4){
					$(this).parent().parent().parent().parent().siblings(".good_content").children(".good_list").children(".price_box").children(".price").css('display','block');;
				}
			
				
			}
			else{
				if($(this).val()==0){
					$(this).parent().parent().parent().parent().siblings(".good_content").children(".good_list").children(".listnames").children(".good_name").css('display','none');	
				}
				else if($(this).val()==1){
					$(this).parent().parent().parent().parent().siblings(".good_content").children(".good_list").children(".listnames").children(".good_inventory").css('display','none');
				}
				else if($(this).val()==2){
					$(this).parent().parent().parent().parent().siblings(".good_content").children(".good_list").children(".sellpoint").css('display','none');
				}
				else if($(this).val()==3){
					$(this).parent().parent().parent().parent().siblings(".good_content").children(".good_list").children(".price_box").children(".level_ico").css('display','none');
				}
				else if($(this).val()==4){
					$(this).parent().parent().parent().parent().siblings(".good_content").children(".good_list").children(".price_box").children(".price").css('display','none');
				}
			}
		});
	// 组件添加
	// 添加顶部固定搜索框
	var dhtm3="";
	dhtm3+='<div class="seach_box click">';
	dhtm3+='<i class="delect_ico noshow"><img src="__STATIC__/admin/index/img/close1.png" alt=""></i>';
	dhtm3+='<div  class="page_seach_edit_contentss">';
	dhtm3+='<div class="seach_box_left">';
	dhtm3+='<div class="nfc">';
	dhtm3+='<img src="__STATIC__/admin/index/img/u428.png" alt="">';
	dhtm3+='</div>';
	dhtm3+='<div class="seach_box_text">防伪溯源</div>';
	dhtm3+='</div>';
	dhtm3+='<div class="seach_box_center">';
	dhtm3+='<input type="text">';
	dhtm3+='</div>';
	dhtm3+='<div class="seach_box_right">';
	dhtm3+='<div class="cold"><img src="__STATIC__/admin/index/img/u426.png" alt=""></div>';
	dhtm3+='<div class="page_input">';
	dhtm3+='</div>';
	dhtm3+='</div>';
	dhtm3+='<div class="page_seach_edit noshow click_content">';
	dhtm3+='<div class="page_input">';
	dhtm3+='<span  class="input_laber">背景颜色：</span>';
	dhtm3+='<input type="color" placeholder="" class="page_input_color seach_bg_color" value="#821006" data-id = "#821006"/>';
	dhtm3+='<span style="color: #30B6F8; cursor: pointer;" class="recolor">重置</span>';
	dhtm3+='</div>';
	dhtm3+='<div class="page_input" style="display: flex;">';
	dhtm3+='<span  class="input_laber">透明度：</span>';
	dhtm3+='<div id="slider"></div>';
	dhtm3+='<div id="amount">1</div>';
	dhtm3+='<div style="font-size:12px;">（最大是1）</div>';
	dhtm3+='</div>';			
	dhtm3+='<div class="page_input">';
	dhtm3+='<span  class="input_laber">左侧按钮：</span>';
	dhtm3+='<input type="radio" name="seach_right_button" value="0" title="不显示" checked="">不显示';
	dhtm3+='<input type="radio" name="seach_right_button" value="1" title="图标" checked="">图标';
	dhtm3+='</div>';
	dhtm3+='<div class="page_input" style="display:flex; align-items: center;">';
	dhtm3+='<span  class="input_laber">左侧按钮：</span>';
	dhtm3+='<div class="border">';
	dhtm3+='<div class="ico_show">';
	dhtm3+='<img src="__STATIC__/admin/index/img/u428.png" alt="">';
	dhtm3+='</div>';
	dhtm3+='<div class="input_file" style="position: relative; display: flex; align-items: center;justify-content: center;">';
	dhtm3+='<div class="file_sty">选择图标</div>';
	dhtm3+='<input type="file" class="files" id="file_0">';
	dhtm3+='</div>';
	dhtm3+='<div class="input_file_color">';
	dhtm3+='<span>图标颜色</span>';
	dhtm3+='<input type="color">';
	dhtm3+='</div>';
	dhtm3+='<div class="input_file_text"  style="box-sizing: border-box;">';
	dhtm3+='<input type="text" placeholder="最多4个字" style="border: 0;padding-left: 4px;box-sizing: border-box;">';
	dhtm3+='</div>';
	dhtm3+='</div>';
	dhtm3+='</div>';
	dhtm3+='<div class="page_input" style="display: flex;align-items: center;">';
	dhtm3+='<span  class="input_laber">链接：</span>';
	dhtm3+='<div class="link">';
	dhtm3+='<select name="" >';
	dhtm3+='<option value="0">首页</option>';
	dhtm3+='</select>';
	dhtm3+='</div>  ';
	dhtm3+='</div>';
	dhtm3+='<div class="page_input">';
	dhtm3+='<span  class="input_laber">右侧按钮：</span>';
	dhtm3+='<input type="radio" name="right" value="0" title="不显示" >不显示';
	dhtm3+='<input type="radio" name="right" value="1" title="图标" checked="">图标';
	dhtm3+='</div>';
	dhtm3+='<div class="page_input" style="display:flex; align-items: center;">';
	dhtm3+='<span  class="input_laber">右侧按钮：</span>';
	dhtm3+='<div class="border">';
	dhtm3+='<div class="ico_show">';
	dhtm3+='<img src="__STATIC__/admin/index/img/u428.png" alt="">';
	dhtm3+='</div>';
	dhtm3+='<div class="input_file" style="position: relative; display: flex; align-items: center;justify-content: center;">';
	dhtm3+='<div class="file_sty">选择图标</div>';
	dhtm3+='<input type="file" class="files" id="file_0">';
	dhtm3+='</div>';
	dhtm3+='<div class="input_file_color">';
	dhtm3+='<span>图标颜色</span>';
	dhtm3+='<input type="color">';
	dhtm3+='</div>';
	dhtm3+='<div class="input_file_text"  style="box-sizing: border-box;">';
	dhtm3+='<input type="text" placeholder="最多4个字" style="border: 0;padding-left: 4px;box-sizing: border-box;">';
	dhtm3+='</div>';
	dhtm3+='</div>';
	dhtm3+='</div>';
	dhtm3+='<div class="page_input" style="display: flex;align-items: center;">';
	dhtm3+='<span  class="input_laber">链接：</span>';
	dhtm3+='<div class="link">';
	dhtm3+='<select name="" >';
	dhtm3+='<option value="0">首页</option>';
	dhtm3+='</select>';
	dhtm3+='</div>  ';
	dhtm3+='</div>';			
	dhtm3+='</div>';
	dhtm3+='</div>';  