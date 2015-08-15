<?
include(getenv("g_INC")."conf.php");
include(PATH_INC."main_inc.php");
ignore_user_abort(true);
set_time_limit(36000);

$HOST=$HTTP_HOST;
$PORT=80;


$sel_art_q="select id from articles where id>15000 ";
$res_art=db_getArray($conn, $sel_art_q);
foreach($res_art as $k=>$v)
         {
         //$res=exec("GET http://loc.ism.xsite/objects/index_proc.php?obj_id=5&id=".$v['ID']);
         //echo"res=$res, id=".$v['ID']."<br>";

        $fp = fsockopen ($HOST, $PORT, $errno, $errstr, 30);

        if (!$fp)
             {
             echo"No $errstr ($errno)<br>\n";
             flush();
             echo date("H:i");
             exit();
             }
        else
            {

            $string="GET http://".$HOST."/objects/index_proc.php?obj_id=5&id=".$v['ID']." HTTP/1.0\r\n\r\n ";
            echo"string=$string<br>";
            $res_post_out=fputs ($fp, $string);
            if(!$res_post_out)
                {
                echo"Нет соединения - ";
                echo date("H:i");
                exit();
                }
            while (!feof($fp))
                        {
                        echo fgets ($fp,1000000);
                        flush();
                        }
            fclose ($fp);
            sleep(1);
            }

         }

?>