// Функция делает checkbox с id=Check_ID отмеченным
function new_c(Check_ID)
{
        document.getElementById(Check_ID).checked=true;
        return false;
}


// Очищает поля с именем файла при выборе нового файла
function upload_file_name_change (clean_field)
{
        document.getElementById(clean_field).value = "";
}

// Открывает окно вспомогательного арма
function ow(path)
{
        win_height = screen.height-100;
        win_width = ((5*screen.width)/6);
        w=window.open(path, window.name,"status=yes,scrollbars=yes,resizable=yes,width="+win_width+",height="+win_height+",top=0, left=0");
        w.focus();
        return false;
}

// Открывает окно для просмотра файла
function preview1(path, id)
{
        full_path = path+document.getElementById(id).value;
        w=window.open(full_path, window.name,"status=yes,scrollbars=yes,resizable=yes,width=630,height=360");
        w.focus();
        return false;
}

// Удаляет выбранную запись (выбранную с помощью вспомогательного арма)
function del_record (id)
{
        if ( confirm('Удалить ?'))
        {
                close_flag = true;
        }
        else
        {
               close_flag = false;
        }
        if (close_flag == true)
        {
            document.getElementById(id).value = "";
            document.getElementById(id+'name').value = "";
        }
}


// Записывает id и имя сущности в открывающее окно
function select_entity(entity_id, entity_name, control_id, addit_id,wi,hi)
{
        //alert(entity_id+', '+entity_name+', '+control_id+', '+addit_id);
        close_flag = true;
        winopen = window.opener;
        if ( confirm('Выбрана сущность '+entity_name+'            \nЗакрыть окно?'))
        {
                self.close();
        }
        else
        {
                close_flag = false;
		return false;
        }
        if (close_flag == true)
        {
            if(typeof(window.opener.HandleError)=="undefined"){
            winopen.document.getElementById(control_id).value = entity_id;
            winopen.document.getElementById(control_id+"_name").value = entity_name;
            winopen.LookUpWindowset(control_id);//показывать ссылку превью
				/*
				if(addit_id!="" && typeof(addit_id)!='undefined'){
				winopen.new_c('mark'+addit_id);
				}
				*/
			}
			else
			{
			window.opener.setPixx(entity_id,wi,hi,entity_name);
			//window.opener.setPixx(entity_id,'','',entity_name);
			}
        }
}

// Вяделяет или снимает выделения со всех checkbox id которых начинается с mark_
function select_all_entity(markall, count)
{
//alert('markall='+markall+', count='+count);
var markall2;
        if (document.getElementById(markall).checked == true)
                checkdo = true;
        else
                checkdo = false;

        for (i=0, k=0; k<count; i++)
        {
                if(typeof(document.getElementById('mark_'+i))!="undefinded" && document.getElementById('mark_'+i)!=null)
                   {
                   //alert('i='+i+', '+typeof(document.getElementById('mark_'+i))+', '+document.getElementById('mark_'+i));
                   if (checkdo == true)
                        document.getElementById('mark_'+i).checked = true;
                   else
                        document.getElementById('mark_'+i).checked = false;
                   k++;
                   }
        }

        if (markall == "markall")
                markall2 = "markall2"
        else
                markall2 = "markall"

        if (checkdo == true)
                document.getElementById(markall2).checked = true
        else
                document.getElementById(markall2).checked = false
}

// Проверяет что бы поля pass1 и pass2 были равны

function check_passwords (formname, pass1, pass2)
{
        if (document.getElementById(pass1))
        {
                if (document.getElementById(pass1).value != document.getElementById(pass2).value)
                {
                          alert("Введенные пароли не совпадают!");
                          //formname.reset();
                          return false;
                }
                else
                {
                        return true;
                }
        }
}
//Функция вызывается из дочернего окна и вставляет значение в нужное поле
//принимает параметры id поля и значение
function set_field(name, val){
//alert(name+'='+val);
document.getElementById(name).value=val;
}

//Функция вызывается из дочернего окна и вставляет значение в нужный div
//принимает параметры id div и значение
function set_div(div_id, val){
//alert(div_id+'='+val);
document.getElementById(div_id).innerHTML=val;
}

//функция вносит изменения в название сущности и рисует ссылку предпросмотра
function LookUpWindowset(control_name){
var control_name_view=control_name+'_view';
var control_name_view_href=control_name+'_view_href';


var control_name_view_href_start=document.getElementById(control_name_view_href).href;
var repl=/&our_ent_id=\d/;
control_name_view_href_start=control_name_view_href_start.replace(repl, '');

if(document.getElementById(control_name).value)
    {
    document.getElementById(control_name_view).style.display='';
    document.getElementById(control_name_view_href).href=control_name_view_href_start+'&our_ent_id='+document.getElementById(control_name).value;
    }
else
    {
    document.getElementById(control_name_view).style.display='none';
    document.getElementById(control_name_view_href).href=control_name_view_href_start;
    }

}
/*
function test_html()
{
num=document.edit_form.elements.length;
num=num-2;
alert("num="+num);

for(i=0; i<=num; i++)
    {
	if(document.edit_form.elements[i].name && document.edit_form.elements[i].value)
		{
		alert(document.edit_form.elements[i].name+'='+document.edit_form.elements[i].value);
		}
	}

}
*/