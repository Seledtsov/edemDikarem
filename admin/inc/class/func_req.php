<?

    /********************************************************************************
    *
    *   Возвращает смешанный хэш, содержащий данные пришедшие как по GET так и по POST
    *
    ********************************************************************************/

	function get_request(){
    	global $HTTP_GET_VARS, $HTTP_POST_VARS;
    	$geta = $HTTP_GET_VARS;
    	$posta = $HTTP_POST_VARS;
    	$params = array_merge($geta, $posta);
    	reset($params);
    	while(list($key,$value)=each($params)){
    		if (gettype($params[$key])!="array"){
    		if (get_magic_quotes_gpc()){
                $value = stripslashes(trim($value));
            }
    		$params[$key] = $value;
    		}
    	}
        return $params;
    }

    /********************************************************************************
    *
    ********************************************************************************/

?>