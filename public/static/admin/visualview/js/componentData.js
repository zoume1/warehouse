// 组件数据
var componentPlugin = [
	{
		component: 'lunBo',
		name: '轮播图',
		src: '__STATIC__/admin/visualview/images/lefticon/_0021_swiper.png'
	},
	{
		component: 'tuBiao',
		name: '图标分类',
		src: '__STATIC__/admin/visualview/images/lefticon/_0020_icon_class.png'
	},
	{
		component: 'duoTu',
		name: '多图片广告',
		src: '__STATIC__/admin/visualview/images/lefticon/_0018_multi_img_ad.png'
	},
	{
		component: 'siTu',
		name: '4幅图广告',
		src: '__STATIC__/admin/visualview/images/lefticon/_0017_4img.png'
	},
	{
		component: 'jiuTu',
		name: '9幅图广告',
		src: '__STATIC__/admin/visualview/images/lefticon/_0016_9img.png'
	},
	/*{
		component: 'huaDong',
		name: '相册滑动',
		src: '__STATIC__/admin/visualview/images/lefticon/_0015_photo_slider.png'
	},*/
	{
		component: 'biaoTi',
		name: '模块标题',
		src: '__STATIC__/admin/visualview/images/lefticon/_0014_title.png'
	},
    {
        component: 'gongGao',
        name: '公告',
        src: '__STATIC__/admin/visualview/images/lefticon/_0012_goods.png'
    },
    {
        component: 'chanPin',
        name: '产品',
        src: '__STATIC__/admin/visualview/images/lefticon/_0012_goods.png'
    },
	{
		component: 'souSuo',
		name: '搜索',
		src: '__STATIC__/admin/visualview/images/lefticon/_0011_search.png'
	},
	{
		component: 'xinWen',
		name: '新闻',
		src: '__STATIC__/admin/visualview/images/lefticon/_0010_news.png'
	},
	{
		component: 'danHang',
		name: '单行图文',
		src: '__STATIC__/admin/visualview/images/lefticon/_0001_one_line_text.png'
	},
	{
		component: 'duoHang',
		name: '多图文',
		src: '__STATIC__/admin/visualview/images/lefticon/_0013_multi_text_img.png'
	},
	{
		component: 'fuZa',
		name: '复杂图文单行',
		src: '__STATIC__/admin/visualview/images/lefticon/_0000_multi_line_text.png'
	},
	{
		component: 'shiPin',
		name: '视频',
		src: '__STATIC__/admin/visualview/images/lefticon/_0005_vedio.png'
	},
    {
        component: 'anNiu',
        name: '按钮组合',
        src: '__STATIC__/admin/visualview/images/lefticon/_0008_btns.png'
    },
    {
        component: 'yiTuPreview',
        name: '预览图片',
        src: '__STATIC__/admin/visualview/images/lefticon/_0019_big_img.png'
    },
    {
        component: 'yiTu',
        name: '自由图一',
        src: '__STATIC__/admin/visualview/images/lefticon/_0019_big_img.png'
    },
    {
        component: 'erTu',
        name: '自由图二',
        src: '__STATIC__/admin/visualview/images/lefticon/_0019_big_img.png'
    },
    {
        component: 'sanTu',
        name: '自由图三',
        src: '__STATIC__/admin/visualview/images/lefticon/_0019_big_img.png'
    },
    // {
    //     component: 'contact',
    //     name: '客服按钮',
    //     src: '__STATIC__/admin/visualview/images/lefticon/contact.png'
    // }
];

var marketingPlugin = [
	{
		component: 'huoDong',
		name: '活动',
		src: '__STATIC__/admin/visualview/images/lefticon/_0009_exercise.png'
	},
	{
		component: 'menDian',
		name: '门店地址',
		src: '__STATIC__/admin/visualview/images/lefticon/_0007_address.png'
	},
	{
		component: 'kanJia',
		name: '砍价',
		src: '__STATIC__/admin/visualview/images/lefticon/_0003_bargain.png'
	},
	{
		component: 'pinTuan',
		name: '拼团',
		src: '__STATIC__/admin/visualview/images/lefticon/_0002_group.png'
	},
	{
		component: 'tuanDui',
		name: '团队',
		src: '__STATIC__/admin/visualview/images/lefticon/_0006_team.png'
	},
	{
		component: 'yuYue',
		name: '预约',
		src: '__STATIC__/admin/visualview/images/lefticon/_0004_book.png'
	},
    {
        component: 'miaoSha',
        name: '秒杀',
        src: '__STATIC__/admin/visualview/images/lefticon/imgmiaosha.png'
    },
];

