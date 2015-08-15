<?
$COUNT_TABLE=0;
//файл html-заготовок
if(!defined("FUNC_HTML"))
{
function count_table()
        {
        global $COUNT_TABLE;
        $ans="";
        for($i=0; $i<$COUNT_TABLE; $i++)
                $ans.="        ";
        echo"\r\n$ans";
        }

//функции форм--------------------------------------------------------------------
function form($action, $method="POST", $addition="")
        {
        if(!$method)
                $method="POST";
        echo"<FORM ACTION=\"$action\" METHOD=\"$method\" $addition>";
        }
function formend()
        {
        echo"</form>";
        }

function forminput($type, $name="", $value="", $class="button", $addition="", $ret=0, $delete=0)
        {
        if($type=="checkbox" || $type=="radio") $class="no_border";
        $ans="<input type=\"$type\" ";
        if($name) $ans.="name=\"$name\" ";
        if(strlen($value))
           $ans.="value=\"$value\" ";
        if($class) $ans.="class=\"$class\" ";
                if($delete) $ans .= "onclick=\"return confirm('Вы действително хотите удалить данные?');\" ";

        $ans.="$addition>";
        if($ret) return $ans;
        else echo $ans;
        }

function textarea($name, $addition)
        {
        echo"<textarea name=\"$name\" $addition>";
        }

function textareaend()
        {
        echo"</textarea>";
        }

function optioninp($val, $opt, $selected="", $addition="", $set_val="")
        {
        //echo gettype($set_val).", ".gettype($val)."<br>";
        if((string)$set_val==(string)$val)
                $selected=1;
        if($selected)
                $addition.=" selected";
        echo"<option value=\"$val\" $addition>$opt</option>";
        }
function select_up($name, $addition="")
        {
        echo"<select name=\"$name\" $addition>";
        }
function select_down()
        {
        echo"</select>";
        }

//функции таблиц--------------------------------------------------------------
function table($additional="")
        {
        global $COUNT_TABLE;
        if(!eregi("width", $additional))
                $additional.=" width=\"100%\"";
        if(!eregi("border=", $additional))
                $additional.=" border=\"0\"";
        if(!eregi("cellspacing=", $additional))
                $additional.=" cellspacing=\"0\"";
        if(!eregi("cellpadding=", $additional))
                $additional.=" cellpadding=\"0\"";
        count_table();
        echo"<table $additional>\r\n";
        $COUNT_TABLE++;
        }
function tableend()
        {
        global $COUNT_TABLE;
        count_table();
        echo"</table>";
        $COUNT_TABLE--;
        }

function tr($additional="")
        {
        global $COUNT_TABLE;
        if(!eregi("class=", $additional))
                $additional.=" ";
        count_table();
        echo"<tr $additional>";
        $COUNT_TABLE++;
        }
function trend()
        {
        global $COUNT_TABLE;
        count_table();
        echo"</tr>";
        $COUNT_TABLE--;
        }


function td($additional="")
        {
        global $COUNT_TABLE;
        count_table();
        if(!eregi("class=", $additional))
                $additional.="";
        echo"<td $additional>";
        $COUNT_TABLE++;
        }
function tdend()
        {
        global $COUNT_TABLE;
        //count_table();
        echo"</td>";
        $COUNT_TABLE--;
        }

function tdtd($additional="")
        {
        tdend();
        td($additional);
        }

function trtd($additionaltr="", $additionaltd="")
        {
        tr($additionaltr);
        td($additionaltd);
        }
function tdtr()
        {
        tdend();
        trend();
        }




//ДОПОЛНИТЕЛЬНЫЕ ФУНКЦИИ---------------------------------------------------
function css($src="/css/rjd.css")
        {
        echo"<link rel=\"stylesheet\" href=\"$src\" type=\"text/css\">";
        }
function nbsp($num=1, $ret=0)
        {
        for($i=0; $i<=$num; $i++)
                $res.="&nbsp;";
        if($ret)
           return $res;
        else
            echo $res;
        }

function br($num=1)
        {
        global $COUNT_TABLE;
        for($i=0; $i<$num; $i++)
                {
                //count_table();
                echo"<br>";
                }
        }

function charset($cod)
        {?>
        <meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
        <?}

function img_src($src, $additional="", $ret=0)
        {
        if(!eregi("border", $additional))
                $additional.=" border=\"0\"";

        $ans="<img src=\"$src\" $additional>";
        if(!$ret)
                echo $ans;
        else
                return $ans;
        }

function href($href, $text, $addition="", $ret=0)
        {
        $ans="<a href=\"$href\" $addition>$text</a>";
        if(!$ret)
                echo $ans;
        else
                return $ans;
        }

function href_win($href, $text, $name_win, $addition, $addition_win)
        {
        if(!eregi("scrollbars=", $addition_win))
                $addition_win.=" scrollbars=1";
        if(!eregi("menubar=", $addition_win))
                $addition_win.=" menubar=0";
        if(!eregi("toolbar=", $addition_win))
                $addition_win.=" toolbar=0";
        if(!eregi("menubar=", $addition_win))
                $addition_win.=" menubar=0";
        if(!eregi("location=", $addition_win))
                $addition_win.=" location=0";
        if(!eregi("status=", $addition_win))
                $addition_win.=" status=0";
        if(!eregi("width=", $addition_win))
                $addition_win.=" width=400";
        if(!eregi("height=", $addition_win))
                $addition_win.=" height=300";

        echo("<a href=\"$href\" target=\"$name_win\" $addition onClick=\"window.open('$href', '$name_win', '$addition_win'); return false;\" >$text</a>");
        }

}
function body($additional="")
        {
        if(!eregi("leftmargin", $additional))
                $additional.=" leftmargin=\"0\"";
        if(!eregi("topmargin", $additional))
                $additional.=" topmargin=\"0\"";
        if(!eregi("marginwidth", $additional))
                $additional.=" marginwidth=\"0\"";
        if(!eregi("marginheight", $additional))
                $additional.=" marginheight=\"0\"";
        echo"<body $additional>";
        }
