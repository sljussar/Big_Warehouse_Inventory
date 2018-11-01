<?php
	require_once('inventory.common.php');
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head id=CONTENIDO_HEADER>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Inventory System</title>
<script type="text/javascript"> if(browser == IE6){</script>
<LINK rel="stylesheet" type="text/css" media="screen" href="estilos/mobile.css">
<script type="application/javascript"> }else{ </script>
<LINK rel="stylesheet" type="text/css" media="screen" href="estilos/mobile.css">
<script type="application/javascript"> } </script>
<?php  $xajax->printJavascript('../xajax/');?>
<script type="text/javascript" src="jquery-1.3.2.min.js"></script>
<script>

// Mouseover/ Click sound effect- by JavaScript Kit (www.javascriptkit.com)
// Visit JavaScript Kit at http://www.javascriptkit.com/ for full source code

//** Usage: Instantiate script by calling: var uniquevar=createsoundbite("soundfile1", "fallbackfile2", "fallebacksound3", etc)
//** Call: uniquevar.playclip() to play sound

var html5_audiotypes={ //define list of audio file extensions and their associated audio types. Add to it if your specified audio file isn't on this list:
	"mp3": "audio/mpeg",
	"mp4": "audio/mp4",
	"ogg": "audio/ogg",
	"wav": "audio/wav"
}

function createsoundbite(sound){
	var html5audio=document.createElement('audio')
	if (html5audio.canPlayType){ //check support for HTML5 audio
		for (var i=0; i<arguments.length; i++){
			var sourceel=document.createElement('source')
			sourceel.setAttribute('src', arguments[i])
			if (arguments[i].match(/\.(\w+)$/i))
				sourceel.setAttribute('type', html5_audiotypes[RegExp.$1])
			html5audio.appendChild(sourceel)
		}
		html5audio.load()
		html5audio.playclip=function(){
			html5audio.pause()
			html5audio.currentTime=0
			html5audio.play()
		}
		return html5audio
	}
	else{
		return {playclip:function(){throw new Error("Your browser doesn't support HTML5 audio unfortunately")}}
	}
}

//Initialize two sound clips with 1 fallback file each:

var mouseoversound=createsoundbite("whistle.ogg", "censor-beep-01.mp3")
var clicksound=createsoundbite("click.ogg", "click.mp3")

</script>

</head>

<body onload="xajax_busca_opcion('position'); document.getElementById('position').focus()">
 <div align="left"><h2><?php echo 'Welcome '. $_SESSION['name'].'  ';?></h2>
 
   
     <input name="inventory" type="button" value="Inventory" onclick="xajax_busca_opcion('position'); " />
     <?php if ($_SESSION['id_user']==1){?>
     <input name="positions" type="button" value="Positions" onclick="xajax_busca_opcion('positions');" />
     <?php }?>
       
     <?php if ($_SESSION['id_user']==1){?>
     <input name="reports" type="button" value="Reports" onclick="xajax_busca_opcion('reports');" />
     <?php }?>
      <?php if ($_SESSION['id_user']==1){?>
     <input name="sync" type="button" value="Sync" onclick="xajax_sync_products();" />
     <?php }?>
       
       
     <input name="logout" type="button" value="Logout" onclick="xajax_salir();" />
       
     
   
 </div>
 <div align="center"></div>
<div id="logo">


</div>
 

 <div id="operativo"></div><!--aqui se cargan los formularios para trabajar-->
 <div id="inventory"></div><!--aqui se cargan los formularios para trabajar-->


</body>


</html>
