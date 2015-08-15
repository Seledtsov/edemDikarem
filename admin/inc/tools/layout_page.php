<?
if($show_err_test)
{
echo"layer_id=$layer_id<br>";
}
$sel_lcache="SELECT cache_flag FROM cham_layer WHERE id=$layer_id";
$res_lcache=db_getArray($conn, $sel_lcache, 2);
//делаем пока, чтобы не кешировалось
$res_lcache['CACHE_FLAG']=0;
if($res_lcache['CACHE_FLAG'] && defined("CACHE_YES") && $nc!=1)
	{
	//$logic_url="";
	$logic_url=logic_url($_REQUEST);
	if($show_err_test)
	{
	echo"logic_url=$logic_url<br>";
	}
	$sel_cache="SELECT html_text FROM cham_cache WHERE name='$logic_url' AND layer_id=$layer_id";
	$res_cache=db_getArray($conn, $sel_cache, 2);
	if($res_cache['HTML_TEXT'])//есть страница в кеше
		{
if($show_err_test)
	echo"Cache Yes!";

		$ret=$res_cache['HTML_TEXT'];
		$res_cache['HTML_TEXT']="1";//для того, чтобы нормально отработало условие проверки данных
		db_disconnect($conn);
		}
	}
elseif($res_lcache['CACHE_FLAG'] && defined("CACHE_YES") && $nc==1)//сброс кеша
	{
	$logic_url=logic_url($_REQUEST);
	$del_cache_q="DELETE  FROM cham_cache WHERE name='$logic_url' AND layer_id=$layer_id";
	$del_cache=db_query($conn, $del_cache_q);
	//echo"del_cache_q=$del_cache_q, $del_cache<br>";
	}

if($show_err_test)
	{
	echo __LINE__." res_lcache['CACHE_FLAG']=".$res_lcache['CACHE_FLAG']."<br>";
	echo __LINE__." res_cache['HTML_TEXT']=".$res_cache['HTML_TEXT']."<br>";
	echo __LINE__." nc=$nc<br>";
	}
if(!$res_lcache['CACHE_FLAG'] || !$res_cache['HTML_TEXT'] || $nc==1)//страница не кешируется или кеша нет в базе или надо сбросить кеш
{
$layer_nocache_flag=1;
if($show_err_test)
	{
	echo"NO<br>";
	echo __LINE__." layer_id=$layer_id<br>";
	}
$layer_xml_ar=layer_xml($conn, $layer_id, array_merge($_REQUEST, array('nav_id'=>$nav_id)), array_merge($_SESSION, array("our_const_xml"=>$our_const_xml)), 2);
$layer_xml=$layer_xml_ar['xml_page'];
if($show_err_test)
	{
	echo __LINE__."<pre>";
	print_r($layer_xml_ar);
	}
$layer_xsl=layer_xsl($conn, $layer_id);
if($show_err_test)
	{
	echo __LINE__." layer_xsl=<br><pre>";
	print_r($layer_xsl);
}

if($res_lcache['CACHE_FLAG'] && defined("CACHE_YES"))//страница кешируется
	{
	$main_xml.="<CACHE_DATE>".date("Y-m-d H:i:s")."</CACHE_DATE>";
	}

$xml="<ROOT><PageUrl>".g_URL."?".str_replace("&", "&amp;", g_QUERY)."</PageUrl><viewerPath>".$layer_xsl['VFILE_NAME']."</viewerPath>".$main_xml.$layer_xml."</ROOT>";
//echo"xml=$xml<br>\r\n";

if($xml_show_flag)
	{
	$struct_xml=struct_xml($xml);
	echo $struct_xml;
	exit;
	}
if($show_xml_flag==1)
	{
	$struct_xml=struct_xml($xml);
	echo $struct_xml;
	}
if(!is_array($layer_xsl) || count($layer_xsl)==0)//xsl не определен
	{
	$struct_xml=struct_xml($xml);
	echo"NO XSL<br>".$struct_xml;
	}
elseif(BROUSER_XML==1)//выдать xml
	{
	header("Content-type: text/xml");
	$xsl_file=ereg_replace("^\./", "/xsl/", $layer_xsl['VFILE_PATH']."/".$layer_xsl['VFILE_NAME']);
	if(!strpos($xsl_file, "/xsl/"))
		$xsl_file="/xsl".$xsl_file;
	$ret="<?xml version=\"1.0\" encoding=\"Windows-1251\"?>\n<?xml-stylesheet href=\"$xsl_file\" type=\"text/xsl\"?>\n".$xml;
	}
else
	{
	//echo __LINE__."<pre>";
	//print_r($layer_xsl);
	header("Content-type: text/html; charset=windows-1251");
	//echo"xmlData=$xmlData<br>";
	//$filename=random();
	$time_xml_to_html1=mktime();

	$ret=xml_to_html($xml, $layer_xsl, $SERVER_NAME);
	$time_xml_to_html=mktime()-	$time_xml_to_html1;
	//echo __LINE__."<br>";
	if($res_lcache['CACHE_FLAG'] && defined("CACHE_YES"))//страница кешируется
		{
		//if(DOM_XML==1 || ereg("test", g_HOST))
			add_cache($conn, $layer_id, $logic_url, $ret, $xml, $layer_xml_ar['id_sql']);
		}
	//echo __LINE__."<br>";

	}
db_disconnect($conn);
}
if($ret)
	{
	//echo __LINE__."<br>";
	//file_log (XML_LOG_FILE, $ret);
	echo $ret;
	if($xml_show_flag=="y")
		echo $xml;
	}
elseif(!DEFINED("BROUSER_XML"))
	{
	print ("There was an error that occurred in the XSL transformation...\n");
	print ("\tError number: " . xslt_errno($xh) . "\n");
	print ("\tError string: " . xslt_error($xh) . "\n");
	exit;
	}
?>