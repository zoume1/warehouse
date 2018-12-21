
	function onblurr(inputId,spanId,zhengZe,msg){
	 inputId.blur(function(){
		var val=inputId.val().search(zhengZe);
		if (inputId.val()!=""){
		if (val!= -1){
			spanId.html("通过").removeClass().addClass("ok");
		}else{
			spanId.html("格式错误")
				.removeClass().addClass("err");
		  }
		}else{
		spanId.html(msg).removeClass().addClass("err");}
	 });
	 inputId.focus(function(){
		spanId.removeClass("err").html(" ");
	 })
	};
	var $uname=$("#uname"),$unameMsg=$("#unameMsg"),
		$upwd=$("#upwd"),$upwdMsg=$("#upwdMsg");
	 onblurr($uname,$unameMsg,/^[\u4e00-\u9fa5\w]{2,16}$/,"请输入您的登录账户");
	 onblurr($upwd,$upwdMsg,/^[a-zA-Z0-9_-]{4,16}$/,"请输入您的登录密码");