// 隐藏左右侧边栏
$(function(){
	$('.kzlst').click();
	$(".kzlst2").click();
	window.onbeforeunload = function(e){
		//退出页面时判断是否有修改过，如有就弹框提示保存，未修改过就不弹  CSP 2018/5/29
		var newAllData = DuibiAllData;
		app.saveData("duibi");
		if(JSON.stringify(newAllData) === JSON.stringify(DuibiAllData) ? false : true){
			return "确定离开当前页面吗？？";
		}
	};

});

// 请求链接
var requestUrl = "?_easy=sp.api.get_xcxpage";
// 定义保存/读取方法
function GetQueryString(name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
	var r = window.location.search.substr(1).match(reg);
	if(r != null) return decodeURI(r[2]);
	return null;
}
// if(typeof pageId == 'undefined')

var pageId = GetQueryString("uid");
var copyId = GetQueryString("copyUid");

// if (copyId) pageId = copyId;

// console.log("page id >>>", pageId);
// console.log("copyId id >>>", copyId);

var nodeStorage = {
	fetch: function() {
		var requestId = pageId || copyId;
		console.log("pageId >>>", pageId);
		$.post(requestUrl, {
			uid: requestId
		}, function(ret) {
			ret = $.parseJSON(ret);
			if(ret.data) {
				var storageData = JSON.parse(ret.data.content);
				console.log("get storageData >>>>>>", storageData);

				if (storageData.nodes) app.nodes = storageData.nodes;
				if (storageData.basicInfo) {
					app.basicInfo = storageData.basicInfo;

					if (storageData.basicInfo.contactInfo) {
						app.contactInfo = storageData.basicInfo.contactInfo;
					}
					console.log("app.xcxtab >>>>>", app.xcxtab);
				}
			} else {
				console.log("no save nodes, build a new one");
			}
		});

		// 获取tabbar保存数据
		$.post("?_u=xiaochengxu.get_ext_json", {
			uid: requestId
		}, function(ret) {

			ret = $.parseJSON(ret);
			console.log("get tabData ret >>>>>>", ret);
			if(ret.data && ret.data.tabBar) {
				var tabData = ret.data.tabBar;
				console.log("get tabData >>>>>>", tabData);
				if (tabData) {
					app.xcxtab = tabData;
    			}
			} else {
				console.log("no save tabdata, build a new tabbar");
			}
            app.saveData("duibi");
		});
	},
	save: function(type, allData) {

		var postUrl = "?_easy=sp.api.add_xcxpage";
		var xcxtitle = allData.basicInfo.xcxname ||  allData.basicInfo.pageTitle;

		if (type === "online") {
			postUrl = "?_easy=sp.api.add_xcxpage&sort=999999";
		}

		console.log("save allData >>>>>>", allData);
		var dataString = JSON.stringify(allData);
		// console.log("save dataString 8888>>>>>>", dataString);
		$.post(postUrl, {
			content: dataString,
			title: xcxtitle,
			public_uid: g_public_uid,
			uid: pageId
		}, function(ret) {
			ret = $.parseJSON(ret);
			if(ret.data && ret.data != 0) {
				if(ret.data != pageId) {
					pageId = ret.data;
				}
				alert("保存成功！");
			}
		});

		var tabData = {"tabBar":allData.basicInfo.tabData};
		var tabDataString = JSON.stringify(tabData);
		console.log("save tabData >>>>>>", tabData);
		// console.log("save tabDataString >>>>>>", tabDataString);

		// 保存tabbar数据
		$.post("?_u=xiaochengxu.set_ext_json", {
			json: tabDataString,
			// uid: pageId
		}, function(ret) {
			if (!ret) return;
			ret = JSON.stringify(ret);
			console.log("save tabDatas ret >>>>>>", ret);
			if(ret.data && ret.data != 0) {
				// if(ret.data != pageId) {
				// 	pageId = ret.data;
				// }
				// window.alert("保存成功！");
			}
		});
	}
};


// var prefixUrl = "https://weixin.uctphp.com/?_uct_token=00759ef40eee1d3f971ffabcf901e1df&";

