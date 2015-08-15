<?
    if(getenv("g_INC"))
        include_once(getenv("g_INC")."conf.php");
    else
        include_once($_SERVER['DOCUMENT_ROOT']."/inc/conf.php");
    include(PATH_INC."inc.php");
    if(!file_exists (IMAGE_PATH.IMAGE_ORIGINAL)){
        mkdir(IMAGE_PATH.IMAGE_ORIGINAL);
    }

    if($_FILES["file"]){
        //��������� ������� ������ ��� ��������
        if ($_FILES["file"]["error"] > 0)
        {
            echo "Error: " . $_FILES["file"]["error"] . "<br />";
        }
        else
        {
            $patch = $_FILES["file"]["tmp_name"];
            //echo '<pre>'; var_dump($_FILES); var_dump(exif_read_data ($patch));
            if (is_uploaded_file($patch)) {
                if(is_readable($patch)){
                    if(substr_count($_FILES["file"]["type"],'zip')>0){
                        //echo '��� ����� ZIP';
                        $zip = zip_open($patch);
                        if ($zip){
                            while ($zip_entry = zip_read($zip)) {
                                if (zip_entry_open($zip, $zip_entry, "r")) {
                                    $fn = zip_entry_name($zip_entry);//��������� ���
                                    $fn = iconv("CP866", "CP1251", $fn);//����������� ���        
                                    $path_parts = explode("/",$fn);// �������� ��������  ��� �����
                                    $filename = end($path_parts);
                                    if($filename!=""){
                                        $fn = $filename;
                                        //��������� � �������� ������������ �������
                                        //if ( ! eregi("[^A-Za-z�-��-�0-9///./~/^/-/_/(/)/{/}'`@#$%_]",$fn)){
                                        if ( ! eregi("[^A-Za-z�-��-�0-9///./-/_'`_]",$fn)){ 
                                           //echo "��� ����������� ���� (OK)"; 
                                        }else{ 
                                           //echo "���� ����������� ����� (FALSE)";
                                           $fn = eregi_replace("\s","_" ,$fn);
                                           $fn = eregi_replace("[^A-Za-z�-��-�0-9///./-/_'`]","X" ,$fn);
                                        }
                                        //�������� ��������
                                        if(ereg("[�-��-�]+",$fn)){
                                           $fn = my_translit($fn);  
                                        }
                                        // exif_read_data (
                                        
                                        //��������� ���� � �����
                                        $fp =IMAGE_PATH.IMAGE_ORIGINAL.$fn;
                                        
                                        //���� ��� ���������� �� ����������, �� ������� ��
                                        if(stripos($fn,".")===false){
                                            if(!is_dir($fp)){
                                                mkdir($fp);
                                            }
                                        }else{
                                        //��������� ���� �� ������������� (������� ������ � ������� ������)
                                        //���� ���� � ����� ������ ����������, �� ���������������
                                            while(file_exists ($fp)){
                                                $fp =IMAGE_PATH.IMAGE_ORIGINAL.str_replace(".",'_'.time().".",$fn);
                                            }
                                            //���������� ���� �� ������
                                            $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                                            $wr = file_put_contents ( $fp , $buf,LOCK_EX);
                                            if($wr===false){
                                                echo '�� ������� ��������� ����: '.$filename.' ��� '.$fp." <br/>"; 
                                            }else{
                                                echo '���� '.$filename.' �������� ��� '.$fp." <br/>";
                                                $ftype = filetype($fp);
                                                $sql = "SELECT count(name) FROM ti_file_types  WHERE name='{$_FILES["file"]["type"]}'";
                                                $res =  db_getVar($conn, $sql);
                                                if($res==0){
                                                    echo '���� � ����� '.$ftype.' �� �������������� (������������) ��������! <b>���� ������ � �������! </b><br/>';
                                                   
                                                }else{
                                                    // ����� ���� � ���� 
                                                }
                                            }
                                        }
                                        zip_entry_close($zip_entry);    
                                        echo "<br>\n";
                                    }  
                                }
                            }
                            zip_close($zip);
                        }
                    }else{
                        $sql = "SELECT count(name) FROM ti_file_types  WHERE name='{$_FILES["file"]["type"]}'";
                        $res =  db_getVar($conn, $sql);
                        //echo '<pre>'; var_dump($res);
                        if($res>0){
                            $fp =IMAGE_PATH.IMAGE_ORIGINAL.$_FILES["file"]["name"];
                            if(file_exists ($fp)){
                                echo '����� ���� ��� ����������.';
                                $fp =IMAGE_PATH.IMAGE_ORIGINAL.str_replace(".",'_'.time().".",$_FILES["file"]["name"]);
                            }//else{
                            $buf = file_get_contents($patch);
                            $wr = file_put_contents ( $fp , $buf, LOCK_EX);
                            if($wr===false){ chmod(IMAGE_PATH.IMAGE_ORIGINAL, 0777); 
                                $perm = fileperms($fp);
                                // echo '<pre>'; var_dump($perm);
                                $perm = fileperms(IMAGE_PATH.IMAGE_ORIGINAL);  var_dump($perm);
                                echo '�� ������� ������� ����.  ��������� �������:';
                                $handle = fopen($fp,"w");
                                $fwrite = fwrite($fp, $buf);
                                fclose($fp);
                                if ($fwrite === false) {
                                    echo ' ��������� ������� �� �������.';
                                }

                            }else{
                                // ����� ���� � ����
                                echo '���� ������� �������� �� ���� '.$_FILES["file"]["name"];
                            }
                            //}
                        }else{
                            echo '���� ������ ����� �� ��������������:  '.$_FILES["file"]["type"]."<br>";
                        }
                    }
                }else{
                    echo "���� ���������� ��� ������!";
                }
            }else{
                echo "��������� ����� � �������� �������� �����: ";
                echo "���� '". $patch . "'.";
            }
        }
        /*echo '<pre>';
        print_r($_REQUEST);
        print_r($_SERVER);
        print_r($_SESSION);
        echo '</pre>';*/
    }

?>
<form  name="zip" enctype="multipart/form-data" method="post">
    <input type="file" name="file" value="">
    <input type="submit" name="submit" value="���������">
</form>
<?
?>