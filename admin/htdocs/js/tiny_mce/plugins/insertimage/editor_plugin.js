(function(){tinymce.PluginManager.requireLangPack("insertimage");tinymce.create("tinymce.plugins.insertimagePlugin",{init:function(a,b){a.addCommand("mceinsertimage",function(){a.windowManager.open({file:b+"/dialog.htm",width:320+parseInt(a.getLang("insertimage.delta_width",0)),height:120+parseInt(a.getLang("insertimage.delta_height",0)),inline:1},{plugin_url:b,some_custom_arg:"custom arg"})});a.addButton("insertimage",{title:"insertimage.desc",cmd:"mceinsertimage",image:b+"/img/icon.gif"});a.onNodeChange.add(function(d,c,e){c.setActive("insertimage",e.nodeName=="IMG")})},createControl:function(b,a){return null},getInfo:function(){return{longname:"insertimage plugin",author:"Some author",authorurl:"http://tinymce.moxiecode.com",infourl:"http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/example",version:"1.0"}}});tinymce.PluginManager.add("insertimage",tinymce.plugins.insertimagePlugin)})();