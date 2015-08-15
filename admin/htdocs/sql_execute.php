<?
//var_dump($_POST);
include(getenv("g_INC")."conf.php");
include(PATH_INC."inc.php");
echo "<h1>Арм выполнения скриптов</h1>";
//br();
if ($go=="Выполнить")
{
//	echo $dir_name;
	chdir($dir_name);
	$dir = opendir(".");
	//br();
	if (!$file = @fopen("scripts.properties", "r"))
	{
		echo "Невозможно открыть файл свойств!!!";
	}
	else
	{
		$scripts_file_name = file("scripts.properties");
		for ($i=0; $i<count($scripts_file_name); $i++)
		{	
			//echo $i." - ".count($scripts_file_name);
			if ($scripts_file_name[$i] != "")
			{				
				//echo $scripts_file_name[$i];
				$current_file_descriptor = $file."_".$i;
				$scripts_file_name[$i] = substr($scripts_file_name[$i], 0, -2);
				br(); 								
				if (!$current_file_descriptor = @fopen($scripts_file_name[$i], "r"))
				{					
					echo "Невозможно открыть файл ".$scripts_file_name[$i]."!!!";
				}
				else
				{	
					echo "<h3>Обрабатывается файл: ".$scripts_file_name[$i]."</h3>";				
					$file_str = file($scripts_file_name[$i]);
					for ($j=0; $j < count($file_str); $j++)
					{
						$str = $file_str[$j];											
						if (strpos($str, ";"))
						{
							$sql .= substr($str, 0, strpos($str, ";")+1);
							echo $sql;
							br(2);
							$res = db_query($conn, $sql);
							if ($res == 0)
							{
							 echo $sql;	
							 br(2);
							}							
							$sql = "";
						}
						else
							$sql .= $str;												
					}
				}
			}
		}
	}
}
//echo $sql;
//db_query($conn, $sql);
form("sql_execute.php");
echo "Укажите директорию SQL скриптов";
forminput("text", "dir_name");
br();
forminput("submit", "go", "Выполнить");
formend();




?>