<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
require_once(VENDOR_PATH . 'getui/' . 'IGt.Push.php');
require_once(VENDOR_PATH . 'getui/' . 'igetui/IGt.AppMessage.php');
require_once(VENDOR_PATH . 'getui/' . 'igetui/IGt.APNPayload.php');
require_once(VENDOR_PATH . 'getui/' . 'igetui/template/IGt.BaseTemplate.php');
require_once(VENDOR_PATH . 'getui/' . 'IGt.Batch.php');
require_once(VENDOR_PATH . 'getui/' . 'igetui/utils/AppConditions.php');
define('HOST','http://sdk.open.api.igexin.com/apiex.htm');
define('APPKEY','Qzh7e8Ax9397SxLOV3OibA');
define('APPID','EqV8N0hswl8jsyMbNjDKL8');
define('MASTERSECRET','zZqi4GEE6C6gP6UD7s8cC8');
define('CID','8802cda119538b060e986b54c3e69cc7');
define('DEVICETOKEN','');
define('Alias','nicky');
class Push extends Controller
{
    /**
     * 测试推送到一台安卓手机
     */
    public function test()
    {
        //$igt = new IGeTui(HOST,APPKEY,MASTERSECRET);
        $igt = new \IGeTui(NULL,APPKEY,MASTERSECRET,false);

        //消息模版：
        // 1.TransmissionTemplate:透传功能模板
        // 2.LinkTemplate:通知打开链接功能模板
        // 3.NotificationTemplate：通知透传功能模板
        // 4.NotyPopLoadTemplate：通知弹框下载功能模板

//    	$template = IGtNotyPopLoadTemplateDemo();
//    	$template = IGtLinkTemplateDemo();
//    	$template = IGtNotificationTemplateDemo();
        $template = $this->_IGtTransmissionTemplateDemo();

        //个推信息体
        $message = new \IGtSingleMessage();

        $message->set_isOffline(true);//是否离线
        $message->set_offlineExpireTime(3600*12*1000);//离线时间
        $message->set_data($template);//设置推送消息类型
//	$message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        //接收方
        $target = new \IGtTarget();
        $target->set_appId(APPID);
        $target->set_clientId(CID);
//    $target->set_alias(Alias);


        try {
            $rep = $igt->pushMessageToSingle($message, $target);
            var_dump($rep);
            echo ("<br><br>");

        }catch(RequestException $e){
            $requstId =e.getRequestId();
            $rep = $igt->pushMessageToSingle($message, $target,$requstId);
            var_dump($rep);
            echo ("<br><br>");
        }
    }

    /**
     * @return IGtTransmissionTemplate
     * 推送消息的模板
     */
    private function _IGtTransmissionTemplateDemo(){
            $template =  new \IGtTransmissionTemplate();
            $template->set_appId(APPID);//应用appid
            $template->set_appkey(APPKEY);//应用appkey
            $template->set_transmissionType(1);//透传消息类型
            $template->set_transmissionContent("你是人类已知最帅男子");//透传内容
            //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
            //APN简单推送
//        $template = new IGtAPNTemplate();
//        $apn = new IGtAPNPayload();
//        $alertmsg=new SimpleAlertMsg();
//        $alertmsg->alertMsg="";
//        $apn->alertMsg=$alertmsg;
////        $apn->badge=2;
////        $apn->sound="";
//        $apn->add_customMsg("payload","payload");
//        $apn->contentAvailable=1;
//        $apn->category="ACTIONABLE";
//        $template->set_apnInfo($apn);
//        $message = new IGtSingleMessage();

            //APN高级推送
            $apn = new \IGtAPNPayload();
            $alertmsg=new \DictionaryAlertMsg();
            $alertmsg->body="body";
            $alertmsg->actionLocKey="ActionLockey";
            $alertmsg->locKey="LocKey";
            $alertmsg->locArgs=array("locargs");
            $alertmsg->launchImage="launchimage";
//        IOS8.2 支持
            $alertmsg->title="Title";
            $alertmsg->titleLocKey="TitleLocKey";
            $alertmsg->titleLocArgs=array("TitleLocArg");

            $apn->alertMsg=$alertmsg;
            $apn->badge=7;
            $apn->sound="";
            $apn->add_customMsg("payload","payload");
            $apn->contentAvailable=1;
            $apn->category="ACTIONABLE";
            $template->set_apnInfo($apn);

            //PushApn老方式传参
//    $template = new IGtAPNTemplate();
//          $template->set_pushInfo("", 10, "", "com.gexin.ios.silence", "", "", "", "");

            return $template;
        }
}