// 获取所有图片函数
var allImg = [];
function getAllImage() {
	var requestImgUrl = "/?_a=upload&_u=index.sp_img_list";
	$.post(requestImgUrl, {
		limit: -1,
		// file_group: ""
	}, function(ret) {
		ret = $.parseJSON(ret);
		// console.log("get all img ret >>>", ret);
		if(ret.data) {
			var allImgList = ret.data.list;
			allImgList.forEach(function(ele) {
				ele.isImgselect = false;
			});

			allImg = ret.data.list;
			app.modalimg.imglist = allImg;
		} else {
			allImg.imglist = [];
			// app.modalimg.imglist = [];
		}
	});
}
getAllImage();

// 获取tabbar图片
var allTabImg = [{
	url: 'page/resources/pic/tab/new/home.jpg',
	isImgselect: false
},{
	url: 'page/resources/pic/tab/new/goods.jpg',
	isImgselect: false
},{
	url: 'page/resources/pic/tab/new/my.jpg',
	isImgselect: false
},{
	url: 'page/resources/pic/tab/new/qrcode.jpg',
	isImgselect: false
}];
(function() {
	colorList.forEach(function(ele) {
		for (var i = 1; i < 20; i++) {
			var color = ele.color.replace("#", "");
			var imgUrl = "page/resources/pic/tab/" + color + "/default/" + color + " (" + i + ").png";
			var selectedImgUrl = "page/resources/pic/tab/" + color + "/fill/" + color + "FILL (" + i + ").png";

			allTabImg.push({url: imgUrl, isImgselect: false});
			allTabImg.push({url: selectedImgUrl, isImgselect: false});
		}
	});
	// console.log("all tab img >>>", allTabImg);
	// allTabImg.concat();
})();

// http://127.0.0.1:88../static/admin/visualview/images/tabbar/#0099CC/default/#0099CC (9).png
// http://127.0.0.1:88../static/admin/visualview/images/tabbar/FF6666/default/FF6666%20(14).png

// 获取所有商品函数
function getAllGoods() {
	var requestImgUrl = "/?_a=shop&_u=api.products";
	$.post(requestImgUrl, {
		limit: -1,
	}, function(ret) {
		ret = $.parseJSON(ret);
		console.log("get all goods ret >>>", ret);
		if (ret.data) {
			var list = ret.data.list;

			list.forEach(function(ele) {
				ele.price = parseFloat(ele.price/100).toFixed(2);
				ele.isProductselect = false;
			});

			app.modalproduct.productlist = list;
            app.markSelected();
        } else {
        	app.modalproduct.productlist = [];
		}
	});
}


// 获取所有砍价商品函数
var allGetBargain = [];
function getAllBargainGoods() {
	var requestImgUrl = "/?_a=bargain&_u=api.get_bargains";
	$.post(requestImgUrl, {
		limit: -1,
	}, function(ret) {
		ret = $.parseJSON(ret);
		// console.log("get all bargain ret >>>", ret);
		if(ret.data) {
			var list = ret.data.list;

			list.forEach(function(ele) {
				ele.main_img = ele.product_info.img;
				ele.price = parseFloat(ele.lowest_price/100).toFixed(2);
				ele.ori_price = parseFloat(ele.ori_price/100).toFixed(2);
				ele.isProductselect = false;
			});

			app.modalproduct.productlist = list;
            app.markSelected();
		} else {
			app.modalproduct.productlist = [];
		}
	});
}


// 获取所有拼团商品函数
// var allGetGoods = [];
function getAllGroupGoods() {
	var requestImgUrl = "/?_a=shop&_u=api.products";
	$.post(requestImgUrl, {
		limit: -1,
		is_group: 1
	}, function(ret) {
		ret = $.parseJSON(ret);
		// console.log("get all cluster ret >>>", ret);
		if(ret.data) {
			var list = ret.data.list;

			list.forEach(function(ele) {
				ele.price = parseFloat(ele.price/100).toFixed(2);
				ele.ori_price = parseFloat(ele.ori_price/100).toFixed(2);
				ele.isProductselect = false;
			});

			app.modalproduct.productlist = list;

			app.markSelected();
		} else {
			app.modalproduct.productlist = [];
		}
	});
}
// 获取所有秒杀商品函数
// var allGetGoods = [];
function getAllKillGoods() {
	var requestImgUrl = "/?_a=shop&_u=ajax.products&info=32";
	$.post(requestImgUrl, {
		limit: -1
	}, function(ret) {
		ret = $.parseJSON(ret);
		// console.log("get all kill ret >>>", ret);
		if(ret.data) {
			var list = ret.data.list;

			list.forEach(function(ele) {
				ele.price = parseFloat(ele.price/100).toFixed(2);
				ele.ori_price = parseFloat(ele.ori_price/100).toFixed(2);
				ele.isProductselect = false;
			});

			app.modalproduct.productlist = list;

			app.markSelected();
		} else {
			app.modalproduct.productlist = [];
		}
	});
}