var colorList = [
    {
        color: "#000000"
    },
    {
        color: "#0099CC"
    },
    {
        color: "#FF6666"
    },
    {
        color: "#0066CC"
    },
    {
        color: "#CC0033"
    },
    {
        color: "#009966"
    },
    {
        color: "#006633"
    },
    {
        color: "#9933CC"
    },
    {
        color: "#66CCCC"
    },
    {
        color: "#339999"
    },
    {
        color: "#6666CC"
    },
    {
        color: "#66CC99"
    },
    {
        color: "#66CCFF"
    },
    {
        color: "#663399"
    },
    {
        color: "#CC3399"
    },
    {
        color: "#FF3399"
    }
];

var linkList = [
    {
        name:"首页",
        link:"/page/index/index",
        isActive:false,
        tips:""
    },
    {
        name:"分类",
        link:"/page/class/class",
        isActive:false,
        tips:""
    },
    {
        name:"分类（二级分类）",
        link:"/page/class/pages/newClass/newClass",
        isActive:false,
        tips:""
    },
    {
        name:"购物车",
        link:"/page/cart/cart",
        isActive:false,
        tips:""
    },
    {
        name:"个人中心",
        link:"/page/my/pages/my_v1/my_v1",
        isActive:false,
        tips:""
    },
    {
        name:"全部商品",
        link:"/page/class/pages/classGoods/classGoods",
        isActive:false,
        tips:""
    },
    {
        name:"精选商户",
        link:"/page/index/pages/business/business",
        isActive:false,
        tips:""
    },
    {
        name:"商户详情",
        tips: "请在链接末尾填写对应的商户编号（该链接不适用于底部菜单栏）",
        link:"/page/cart/pages/businessDetail/businessDetail?uid=",
        isActive:false
    },
    {
        name:"外卖订餐式页面",
        tips: "",
        link:"/page/index/pages/resturant/resturant",
        isActive:false
    },


    {
        name:"公告详情（该链接不适用于底部菜单栏）",
        link:"/page/index/pages/placard/placard?articleId=",
        isActive:false,
        tips:"请在链接末尾填写对应的公告编号"
    },
    {
        name:"文案详情（该链接不适用于底部菜单栏）",
        link:"/page/index/pages/article/article?uid=",
        isActive:false,
        tips:"请在链接末尾填写对应的文案编号"
    },
    {
        name:"搜索页",
        link:"/page/index/pages/search/search",
        isActive:false,
        tips:""
    },
    {
        name:"领券中心",
        link:"/page/index/pages/couponCenter/couponCenter",
        isActive:false
    },
    {
        name:"我的积分",
        link:"/page/my/pages/wallet/wallet",
        isActive:false
    },
    {
        name:"余额充值",
        link:"/page/cart/pages/charge/charge",
        isActive:false
    },
    {
        name:"邀请好友",
        tips:"我的二维码",
        link:"/page/my/pages/qrCode/qrCode",
        isActive:false
    },

    {
        name:"拼团商品列表",
        link:"/page/index/pages/cluster/cluster",
        isActive:false
    },
    {
        name:"我的拼团",
        link:"/page/my/pages/group/group",
        isActive:false
    },
    {
        name:"我的粉丝",
        link:"/page/my/pages/team/team",
        isActive:false
    },
    {
        name:"分销中心",
        link:"/page/cart/pages/distributionCenter/distributionCenter",
        isActive:false
    },
    {
        name:"分销订单列表",
        link:"/page/cart/pages/distributionList/distributionList",
        isActive:false
    },
    {
        name:"物流查询（该链接不适用于底部菜单栏）",
        link:"/page/my/pages/myOrders/myOrders?index=3",
        isActive:false
    },
    {
        name:"我的订单",
        link:"/page/my/pages/myOrders/myOrders",
        isActive:false
    },
    {
        name:"积分商品订单",
        link:"/page/my/pages/pointOrders/pointOrders",
        isActive:false
    },
    {
        name:"我的收藏",
        link:"/page/my/pages/favorite/favorite",
        isActive:false
    },
    {
        name:"我的会员卡",
        link:"/page/my/pages/myvip/myvip",
        isActive:false
    },
    {
        name:"商户申请",
        tips: "商户申请入驻",
        link:"/page/cart/pages/apply/apply",
        isActive:false
    },
    {
        name:"商户中心",
        tips: "商户入驻",
        link:"/page/cart/pages/businessCenter/businessCenter",
        isActive:false
    },
    {
        name:"积分商城",
        link:"/page/class/pages/pointGood/pointGood",
        isActive:false
    },
    {
        name:"积分商品详情（该链接不适用于底部菜单栏）",
        tips: "请在链接末尾填写对应的积分商品编号",
        link:"/page/class/pages/pointGoodDetail/pointGoodDetail?uid=",
        isActive:false
    },
    {
        name:"产品详情（该链接不适用于底部菜单栏）",
        tips: "请在链接末尾填写对应的产品编号",
        link:"/page/class/pages/goodDetail/goodDetail?uid=",
        isActive:false
    },
    {
        name:"产品分类（该链接不适用于底部菜单栏）",
        tips: "请在链接末尾填写对应的分类编号",
        link:"/page/class/pages/classGoods/classGoods?classId=",
        isActive:false
    },
    {
        name:"秒杀活动列表",
        link:"/page/index/pages/seckill/seckill",
        isActive:false
    },
    {
        name:"砍价商品列表",
        link:"/page/index/pages/bargainList/bargainList",
        isActive:false
    },
    {
        name:"砍价商品详情（该链接不适用于底部菜单栏）",
        tips: "请在链接末尾填写对应的砍价商品编号",
        link:"/page/index/pages/bargain/bargain?id=",
        isActive:false
    },
    {
        name:"抽奖（该链接不适用于底部菜单栏）",
        tips: "请在链接末尾填写对应的抽奖活动编号",
        link:"/page/cart/pages/reward/reward?rewardId=",
        isActive:false
    },
    {
        name:"精选活动",
        link:"/page/cart/pages/exercise/exercise",
        isActive:false
    },
    {
        name:"活动详情（该链接不适用于底部菜单栏）",
        tips: "请在链接末尾填写对应的活动编号",
        link:"/page/cart/pages/exerciseDetail/exerciseDetail?f_uid=",
        isActive:false
    },
    {
        name:"自定义页面（该链接不适用于底部菜单栏）",
        link:"/page/class/pages/custom/custom?uid=",
        isActive:false,
        tips:"请在链接末尾填写对应的页面编号"
    },

    {
        name:"底部菜单页一",
        link:"/page/menu/menu1/menu1",
        isActive:false,
        tips:"仅用于底部菜单页"
    },
    {
        name:"底部菜单页二",
        link:"/page/menu/menu2/menu2",
        isActive:false,
        tips:"仅用于底部菜单页"
    },
    {
        name:"底部菜单页三",
        link:"/page/menu/menu3/menu3",
        isActive:false,
        tips:"仅用于底部菜单页"
    },
    {
        name:"底部菜单页四",
        link:"/page/menu/menu4/menu4",
        isActive:false,
        tips:"仅用于底部菜单页"
    },
    {
        name:"底部菜单页五",
        link:"/page/menu/menu5/menu5",
        isActive:false,
        tips:"仅用于底部菜单页"
    },

    {
        name:"打开地图位置",
        link:"map-39.908156-116.397400",
        isActive:false,
        tips:"请将链接中的经纬度替换为目的地的经纬度（该功能不适用于底部菜单栏）"
    },
    {
        name:"拨打电话",
        link:"phone-13312341234",
        isActive:false,
        tips:"请替换链接中的电话号码（该功能不适用于底部菜单栏）"
    },
    {
        name:"跳转到其他小程序",
        link:"app-",
        isActive:false,
        tips:"请在链接末尾填写跳转小程序的appid（该功能不适用于底部菜单栏）"
    },

    {
        name:"显示网页内容",
        link:"web-https://mp.weixin.qq.com/",
        isActive:false,
        tips:"请将链接末尾的网页链接替换为对应的网页链接（该功能不适用于底部菜单栏）"
    },

    {
        name:"播放视频",
        link:"vedio-http://wxsnsdy.tc.qq.com/105/20210/snsdyvideodownload?filekey=30280201010421301f0201690402534804102ca905ce620b1241b726bc41dcff44e00204012882540400",
        isActive:false,
        tips:"请将链接末尾的参考链接替换为对应的视频链接（该功能不适用于底部菜单栏）"
    },
];

