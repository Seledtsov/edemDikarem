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
        //проверяем наличие ошибок при загрузке
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
                        //echo 'Это архив ZIP';
                        $zip = zip_open($patch);
                        if ($zip){
                            while ($zip_entry = zip_read($zip)) {
                                if (zip_entry_open($zip, $zip_entry, "r")) {
                                    $fn = zip_entry_name($zip_entry);//Извлекаем имя
                                    $fn = iconv("CP866", "CP1251", $fn);//Раскодируем его        
                                    $path_parts = explode("/",$fn);// Получаем исходное  Имя файла
                                    $filename = end($path_parts);
                                    if($filename!=""){
                                        $fn = $filename;
                                        //Фильтруем и заменяем недопустимые символы
                                        //if ( ! eregi("[^A-Za-zА-Яа-я0-9///./~/^/-/_/(/)/{/}'`@#$%_]",$fn)){
                                        if ( ! eregi("[^A-Za-zА-Яа-я0-9///./-/_'`_]",$fn)){ 
                                           //echo "нет посторонних букв (OK)"; 
                                        }else{ 
                                           //echo "есть посторонние буквы (FALSE)";
                                           $fn = eregi_replace("\s","_" ,$fn);
                                           $fn = eregi_replace("[^A-Za-zА-Яа-я0-9///./-/_'`]","X" ,$fn);
                                        }
                                        //Транслит кирилицы
                                        if(ereg("[А-Яа-я]+",$fn)){
                                           $fn = my_translit($fn);  
                                        }
                                        // exif_read_data (
                                        
                                        //Формируем путь к файлу
                                        $fp =IMAGE_PATH.IMAGE_ORIGINAL.$fn;
                                        
                                        //Если эта директория не обнаружена, то создаем ее
                                        if(stripos($fn,".")===false){
                                            if(!is_dir($fp)){
                                                mkdir($fp);
                                            }
                                        }else{
                                        //Проверяем файл на существование (наличие файлов с такимже именем)
                                        //Если файл с таким именем существует, то переименовываем
                                            while(file_exists ($fp)){
                                                $fp =IMAGE_PATH.IMAGE_ORIGINAL.str_replace(".",'_'.time().".",$fn);
                                            }
                                            //Записываем файл на сервер
                                            $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                                            $wr = file_put_contents ( $fp , $buf,LOCK_EX);
                                            if($wr===false){
                                                echo 'Не удалось сохранить файл: '.$filename.' как '.$fp." <br/>"; 
                                            }else{
                                                echo 'Файл '.$filename.' сохранен как '.$fp." <br/>";
                                                $ftype = filetype($fp);
                                                $sql = "SELECT count(name) FROM ti_file_types  WHERE name='{$_FILES["file"]["type"]}'";
                                                $res =  db_getVar($conn, $sql);
                                                if($res==0){
                                                    echo 'Файл с типом '.$ftype.' не поддерживается (отображается) системой! <b>Файл удален с сервера! </b><br/>';
                                                   
                                                }else{
                                                    // пишем файл в базу 
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
                                echo 'Такой файл уже существует.';
                                $fp =IMAGE_PATH.IMAGE_ORIGINAL.str_replace(".",'_'.time().".",$_FILES["file"]["name"]);
                            }//else{
                            $buf = file_get_contents($patch);
                            $wr = file_put_contents ( $fp , $buf, LOCK_EX);
                            if($wr===false){ chmod(IMAGE_PATH.IMAGE_ORIGINAL, 0777); 
                                $perm = fileperms($fp);
                                // echo '<pre>'; var_dump($perm);
                                $perm = fileperms(IMAGE_PATH.IMAGE_ORIGINAL);  var_dump($perm);
                                echo 'Не удалось создать файл.  Повторная попытка:';
                                $handle = fopen($fp,"w");
                                $fwrite = fwrite($fp, $buf);
                                fclose($fp);
                                if ($fwrite === false) {
                                    echo ' Повторная попытка не удалась.';
                                }

                            }else{
                                // пишем файл в базу
                                echo 'Файл успешно загружен на диск '.$_FILES["file"]["name"];
                            }
                            //}
                        }else{
                            echo 'Этот формат файла не поддерживается:  '.$_FILES["file"]["type"]."<br>";
                        }
                    }
                }else{
                    echo "Файл недоступен для чтения!";
                }
            }else{
                echo "Возможная атака с участием загрузки файла: ";
                echo "файл '". $patch . "'.";
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
    <input type="submit" name="submit" value="Загрузить">
</form>
<?
?>