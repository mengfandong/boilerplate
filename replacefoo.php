<?php
# file:                replace_utf8.php
#                      本程序适用于对UTF-8的页面进行修改。

set_time_limit(3600);  //脚本运行时间

require 'vendor/autoload.php';
use  Joomla\Filesystem\Folder;

define ('JPATH_ROOT', __DIR__);

echo JPATH_ROOT;

$destfolder = 'myext';
Folder::copy('plugins/system',$destfolder,JPATH_ROOT,true);


?>
<?php
if (isset($_POST['Submit']) && $_POST['Submit'] == 'submit') {
  $dir = $_POST['searchpath'];
  $isall = 1;

  if (!get_magic_quotes_gpc()) {
    $rpstr = $_POST['rpstr'];
    $proj_name = $_POST['proj_name'];
    $author_name = $_POST['author_name'];
  } else {
    $rpstr = stripslashes($_POST['rpstr']);
    $proj_name = stripslashes($_POST['proj_name']);
    $author_name = stripslashes($_POST['author_name']);
  }


  //分析shortname
  $arrext = explode("|", 'php|xml|ini');

  if (!is_dir($dir)) return;

  //把末尾的/去掉
  if (substr($dir, -1) == '/') $dir = substr($dir, 0, strrpos($dir, "/"));

  //罗列所有目录
  hx_dirtree($rpstr,$dir, 'foo',$arrext);
  
  hx_dirtree($proj_name,$dir, '[PROJECT_NAME]',array('ini'));

  hx_dirtree($author_name,$dir, '[AUTHOR]',array('xml'));

  exit();
}


function hx_dirtree($rpstr,$path = ".", $sstr = 'foo', $arrext = array('php','xml','ini'))
{
  $d = dir($path);

  while (false !== ($v = $d->read())) {
    if ($v == "." || $v == ".." || $v == "replacefoo.php") continue;

    $file = $d->path . "/" . $v;
    $newfilename = $d->path . "/" . str_replace($sstr, strtolower($rpstr), $v);

    if (is_dir($file)) {
      echo "<p>文件夹：$file";
      if (strpos($file, 'foo')) {
        rename($file, $newfilename);
        echo "改名为：$newfilename";
        hx_dirtree($rpstr,$newfilename, $sstr,$arrext);
      } else {
        hx_dirtree($rpstr,$file, $sstr,$arrext);
      }
      echo "</p>";
    } else {
      $ext = substr(strrchr($v, "."), 1);

      if (in_array($ext, $arrext)) {
        echo "<li>文件：$file ";
        $body = file_get_contents($file);

        $body2 = str_replace(strtolower($sstr), strtolower($rpstr), $body);
        $body3 = str_replace(strtoupper($sstr), strtoupper($rpstr), $body2);
        $body4 = str_replace(ucfirst($sstr), ucfirst($rpstr), $body3);

        if ($body != $body4 && $body4 != '') {
          $newname = tofile($file, $newfilename, $body4);
          echo "改名为：$newname" . '字符' . $sstr . '--已替换';
        } else {
          echo ' 未发现字符'.$sstr;
        }

        echo '</li>';
      }
    }
  }
  $d->close();
}


//把生成文件的过程写出函数
function tofile($file_name, $newfilename, $file_content)
{
  if (is_file($file_name)) {
    @unlink($file_name);
  }
  $handle = fopen($newfilename, "w");
  if (!is_writable($newfilename)) {
    return false;
  }
  if (!fwrite($handle, $file_content)) {
    return false;
  }
  fclose($handle); //关闭指针
  return $newfilename;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>php字符批量修改替换程序</title>
  <style type="text/css">
    body {
      background: #FFFFFF;
      color: #000;
      font-size: 12px;
    }

    #top {
      text-align: center;
    }

    h1,
    p,
    form {
      margin: 0;
      padding: 0;
    }

    h1 {
      font-size;
      14px;
    }
  </style>
</head>

<body>
  <div id="top">
    <h1>批量替换程序(UTF-8版)</h1>
    <div>本程序可以扫描指定目录的所有文件，进行<strong>内容替换</strong>。可用于被批量挂马的删除以及批量更新页面某些内容。<br />
      在文件数量非常多的情况下，本操作比较占用服务器资源，请确脚本超时限制时间允许更改，否则可能无法完成操作。</div>
  </div>


  <form action="<?= $_SERVER['SCRIPT_NAME'] ?>" name="form1" target="stafrm" method="post">
    <table width="95%" border="0" align="center" cellpadding="3" cellspacing="1" bgcolor="#666666">
      <tr>
        <td width="10%" bgcolor="#FFFFFF"><strong>&nbsp;起始根路径：</strong></td>
        <td width="90%" bgcolor="#FFFFFF">
          <input name="searchpath" type="text" id="searchpath" value="." size="20" /> 点表示当前目录，末尾不要加/
        </td>
      </tr>

      <tr id="rpct">
        <td height="64" colspan="2" bgcolor="#FFFFFF">
          <table width="100%" border="0" cellspacing="1" cellpadding="1">
            <tr>
              <td width="20%">扩展名foo替换为：</td>
              <td><textarea name="rpstr" id="rpstr" style="width:80%;height:45px">mycomp</textarea></td>
            </tr>
          </table>
        </td>
      </tr>

      <tr>
        <td width="10%" bgcolor="#FFFFFF">项目名及描述：</td>
        <td width="40%" bgcolor="#FFFFFF">
          <input name="proj_name" type="text" id="proj_name" value="项目名" size="20" /> 
        </td>
        </tr>
        <tr>
        <td width="10%" bgcolor="#FFFFFF">作者名：</td>
        <td width="40%" bgcolor="#FFFFFF">
          <input name="author_name" type="text" id="author_name" value="作者名" size="20" /> 
        </td>

      </tr>


      <tr>
        <td colspan="2" height="20" align="center" bgcolor="#E2F5BC"><input type="submit" name="Submit" value="submit" class="inputbut" /></td>
      </tr>
    </table>
  </form>
  <table width="95%" border="0" align="center" cellpadding="3" cellspacing="1" bgcolor="#666666">
    <tr bgcolor="#FFFFFF">
      <td id="mtd">
        <div id='mdv' style='width:100%;height:500px;'>
          <iframe name="stafrm" frameborder="0" id="stafrm" width="100%" height="100%"></iframe>
        </div>
        <script type="text/javascript">
          document.all.mdv.style.pixelHeight = screen.height - 450;
        </script>
      </td>
    </tr>
  </table>
</body>

</html>