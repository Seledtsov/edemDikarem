/////////////////// Plug-in file for CalendarXP 7.0 /////////////////
// This file is totally configurable. You may remove all the comments in this file to shrink the download size.
/////////////////////////////////////////////////////////////////////

///////////// Calendar Onchange Handler ////////////////////////////
// It's triggered whenever the calendar gets changed.
// d = 0 means the calendar is about to switch to the month of (y,m);
// d > 0 means a specific date [y,m,d] is about to be selected.
// Return a true value will cancel the change action.
// NOTE: DO NOT define this handler unless you really need to use it.
////////////////////////////////////////////////////////////////////
function fOnChange(y,m,d) {
        if (d>0) fDemoSetDate(y,m,d);
        return false;  // return true to cancel the change.
}


///////////// Calendar AfterSelected Handler ///////////////////////
// It's triggered whenever a date gets fully selected.
// The selected date is passed in as y(ear),m(onth),d(ay)
// NOTE: DO NOT define this handler unless you really need to use it.
////////////////////////////////////////////////////////////////////
function fAfterSelected(y,m,d) {
        if (gdCtrl.name=="dc2")
                gdCtrl.form.textarea.value=gdCtrl.value+' '+gdCtrl.form.timec.options[gdCtrl.form.timec.selectedIndex].value;
}

///////////// Calendar Cell OnDrag Handler ///////////////////////
// It triggered when you try to drag a calendar cell. (y,m,d) is the cell date.
// aStat = 0 means a mousedown is detected (dragstart)
// aStat = 1 means a mouseover between dragstart and dragend is detected (dragover)
// aStat = 2 means a mouseup is detected (dragend)
// Return true to skip the set date action, if any.
// NOTE: DO NOT define this handler unless you really need to use it.
////////////////////////////////////////////////////////////////////
// function fOnDrag(y,m,d,aStat) {}


// ====== Following are self-defined and/or custom-built functions! =======
var _dc, _mc, _yc;
function fDemoPopArrive(dayc,monc,yearc,dc1,dc2) {
  _dc=dayc; _mc=monc; _yc=yearc;
  dc1.value=fFormatDate(_yc.value,_mc.value,_dc.value);
  var sd=fParseDate(dc2.value);
  var range=(sd+''==[2001,6,20]+'')?[[2001,5,2],[2001,6,10],sd]:[];
  fPopCalendar(dc1, range, null, yearc);
}
function fDemoPopDepart(dc1,dc2) {
  var sd=fParseDate(dc1.value);
  var range=(sd+''==[2001,6,6]+'')?[[2001,6,10],[2001,7,20],sd,[2001,6,13]]:[];
  fPopCalendar(dc2, range);
}
function fDemoSetDate(y,m,d) {
        if (gdCtrl.name=="dc1")        {_dc.value=d; _mc.value=m; _yc.value=y;};
}