var initNodeData = function(type) {
    console.log("Node type >>>", type);
    var result = {};

    switch(type) {
        case "lunBo":
        	case "lunBo":
            result.checkeda=true;
            result.list = [{
                    imgUrl: "__STATIC__/admin/visualview/images/demoimg/750-300.png",
                    link: ""
                },
                {
                    imgUrl: "__STATIC__/admin/visualview/images/demoimg/750-300.png",
                    link: ""
                },
                {
                    imgUrl: "__STATIC__/admin/visualview/images/demoimg/750-300.png",
                    link: ""
                }
            ];
            result.item = {
                imgUrl: "__STATIC__/admin/visualview/images/demoimg/750-300.png",
                link: ""
            };
            result.swiperOptionA= {
                loop: false,
                autoplay: {
                    delay: 2500,
                    disableOnInteraction: false
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true
                },
                simulateTouch : false,
                observer:true,
                observeParents:true,
            };
            break;
        case "tuBiao":
            result.checkeda=true;
        	result.list = [
        		{
        			imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
        			title: "标题1",
        			link: ""
        		},
        		{
        			imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
        			title: "标题2",
        			link: ""
        		},
        		{
        			imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
        			title: "标题3",
        			link: ""
        		},
        		{
        			imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
        			title: "标题4",
        			link: ""
        		}
        	];
        	result.item = {
    			imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
    			title: "标题1",
    			link: ""
    		};
            break;
        case "yiTuPreview":
            result = {
                imgUrl: "__STATIC__/admin/visualview/images/demoimg/750-300.png",
                link: "",
                checkeda:true
            };
            break;
        case "yiTu":
        	result = {
        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/750-300.png",
        		link: "",
                checkeda:true
        	};
            break;
        case "erTu":
            result.checkeda=true;
            result.list = [{
                            imgUrl: "__STATIC__/admin/visualview/images/demoimg/750-300.png",
                            link: ""
                        },
                        {
                            imgUrl: "__STATIC__/admin/visualview/images/demoimg/750-300.png",
                            link: ""
                        }];
            break;
        case "sanTu":
            result.checkeda=true;
            result.list = [{
                            imgUrl: "__STATIC__/admin/visualview/images/demoimg/750-300.png",
                            link: ""
                        },
                        {
                            imgUrl: "__STATIC__/admin/visualview/images/demoimg/750-300.png",
                            link: ""
                        },
                        {
                            imgUrl: "__STATIC__/admin/visualview/images/demoimg/750-300.png",
                            link: ""
                        }];
            break;
        case "duoTu":
            result.checkeda=true;
        	result.list = [
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-65.png",
	        		link: ""
        		},
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-65.png",
	        		link: ""
        		},
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-65.png",
	        		link: ""
        		},
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-65.png",
	        		link: ""
        		},
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-65.png",
	        		link: ""
        		},
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-65.png",
	        		link: ""
        		}
        	];
        	result.item = {
        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-65.png",
        		link: ""
    		};
            break;
        case "siTu":
            result.checkeda=true;
        	result.list = [
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/187-150.png",
	        		link: ""
        		},
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/187-150.png",
	        		link: ""
        		},
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/187-150.png",
	        		link: ""
        		},
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/187-150.png",
	        		link: ""
        		}
        	];
        	result.item = {
        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/187-150.png",
        		link: ""
    		};
            break;
        case "jiuTu":
            result.checkeda=true;
        	result.list = [
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-100.png",
	        		link: ""
        		},
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-100.png",
	        		link: ""
        		},
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-100.png",
	        		link: ""
        		},
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-100.png",
	        		link: ""
        		},
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-100.png",
	        		link: ""
        		},
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-100.png",
	        		link: ""
        		},
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-100.png",
	        		link: ""
        		},
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-100.png",
	        		link: ""
        		},
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-100.png",
	        		link: ""
        		}
        	];
        	result.item = {
        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/125-100.png",
        		link: ""
    		};
            break;
        case "huaDong":
            result.checkeda=true;
        	result.list = [
        		{
        			imgUrl: "__STATIC__/admin/visualview/images/demoimg/180-100.png",
        			link: ""
        		},
        		{
        			imgUrl: "__STATIC__/admin/visualview/images/demoimg/180-100.png",
        			link: ""
        		},
        		{
        			imgUrl: "__STATIC__/admin/visualview/images/demoimg/180-100.png",
        			link: ""
        		}
        	];
        	result.item = {
    			imgUrl: "__STATIC__/admin/visualview/images/demoimg/180-100.png",
    			link: ""
    		};
            result.swiperOptionB= {
                slidesPerView: 3,
                freeMode: true,
                loop: true,
                autoplay: {
                    delay: 1000,
                    disableOnInteraction: false
                },
                simulateTouch : false,
            };
            break;
		case "biaoTi":
        	result = {
        		title: "标题",
        		link: "",
                checkeda:true,
                moduleStyle:{
                    textColor: "rgb(0,0,0)",
                    fontSize: 12,
                    bgColor:"rgb(255,255,255)",
                }
        	};
            break;
        case "souSuo":
        	result = {
        		title: "搜索",
                checkeda:true
        	};
            break;
        case "xinWen":
        result.checkeda=true;
            result.list = [];
        	result.item = {
        		title: "新闻标题",
        		intro: "新闻介绍",
        		catName: "财经",
        		addTime: "2018-01-23",
        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
        		link: ""
        	};
            break;
        case "danHang":
        	result = {
        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
        		title: "单行标题",
        		link: "",
                checkeda:true
        	};
            break;
        case "duoHang":
        result.checkeda=true;
        	result.list = [
        		{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/187-150.png",
	        		title: "标题1",
	        		link: ""
	        	},
	        	{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/187-150.png",
	        		title: "标题2",
	        		link: ""
	        	},
	        	{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/187-150.png",
	        		title: "标题3",
	        		link: ""
	        	},
	        	{
	        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/187-150.png",
	        		title: "标题4",
	        		link: ""
	        	},
        	];
        	result.item = {
        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/187-150.png",
        		title: "标题4",
        		link: ""
        	};
            break;
        case "fuZa":
        result.checkeda=true;
        	result = {
        		imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
        		title1: "主标题",
        		title2: "副标题",
        		keyword1: "内容",
        		button: "按钮文本"
        	};
            break;
        case "shiPin":
        	result = {
                checkeda:true,
        		vedioUrl: "http://wxsnsdy.tc.qq.com/105/20210/snsdyvideodownload?filekey=30280201010421301f0201690402534804102ca905ce620b1241b726bc41dcff44e00204012882540400&bizid=1023&hy=SH&fileparam=302c020101042530230204136ffd93020457e3c4ff02024ef202031e8d7f02030f42400204045a320a0201000400"
        	};
            break;


        // 营销组件
        case "huoDong":
        result.checkeda=true;
            result.list = [];
            result.item = {
                imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
                title: "产品标题",
                price: "0.00",
                sellCnt: "0"
            };
            result.uidsArr = [];
            break;
        case "anNiu":
        result.checkeda=true;
            result.list = [
                {
                    imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
                    title: "按钮标题1",
                    link: ""
                },
                {
                    imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
                    title: "按钮标题2",
                    link: ""
                }
            ];
            result.item = {
                imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
                title: "按钮标题",
                link: ""
            };
            break;
        case "menDian":
            result = {
                imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
                title: "市民广场",
                time: "周一至周日 早5:00-24:00",
                address: "广东省深圳市福田区福中三路",
                phone: "13312341234",
                lng: "22.541660",
                lat: "114.059530",
                checkeda:true
            };
            break;
        case "kanJia":
        result.checkeda=true;
            result.list = [];
            result.item = {
                imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
                title: "产品标题",
                price: "0.00",
                sellCnt: "0"
            };
            break;
        case "pinTuan":
        result.checkeda=true;
            result.list = [];
            result.item = {
                imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
                title: "产品标题",
                price: "0.00",
                sellCnt: "0"
            };
            break;
        case "miaoSha":
        result.checkeda=true;
            result.list = [];
            result.item = {
                imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
                title: "产品标题",
                price: "0.00",
                sellCnt: "0"
            };
            break;

        case "chanPin":
            result.ohidden=false;/*是否显示销量*/
            result.selecteda="xcx_producta";/*判断使用哪种产品样式*/
            result.checkeda=true;/*是否显示模块边距*/
            result.list = [];
            result.uidsArr = [];
            result.item = {
                imgUrl: "__STATIC__/admin/visualview/images/demoimg/750-750.png",
                title: "产品标题",
                price: "0.00",
                sellCnt: "0"
            };
            break;
        case "gongGao":
            result.checkeda=true;
            result.list = [];
            result.item = {
                txt: "公告标题"
            };
            result.swiperOptionC= {
                loop: false,
                autoplay: {
                    delay: 2500,
                },
                simulateTouch : false,
                direction : 'vertical'
            };
            break;
        case "yuYue":
        result.checkeda=true;
            result.list = [];
            result.item = {
                main_img: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
                title: "产品标题"
            };
            break;
        case "tuanDui":
            result.checkeda=true;
            result.list = [];
            result.item = {
                imgUrl: "__STATIC__/admin/visualview/images/demoimg/100-100.png",
                title: "",
                job: "职务",
                tag1: "标签1",
                tag2: "标签2",
                tag3: "标签3",
                price: "0.00",
                praiseNum: "0",
                commentNum: "0",
                bookNum: "0"
            };
            break;

        // case "contact":
        //     result = {
        //         imgUrl: "",
        //         defaultImgUrl: "__STATIC__/admin/visualview/images/lefticon/contact.png",
        //         link: "",
        //         show:true
        //     };
        //     break;
    }

    return result;
};

