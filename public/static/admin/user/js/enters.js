var	$uname=$("#uname"),
	$unameMsg=$("#unameMsg"),
	$upwds=$("#upwds"),
	$upwdsMsg=$("#upwdsMsg"),
	$upwd=$("#upwd"),
	$upwdMsg=$("#upwdMsg"),
	$email=$("#email"),
	$emailMsg=$("#emailMsg"),
	$phone=$("#phone"),
	$phoneMsg=$("#phoneMsg"),
	$userName=$("#userName"),
	$userNameMsg=$("#userNameMsg");
/*获取焦点事件和按住事件*/
	function onfocus(inputId,divId,divMsg){
		inputId.focus(function(){
         divId.html(divMsg).removeClass().addClass("asd").css("color","red");
      });
		inputId.keyup(function(){
        divId.html(divMsg);
      });
    }
/*鼠标失去焦点验证格式*/
	function onblurr(inputId,spanId,zhengZe){
	 inputId.blur(function(){
		var val=inputId.val().search(zhengZe);
		if (inputId.val()!=""){
			if (val!= -1){
				spanId.html("").removeClass().addClass("ok").css("color","red");
			}else{
				spanId.html("格式错误")
					.removeClass().addClass("err").css("color","red");
				$(".refer").attr("type","button");

			}
		}else{
		spanId.html("必填").removeClass().addClass("err").css("color","red");
		$(".refer").attr("type","button");
		}
	 })
	}
/**/
 onblurr($uname,$unameMsg,/^[\u4e00-\u9fa5\w]{2,16}$/);
 onfocus($uname,$unameMsg,"请输入用户名");
 onblurr($upwd,$upwdMsg,/^[a-zA-Z0-9_-]{4,16}$/);
 onfocus($upwd,$upwdMsg,"请输入密码");
 onfocus($upwds,$upwdsMsg);
 onblurr($email,$emailMsg,
 /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/);
 onfocus($email,$emailMsg,"请输入正确格式的邮箱");
 onblurr($phone,$phoneMsg,
 /^((13[0-9])|(14[5|7])|(15([0-3]|[5-9]))|(18[0,5-9]))\d{8}$/);
 onfocus($phone,$phoneMsg,"请输入手机号码");
 onblurr($userName,$userNameMsg,/^[\u4e00-\u9fa5]{1,16}$/);
 onfocus($userName,$userNameMsg,"请输入真实姓名",
 /^[\u4e00-\u9fa5]{1,16}$/);
/*验证两次密码是否一致*/
	$upwds.blur(function(){
		if ($upwds.val()==""){
			$upwdsMsg.html("密码不能为空")
			.removeClass().addClass("err").css("color","red");
		}else if ($upwds.val()==$("#upwd").val()){
			$upwdsMsg.html("通过")
			.removeClass().addClass("ok").css("color","red");
		}else{
			$upwdsMsg.html("两次密码输入不一致")
			.removeClass().addClass("err");
			$(".refer").attr("type","button");
		}
	});


