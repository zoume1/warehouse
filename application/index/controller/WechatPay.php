<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/23 0023
 * Time: 14:38
 */

namespace app\index\controller;

use think\Controller;
use  think\Db;

class WechatPay extends Controller
{

    /*
    微信支付配置参数
    */
    private $config = array(
        'appid' => "wx37e840c96f13f585",    /*微信开放平台上的应用id*/
//        'appid' => "wx7214a4fde280c2b7",    /*微信开放平台上的应用id*/
        'mch_id' => "1412019602",   /*微信申请成功之后邮件中的商户id*/
        'api_key' => "ffcfd0f5898e756ba07cc7f17ed4dbfb",    /*在微信商户平台上自己设定的api密钥 32位*/
        'notify_url' => '回调地址',

    );


//    private $config = array(
//        'appid' => "wx4b0deb320539d8d3",    /*微信开放平台上的应用id*/
//        'mch_id' => "1412019602",   /*微信申请成功之后邮件中的商户id*/
//        'api_key' => "ffcfd0f4898e856ba07cc7f17ed4dbfb",    /*在微信商户平台上自己设定的api密钥 32位*/
//        'notify_url' => '回调地址',
//
//    );

    //微信支付下单
//    public function wxpay($body, $orderid, $out_trade_no, $total_fee, $type)
    public function wxpay()
    {
        $datas =$_POST;
        $body =1;
        $orderid =100001;
        $out_trade_no =2018121212;
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $data["appid"] = $this->config["appid"];
        $data["body"] = '茶仓-' . $body;
        $data["mch_id"] = $this->config['mch_id'];
        $data["nonce_str"] = $this->createNoncestr(); //随机数
        $data["notify_url"] = $this->config["notify_url"];  //回调地址
        $data['trade_type'] = 'APP';
        $data["total_fee"] = "1";//"$total_fee"
        $data["out_trade_no"] = $out_trade_no;
        $data["spbill_create_ip"] = $this->get_client_ip(); //获取当前服务器的IP
        $sign = $this->getSign($data);  //微信支付签名
        $data["sign"] = $sign;

        $xml = $this->arrayToXml($data);  //数组转化为xml

        $response = $this->postXmlCurl($xml, $url); //以post方式提交xml到对应的接口url
        return ajax_success('数据返回',$response);
        $response = $this->xmlToArray($response);  //将xml转为array
        $response = $this->two_sign($response, $data["nonce_str"]); //微信支付二次签名

//        return ajax_success('数据返回',$response);
        //返回数据
        echo json_encode(['status' => 1, 'indo' => 'success', 'orderid' => $orderid, 'data' => $response]);
    }

    //微信支付回调地址--商品支付
    public function wxNotifyUrl()
    {
        $type = 3;
        $this->payCommom($type);
    }


    /**
     * [payCommom 充值公共方法]
     * @param  [type] $type []
     * @return [type]       [description]
     */
    public function payCommom($type)
    {
        $xml_data = file_get_contents('php://input');
        $re = $this->wx_verification($xml_data);
        $flow_sn = $re['transaction_id'];//获取流水号
        if ($re['code'] == 1) {
            $data = $this->xmlToArray($xml_data);
            // 启动事务
            Db::startTrans();
            try {

                #你的订单业务逻辑....

                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
            }
        }
        echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
    }

    /**
     * 微信支付退款
     * $pay_sn      支付单号
     * $total_fee   订单金额
     * $refund_fee  退款金额
     */
    public function wxrefundOrder($pay_sn, $total_fee, $refund_fee)
    {
        $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";

        $data["appid"] = $this->config["appid"];
        $data["mch_id"] = $this->config['mch_id'];
        $data["nonce_str"] = $this->createNoncestr();
        $data['out_trade_no'] = $pay_sn;
        $data['out_refund_no'] = $pay_sn;
        $data["total_fee"] = intval($total_fee * 100);
        $data["refund_fee"] = intval($refund_fee * 100);
        $sign = $this->getSign($data);
        $data["sign"] = $sign;

        $xml = $this->arrayToXml($data);
        $response = $this->postXmlCurl($xml, $url);
        $response = $this->xmlToArray($response);
        if ($response['return_code'] == 'SUCCESS' && $response['refund_id']) {
            return ['code' => 1, 'refund_id' => $response['refund_id']];
        } else {
            return ['code' => 0, 'err_code_des' => $response['err_code_des']];
        }
    }

    /**
     * [order_record 订单记录]
     * @return [type] [description]
     */
    public function order_record($orderid, $unionid, $title, $price, $user_money)
    {
        $record['orderid'] = $orderid;
        $record['unionid'] = $unionid;
        $record['title'] = $title;
        $record['price'] = $price;
        $record['money'] = $user_money;
        $record['addtime'] = time();
        Db::name('order_record')->insert($record);
    }

//微信支付二次签名
    public function two_sign($response = NULL, $nonce_str)
    {
        if ($response != NULL && $response['return_code'] != 'SUCCESS') {
            return [];
        } else {
            //接收微信返回的数据,传给APP!
            $arr = array(
                'appid' => $this->config["appid"],
                'partnerid' => $this->config['mch_id'],
                'prepayid' => $response['prepay_id'],
                'package' => 'Sign=WXPay',
                'noncestr' => $nonce_str,
                'timestamp' => time(),
            );
            //第二次生成签名
            $sign = $this->getSign($arr);
            $arr['sign'] = $sign;
            return $arr;
        }
    }

//微信支付签名
    public function getSign($Obj)
    {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        // echo '【string1】：'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $this->config['api_key'];
        // echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        // echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        // echo "【result】 ".$result_."</br>";
        return $result_;
    }


    /**
     *  作用：产生随机字符串，不长于32位
     */
    public function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /*
    获取当前服务器的IP
    */
    public function get_client_ip()
    {
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }

//数组转xml
    public function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }


    /**
     *  作用：将xml转为array
     */
    public function xmlToArray($xml)
    {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }


    /**
     *  作用：以post方式提交xml到对应的接口url
     */
    public function postXmlCurl($xml, $url, $second = 30)
    {
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        //退款证书
//        curl_setopt($ch, CURLOPT_SSLCERT, PUBLIC_PATH . '/wx/apiclient_cert.pem');
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        //退款证书
//        curl_setopt($ch, CURLOPT_SSLKEY, PUBLIC_PATH . '/wx/apiclient_key.pem');

        //运行curl
        $data = curl_exec($ch);
        //返回结果

        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            curl_close($ch);
            return false;
        }
    }

    /**
     *  作用：格式化参数，签名过程需要使用
     */
    public function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

//微信支付回调验证
    public function wx_verification($xml_data)
    {
        $data = $this->xmlToArray($xml_data);
        $sign = $data['sign'];
        unset($data['sign']);
        if ($sign == $this->getSign($data)) {
            if ($data['result_code'] == 'SUCCESS') {
                $arr = ['code' => 1];
            } else {
                $arr = ['code' => 0, 'msg' => $data['return_msg']];
            }
        } else {
            $arr = ['code' => 0, 'msg' => '签名验证失败'];
        }
        return $arr;
    }

}
