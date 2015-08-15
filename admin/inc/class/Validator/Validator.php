<?php
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

#   INTEGER ----------------------
function _integer_($value)
{
    if (!preg_match("/^(-){0,1}[0-9]+$/i",$value,$a)){return 1;}else{return 0;}
}
#   POSITIVE INTEGER    ----------
function _p_integer_($value)
{
    if (!preg_match("/^[0-9]+$/i",$value,$a)){return 1;}else{return 0;}
}
#   FLOAT   ----------------------
function _float_($value)
{
    if (!preg_match("/^(-){0,1}[0-9\.]+$/i",$value,$a)){return 1;}else{return 0;}
}
#   POSITIVE FLOAT  --------------
function _p_float_($value)
{
    if (!preg_match("/^[0-9\.]+$/i",$value,$a)){return 1;}else{return 0;}
}
#   EMIL    ----------------------
function _email_($value)
{
    if (!preg_match("/^[a-z0-9\-_\.]+@[a-z0-9\-_\.]+\.[a-z]{2,4}$/i",$value,$a)){return 1;}else{return 0;}
}
#   EMILS    ---------------------
function _emails_($value)
{
    if (!preg_match("/^([a-z0-9\-_\.]+@[a-z0-9\-_\.]+\.[a-z]{2,4}[,;:]{0,})+$/i",$value,$a)){return 1;}else{return 0;}
}
#   WORD    ----------------------
function _word_($value)
{
    if (!preg_match("/^[a-zA-Z0-9_\-\)\(\.!\, ]+$/i",$value,$a)){return 1;}else{return 0;}
}
#   URL --------------------------
function _url_($value)
{
    if (!preg_match("/^[a-z0-9\-_\.\/\:\=~]*$/i",$value,$a)){return 1;}else{return 0;}
}
#   PRICE   ----------------------
function _price_($value)
{
    if (!preg_match("/^[0-9]+[\.]{0,1}[0-9]{0,2}$/i",$value,$a)){return 1;}else{return 0;}
}
#   FILE    ----------------------
function _file_($name,$file_type)
{
    if ("" != $file_type){
        if (isset($_FILES[$name]["tmp_name"]) && $_FILES[$name]["tmp_name"] != ""){
            $exp = array();
            $exp = split("\|",$file_type);
            $file_exp = preg_split("/\./",$_FILES[$name]['name']);
            $file_exp = $file_exp[count($file_exp)-1];
            if (in_array(strtolower($file_exp),$exp)) return 0;
            else return 1;
        }
		else return 0;
    }
	else return 0;
}

#******************************************************************
#
#******************************************************************


class Validator{
    var $m_errors;
    var $m_valid_array;
    var $m_error_messages;
    var $m_ERROR_FIELD;
    var $m_return_hash;

    ###################################################################
    #
    ###################################################################

