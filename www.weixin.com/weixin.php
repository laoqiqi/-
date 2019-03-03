<?php 
$wx = new Wx();
class Wx{
    # token;
    private const TOKEN = 'weixin';
    private $obj;
    public function __construct(){
        # 判定是否有echostr;
        if(isset($_GET['echostr'])){
            echo $this->checkSignature();
        }else{
            $this->config = include 'config.php';
            // 接收传送信息；
            $this->acceptMesage();
        }
    }

    // 接收消息处理；
    private function acceptMesage(){
        // 接收消息；
        $xml = file_get_contents('php://input');
        // 写入日志；
        $this->writeLog($xml);
        // 转换接收的信息（对象）；
        $this->obj=simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA);
        // 获取接收的类型；       
        $type = $this->obj->MsgType;
        $msg ='';
        #也可以使用switch,不同的提交数据回复分开；

        // 动态获取想要调用的方法；textfun
        $funName = $type.'Fun';
        // 消息管理方法处理
		echo $msg = $this->$funName();
		// 一般在框架底层用到
        // echo $msg = call_user_func([$this,$funName]);
        // 记录发送日志；
        if(!empty($msg)){
            $this->writeLog($msg,1);
       }
    }

    // text消息处理机制；
    private function textFun(){
        // 接收文本中的内容；
        $content = (string)$this->obj->Content;
        // 调用生成返回文本；
        return $this->createText($content);
    }

    // 图文消息处理机制；
    private function newsFun(){
        // 接收文本内容；
        $content = (string)$this->obj->Content;
        // 调用返回图文；
        return $this->createNews($content);
    }

    // 生成返回的文本；
    private function createText(string $content){
        // 替换模板中的占位符；
        return sprintf($this->config['text'],$this->obj->FromUserName,$this->obj->ToUserName,time(),'服务器'.$content);
    }

    // 生成返回的图文；
    private function createNews(string $content){
        $ToUserName = $this->obj->FromUserName;
        $FromUserName = $this->obj->ToUserName;
        $url='http://www.itcast.cn/2018czgw/images/logo.png';
        // 替换模板中的占位符；
        return sprintf($this->config['news'],$ToUserName,$FromUserName,time(),$content,'服务器返回图文（描述）',$url,'http://www.itcast.cn/2018czgw/images/logo.png');
    }

    # 日志；
    private function writeLog(string $xml,int $flag = 0){
        # 头部信息；
        $title = $flag == 0 ? '接收':'发送';
        $dtime = date('Y年m月d日 H:i:s');
        # 日志内容；
        $log = $title."【{$dtime}】\n";
		$log .= "-------------------------------------------------------------------------\n";
		$log .= $xml."\n";
        $log .= "-------------------------------------------------------------------------\n";
        # 写日志 追加记录日志
		file_put_contents('wx.xml',$log,FILE_APPEND); 
    }

    # 首次连接服务器；
    private function checkSignature()
    {
        #接收服务器传来的参数
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $echostr = $_GET['echostr'];
        #组织参数
        $tmpArr['token'] = self::TOKEN;
        $tmpArr['timestamp'] = $timestamp;
        $tmpArr['nonce'] = $nonce;
        #字典排序
        sort($tmpArr, SORT_STRING);
        #转化成字符串；
        $tmpStr = implode( $tmpArr );
        #加密
        $tmpStr = sha1( $tmpStr );
        #判定是否验证成功
        if( $tmpStr==$signature){
        return $echostr;
        }
        return '';
    }
}
