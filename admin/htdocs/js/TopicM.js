<!--
//
function TopicMset(control, control_id){
var ret='';
var retHTML='';
var control_view=control_id+'_view';
var i;
var i_sel=0;
winopen = window.opener;
//alert('control='+control);
for(i=0; i<control.length; i++){
    if(control.options[i].selected==true)
       {
       retHTML+='<option>'+control.options[i].id+'</option>';
       if(ret)
          ret+=',';
       ret+=control.options[i].value;
       i_sel++;
       }
    }
retHTML='<select multiple size='+i_sel+' disabled>'+retHTML+'</select>';
//alert('retHTML='+retHTML);
winopen.set_field(control_id, ret);
winopen.set_div(control_view, retHTML);
window.close();
return false;
}
//конец TopicMset
//=======================================================================
function TopicMset_begin(control, control_id){
var control_view=control_id+'_view';
var i, k;
winopen = window.opener;
//alert('control_id='+control_id);
var setsel=winopen.document.getElementById(control_id).value;
//alert('setsel='+setsel);
setsel_ar=setsel.split(',');

for(i=0; i<control.length; i++){
    for(k=0; k<setsel_ar.length; k++){
        if(control.options[i].value==setsel_ar[k])
           {
           control.options[i].selected=true;
           }
        }
    }

}
//-->