//默认数据
var xcxTab = {
	color: "#666666",
	selectedColor: "#ff882d",
    backgroundColor: "#0066CC",
	list: [{
			text: "首页",
			iconPath: "page/resources/pic/tab/FF6666/default/FF6666 (14).png",
			selectedIconPath: "page/resources/pic/tab/FF6666/fill/FF6666FILL (14).png",
			pagePath: "page/index/index",
			isActive: false
		},
		{
			text: "分类",
			iconPath: "page/resources/pic/tab/FF6666/default/FF6666 (18).png",
			selectedIconPath: "page/resources/pic/tab/FF6666/fill/FF6666FILL (18).png",
			pagePath: "page/class/class",
			isActive: true
		},
		{
			text: "购物车",
			iconPath: "page/resources/pic/tab/FF6666/default/FF6666 (10).png",
			selectedIconPath: "page/resources/pic/tab/FF6666/fill/FF6666FILL (10).png",
			pagePath: "page/cart/cart",
			isActive: false
		},
		{
			text: "我的",
			iconPath: "page/resources/pic/tab/FF6666/default/FF6666 (8).png",
			selectedIconPath: "page/resources/pic/tab/FF6666/fill/FF6666FILL (8).png",
			pagePath: "page/my/pages/my_v1/my_v1",
			isActive: false
		}
	],
	item : {
		text: "名称",
		iconPath: "page/resources/pic/tab/FF6666/default/FF6666 (5).png",
		selectedIconPath: "page/resources/pic/tab/FF6666/fill/FF6666FILL (5).png",
		pagePath: "pages/shop/mall/mall",
		isActive: false
	}
};

var DuibiAllData = {};