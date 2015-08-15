<?
################################################################################
# tools'û
//==============================================================================
function search_template($ar)
	{
	extract($ar);
	if($OBJECT_TREE[$entity_id]['ent_info']['LAYOUT_ID'])
		{
		$layout_id=$OBJECT_TREE[$entity_id]['ent_info']['LAYOUT_ID'];
		//echo"photo_gal - ".$OBJECT_TREE[$entity_id]['ent_info']['LAYOUT_ID'];
		$layout_text=layout_xml($conn, $layout_id, array("id"=>$id));
		//echo"layout_text=$layout_text<br>";
		$layout_ar[$layout_id]=$layout_id;
		$layout_text=add_xml_links($conn, array("xml_page"=>"<CLIENT_AREA>$layout_text</CLIENT_AREA>", "layout_ar"=>$layout_ar, "search"=>1));
		$layout_text=ereg_replace("</?CLIENT_AREA>", "", $layout_text);
		return $layout_text;
		}
	}
?>