<?php
define('ERROR_NEED_REDIRECT',           302);
define('ERROR_WAIT_CONFIRM',            303);
define('ERROR_NOT_CHANGE',              304);

define('ERROR_CHALLENGE_FAILED',   		333);

//错误码定义
define('ERROR_INVALID_REQUEST_PARAM',   401);
define('ERROR_USER_HAS_NOT_LOGIN',      402);
define('ERROR_OUT_OF_LIMIT',            403);
define('ERROR_OBJ_NOT_EXIST',           404);
define('ERROR_OBJECT_NOT_EXIST',        404); //alias

define('ERROR_INVALID_WEIXIN_ARGS',     405);
define('ERROR_SERVICE_NOT_AVAILABLE',   406);
define('ERROR_CONFIG_ERROR',            407);
define('ERROR_RESOURCE_NOT_AVAILABLE',  408);

define('ERROR_USERNAME_ALREADY_EXIST',  425);
define('ERROR_EMAIL_ALREADY_EXIST',     426);
define('ERROR_INVALID_EMAIL_OR_PASSWD', 427);
define('ERROR_INVALID_VERIFT_CODE',     428);
define('ERROR_INVALID_INVITE_CODE',     429);
define('ERROR_INVALID_SAFETY_PASSWD',   430);


define('ERROR_UNKNOWN_DB_ERROR',        504);
define('ERROR_DB_CHANGED_BY_OTHERS',    506);

define('ERROR_OPERATION_TOO_FAST',      604);
define('ERROR_OBJECT_ALREADY_EXIST',    605);
define('ERROR_OBJ_ALREADY_EXIST',       605); //alias
define('ERROR_PERMISSION_DENIED',       606);
define('ERROR_BAD_STATUS',              607);
define('ERROR_DENYED_FOR_SAFETY',       608);

define('ERROR_DBG_STEP_1',              701);
define('ERROR_DBG_STEP_2',              702);
define('ERROR_DBG_STEP_3',              703);
define('ERROR_DBG_STEP_4',              704);
define('ERROR_DBG_STEP_5',              705);
define('ERROR_DBG_STEP_6',              706);

define('ERROR_IN_WEIXIN_API',			801);
define('ERROR_UNDEFINE',			    901);

// 定义数据库数据排序
define('SORT_CREATE_TIME',			1);
define('SORT_CREATE_TIME_DESC',		2);
define('SORT_MODIFI_TIME',			3);
define('SORT_MODIFI_TIME_DESC',		4);
define('SORT_REPLY_COUNT',			5);
define('SORT_REPLY_COUNT_DESC',		6);
define('SORT_FAV_COUNT',			7);
define('SORT_FAV_COUNT_DESC',		8);
define('SORT_CLICK_COUNT',			9);
define('SORT_CLICK_COUNT_DESC',		10);
define('SORT_SALSE_COUNT',			11);
define('SORT_SALSE_COUNT_DESC',		12);
define('SORT_PRICE',			    13);
define('SORT_PRICE_DESC',	      	14);
define('SORT_VOTE',			        15);
define('SORT_VOTE_DESC',	      	16);
define('SORT_SCORE_TOTAL_DESC',	    17);
define('SORT_SCORE_TOTAL',	      	18);
define('SORT_LIKE',	      	        19);
define('SORT_LIKE_DESC',	        20);
define('SORT_ORI_PRICE',			23);
define('SORT_ORI_PRICE_DESC',	    24);

define('SORT_BY_UID',			    21);
define('SORT_BY_UID_DESC',		    22);

define('SORT_QUANTITY',			    25);
define('SORT_QUANTITY_DESC',	    26);


define('SORT_BY_USER',	            32);
define('SORT_BY_FIND_IN_SET',	    33);

define('SORT_BY_POINT_MAX_DESC',	35);
define('SORT_BY_POINT_DESC',	    34);
define('SORT_BY_CASH_MAX_DESC',	    36);
define('SORT_BY_CASH_DESC',	        37);


define('PATTERN_ACCOUNT', '/^([^<\'"#\s]{1,32})$/u');
define('PATTERN_USER_NAME', '/^([^<\'"@#]{1,32})$/u');
define('PATTERN_PASSWD', '/^(.{6,32})$/u'); //支持中文密码
define('PATTERN_AT_SUGGEST', '/([^<\'"@#\s]{1,8})/u');
define('PATTERN_MD5', '/^([0-9a-z]{32})$/');
define('PATTERN_SHA1', '/^([0-9a-z]{40})$/');
define('PATTERN_CRC32', '/^([0-9a-z]{8})$/');
define('PATTERN_AES', '/^([0-9a-zA-Z]{16,128})$/');
define('PATTERN_TOKEN', '/^([\w\-_]{3,64})$/');
define('PATTERN_EMAIL', '/^([a-z0-9A-Z_\-]{2,16}@[a-z0-9A-Z_\-\.]{2,16}$)/');
define('PATTERN_URL', '/^(((http|https|ftp):\/\/)?[^<\'"\s]{3,455})$/i');
define('PATTERN_FULLURL', '/^((http|https|ftp):\/\/[^<\'"\s]{3,455})$/i');
define('PATTERN_SEARCH_KEY', '/^([^<\'"]{1,32})$/u');
define('PATTERN_PHONE', '/^([0-9\-\+]{3,16})$/');
define('PATTERN_MOBILE', '/^(1[0-9]{10})$/');
define('PATTERN_IP', '/^([0-9:\.a-zA-Z]{7,40})$/');
define('PATTERN_IPV4', '/^((\d{1,3}\.){3}\d{1,3})$/');
define('PATTERN_FILE_NAME', '/^([^\/\\\:*?"<>|]{1,64})$/u');
define('PATTERN_UIDM', '/^(\d+?[\da-z]{4,6})$/');
define('PATTERN_QQ', '/^([0-9]{3,16})$/');
define('PATTERN_APP_NAME', '/^([\w\.]+)$/');
define('PATTERN_NORMAL_STRING', '/^([\w\._\-]{1,255})$/u');
define('PATTERN_NORMAL_STRING_SHORT', '/^([\w\._\-]{1,32})$/u');
define('PATTERN_ORDER_UID', '/^([a-y]\d+)$/');
define('PATTERN_DOMAIN_NAME', '/^([a-zA-Z0-9_\-\.]+)(:\d+)?$/');
define('PATTERN_DATE', '/^(\d{2,4}-?\d{1,2}-?\d{1,2})$/');
define('PATTERN_SKU_UID', '/^(\d+[\x{4e00}-\x{9fa5}\+（）\(\)\.\w\\/:;< 　]*)$/u');
define('PATTERN_DATETIME', '/^([\d\-:\sT]{2,24})$/');
define('PATTERN_COLOR', '/^(#?[\w]+)$/');
define('PATTERN_WORKER_ID', '/^([a-zA-Z0-9_\-\.:\*]+)$/');
define('PATTERN_PS_INT', '/^([a-zA-Z0-9\-]+)$/');
define('PATTERN_MOBILE_AND_CODE', '/^(1[0-9]{10}(:[\w\x{4e00}-\x{9fa5}]{0,16})?)$/u'); //手机号码与编号
define('PATTERN_EXPRESS_ID', '/^([a-zA-Z0-9]{5,})$/'); //快递单号


//字符参数分割符
define('SPLIT_STRING', ';');

define('MAX_UPLOAD_SIZE', 20971520 ); //20M

