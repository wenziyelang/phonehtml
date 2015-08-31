<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>电话分析</title>
<style>
li {height:50px;list-style:none outside none;margin:0; padding:0;}
.but{background:url("../but.gif") repeat-x scroll 50% top #CDE4F2;border:1px solid #C5E2F2;cursor:pointer;height:30px;margin-bottom:5px;margin-left:5px;width:80px;}
input{color:#22AC38;height:25px;width:300px;background:none repeat scroll 0 0 #FFFFFF;border:1px solid #94C6E1;font-weight:bold;margin-bottom:5px;padding:5px;}

</style>
</head>

<body>
<?php
require_once("class/class_form.php");
$form=new Form();
$time = '20150101';
?>

    <form action="phone.php" method="post" enctype="multipart/form-data">
    <ul>
        <li>
            <?php
            echo "<li>日期：";  echo $form->date('time',$time);
            ?>
        </li>
        <li>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="处理" class="but" style="width:200px;">
        </li>
    </ul>
</form>
<h2 style="color:red">操作步骤：</h2><br />
1、将53下载下来的html解压放到一个文件夹中，文件夹的命名为时间格式，例如：20141220，中间不允许有-<br />
2、将20141220上传至/phonehtml/目录下<br />
3、选择时间：20141220，选择的时间要与刚才上传的文件夹相匹配<br />
4、点击执行<br />
5、处理成功后在phonetxt文件夹下下载20141220.txt到本地<br />
</body>
</html>