    function Validator()
    {
        $this->m_errors = array();
        $this->m_error_messages = array();
        $this->m_ERROR_FIELD = array();
    }

    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    //
    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    function addError($error)
    {
        $this->m_errors[]["error"] = $error;
    }

    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    //
    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    function clearErrors()
    {
        $this->m_errors = array();
    }

    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    //
    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    function getErrors()
    {
        return $this->m_errors;
    }

    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    //
    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    function getIniFile($valid_file)
    {
        if (!file_exists($valid_file)){trigger_error("Class: Validator -> Function: getIniFile() -> Error: IniFile not exists", E_USER_ERROR);exit;}
        $_file_content = file ($valid_file);
        $_valid_array = array();
        $_current_element = "";
        $_index = -1;
        foreach ($_file_content as $_ind => $_value){
            $_value = str_replace("\n","",$_value);
            $_value = str_replace("\r","",$_value);
            $_value = trim($_value);
            if (isset($_value) && $_value != "" && substr($_value,0,1) != ";"){
                if(substr($_value,0,1) == "["){
                    $_index ++;
                    $_pos = strrpos($_value,"]");
                    if (strtolower(substr($_value,1,$_pos-1)) != "adderror"){
                        $_valid_array[$_index] = array();
                        $_valid_array[$_index]["FIELD_NAME"] = substr($_value,1,$_pos-1);
                        $_current_element = substr($_value,1,$_pos-1);
                    }else{
                        $_current_element = "ADDERROR";
                    }
                }else{
                    if ($_current_element != "" && $_current_element != "ADDERROR"){
                        $_pos = strpos($_value,"=");
                        $_pos_start = strpos($_value,"\"");
                        $_pos_end = strrpos($_value,"\"");
                        $_pos_end = $_pos_end - $_pos_start;
                        $_valid_array[$_index][trim(substr($_value,0,$_pos-1))] = trim(substr($_value,$_pos_start+1,$_pos_end-1));
                    }else{
                        $_pos = strpos($_value,"=");
                        $_pos_start = strpos($_value,"\"");
                        $_pos_end = strrpos($_value,"\"");
                        $_pos_end = $_pos_end - $_pos_start;
                        $this->m_error_messages[trim(substr($_value,0,$_pos-1))] = trim(substr($_value,$_pos_start+1,$_pos_end-1));
                    }
                }
            }
        }
        return $_valid_array;
    }

    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    //
    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    function checkError($hash,$valid_file)
    {
        $this->m_valid_array = $this->getIniFile($valid_file);
        #--------------------------------------------------------------
        if (count($_FILES) != 0){
            foreach($_FILES as $fname => $fval){
                if ($_FILES[$fname]["tmp_name"] != "") $hash[$fname] = "file";
                else $hash[$fname] = "";
            }
        }
        #--------------------------------------------------------------
        foreach ($hash as $_name => $_value){
            foreach ($this->m_valid_array as $_ind => $_valid_field){
                if (isset($_valid_field["FIELD_NAME"]) && isset($_name) && $_name == $_valid_field["FIELD_NAME"]){

                    $_valid_field = $this->setDefaultField($_valid_field);

                    #------------------------------------------------------------
                    #   DEFAULT OR UNDEFAULT IF
                    #------------------------------------------------------------
                    if (strtolower($_valid_field["DEFAULT"]) == "if"){
                        if (!isset($this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]]) || "" == $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]]){
                            if (isset($_valid_field["IF"]) && "" != $_valid_field["IF"]){
                                #-----------------------------------------------------------------------
                                if(preg_match_all("/::(.*)$/i",$_valid_field["IF"],$tmp)){
                                    $DEFAULT_TYPE = $tmp[1][0];
                                    if ($DEFAULT_TYPE == "default") $NO_DEFAULT_TYPE = "undefault";
                                    else $NO_DEFAULT_TYPE = "default";
                                    $_valid_field["IF"] = str_replace($tmp[0][0],"",$_valid_field["IF"]);
                                }else{
                                    $DEFAULT_TYPE = "default";
                                    $NO_DEFAULT_TYPE = "undefault";
                                }
                                #-----------------------------------------------------------------------
                                if (substr($_valid_field["IF"],0,1) != "(" || substr($_valid_field["IF"],strlen($_valid_field["IF"])-1,1) != ")") {trigger_error("Class: Validator -> Function: checkError() -> Error: Incorrect 'IF'", E_USER_ERROR);exit;}
                                preg_match_all("/(\()/i",$_valid_field["IF"],$arr1,PREG_OFFSET_CAPTURE);
                                preg_match_all("/(\))/i",$_valid_field["IF"],$arr2,PREG_OFFSET_CAPTURE);
                                if (count($arr1[0]) != count($arr2[0])){trigger_error("Class: Validator -> Function: checkError() -> Error: Incorrect 'IF':<br>".$_valid_field["IF"]."<br>Field Name: ".$_valid_field["FIELD_NAME"]."", E_USER_ERROR);exit;}
                                #-----------------------------------------------------------------------
                                preg_match_all("/(\(|\))/i",$_valid_field["IF"],$arr1,PREG_OFFSET_CAPTURE);
                                $arr1 = $arr1[0];
                                $index = 0;
                                $lavel = 0;
                                $lavel_arr = array();
                                $ind_lavel = array();
                                for ($i=0;$i<count($arr1); $i++){
                                    if ($arr1[$i][0] == "("){
                                        $lavel ++;
                                        $lavel_arr[$index]["start"] = $arr1[$i][1];
                                        $ind_lavel[] = $index;
                                        $index++;
                                    }elseif($arr1[$i][0] == ")"){
                                        $lavel_arr[$ind_lavel[count($ind_lavel)-1]]["end"] = $arr1[$i][1];
                                        $lavel_arr[$ind_lavel[count($ind_lavel)-1]]["level"] = $lavel;
                                        $lavel_arr[$ind_lavel[count($ind_lavel)-1]]["content"] = substr($_valid_field["IF"],$lavel_arr[$ind_lavel[count($ind_lavel)-1]]["start"],$lavel_arr[$ind_lavel[count($ind_lavel)-1]]["end"] - $lavel_arr[$ind_lavel[count($ind_lavel)-1]]["start"] + 1);
                                        $lavel_arr[$ind_lavel[count($ind_lavel)-1]]["or_content"] = $lavel_arr[$ind_lavel[count($ind_lavel)-1]]["content"];
                                        array_pop ($ind_lavel);
                                        $lavel--;
                                    }
                                }
                                uasort ($lavel_arr, "sort_array");
                                #-------------------------------------------------------------------------
                                $newarr = array();
                                foreach ($lavel_arr as $element){
                                    $newarr[] = $element;
                                }
                                unset($lavel_arr);
                                for ($i=0;$i<count($newarr); $i++){
                                    $newarr[$i]["result"] = $this->getResult($hash,$newarr[$i]["content"]);
                                    for ($j=$i+1; $j<count($newarr); $j++){
                                        $newarr[$j]["content"] = str_replace($newarr[$i]["content"],$newarr[$i]["result"],$newarr[$j]["content"]);
                                    }
                                }
                                #-------------------------------------------------------------------------
                                if ($newarr[count($newarr)-1]["result"] == "true") $_valid_field["DEFAULT"] = $DEFAULT_TYPE;
                                else $_valid_field["DEFAULT"] = $NO_DEFAULT_TYPE;
                            }
                        }
                    }
                    #------------------------------------------------------------
                    #
                    #------------------------------------------------------------