function jscript($src, $lang, $additional="")
        {
        if(!$lang)
                $lang="JavaScript";
        echo"<SCRIPT LANGUAGE=\"$lang\" SRC=\"$src\"></script>";
        }
//функция картинки-распоки
function rasp($width=1, $height=1, $ret=0, $addit="")
        {
        img_src("/images/1pixel.gif", "width=\"$width\" height=\"$height\" alt=\"\" hspace=\"0\" vspace=\"0\" $addit", $ret);
        }

//функция красной картинки-распоки
function raspRED($width=1, $height=1, $ret=0, $addit="")
        {
        img_src("/images/1pixelRED.gif", "width=\"$width\" height=\"$height\" alt=\"\" hspace=\"0\" vspace=\"0\" $addit", $ret);
        }

function font($additional, $view=0)
        {
        $ans="<font $additional>";
        //echo"view=$view<br>";
        if(!$view)
                echo $ans;
        else
                return $ans;
        }
function fontend($view=0)
        {
        $ans="</font>";

        if(!$view)
                echo $ans;
        else
                return $ans;

        }

function div($additional, $view=0)
        {
        $ans="<div $additional>";
        if(!$view)
                echo $ans;
        else
                return $ans;
        }
function divend($view=0)
        {
        $ans="</div>";
        if(!$view)
                echo $ans;
        else
                return $ans;

        }

function hr($addition="")
        {
        echo"<hr $addition>";
        }

define("FUNC_HTML", 1);

function shadow($text, $color, $prc, $OffX=1, $OffY=1)
        {
        return"<span style=\"cursor:hand;text-decoration:none; filter: DropShadow(Color=$color, OffX=$OffX, OffY=$OffY, Positive=1);  width=90%); width=$prc\">$text</span>";
        }
//функция рисует кнопочки для сайта ОЖД
function ozd_buttons($text, $nameform="form1", $img=1, $onclick="", $type=0)
         {
         if(!$nameform)
              $nameform="form1";
         if(!$onclick)
              {
              $onclick=$nameform.".submit();";
              }
         if(DOMEN=="kbshzd")
            $class_link='class="text" style="font-weight:bold;"';
         else
            $class_link='class="text6"';
         if($img>0)
             {
             $img_name1="arr_prev.gif";
             $img_name2="arr_next.gif";
             }
         else
             {
             $img_name2="arr_prev.gif";
             $img_name1="arr_next.gif";
             }
         $img=abs($img);
         if($img==1)
             {
             ?><input type=image src="/images_<?echo DOMEN;?>/<?echo $img_name1;?>" alt="<?echo $text;?>" ><?
             }
         elseif($img==2 ||$img==4)
                 {
                 if(!$type)
                      img_src("/images_". DOMEN."/$img_name1", 'onclick="'.$onclick.';" alt='.$text.'');
                 else
                     href($onclick, img_src("/images_".DOMEN."/$img_name1","", 1));
                 }

         if(!$type)
              {
              ?><a href="javascript:onClick=<?echo $onclick;?>" <?echo $class_link;?>>&nbsp;<?echo $text;?>&nbsp;</a><?
              }
         else
              href($onclick, "&nbsp;$text&nbsp;", $class_link);


         if($img==1)
             {
             ?><input type=image src="/images_<?echo DOMEN;?>/<?echo $img_name2;?>" alt="<?echo $text;?>"><?
             }
         elseif($img==3 ||$img==4)
                 {
                 if(!$type)
                      img_src("/images_".DOMEN."/$img_name2", 'onclick="'.$onclick.';" alt='.$text.'');
                 else
                     href($onclick, img_src("/images_". DOMEN."/$img_name2","", 1));
                 }
         }


function thead($addition="")
        {
        echo"<thead $addition>";
        }
function theadend()
        {
        echo"</thead>";
        }

function th($addition="")
        {
        echo"<th $addition>";
        }
function thend()
        {
        echo"</th>";
        }
function thth($addition="")
        {
        thend();
        th($addition);
        }
function span($addit)
         {
         echo"<span $addit>";
         }
function spanend()
         {
         echo"</span>";
         }
?>