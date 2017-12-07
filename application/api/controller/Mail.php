<?php

namespace app\api\controller;
//require VENDOR_PATH.'autoload.php';
use think\Controller;
use think\Request;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;

/**
 * Class Mail
 * @package app\demo\controller
 * 利用第三方库发送邮件
 */
class Mail extends Controller
{
    /**
     * 邮件页面的显示
     *
     * @return \think\Response
     */
    public function index()
    {

    }

    /**
     * 发送邮件
     *
     * @return \think\Response
     */
    public function send()
    {
        $mail = new Message;
        $mail->setFrom('daituwyp@126.com')
            ->addTo('405042236@qq.com')
            ->setSubject( '测试邮件')
            ->setBody( '测试内容' );

        $mailer = new SmtpMailer([
            'host' => 'smtp.126.com',
            'username' => 'daituwyp@126.com',
            'password' => 'Kiss890825', /* smtp独立密码 */
            'secure' => 'ssl',
        ]);
        $rep = $mailer->send($mail);
//        return true;
    }

}
