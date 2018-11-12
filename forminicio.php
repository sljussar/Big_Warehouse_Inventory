<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Inventory System</title>

</head>
<body >
<script defer src="ie_onload.js" type="text/javascript">

	
	</script>
</br>
<div align="center"></div>
<form action="index.php" method="post"  name="inicio">
  
  <table width="138" height="81" border="1" align="center">
   <tr>
      <th scope="col" align="left">Warehouse:</th></tr>
      <tr>
      <th scope="col"><select name="warehouse"id="warehouse" >
      <option value="000">000-Main Warehouse</option>
      <option value="100">100-FTZ</option>
      </select></th>
    </tr>
    <tr>
      <th scope="col" align="left">User:</th>
      </tr><tr>
      <th scope="col"><input name="login" type="text" id="login" OnKeyup="if (window.event.keyCode == 13)clave.focus();"/></th>
    </tr>
    <tr>
      <th scope="col" align="left">Password:</th></tr><tr>
	  <th scope="col"><input name="clave" type="password" id="clave" OnKeyup="if (window.event.keyCode == 13)enter.focus();" /></th>
    </tr>
  </table>
  <table width="138" border="1" align="center">
    <tr>
      <th width="138" scope="col"><div align="center">
         <input name="enter" type="button" value="Login" onclick="inicio.submit();" />
        <input name="delete" type="reset" value="Delete" />
      </div></th>
    </tr>
  </table>
 
</form>
<?php //echo md5('1234').'<-user1';
?>

</body>
</html>
