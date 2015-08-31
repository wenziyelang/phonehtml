<?php
header("Content-Type:text/html;charset=utf-8");
date_default_timezone_set('PRC');
set_time_limit(0);
require_once("class/class_db.php");
$db=new DB();
$time = addslashes($_POST['time']);
$submit = addslashes($_POST['submit']);

$array = array();

if($submit == '处理'){
    $daytime = str_replace("-", "", $time);
    
    $exceltable="IP$daytime";
    
    $talktable = "talk$daytime";
    
    $db->connect('localhost', 'root', '', 'phone', 'utf8');
    
    $select_sql = $db->get_all("SELECT * FROM {$exceltable} LIMIT 0,1");
        
    if($select_sql){
        $dropcon = $db->query("DROP TABLE {$exceltable}");
    }
    
     $sql="CREATE TABLE IF NOT EXISTS `$exceltable` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `speaknum` int(11)  DEFAULT NULL,
      `segment` varchar(255) DEFAULT NULL,
      `city` varchar(255) DEFAULT NULL,
      `phone` text DEFAULT NULL,
      `ip` varchar(255) DEFAULT NULL,
      `pageurl` text DEFAULT NULL,
      `pagefrom` text DEFAULT NULL,
      `keywords` text DEFAULT NULL,
      `fromtime` int(11) DEFAULT NULL,
      `endway` text DEFAULT NULL,
      `duration` text DEFAULT NULL,     
      PRIMARY KEY (`id`)
     )ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            
    $db->query($sql);
    
    $getcwd_file = getcwd();

    $dir = $getcwd_file."/phonehtml/".$daytime;

    if (is_dir($dir)){

        if ($dh = opendir($dir)){
            $i = 0;
            while (($file = readdir($dh))!= false){
                $i++;

                $is_xls = strstr($file, '.html');

                if($is_xls){
                    $array[$i] = $file;
                }
            }
            closedir($dh);
        }
    }else{
		die('没有/phonehtml/phonehtml/'.$daytime.'这个目录，请上传'.$time.'日数据');
    }
    
    foreach ($array as $filekey => $filevalue) {
        $duqu = $dir."/".$filevalue;

        $phpstr = file_get_contents($duqu);
        //$rankrules = '/<span id="([^<>]+)" style="cursor:pointer;"[\s\S]*?<tr class="worker_msg">/i';
         //preg_match_all('/<tr[^>]*?class="worker_msg"[^>]*>[\s\S]*?<\/tr>/i', $phpstr, $ArticleRow);</table>
        
        preg_match_all('/<span id="([^<>]+)" style="cursor:pointer;"[\s\S]*?<\/table>/i', $phpstr, $contentRows);
        
        //preg_match_all('/<span id="([^<>]+)" style="cursor:pointer;"[\s\S]*?<span id="([^<>]+)" style="cursor:pointer;"/i', $phpstr, $contentRows);
        
        $contentRows = $contentRows[0];
        
        foreach($contentRows as $key => $value){
            preg_match_all('/<td[^>]*?class="guest_msg"[^>]*>[\s\S]*?<\/td>/i', $value, $guest_msg_content);
            $guest_msg_content = $guest_msg_content[0];
            
            foreach($guest_msg_content as $guest_msg_key => $guest_msg_value){
                preg_match_all("((\d{11})|^((\d{7,8})|(\d{4}|\d{3})-(\d{7,8})|(\d{4}|\d{3})-(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})|(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1}))$)", $guest_msg_value, $phone_content);

                foreach ($phone_content as $phone_value) {
                   
                    if(!empty($phone_value[0])){
                        preg_match_all('/<span id="([^<>]+)" style="cursor:pointer;"[\s\S]*?<\/tr>/i', $value, $user_content);
                        
                        $user_content = $user_content[0][0];
                        
                        preg_match_all('/<td[^>]*?class="td_0"[^>]*>[\s\S]*?<\/td>/i', $user_content, $user_xiangxi_content);
                        $user_xiangxi_content = $user_xiangxi_content[0];
                        $arr1["speaknum"] =  strip_tags($user_xiangxi_content[2]);//条数
                        
                        preg_match_all('((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d))))', $user_xiangxi_content[3], $ipstr);
                        $arr1["ip"] = $ipstr[0][0];//ip地址
                        
                       	$ipstr = strip_tags($user_xiangxi_content[3]);
                        $ip_city_start = strripos($ipstr,"[");
                        $ip_wangduan_end = strripos($ipstr,"]");
                        if(empty($ip_city_start) || empty($ip_wangduan_end)){
                            $arr1["city"] = $ipstr;
                        }else{
                            $arr1["city"] = mb_substr($ipstr, 0, $ip_city_start);//IP所属城市
                        }
                       
                        $lengthwang = $ip_wangduan_end - $ip_city_start;
                        $wangduan = mb_substr($ipstr, $ip_city_start, $lengthwang);
                        $wangduan = str_replace("[", "", $wangduan);
                        $arr1["segment"] = str_replace("]", "", $wangduan);//IP所属服务商
                        
                        $arr1["pageurl"] = strip_tags($user_xiangxi_content[4]);//受访页面
                        
                        $pagefromkeywords = strip_tags($user_xiangxi_content[5]);
                        
                        $startkeywords = strripos($pagefromkeywords, "(");
                        $endkeywords = strripos($pagefromkeywords, ")");
                        $lengthkeywords = $endkeywords - $startkeywords;
                        $search_keywords = mb_substr($pagefromkeywords, $startkeywords, $lengthkeywords, 'utf-8');
                        $search_keywords = str_replace("(", "", $search_keywords);
                        $arr1["keywords"] = str_replace(")", "", $search_keywords);//关键词
                        
                        if(!empty($user_xiangxi_content[5])){
                            preg_match_all('/href\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^"\'>\s]+))/', $user_xiangxi_content[5], $pagefrom);//访问来源页面
                            
                            $arr1["pagefrom"] = @urldecode($pagefrom[2][0]);//访问来源页面
                        }else{
                            $arr1["pagefrom"] = '';
                        }
                        
                        $arr1["fromtime"] = strtotime(strip_tags($user_xiangxi_content[6]));//来访时间
                        $arr1["endway"] = strip_tags($user_xiangxi_content[8]);//53弹窗结束方式
                        $arr1["duration"] = strip_tags($user_xiangxi_content[10]);//用户谈话时间
                        $arr1["phone"] = substr_replace($phone_value[0],"****",-4,4);
                        $content = $arr1["speaknum"]."|".$arr1["segment"]."|".$arr1["city"]."|".$arr1["phone"]."|".$arr1["ip"]."|".$arr1["pageurl"]."|".$arr1["pagefrom"]."|".$arr1["keywords"]."|".$arr1["fromtime"]."|".$arr1["endway"]."|".$arr1["duration"]."|";
                   
                        $db->insert($exceltable, $arr1);
                    }
                }
            }
        }
    }
    
    
    $select_sql = $db->get_all("SELECT * FROM {$talktable} LIMIT 0,1");
        
    if($select_sql){
        $dropcon = $db->query("DROP TABLE {$talktable}");
    }
    
     $sql="CREATE TABLE IF NOT EXISTS `{$talktable}` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `speaknum` int(11)  DEFAULT NULL,
      `segment` varchar(255) DEFAULT NULL,
      `city` varchar(255) DEFAULT NULL,
      `phone` text DEFAULT NULL,
      `ip` varchar(255) DEFAULT NULL,
      `pageurl` text DEFAULT NULL,
      `pagefrom` text DEFAULT NULL,
      `keywords` text DEFAULT NULL,
      `fromtime` int(11) DEFAULT NULL,
      `endway` text DEFAULT NULL,
      `duration` text DEFAULT NULL,     
      PRIMARY KEY (`id`)
     )ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            
    $db->query($sql);
    
    
    foreach ($array as $filekey => $filevalue) {
        $duqu = $dir."/".$filevalue;

        $phpstr = file_get_contents($duqu);

        $rankrules = '/<span id="([^<>]+)" style="cursor:pointer;"[\s\S]*?<\/table>/i';

        preg_match_all($rankrules, $phpstr, $contentRows);

        $contentRows = $contentRows[0];

        foreach($contentRows as $span_key => $span_value){
                preg_match_all('/<span id="([^<>]+)" style="cursor:pointer;"[\s\S]*?<\/tr>/i', $span_value, $user_content);

                $user_content = $user_content[0][0];

                if(!empty($user_content)){
                        preg_match_all('/<td[^>]*?class="td_0"[^>]*>[\s\S]*?<\/td>/i', $user_content, $user_xiangxi_content);

                        $user_xiangxi_content = $user_xiangxi_content[0];

                        if(!empty($user_xiangxi_content[3])){
                                $iprules = "((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d))))";
                                preg_match_all($iprules, $user_xiangxi_content[3], $ipstr);

                                $arr1["ip"] = $ipstr[0][0];

                                $arr1["speaknum"] =  strip_tags($user_xiangxi_content[2]);//条数

                                $ipstr = strip_tags($user_xiangxi_content[3]);
                                $ip_city_start = strripos($ipstr,"[");
                                $ip_wangduan_end = strripos($ipstr,"]");
                                if(empty($ip_city_start) || empty($ip_wangduan_end)){
                                        $arr1["city"] = $ipstr;
                                }else{
                                        $arr1["city"] = mb_substr($ipstr, 0, $ip_city_start);//IP所属城市
                                }

                                $lengthwang = $ip_wangduan_end - $ip_city_start;
                                $wangduan = mb_substr($ipstr, $ip_city_start, $lengthwang);
                                $wangduan = str_replace("[", "", $wangduan);
                                $arr1["segment"] = str_replace("]", "", $wangduan);//IP所属服务商

                                $arr1["pageurl"] = strip_tags($user_xiangxi_content[4]);//受访页面

                                $pagefromkeywords = strip_tags($user_xiangxi_content[5]);
                                $startkeywords = strripos($pagefromkeywords, "(");
                                $endkeywords = strripos($pagefromkeywords, ")");
                                $lengthkeywords = $endkeywords - $startkeywords;
                                $search_keywords = mb_substr($pagefromkeywords, $startkeywords, $lengthkeywords, 'utf-8');
                                $search_keywords = str_replace("(", "", $search_keywords);
                                $arr1["keywords"] = str_replace(")", "", $search_keywords);//关键词

                                if(!empty($user_xiangxi_content[5])){
                                         preg_match_all('/href\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^"\'>\s]+))/', $user_xiangxi_content[5], $pagefrom);//访问来源页面
                                }

                                $arr1["pagefrom"] = @urldecode($pagefrom[2][0]);//访问来源页面

                                $arr1["fromtime"] = strtotime(strip_tags($user_xiangxi_content[6]));//来访时间

                                $arr1["endway"] = strip_tags($user_xiangxi_content[8]);//53弹窗结束方式

                                $arr1["duration"] = strip_tags($user_xiangxi_content[10]);//用户谈话时间

                                if(!empty($user_xiangxi_content[0])){
                                        $phone_rules = "((\d{11})|^((\d{7,8})|(\d{4}|\d{3})-(\d{7,8})|(\d{4}|\d{3})-(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})|(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1}))$)";

                                        preg_match_all($phone_rules, $user_xiangxi_content[0], $phone_content);


                                }
                                if(!empty($phone_content[0][0])){
                                        $phone_html_num = $phone_content[0][0];
                                }else{
                                         $phone_html_num = '';
                                }

                                if(!empty($phone_html_num)){
                                        $arr1["phone"] = substr_replace($phone_html_num,"****",-4,4);
                                }
                                $db->insert($talktable, $arr1);
                        }
                }
        }
    }
    
   
    $html_content = $db->get_all("SELECT ip FROM {$exceltable} GROUP BY ip");
    
    foreach($html_content as $html_key => $html_value){
        $ip_content = $db->query("INSERT INTO {$exceltable} (speaknum, segment, city, phone, ip, pageurl, pagefrom, keywords, fromtime, endway, duration) SELECT speaknum, segment, city, phone, ip, pageurl, pagefrom, keywords, fromtime, endway, duration FROM {$talktable} WHERE ip = '{$html_value['ip']}'");
        
    }

    $txtfile = 'phonetxt/'.$daytime.'.txt';

    if(file_exists($txtfile)){
        unlink($txtfile);
    }
    
    $open = fopen($getcwd_file."/phonetxt/".$daytime.".txt","a");
    
    $html_content = $db->get_all("SELECT * FROM {$exceltable}");
    
    foreach($html_content as $html_key => $html_value){
        $content = $html_value['speaknum']."|".$html_value['speaknum']."|".$html_value['city']."|".$html_value['phone']."|".$html_value['ip']."|".$html_value['pageurl']."|".$html_value['pagefrom']."|".$html_value['keywords']."|".$html_value['fromtime']."|".$html_value['endway']."|".$html_value['duration']."|";
        
        if(PATH_SEPARATOR==':') {
            fwrite($open, $content."\n");//linux下写入
        }else{
            fwrite($open, $content."\r\n");  //windows下写入
        }  
    }
    
    $db->query("DROP TABLE {$exceltable}");
    
    $db->query("DROP TABLE {$talktable}");
    
    
    echo "<h1 style='color:red'>数据写入成功</h1>";
    echo "<br /><br /><span style='color:red'>1、请您把phonetxt目录下".$daytime.".txt传给技术部风龙，完成后删除该txt文档</span><br />";
    echo "<span style='color:red'>2、删除phonehtml目录下的".$daytime."文件夹</span>";
}