// 获取所有公告函数
// var allGetGoods = [];
function getAllNotice() {
	var requestImgUrl = "/?_a=shop&_u=ajax.radio_list";
	$.post(requestImgUrl, {
		limit: -1
	}, function(ret) {
		ret = $.parseJSON(ret);
		// console.log("get all radio_list ret >>>", ret);
		if(ret.data) {
			var list = ret.data.list;

			list.forEach(function(ele) {
				ele.txt = ele.title;
				ele.isProductselect = false;
			});

			app.modalproduct.productlist = list;

			app.markSelected();
		} else {
			app.modalproduct.productlist = [];
		}
	});
}


// 获取所有活动函数
var allGetExers = [];
function getAllExers() {
	var requestImgUrl = "/?_a=form&_u=api.formlist";
	$.post(requestImgUrl, {
		type: "activity",
		limit: -1,
		no_brief: true
	}, function(ret) {
		ret = $.parseJSON(ret);
		// console.log("get all exercise ret >>>", ret);
		if(ret.data) {
			var exerList = ret.data.list;

            exerList.forEach(function(ele) {
            	ele.isProductselect = false;
                ele.main_img = ele.img;
                ele.price = parseFloat(ele.access_rule.order.price/100).toFixed(2);
            });

            app.modalproduct.productlist = exerList;

            app.markSelected();
		} else {
        	app.modalproduct.productlist = [];
        }
	});
}


// 获取所有新闻函数
var allGetNews = [];
function getAllNews() {

	var requestImgUrl = "/?_easy=site.api.article_list";
	$.post(requestImgUrl, {
		type: "activity",
		limit: -1,
		no_brief: true
	}, function(ret) {
		ret = $.parseJSON(ret);
		 console.log("get all news ret >>>", ret);
		if(ret.data) {
			var newsList = ret.data.list;
            newsList.forEach(function(ele) {
            	ele.isProductselect = false;
                ele.main_img = ele.image;
                if (!ele.digest) ele.digest = "（无）";
				console.log(ele);
                if (!ele.main_img) ele.main_img = "../static/admin/visualview/images/noImage.png";
            });

            app.modalproduct.productlist = newsList;

            app.markSelected();
		}  else {
			app.modalproduct.productlist = [];
		}
	});
}


// 获取所有预约函数
var allBookList = [];
function getAllBook() {
	var requestImgUrl = "/?_a=book&_u=api.book_item_list";
	$.post(requestImgUrl, {
		limit: -1,
	}, function(ret) {
		ret = $.parseJSON(ret);
		// console.log("get all book list ret >>>", ret);
		if(ret.data) {
			var bookList = ret.data.list;
            bookList.forEach(function(ele) {
            	ele.isProductselect = false;
            	ele.price = parseFloat(ele.price/100).toFixed(2);
                if (!ele.main_img) ele.main_img = "../static/admin/visualview/images/noImage.png";
            });

            app.modalproduct.productlist = bookList;

            app.markSelected();
		} else {
			app.modalproduct.productlist = [];
		}
	});
}


//Vue开始
// 注册功能组件
for (var i = 0; i < componentPlugin.length; i++) {
	var component = componentPlugin[i].component,
		template = "#" + component;
	Vue.component(component, {
		props: ['nodedata'],
		template: template
	});
}

// 注册营销组件
for (var i = 0; i < marketingPlugin.length; i++) {
	var component = marketingPlugin[i].component,
		template = "#" + component;
	Vue.component(component, {
		props: ['nodedata'],
		template: template
	});
}


