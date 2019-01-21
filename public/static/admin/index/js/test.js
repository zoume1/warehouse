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
$(".edit_content").on("click",".delect",function(){
     $(this).parent().parent().remove();
});
