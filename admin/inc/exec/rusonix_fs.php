<?
//phpinfo();
      $argv = $_SERVER["argv"];
      foreach($argv as $k_av=> $v_av)
                {
                $argv_ar=split("=", $v_av);
                $argv_name=$argv_ar[0];
                $$argv_name= trim($argv_ar[1]);
                }

//--------------------------
function file_tree($dir, $dir_to)
	{
	echo"\r\nDIR=$dir\r\n";
	$files_dir=scandir($dir);
	foreach ($files_dir as $k=>$v)
		{
		if($v<>'.' && $v<>'..')
			{
			$file_full=$dir."/".$v;
			$file_full_to=$dir_to."/".$v;
			if(is_dir($file_full))
				{
				echo"dir=$file_full\r\n";
				if(!file_exists ($file_full_to))
					{
					echo"NO dir - $file_full_to\r\n";
					mkdir($file_full_to);
					//exec("mkdir $file_full_to");
					}
				else
					echo"Yes dir - $file_full_to\r\n";	
				file_tree($file_full, $file_full_to);
				//			echo "dir=".$file_full."\r\n";
				}
			else
				{
				echo $file_full."->$file_full_to\r\n";
				if(!file_exists ($file_full_to))
					{
					echo"NO file - $file_full_to ($file_full)\r\n";
					link($file_full, $file_full_to);
					}
				else
					{
					echo"OLD file - $file_full_to ($file_full)\r\n";
					unlink($file_full_to);
					link($file_full, $file_full_to);
					}
				}
			}
		}
	}
file_tree($dir_src, $dir_to);
?>