Vue.use(VueDragging);
Vue.use(VueAwesomeSwiper);
var app = new Vue({
	el: '#app',
	data: {

		leftnav: [{
			name: '后台管理',
			url: '',
			icon: " glyphicon-cog"
		}],
		rightnav: [{
				name: '页面管理',
				url: '?_a=sp&_u=index.xcxpagelist',
				icon: " ",
			}
		],
		//动画效果
		transitionname: "slide-fade",
		modaltransition: "fade",
		/*模态框数据*/
		small: false,
		large: false,
		full: false,
		// 为true时无法通过点击遮罩层关闭modal
		// 自定义组件transition
		transition: 'modal',
		// 确认按钮text
		okText: '提交更改',
		// 取消按钮text
		cancelText: '取消',
		// 确认按钮className
		okClass: 'btn btn-success',
		// 取消按钮className
		cancelClass: 'btn btn-default',
		/*选择图片数据*/
		imgselect: 'imgselect glyphicon glyphicon-ok',

		modalimg: {
			selectDataIdx: -1,
			selectImgUrl: "",
			show: false,
			title: '选择图片',
			// 为true时无法通过点击遮罩层关闭modal
			force: false,
			// 点击确定时关闭Modal
			// 默认为false，由父组件控制prop.show来关闭
			closeWhenOK: {
				default: false
			},
			imglist: []
		},
		/*选择网络图片数据*/
		modalimgweb: {
			imgUrl: "",
			title: '填写网络图片地址',
			show: false,
			force: false,
			//返回按钮className
			backClass: 'btn btn-info ',
			// 点击确定时关闭Modal
			// 默认为false，由父组件控制prop.show来关闭
			closeWhenOK: {
				default: false
			}
		},
		/*选择链接数据*/
		modallink: {
			title: '选择链接地址',
			linklist: linkList,
			show: false,
			force: false,
			// 点击确定时关闭Modal
			// 默认为false，由父组件控制prop.show来关闭
			closeWhenOK: {
				default: false
			}
		},
		/*选择产品数据*/
		productselect: 'productselect glyphicon glyphicon-ok',
		modalproduct: {
			title: '选择产品',
			selectedGoodsArr: [],
			show: false,
			force: false,
			// 点击确定时关闭Modal
			// 默认为false，由父组件控制prop.show来关闭
			closeWhenOK: {
				default: false
			},
			productlist: []
		},
		colorlist: colorList,
		componentPlugin: componentPlugin,
		marketingPlugin: marketingPlugin,

		nodes: [],
		basicInfo: {
			xcxname: "",
			pageTitle: "微信小程序",
			// xcxcolor: "#0066CC"
			isxcxtab: false,
		},

		contactInfo: {
			show: false,
			imgUrl: "",
			defaultImgUrl: "../static/admin/visualview/images/contact.jpg",
			link: ""
		},
		editedNode: null,
		/*		底部数据new aad*/
        xcxtab: xcxTab
	},
	computed: {
		modalClass: function() {
			return {
				'modal-lg': this.large,
				'modal-sm': this.small,
				'modal-full': this.full
			};
		},
		// fontNumPx:function(){
		// 	return
		// }
	},
	methods: {
		// 添加组件
		add: function(component) {
			var node = {
				'component': component,
			};

			node.nodedata = initNodeData(component);
			this.nodes.unshift(node);
			this.editedNode = node;
			// console.log(this.nodes);
			this.$options.methods.tabselect();
		},

		// 编辑组件
		editNode: function(index) {
			// console.log("tap index >>>", index);
			this.editedNode = this.nodes[index];
			this.$options.methods.tabselect();
		},

		// 删除组件
		deleteNode: function(index) {
			// console.log("delete index >>>", index);
			var idx = parseInt(index);

			if(this.nodes[index] == this.editedNode) {
				this.editedNode = null;
			}
			if (confirm("是否确认删除组件！")){
				this.nodes.splice(idx, 1);
			}
		},

		addItem: function() {
			var item = {};
			var obj = this.editedNode.nodedata.item;
            for (var key in obj) {
                item[key] = obj[key];
            }
            // console.log("add item data >>>", item);
            this.editedNode.nodedata.list.push(item);
		},

		deleteItem: function(index) {
			var idx = parseInt(index);

			this.editedNode.nodedata.list.splice(idx, 1);
		},

		// 上传图片
		uploadImg: function(e) {
			// console.log("upload e >>>", e);
			var imgFile = e.target.files;
			// var imgFile = e.target.files[0]; //获取要上传的文件

			// console.log("upload num >>>", imgFile.length);

			var uploadImgNum = imgFile.length;
			var count = 0;

			for (var i = 0; i < uploadImgNum; i++) {
				var formData = new FormData();
				formData.append("file", imgFile[i]);

				$.ajax({
	                url: '/?_a=upload&_u=index.upload',
	                type: 'POST',
	                dataType: 'json',
	                cache: false,
	                data: formData,
	                processData: false,
	                contentType: false,
	                success: function(res) {
						// console.log("upload res >>>>>>>", res);
	                    if (res.data && res.data.url) {
	 						// console.log("upload success");
	 						count++;
	 						if (count >= uploadImgNum) {
	 							getAllImage();
				                alert("上传成功");
	 						}
	                    } else {
	                    	var content = "";
		                	if (count === 0) {
		                		content = "上传失败，请重新上传";
		                	} else if (count > 0) {
		                		content = "部分图片上传失败，请重新上传";
		                		getAllImage();
		                	}
			                alert(content);
	                    }
	                },
	                error: function(err) {
		                alert("上传错误，请检查您的网络");
	                }
	            });
			}

			// console.log("formData >>>", formData);


		},

		// 点击选择图片按钮
		selectImg: function(idx, type) {
			console.log("selectImg idx >>", idx);
            console.log("selectImg type >>", type);
			// 清空之前选择的图片数据
			this.modalimg.selectImgUrl = "";

			var originImg = "images/demoimg";

			if (type) {
				// 存储修改数据目标对象的下标
				app.modalimg.imglist = allTabImg;
				this.modalimg.selectType = type;
				this.modalimg.selectDataIdx = idx;
				if (type === "tabDefault") originImg = this.xcxtab.list[idx].iconPath;
				if (type === "tabSelected") originImg = this.xcxtab.list[idx].selectedIconPath;
			} else {
				this.modalimg.selectType = "node";
				app.modalimg.imglist = allImg;
				if (idx === "contactImg") {
					this.modalimg.selectDataIdx = idx;
					originImg = this.contactInfo.imgUrl;
				} else if(this.editedNode.nodedata.list) {
					if(idx != -1) {
						// 存储修改数据目标对象的下标
						this.modalimg.selectDataIdx = idx;
						originImg = this.editedNode.nodedata.list[idx].imgUrl;
					}
				} else {
					this.modalimg.selectDataIdx = -1;
					originImg = this.editedNode.nodedata.imgUrl;
				}
			}


			// 判断之前是否已经选了图片
			if (!originImg.startsWith("images/demoimg")) {
				this.modalimg.imglist.forEach(function(ele) {
					if (ele.url === originImg) {
						ele.isImgselect = true;
					} else {
						ele.isImgselect = false;
					}
				});
			} else {
				// 之前未选择过图片
				this.modalimg.imglist.forEach(function(ele) {
					ele.isImgselect = false;
				});
			}

			// 打开选择弹窗
			this.showmodal('modalimg');
		},

		// 选择图片弹窗中的图片
		selectpic: function(index) {
			// console.log("selectImg index >>>", index);
			var idx = index,
				imgUrl = this.modalimg.imglist[idx].url;

			for(var i = 0; i < this.modalimg.imglist.length; i++) {
				this.modalimg.imglist[i].isImgselect = false;
			}
			this.modalimg.imglist[idx].isImgselect = true;
			this.modalimg.selectImgUrl = imgUrl;
			// console.log("modalimg imglist >>>", this.modalimg.imglist);
		},

		/*选择链接*/
		selectPath: function(idx, type) {
			// console.log("selectPath idx >>", idx);
			// 清空之前选择的数据
			this.modallink.selectedLink = "";

			var originLink = "";

			if (type === "tab") {
        // 去掉底部菜单的链接开头 “/”  CSP   2018/5/27
        this.modallink.linklist.forEach(function (liItme){
            if (liItme.link.substr(0,1) == '/')
                liItme.link = liItme.link.substr(1);
        });

				// 存储修改数据目标对象的下标
				this.modallink.selectType = "tab";
				this.modallink.selectDataIdx = idx;
				originLink = this.xcxtab.list[idx].pagePath;

			} else {
        // 去掉底部菜单的链接开头 2 添加其他菜单 “/”  CSP 2018/5/27
        for(var i=0;i<this.modallink.linklist.length;i++){
            if(typeof(this.modallink.linklist[i].link) != "undefined"){
                if (this.modallink.linklist[i].link.substr(0,1) == '/'){  break; }
                this.modallink.linklist[i].link = "/" + this.modallink.linklist[i].link;
            }
        }

				this.modallink.selectType = "node";

				if (idx === "contactLink") {
					// 存储修改数据目标对象的下标
					this.modallink.selectDataIdx = idx;
					originLink = this.contactInfo.link;
				} else if(this.editedNode.nodedata.list) {
					if(idx != -1) {
						// 存储修改数据目标对象的下标
						this.modallink.selectDataIdx = idx;
						originLink = this.editedNode.nodedata.list[idx].link;
					}
				} else {
					this.modallink.selectDataIdx = -1;
					originLink = this.editedNode.nodedata.link;
				}
			}


			// 判断之前是否已经选了链接
			if (originLink) {

                console.log(originLink);
				this.modallink.linklist.forEach(function(ele) {
					if (ele.link === originLink) {
						ele.isActive = true;
					} else {
						ele.isActive = false;
					}
				});
			} else {
				// 之前未选择过链接
				this.modallink.linklist.forEach(function(ele) {
					ele.isActive = false;
				});
			}

			// 打开选择弹窗
			this.showmodal('modallink');
		},

		selectlink: function(index) {
			var idx = index,
				link = this.modallink.linklist[idx].link;
				console.log(this.modallink);
			for(var i = 0; i < this.modallink.linklist.length; i++) {
				this.modallink.linklist[i].isActive = false;
			}
			this.modallink.linklist[idx].isActive = true;

			this.modallink.selectedLink = link;
			// console.log("modallink linklist >>>", this.modallink.linklist);
		},


		/*产品选择*/
		// 点击添加产品按钮
		selectGood: function(type) {
			// var allGoods = [];
			if (type === "good") {
				getAllGoods();
				this.modalproduct.title = "选择产品(可多选)";
			} else if (type === "group") {
				getAllGroupGoods();
				this.modalproduct.title = "选择团购产品";
			} else if (type === "bargain") {
				getAllBargainGoods();
				this.modalproduct.title = "选择砍价产品";
			} else if (type === "exercise") {
				getAllExers();
				this.modalproduct.title = "选择活动";
			} else if (type === "news") {
				getAllNews();
				this.modalproduct.title = "选择新闻";
			} else if (type === "book") {
				getAllBook();
				this.modalproduct.title = "选择预约项目";
			} else if (type === "kill") {
				getAllKillGoods();
				this.modalproduct.title = "选择秒杀产品";
			}else if (type === "notice") {
				getAllNotice();
				this.modalproduct.title = "选择公告信息";
			}

			// allGoods = this.modalproduct.productlist;

			this.showmodal('modalproduct');
		},

		// 标记已被选的选项
		markSelected: function() {
			var originGoods = this.editedNode.nodedata.list,
				allGoods = this.modalproduct.productlist;

			// 选出已选择的产品
			for (var i = 0; i < originGoods.length; i++) {
				for (var j = 0; j < allGoods.length; j++) {
					if (originGoods[i].uid == allGoods[j].uid) {
						allGoods[j].isProductselect = true;
						this.modalproduct.selectedGoodsArr.push(j);
					}
				}
			}
		},

		// 点击选择产品
		selectproduct: function(index) {
			var selectedGood = this.modalproduct.productlist[index],
				selectedGoodsArr = this.modalproduct.selectedGoodsArr;

			selectedGood.isProductselect = !selectedGood.isProductselect;

			if (selectedGood.isProductselect) {
				// 选中产品
				selectedGoodsArr.push(index);
			} else {
				// 删除产品
				for(var i=0; i<selectedGoodsArr.length; i++) {
				    if(selectedGoodsArr[i] == index) {
				      selectedGoodsArr.splice(i, 1);
				      break;
				    }
				}
			}

			// console.log("selectedGoodsArr >>>", selectedGoodsArr);
			// console.log("this.modalproduct.productlist >>>", this.modalproduct.productlist);
		},
		ok: function(e) {
			switch(e) {
				case "modalproduct":
				    var selectedGoodsArr = this.modalproduct.selectedGoodsArr;
					if (selectedGoodsArr.length > 0) {
						this.$emit('ok');
						if(this.modalproduct.closeWhenOK) {
							this.modalproduct.show = false;
						}

						var allGoods = this.modalproduct.productlist;
						this.editedNode.nodedata.list = [];

						if (!this.editedNode.nodedata.uidsArr) {
							this.editedNode.nodedata.uidsArr = [];
						}

						for (var i = 0; i < selectedGoodsArr.length; i++) {
							this.editedNode.nodedata.list.push(allGoods[selectedGoodsArr[i]]);
							this.editedNode.nodedata.uidsArr.push(allGoods[selectedGoodsArr[i]].uid);
						}

					} else {
						alert("请先选择再提交");
					}

					this.modalproduct.selectedGoodsArr = [];

					break;
				case "modallink":
					if (this.modallink.selectedLink) {
						this.$emit('ok');
						if(this.modallink.closeWhenOK) {
							this.modallink.show = false;
						}

						var link = this.modallink.selectedLink;
						var selectDataIdx = this.modallink.selectDataIdx;

						if (this.modallink.selectType === "node") {
							if (selectDataIdx === "contactLink") {
								this.contactInfo.link = link;
							} else if (selectDataIdx == -1) {
								this.editedNode.nodedata.link = link;
							} else {
								this.editedNode.nodedata.list[selectDataIdx].link = link;
							}
						} else if (this.modallink.selectType === "tab") {
							this.xcxtab.list[selectDataIdx].pagePath = link;
						}

					} else {
						alert("请先选择链接再提交");
					}

					break;
				case "modalimgweb":
					this.$emit('ok');
					if(this.modalimgweb.closeWhenOK) {
						this.modalimgweb.show = false;
					}
					if(this.modalimg.closeWhenOK) {
						this.modalimg.show = false;
					}

					var imgUrl = this.modalimgweb.imgUrl;
					// 将选中的图片赋值给组件数据
					if (this.modalimg.selectDataIdx === "contactImg") {
						this.contactInfo.imgUrl = imgUrl;
					} else if(this.modalimg.selectDataIdx == -1) {
						this.editedNode.nodedata.imgUrl = imgUrl;
					} else {
						var selectDataIdx = this.modalimg.selectDataIdx;
						this.editedNode.nodedata.list[selectDataIdx].imgUrl = imgUrl;
					}
					this.modalimgweb.imgUrl = "";
					break;
				// 确认选择图片
				case "modalimg":
					if (this.modalimg.selectImgUrl) {
						this.$emit('ok');
						if(this.modalimg.closeWhenOK) {
							this.modalimg.show = false;
						}

						var imgUrl = this.modalimg.selectImgUrl,
							selectDataIdx = this.modalimg.selectDataIdx,
							type = this.modalimg.selectType;

						// 将选中的图片赋值给组件数据
						// console.log("this.modalimg.selectDataIdx >>>", selectDataIdx);
						if (type === "node") {
							if (this.modalimg.selectDataIdx === "contactImg") {
								this.contactInfo.imgUrl = imgUrl;
							} else if(selectDataIdx == -1) {
								this.editedNode.nodedata.imgUrl = imgUrl;
							} else {
								this.editedNode.nodedata.list[selectDataIdx].imgUrl = imgUrl;
							}
						} else if (type === "tabDefault") {
							this.xcxtab.list[selectDataIdx].iconPath = imgUrl;
						} else if (type === "tabSelected") {
							this.xcxtab.list[selectDataIdx].selectedIconPath = imgUrl;
						}

					} else {
						alert("请先选择图片再提交");
					}

					break;
			}
			// console.log("editedNode data >>>>", this.editedNode);
			document.body.className = document.body.className.replace(/\s?modal-open/, '');
		},
		cancel: function(e) {
			switch(e) {
				case "modalproduct":
					this.$emit('cancel');
					this.modalproduct.show = false;
					break;
				case "modallink":

					this.$emit('cancel');
					this.modallink.show = false;
					break;
				case "modalimgweb":

					this.$emit('cancel');
					this.modalimgweb.show = false;
					break;
				case "modalimg":

					this.$emit('cancel');
					this.modalimg.show = false;

					break;
			}
			document.body.className = document.body.className.replace(/\s?modal-open/, '');
		},
		goback: function() {
			this.$emit('cancel');
			this.modalimgweb.show = false;
			document.body.className = document.body.className.replace(/\s?modal-open/, '');
		},
		// 点击遮罩层
		clickMask: function(e) {
			switch(e) {
				case "modalproduct":

					if(!this.modalproduct.force) {
						this.cancel();
					}
					break;
				case "modallink":

					if(!this.modallink.force) {
						this.cancel();
					}
					break;
				case "modalimgweb":

					if(!this.modalimgweb.force) {
						this.cancel();
					}
					break;
				case "modalimg":

					if(!this.modalimg.force) {
						this.cancel();
					}
					break;
			}
			document.body.className = document.body.className.replace(/\s?modal-open/, '');
		},
		// 右侧导航跳转
		tabselect: function() {
			var a = document.getElementById("tabs-211");
			var b = a.getElementsByTagName("li")[1];
			var c = b.getElementsByTagName("a")[0];
			c.click();
		},
		// 改变颜色
		changecolor: function(index) {
			this.xcxtab.backgroundColor = this.colorlist[index].color;
		},
		//使用h5自带拾色器
		inputcolor: function(e) {
			var a = e.target.name;
			switch(a) {
				case "xcxcolor":
					this.xcxtab.backgroundColor = e.target.value;
					break;
				case "textcolor":
					this.xcxtab.color = e.target.value;
					break;
				case "selectedcolor":
					this.xcxtab.selectedColor = e.target.value;
					break;
			}
		},
		showmodal: function(e) {
			switch(e) {
				case "modalproduct":
					this.$emit('cancel');
					this.modalproduct.show = true;
					break;
				case "modallink":
					this.$emit('cancel');
					this.modallink.show = true;
					break;
				case "modalimgweb":

					this.$emit('cancel');
					this.modalimgweb.show = true;
					break;
				case "modalimg":

					this.$emit('cancel');
					this.modalimg.show = true;
					break;
			}
			document.body.className += ' modal-open';
		},

		// 保存数据
		saveData: function(type) {
			// console.log("saveData type >>>", type);
			var allData = {};

			var basicInfo = this.basicInfo;
			basicInfo.tabData = this.xcxtab;
			basicInfo.contactInfo = this.contactInfo;

			allData.nodes = this.nodes;
			allData.basicInfo = basicInfo;
            console.dir(allData);

			if (type === "online") {
				nodeStorage.save(type, allData);
			} else if(type === "server") {
				nodeStorage.save("server", allData);
			}else {
				//增加保存页面内容，推出页面是用来对比  CSP2018/5/29
                DuibiAllData = deepCopy(allData);
			}
		},
		/*		底部方法new aad*/
		// 设置被激活按钮
		selecticon: function(index) {
			for(var x in this.xcxtab.list) {
				this.xcxtab.list[x].isActive = false;
			}
			this.xcxtab.list[index].isActive = true;
		},
		//tabbar使用默认设置按钮     CSP 2018/5/25
        addMoRen: function(){
			app.xcxtab = deepCopy(xcxTab);
		},
		// 添加tabbar选项提示
		addset: function() {
			var obj = this.xcxtab.item;
			var list = this.xcxtab.list;
			// console.log(obj)
			if(list.length >= 5) {
				alert("注意啦！注意啦！小程序底部菜单选项不能大于5个噢！！！");
			} else {
				// console.log("add item data >>>", obj);
				this.xcxtab.list.push(obj);
			}
		},
		// 删除tabbar选项提示
		deleteset: function(index) {
			var idx = parseInt(index);
			var list = this.xcxtab.list;
			if(list.length <= 2) {
				alert("注意啦！注意啦！小程序底部菜单选项不能少于2个噢！！！");
			} else {
				// console.log("del item data >>>", index);
				this.xcxtab.list.splice(idx, 1);
			}
		}
	},
	// 拖拽方法
	mounted: function() {
        var that = this;
        this.$dragging.$on('dragend', function(data) {
            // console.log('dragend nodes >>', that.nodes);
        });
    },
});
//vue结束

//拷贝复制对象(递归)  CSP 2018/5/29
function deepCopy(obj){
    if(typeof obj != 'object'){
        return obj;
    }
    var newobj = {};
    for ( var attr in obj) {
        newobj[attr] = deepCopy(obj[attr]);
    }
    return newobj;
}


// 获取保存的页面
nodeStorage.fetch();