                    switch (strtolower($_valid_field["DEFAULT"])){
                        #----------------------------------------------------------------------------------------
                        case "default" : {
                        #----------------------------------------------------------------------------------------
                            if ("file" == strtolower($_valid_field['TYPE'])){
                                if (!isset($_FILES[$_valid_field["FIELD_NAME"]]['tmp_name']) || "" == $_FILES[$_valid_field["FIELD_NAME"]]['tmp_name']) {
                                    $this->addError($_valid_field["EMPTY_MESS"]);
                                    $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["EMPTY_MESS"];
                                }
                            }else{
                                if ("" == $_value){
                                    $this->addError($_valid_field["EMPTY_MESS"]);
                                    $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["EMPTY_MESS"];
                                }
                            }
                            break;
                        }
                    }

                    //=========================================================================================

                        //==========     TYPE     ===============================================
                        if (!isset($this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]]) || "" == $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]]){
                            if ("" != $_value || strtolower($_valid_field["TYPE"]) == "file"){
                                switch (strtolower($_valid_field["TYPE"])){
                                    #   INTEGER ---------------------------------------
                                    case "integer" :{
                                       if (_integer_($_value)){
                                            $this->addError($_valid_field["INCORRECT_MESS"]);
                                            $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["INCORRECT_MESS"];
                                       }
                                       break;
                                    }
                                    #   POSITIVE INTEGER    ---------------------------
                                    case "p_integer" :{
                                       if (_p_integer_($_value)){
                                            $this->addError($_valid_field["INCORRECT_MESS"]);
                                            $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["INCORRECT_MESS"];
                                       }
                                       break;
                                    }
                                    #   FLOAT   ---------------------------------------
                                    case "float": {
                                       if (_float_($_value)){
                                            $this->addError($_valid_field["INCORRECT_MESS"]);
                                            $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["INCORRECT_MESS"];
                                       }
                                       break;
                                    }
                                    #   POSITIVE FLOAT  -------------------------------
                                    case "p_float" :{
                                       if (_p_float_($_value)){
                                            $this->addError($_valid_field["INCORRECT_MESS"]);
                                            $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["INCORRECT_MESS"];
                                       }
                                       break;
                                    }
                                    #   EMAIL ADDRESS   ------------------------------
                                    case "email" :{
                                       if (_email_($_value)){
                                            $this->addError($_valid_field["INCORRECT_MESS"]);
                                            $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["INCORRECT_MESS"];
                                       }
                                       break;
                                    }
                                    #   EMAIL ADDRESS   ------------------------------
                                    case "emails" :{
                                       if (_emails_($_value)){
                                            $this->addError($_valid_field["INCORRECT_MESS"]);
                                            $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["INCORRECT_MESS"];
                                       }
                                       break;
                                    }
                                    #   WORD CHARTER    -----------------------------
                                    case "word" :{
                                       if (_word_($_value)){
                                            $this->addError($_valid_field["INCORRECT_MESS"]);
                                            $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["INCORRECT_MESS"];
                                       }
                                       break;
                                    }
                                    #   URL -----------------------------------------
                                    case "url" :{
                                       if (_url_($_value)){
                                            $this->addError($_valid_field["INCORRECT_MESS"]);
                                            $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["INCORRECT_MESS"];
                                       }
                                       break;
                                    }
                                    #   PRICE   -------------------------------------
                                    case "price" :{
                                       if (_price_($_value)){
                                            $this->addError($_valid_field["INCORRECT_MESS"]);
                                            $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["INCORRECT_MESS"];
                                       }
                                       break;
                                    }
                                    #   FILE    -------------------------------------
                                    case "file" :{
                                       if (_file_($_valid_field["FIELD_NAME"],$_valid_field["FILE_TYPE"])){
                                            $this->addError($_valid_field["INCORRECT_MESS"]);
                                            $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["INCORRECT_MESS"];
                                       }
                                       break;
                                    }
                                    #   ALL -----------------------------------------
                                    default          : ;

                                }
                            }
                        }
                        //==========     TYPE     ===============================================

                        //==========  EQUAL_FIELD_NAME  =================================================
                        if (!isset($this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]]) || "" == $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]]){
                            if (isset($_valid_field["EQUAL_FIELD_NAME"]) && "" != $_valid_field["EQUAL_FIELD_NAME"]){
                                $_current_eq = array();
                                $_current_eq = split("\|",$_valid_field["EQUAL_FIELD_NAME"]);
                                $_flag_eq = 0;
                                for ($_i=0;$_i<count($_current_eq);$_i++){
                                    if ( !isset($hash[trim($_current_eq[$_i])]) || $_value != $hash[trim($_current_eq[$_i])] ) $_flag_eq = 1;
                                }
                                if (1 == $_flag_eq){
                                    $this->addError($_valid_field["EQUAL_MESS_ERROR"]);
                                    $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["EQUAL_MESS_ERROR"];
                                }
                            }
                        }
                        //==========  EQUAL_FIELD_NAME  =================================================

                        //==========  UNEQUAL_FIELD_NAME  ===============================================
                        if (!isset($this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]]) || "" == $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]]){
                            if (isset($_valid_field["UNEQUAL_FIELD_NAME"]) && "" != $_valid_field["UNEQUAL_FIELD_NAME"]){
                                $_current_eq = array();
                                $_current_eq = split("\|",$_valid_field["UNEQUAL_FIELD_NAME"]);
                                $_flag_eq = 0;
                                for ($_i=0;$i<count($_current_eq);$_i++){
                                    if ( !isset($hash[trim($_current_eq[$_i])]) || $_value == $hash[trim($_current_eq[$_i])] ) $_flag_eq = 1;
                                }
                                if (1 == $_flag_eq){
                                    $this->addError($_valid_field["UNEQUAL_MESS_ERROR"]);
                                    $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["UNEQUAL_MESS_ERROR"];
                                }
                            }
                        }
                        //==========  UNEQUAL_FIELD_NAME  ===============================================

                        //==========     MIN_VALUE     ===============================================
                        if (!isset($this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]]) || "" == $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]]){
                            if (isset($_valid_field["MIN_VALUE"]) && "" != $_valid_field["MIN_VALUE"] && "" != $_value){
                                if ($hash[$_valid_field["FIELD_NAME"]] < $_valid_field["MIN_VALUE"]){
                                    $this->addError($_valid_field["MIN_MESS_ERROR"]);
                                    $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["MIN_MESS_ERROR"];
                                }
                            }
                        }
                        //==========     MIN_VALUE     ===============================================

                        //==========     MAX_VALUE     ===============================================
                        if (!isset($this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]]) || "" == $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]]){
                            if (isset($_valid_field["MAX_VALUE"]) && "" != $_valid_field["MAX_VALUE"] && "" != $_value){
                                if ($hash[$_valid_field["FIELD_NAME"]] > $_valid_field["MAX_VALUE"]){
                                    $this->addError($_valid_field["MAX_MESS_ERROR"]);
                                    $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["MAX_MESS_ERROR"];
                                }
                            }
                        }
                        //==========     MIN_VALUE     ===============================================

                        //==========     LENGTH     ===============================================
                        if (!isset($this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]]) || "" == $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]]){
                            if (isset($_valid_field["LENGTH"]) && "" != $_valid_field["LENGTH"]){
                                if (strlen($hash[$_valid_field["FIELD_NAME"]]) > $_valid_field["LENGTH"]){
                                    $this->addError($_valid_field["LENGTH_MESS_ERROR"]);
                                    $this->m_ERROR_FIELD["ERROR_".$_valid_field["FIELD_NAME"]] = $_valid_field["LENGTH_MESS_ERROR"];
                                }
                            }
                        }
                        //==========     LENGTH     ===============================================

                    //===========================================================================================================================
                }
            }
        }
        if (count($this->m_ERROR_FIELD) != 0){
            return false;
        }else{
            return true;
        }
    }

    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    //
    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    function setDefaultField($hash)
    {
        if (!isset($hash["DEFAULT"]))            $hash["DEFAULT"] = "undefault";
        if (!isset($hash["TYPE"]))               $hash["TYPE"] = "all";
        if (!isset($hash["MIN_VALUE"]))          $hash["MIN_VALUE"] = "";
        if (!isset($hash["MAX_VALUE"]))          $hash["MAX_VALUE"] = "";
        if (!isset($hash["LENGTH"]))             $hash["LENGTH"] = "";

        if (!isset($hash["EQUAL_FIELD_NAME"]))   $hash["EQUAL_FIELD_NAME"] = "";
        if (!isset($hash["IS_SELECTED"]))        $hash["IS_SELECTED"] = "";
        if (!isset($hash["IS_SELECTED_END_EQ"])) $hash["IS_SELECTED_END_EQ"] = "";
        if (!isset($hash["FILE_TYPE"]))          $hash["FILE_TYPE"] = "";

        if (!isset($hash["EMPTY_MESS"]))         $hash["EMPTY_MESS"] = "";
        if (!isset($hash["INCORRECT_MESS"])      || "" == $hash["INCORRECT_MESS"])        $hash["INCORRECT_MESS"] = $hash["EMPTY_MESS"];
        if (!isset($hash["EQUAL_MESS_ERROR"])    || "" == $hash["EQUAL_MESS_ERROR"])      $hash["EQUAL_MESS_ERROR"] = $hash["INCORRECT_MESS"];
        if (!isset($hash["UNEQUAL_MESS_ERROR"])  || "" == $hash["UNEQUAL_MESS_ERROR"])    $hash["UNEQUAL_MESS_ERROR"] = $hash["INCORRECT_MESS"];
        if (!isset($hash["LENGTH_MESS_ERROR"])   || "" == $hash["LENGTH_MESS_ERROR"])     $hash["LENGTH_MESS_ERROR"] = $hash["INCORRECT_MESS"];
        if (!isset($hash["MIN_MESS_ERROR"])      || "" == $hash["MIN_MESS_ERROR"])        $hash["MIN_MESS_ERROR"] = $hash["INCORRECT_MESS"];
        if (!isset($hash["MAX_MESS_ERROR"])      || "" == $hash["MAX_MESS_ERROR"])        $hash["MAX_MESS_ERROR"] = $hash["INCORRECT_MESS"];
        return $hash;
    }

    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    //
    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    function getErrorMess($mess_name)
    {
        if (isset($this->m_error_messages) && isset($this->m_error_messages[$mess_name])){
            return $this->m_error_messages[$mess_name];
        }else{
            return "Undefined Message";
        }
    }

    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    //
    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    function getResult($hash,$text)
    {
        $replace_array = array();
        $replace_index = 0;
        $split = preg_split("/AND|OR/",$text);
        for ($j=0; $j<count($split); $j++){
            $split[$j] = str_replace("(","",$split[$j]);
            $split[$j] = str_replace(")","",$split[$j]);
            $split[$j] = trim($split[$j]);
            if ($split[$j] !== "true" && $split[$j] != "false" && $split[$j] != ""){
                $replace_array[$replace_index]["replace"] = $split[$j];
                #   DETECT COMAND   -------------------------------
                preg_match_all("/(.*?)\[(.*?)\]/",$split[$j],$tmp);
                $command = $tmp[1][0];
                $varable = $tmp[2][0];
                switch ($command){
                    case "eq" : {
                        list($field,$field_var) = split("=",$varable,2);
                        if (isset($hash[$field]) && $hash[$field] == $field_var) $replace_array[$replace_index]["value"] = "true";
                        else $replace_array[$replace_index]["value"] = "false";
                        break;
                    }
                    case "noteq" : {
                        list($field,$field_var) = split("=",$varable,2);
                        if (isset($hash[$field]) && $hash[$field] != $field_var) $replace_array[$replace_index]["value"] = "true";
                        else $replace_array[$replace_index]["value"] = "false";
                        break;
                    }
                    case "isset" : {
                        if (isset($hash[$varable])) $replace_array[$replace_index]["value"] = "true";
                        else $replace_array[$replace_index]["value"] = "false";
                        break;
                    }
                    case "notisset" : {
                        if (!isset($hash[$varable])) $replace_array[$replace_index]["value"] = "true";
                        else $replace_array[$replace_index]["value"] = "false";
                        break;
                    }
                    case "is_empty" : {
                        if (isset($hash[$varable]) && $hash[$varable] == "") $replace_array[$replace_index]["value"] = "true";
                        else $replace_array[$replace_index]["value"] = "false";
                        break;
                    }
                    case "is_notempty" : {
                        if (isset($hash[$varable]) && $hash[$varable] != "") $replace_array[$replace_index]["value"] = "true";
                        else $replace_array[$replace_index]["value"] = "false";
                        break;
                    }
                }
                #**************************************************
                $replace_index++;
            }
        }
        for ($j=0; $j<count($replace_array); $j++){
            $text = str_replace($replace_array[$j]["replace"],$replace_array[$j]["value"],$text);
        }
        return @eval("if(".$text."){return 'true';}else{return 'false';}");
    }
    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    //
    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
}


//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

function sort_array ($a, $b)
{
    if ($a["level"] == $b["level"]) return 0;
    return ($a["level"] > $b["level"]) ? -1 : 1;
}

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
