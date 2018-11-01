<?php 

//this is the first form for the position
function dig_form_position($pg)
{	
	include('conf.php');
	$warehouse=$_SESSION['warehouse'];
	
	$user=$_SESSION['id_user'];						
	$cad='<div id="principal" align="left"><h1>NEW POSITION</h1><form id="FormPosition" name="FormPosition" method="post" action="" onSubmit="return false;"  >
		  <table width="290px">
			<tr>
			 
			   <th align="right" width="20%"><h1>Position:</h1> </th>
			 <th align="left" >
					 <input name="position" type="text" id="position" size="20" OnKeyup="if (window.event.keyCode == 13)xajax_guarda_posicion(position.value,'.$warehouse.','.$user.');" onChange="xajax_guarda_posicion(position.value,'.$warehouse.','.$user.');" />
			  </th>
			  </tr> 
			';
			
		$cad.= "</table>\n<br><br><br>";
		$cad.= "<table  align=\"center\">\n";	   
		$cad.='<tr>
			   <th><INPUT type="button" name="opcion" value="Save" id="opcion" ';
	$cad.=' onClick="xajax_guarda_posicion(position.value,'.$warehouse.','.$user.');" />';
	$cad.='</th>
				 </tr>';
		$cad.= "</table>\n </form></div>";
	
				
	return utf8_encode($cad);
}
//aqui se valida que exista la posicion y que este abierta y se carga el formulario de inventario
function guarda_posicion($position,$warehouse,$user)

{
	@session_start();
	include('conf.php');

	$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
	$db_selected =mysql_select_db($dbname,$pg);
			
	$warehouse=$_SESSION['warehouse'];	
	
	$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el navegador
		

	$sql="SELECT position FROM position WHERE ";
	$sql.="STRCMP(position,'".$position;
	$sql.="')=0 and STRCMP(id_warehouse,'".$warehouse;
	$sql.="')=0 and close=0";
		//echo $sql;
			$result = mysql_query($sql, $pg);
			$n = mysql_num_rows($result);
			
			if($n==1){
			
	//Si la posicion existe se debe cargar el inventario
	$cad=dig_form_inventory($pg,$position,$warehouse,$user);
	$objResponse->addAssign("operativo", "innerHTML", $cad);
	
	$objResponse->addScript('setTimeout(function() { document.getElementById(\'code\').focus(); }, 10);');
	//$objResponse->addScript('document.getElementById("code").focus()');
	
	//se carga el inventario
	//$cad2=dig_form_inventory_location($datos['position'], $user,$warehouse);
	//$objResponse->addAssign("inventory", "innerHTML", $cad2);
	
	}else{
	$objResponse->addAlert("This position does not exists or is close");
	
	}	
	return $objResponse;
}
//este es el formulario del inventario
function dig_form_inventory($pg,$position,$warehouse,$user)
{	
	include('conf.php');			

	//$position=$datos['position'];
				
	$cad='<h1>POSITION '.strtoupper($position).'</h1><form id="FormInventory" name="FormInventory"  method="post" onSubmit="return false;" >
		 
		  <table align="left" width="280px">
			<tr >
			 
			   <th align="left" width="15%"><h1>Code:</h1> </th>
			 <th  align="left">
					 <input name="code" type="text" id="code" size="20" OnKeyup="if (window.event.keyCode == 13)xajax_valida_codigo(code.value);" onchange= "xajax_valida_codigo(code.value);" />
			  </th>
			</tr>
			  <tr>
			 
			   <th  ><h1>Desc:</h1> </th>
			 <th align="left">
					<textarea name="description" cols="20" rows="3" id="description" readonly></textarea> 
			  </th>
			
			 
			  </tr>
			  
			   <tr>
			 
			   <th align="left" ><h1>Qty x Box:</h1></th><th align="left">
					 <input name="num" type="text" id="num" size="10" OnKeyup="if (window.event.keyCode == 13) document.getElementById(\'boxes\').focus();" onchange= "document.getElementById(\'boxes\').focus();" />
			  </th>
			
			 
			  </tr>
			   <tr>
			 
			   <th align="left"><h1>Boxes:</b> </h1><th align="left">
					 <input name="boxes" type="text" id="boxes" size="10" OnKeyup="if (window.event.keyCode == 13)xajax_calcula_cantidad( boxes.value, num.value);" onchange="xajax_calcula_cantidad( boxes.value, num.value);"  />
			  </th>
			
			  </tr>
			  <tr>
			 
			   <th align="left"><h1>Total:</h1>  </th><th align="left">
					 <input name="quantity" type="text" id="quantity" size="10"  OnKeyup="if (window.event.keyCode == 13)xajax_valida_cantidad( \''.$position.'\' , code.value, description.value, boxes.value, num.value, quantity.value, '.$user.', \''.$warehouse.'\');" onchange= "xajax_valida_cantidad( \''.$position.'\' , code.value, description.value, boxes.value, num.value, quantity.value, '.$user.', \''.$warehouse.'\');"  />
			  </th>
			
			 
			  </tr>
			  
			  
			';
			
		$cad.='</table><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br></form>';
		
		$cad.='<div id="inventory" >';
			$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);

	//aqui debe ir la lista de lo que ya se ha cargado en esta posicion
		$sql="SELECT * FROM inventory WHERE STRCMP(pos_id,'".$position."')=0 and user_id=".$user." and STRCMP(id_warehouse,'".$warehouse."')=0 order by id DESC";
		//	echo $sql;
		$db_selected =mysql_select_db($dbname,$pg);	
		$result = mysql_query($sql, $pg);
		$num= mysql_num_rows($result);
		if($num>0){
				$tabla = get_html_translation_table(HTML_ENTITIES);
				$cad.="<input name=\"close\" type=\"button\" value=\"Close Position\" onclick=\"xajax_close_position('".$position."', '".$warehouse."');\" />";
		
		
		$cad.= '<table ><tr><th><h1>Inventory</h1></th></tr></table> <table  align="left" border="1" width="40%">';
			
				$cad.= "<tr>\n";	
					$cad.= "<td  align=\"center\"><b>#</b></td>\n";
					$cad.= "<td align=\"center\"><b>Code</b></td>\n";
					//$cad.= "<td align=\"center\"><b>Descrip</b></td>\n";
					$cad.= "<td align=\"center\"><b>Boxes</b></td>\n";
					$cad.= "<td align=\"center\"><b>Qty x Box</b></td>\n";
					$cad.= "<td align=\"center\"><b>Qty</b></td>\n";
					
					$cad.= "<td class=\"no_imp\">X</td>\n";
					
				$cad.= "</tr>\n";
			
			$i=0;
				
				while ($row = mysql_fetch_assoc($result))
				{	
				$i++;
				
				$sql2="SELECT description from products where STRCMP(id_product,'".$row['prod_id']."')=0";//se selecciona la descripcion del producto
							$db_selected =mysql_select_db($dbname,$pg);
							$result2 = mysql_query($sql2, $pg);
							$row2=mysql_fetch_assoc($result2);
							
							
								
					$cad.= "<tr>\n";
						$cad.= "<td align=\"center\">".$i."</td>\n";
						$cad.= "<td align=\"center\">".strtr($row["prod_id"],$tabla)."</td>\n";
						//$cad.="<td align=\"center\">".strtr($row2["description"],$tabla)."</td>\n";
						$cad.="<td align=\"center\">".$row['boxes']."</td>\n";
						$cad.="<td align=\"center\">".$row['qtyxb']."</td>\n";
						$cad.="<td align=\"center\">".$row['quantity']."</td>\n";
						$cad.= "<td align=\"center\"><a onClick=\"xajax_elimina_inventario(".$row['id'].",'".$position."' ,".$user.");\"><img src=\"images\delete.gif\" width=\"12\" height=\"12\" alt=\"delete\" /></a></td>\n";
						
					$cad.= "</tr>\n";
					
					
					
					
				}      
			
		$cad.= "</table></br></br></br></br></br></br></br></br></div>";}else{
			$cad.="<input name=\"close\" type=\"button\" value=\"Close Position\" onclick=\"if(confirm('DO YOU WANT TO CLOSE AN EMPTY POSITION?'))xajax_close_position('".$position."', '".$warehouse."');\" /></br>";
		
			
		$cad.='Empty position for this group</div>';
		}
	
		
	
				
	return utf8_encode($cad);
}
function valida_codigo($code){

		include('conf.php');
		$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
		$sql="SELECT * FROM products WHERE STRCMP(id_product,'".$code."')=0";
		//echo $sql;
		$db_selected =mysql_select_db($dbname,$pg);	
		$result = mysql_query($sql, $pg);
		$num= mysql_num_rows($result);
			$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el cliente
			if ($num!=0){//Si encuentra el producto pone descripcion
				$row = mysql_fetch_assoc($result);
				$description=utf8_encode($row['description']);
				
				$objResponse->addAssign("description", "value", $description);
				
				$objResponse->addScript('document.getElementById("num").focus()');
			
			$objResponse->addScript('mouseoversound.playclip();');

				
				}else {
				$objResponse->addAssign("description", "value", ''); }	
				
				
				return $objResponse;


}
//calcula cantidad total cajas x unidadesxcaja
function calcula_cantidad($boxes, $num){
			include('conf.php');
		
			$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el cliente
			
				$total=$boxes*$num;
				$objResponse->addAssign("quantity", "value", $total);
				$objResponse->addScript('document.getElementById("quantity").focus()');
			
			$objResponse->addScript('mouseoversound.playclip();');

				
				
				
				return $objResponse;
}

function dig_form_positions($pg)
{	
	include('conf.php');			

			$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);

	//aqui debe ir la lista de las posiciones cerradas
		$sql="SELECT * FROM position WHERE close=1";
		//echo $sql;
		$db_selected =mysql_select_db($dbname,$pg);	
		$result = mysql_query($sql, $pg);
		$num= mysql_num_rows($result);
		if($num>0){
				//$tabla = get_html_translation_table(HTML_ENTITIES);
		
		$cad= '<h1>Closed Positions </h1><table  align="center" border="1" >';
			
				$cad.= "<tr>\n";
					$cad.= "<td align=\"center\"><b>Warehouse</b></td>\n";
					$cad.= "<td align=\"center\"><b>Position</b></td>\n";
					$cad.= "<td align=\"center\"><b>Details</b></td>\n";
					$cad.= "<td align=\"center\"><b>Open</b></td>\n";
					$cad.= "</tr>\n";
			
				
				while ($row = mysql_fetch_assoc($result))
				{	
			
				$row['position']=substr($row['position'], 0, 5);
								
					$cad.= "<tr>\n";
						$cad.= "<td align=\"center\">".$row['id_warehouse']."</td>\n";
						$cad.= "<td align=\"center\">".$row['position']."</td>\n";
						$cad.= "<td align=\"center\"><a onClick=\"xajax_detalla_posicion( '".$row['position']."', '".$row['id_warehouse']."');\" >Details</a></td>\n";
						$cad.= "<td align=\"center\"><a onClick=\"xajax_open_position( '".$row['position']."', '".$row['id_warehouse']."');\" >OPEN</a></td>\n";
						
					$cad.= "</tr>\n";
					
					//Ahora vamos a detallar los envios de cada PO
					
					
				}      
			
		$cad.= "</table></div>\n";}else{
		$cad='Empty closed positions</br></div>';
		}
	
	//aqui debe ir la lista de las posiciones abiertas con inventario
		$sql="SELECT DISTINCT position.id_warehouse as id_warehouse, position.position as position FROM position, inventory WHERE close=0 and STRCMP(position, pos_id)=0 and STRCMP(position.id_warehouse, inventory.id_warehouse)=0 order by position.id_warehouse, position";
		//echo $sql;
		$db_selected =mysql_select_db($dbname,$pg);	
		$result = mysql_query($sql, $pg);
		$num= mysql_num_rows($result);
		if($num>0){
				$tabla = get_html_translation_table(HTML_ENTITIES);
		
		$cad.= '<h1>Open Positions </h1><table  align="center" border="1" >';
			
				$cad.= "<tr>\n";
					$cad.= "<td align=\"center\"><b>Warehouse</b></td>\n";
					$cad.= "<td align=\"center\"><b>Position</b></td>\n";
					$cad.= "<td align=\"center\"><b>Details</b></td>\n";
					$cad.= "<td align=\"center\"><b>Open</b></td>\n";
					$cad.= "</tr>\n";
			
				
				while ($row = mysql_fetch_assoc($result))
				{	
			
				
								
					$cad.= "<tr>\n";
						$cad.= "<td align=\"center\">".strtr($row['id_warehouse'],$tabla)."</td>\n";
						$cad.= "<td align=\"center\">".strtr($row['position'],$tabla)."</td>\n";
						$cad.= "<td align=\"center\"><a onClick=\"xajax_detalla_posicion('".$row['position']."', '".$row['id_warehouse']."');\">Details</a></td>\n";
						$cad.= "<td align=\"center\"><a onClick=\"if(confirm('DO YOU WANT TO CLOSE AN EMPTY POSITION?'))xajax_close_position('".$row['position']."','".$row['id_warehouse']."');\">CLOSE2</a></td>\n";
						
					$cad.= "</tr>\n";
					
					//Ahora vamos a detallar los envios de cada PO
					
					
				}      
			
		$cad.= "</table></div>\n";}else{
		$cad.='Empty open positions</div>';
		}
		
		//aqui debe ir la lista de las posiciones vacias
		$tabla = get_html_translation_table(HTML_ENTITIES);
		
		        $cad.= '<h1>Empty Positions </h1><table  align="center" border="1" >';
			
				$cad.= "<tr>\n";
					$cad.= "<td align=\"center\"><b>Warehouse</b></td>\n";
					$cad.= "<td align=\"center\"><b>Position</b></td>\n";
					$cad.= "</tr>\n";
			
		$sql="SELECT id_warehouse, position FROM position WHERE close=0 order by id_warehouse, position";
		//echo $sql;
		$db_selected =mysql_select_db($dbname,$pg);	
		$result = mysql_query($sql, $pg);
		$num= mysql_num_rows($result);
		while ($row = mysql_fetch_assoc($result))//aqui revisamos si existe en el inventario
				{	
			$sql2="SELECT id_warehouse, pos_id FROM inventory WHERE STRCMP(pos_id,'".$row['position']."')=0 and STRCMP(id_warehouse,'".$row['id_warehouse']."')=0 order by id_warehouse, pos_id";
		//echo $sql2;
		$db_selected =mysql_select_db($dbname,$pg);	
		$result2 = mysql_query($sql2, $pg);
		$num2= mysql_num_rows($result2);
		if($num2==0){				
					$cad.= "<tr>\n";
						$cad.= "<td align=\"center\">".strtr($row["id_warehouse"],$tabla)."</td>\n";
						$cad.= "<td align=\"center\">".strtr($row["position"],$tabla)."</td>\n";
						
					$cad.= "</tr>\n";
					}      
			
		}
		$cad.= "</table></div>\n";
		
	
				
	return utf8_encode($cad);
}





function dig_form_inventory_location($position, $user,$warehouse){
		@session_start();
		include('conf.php');
		$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
		$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el cliente

	//aqui debe ir la lista de lo que ya se ha cargado en esta posicion
		$sql="SELECT * FROM inventory WHERE STRCMP(pos_id,'".$position."')=0 and user_id=".$user." and STRCMP(id_warehouse,'".$warehouse."')=0 order by id DESC;";
		//echo $sql;
		$db_selected =mysql_select_db($dbname,$pg);	
		$result = mysql_query($sql, $pg);
		$num= mysql_num_rows($result);
		if($num>0){
				$tabla = get_html_translation_table(HTML_ENTITIES);
		
		$cad= '<table><tr><th><h1>Inventory</h1></th></tr></table> <table  align="left" border="1">';
			
				$cad.= "<tr>\n";	
					$cad.= "<td align=\"center\"><b>#</b></td>\n";
					$cad.= "<td align=\"center\"><b>Code</b></td>\n";
					$cad.= "<td align=\"center\"><b>Description</b></td>\n";
					$cad.= "<td align=\"center\"><b>Boxes</b></td>\n";
					$cad.= "<td align=\"center\"><b>Qty x Box</b></td>\n";
					$cad.= "<td align=\"center\"><b>Quantity</b></td>\n";
					
					$cad.= "<td class=\"no_imp\">X</td>\n";
					
				$cad.= "</tr>\n";
			
			$i=0;
				
				while ($row = mysql_fetch_assoc($result))
				{	
				$i++;
				
				$sql2="SELECT description from products where STRCMP(id_product,'".$row['prod_id']."')=0";//se selecciona la descripcion del producto
							$db_selected =mysql_select_db($dbname,$pg);
							$result2 = mysql_query($sql2, $pg);
							$row2=mysql_fetch_assoc($result2);
							
							
								
					$cad.= "<tr>\n";
						$cad.= "<td align=\"center\">".$i."</td>\n";
						$cad.= "<td align=\"center\">".strtr($row["prod_id"],$tabla)."</td>\n";
						$cad.="<td align=\"center\">".strtr($row2["description"],$tabla)."</td>\n";
						$cad.="<td align=\"center\">".$row['boxes']."</td>\n";
						$cad.="<td align=\"center\">".$row['qtyxb']."</td>\n";
						$cad.="<td align=\"center\">".$row['quantity']."</td>\n";
						$cad.= "<td align=\"center\"><a onClick=\"xajax_elimina_inventario(".$row['id'].",'".$position."' ,".$user.");\">X</a></td>\n";
						
					$cad.= "</tr>\n";
					
					
					
					
				}      
			
		$cad.= "</table></br></br></br></br></br></br></br></br>";}else{
		$cad='Empty position for this group';
		}
		//$objResponse->addAlert($cad);
		$cad=utf8_encode($cad);
		$objResponse->addAssign("inventory", "innerHTML", $cad);

return $objResponse;

}


function valida_cantidad($position, $code , $descri,$boxes,$qtyxb, $quantity, $user, $warehouse){
//echo'entro';
		include('conf.php');
		//$warehouse=$_SESSION['warehouse'];
		$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
		$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el cliente
		//validamos que sea un producto existente
		if($descri==''){
		$objResponse->addAlert("Please insert a valid product");
		$objResponse->addAssign("quantity", "value", '');
		//$objResponse->addScript('code.focus();');
		$objResponse->addScript('document.getElementById("code").focus()');
		}else{
		
		if(is_numeric($quantity)==1){
		
		
		//entonces salvamos el producto en el inventario
		$sql="INSERT INTO inventory(pos_id, prod_id, boxes,qtyxb, quantity, user_id, id_warehouse) VALUES( ";
		$sql.="'".$position."', ";
		$sql.="UPPER('".$code."'), ";
		$sql.=$boxes.", ";
		$sql.=$qtyxb.", ";
		$sql.=$quantity.", ";
		$sql.=$user.", ";
		$sql.="'".$warehouse."'); ";
		
	//echo $sql;
		$db_selected =mysql_select_db($dbname,$pg);	
		$result=mysql_query($sql,$pg);
			//echo $result.'<-result';
		
		/*	
		//despues de agregar se vuelve a llamar a la funcion para agregar nuevas cajas a la posicion
		$objResponse->addAssign("description", "value", '');
		$objResponse->addAssign("code", "value", '');
		$objResponse->addAssign("boxes", "value", '');
		$objResponse->addAssign("num", "value", '');
		$objResponse->addAssign("quantity", "value", '');
		//$script="code.focus();";
		//$objResponse->addScript($script);
		$objResponse->addScript('document.getElementById("code").focus()');
		$objResponse->addScript('xajax_dig_form_inventory_location(\''.$position.'\', '.$user.', \''.$warehouse.'\');');
		*/
		
		$objResponse->addScript('xajax_guarda_posicion("'.$position.'",'.$warehouse.','.$user.')');
		
		//$objResponse->addScript('mouseoversound.playclip();');
		
		
		
		}else{
		$objResponse->addAlert("Please insert an Integer value for Quantity");
		$objResponse->addScript('document.getElementById("quantity").focus()');
		}
		
		
		}
				
				return $objResponse;


}




function elimina_inventario($id,$position,$user)
{ 
	@session_start();
	include('conf.php');
	
//echo 'entro';
$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
				$db_selected =mysql_select_db($dbname,$pg);
			
		
	
	$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el navegador
	
	$sql="DELETE FROM inventory WHERE id=".$id;
	
			$result = mysql_query($sql, $pg);
			
			
			if($result){
	$warehouse=$_SESSION['warehouse'];	
	//$objResponse->addAlert("This element was delete from the inventory");
	//$objResponse->addScript('xajax_dig_form_inventory_location(\''.$position.'\', '.$user.',\''.$_SESSION['warehouse'].'\');');
	$objResponse->addScript('xajax_guarda_posicion("'.$position.'",'.$warehouse.','.$user.')');
	
	
	
	}else{
	$objResponse->addAlert("We could not delete this element from the inventory");}
	
	
	
	
	return $objResponse;
}

function close_position($position, $warehouse){

@session_start();
	include('conf.php');
	

$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
				$db_selected =mysql_select_db($dbname,$pg);
			
		
	
	$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el navegador
	
	$sql="UPDATE position  SET close=1 WHERE STRCMP(position,'".$position."')=0 and STRCMP(id_warehouse,'".$warehouse."')=0";
	
			$result = mysql_query($sql, $pg);
			
			
			if($result){
	
	$objResponse->addAlert("This position was closed succesfully!");
	$objResponse->addScript("xajax_busca_opcion('position')");
	$objResponse->addScript("setTimeout(function() { document.getElementById(\'position\').focus(); }, 60);')");
	
	
	
	}else{
	$objResponse->addAlert("We could not close this position");}
	
	
	
	
	return $objResponse;


}

function open_position($position, $warehouse){
//echo 'entro';
@session_start();
	include('conf.php');
	

$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
				$db_selected =mysql_select_db($dbname,$pg);
			
		
	
	$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el navegador
	
	$sql="UPDATE position  SET close=false WHERE STRCMP(position,'".$position."')=0 and STRCMP(id_warehouse,'".$warehouse."')=0";
//	echo $sql;
			$result = mysql_query($sql, $pg);
			
			
			if($result){
	
	$objResponse->addAlert("This position was open succesfully!");
	$objResponse->addScript("xajax_busca_opcion('position')");
	
	
	
	}else{
	$objResponse->addAlert("We could not open this position");
	$objResponse->addScriptCall("xajax_busca_opcion('positions')");
	
	
	}
	
	
	
	
	return $objResponse;


}

function detalla_posicion($position, $warehouse){
@session_start();
	include('conf.php');

$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
				$db_selected =mysql_select_db($dbname,$pg);
			
	$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el navegador
	
	$cad=dig_form_position_details($position,$_SESSION['id_user'] ,$warehouse);
	
	$objResponse->addScript('var win_plan = window.open("", "win", "width=900,height=650,scrollbars=yes");');
	$objResponse->addScript('var doc_plan = win_plan.document;');
	$objResponse->addScript('doc_plan.open("text/html", "replace");');
	
	//La ventana emergente hereda todas las caracteristicas de la ventana madre
	$objResponse->addScript('doc_plan.write("<HTML><HEAD ID=CONTENIDO_HEADER>"+document.getElementById("CONTENIDO_HEADER").innerHTML);');
	$objResponse->addScript('doc_plan.write("</HEAD><BODY >");');

		
		
	$objResponse->addScriptCall('doc_plan.write','<div id="operativo" STYLE="width:815px ;"><div id="logo"></div><div id="operativo" STYLE="width:100%;">');
	
	//Se escribe el formulario
	$objResponse->addScriptCall('doc_plan.write',$cad);
	
	
	//Se hace el llamado a la funcion que activa los calendarios

	$objResponse->addScriptCall('doc_plan.write','</div></div>');
	$objResponse->addScript('doc_plan.write("</BODY></HTML>");');
	$objResponse->addScript('doc_plan.close();');
	
	return $objResponse;
}
function dig_form_position_details($position,$user,$warehouse) {
		@session_start();
		include('conf.php');
		$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
		$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el cliente

	//aqui debe ir la lista de lo que ya se ha cargado en esta posicion
		$sql="SELECT * FROM inventory WHERE STRCMP(pos_id,'".$position."')=0  and STRCMP(id_warehouse,'".$warehouse."')=0;";
		$db_selected =mysql_select_db($dbname,$pg);	
		$result = mysql_query($sql, $pg);
		$num= mysql_num_rows($result);
		if($num>0){
				$tabla = get_html_translation_table(HTML_ENTITIES);
		
		$cad= '<h1>Inventory Position '.$position.'</h1><table  align="center" border="1" >';
			
				$cad.= "<tr>\n";	
					$cad.= "<td align=\"center\"><b>Number</b></td>\n";
					$cad.= "<td align=\"center\"><b>Code</b></td>\n";
					$cad.= "<td align=\"center\"><b>Description</b></td>\n";
					$cad.= "<td align=\"center\"><b>Quantity</b></td>\n";
					$cad.= "<td align=\"center\"><b>User</b></td>\n";
					
					
					
				$cad.= "</tr>\n";
			
			$i=0;
				
				while ($row = mysql_fetch_assoc($result))
				{	
				$i++;
				
				$sql2="SELECT description as description from products where STRCMP(id_product,'".$row['prod_id']."')=0";//se selecciona la descripcion del producto
							$db_selected =mysql_select_db($dbname,$pg);
							$result2 = mysql_query($sql2, $pg);
							$row2=mysql_fetch_assoc($result2);
							
				$sql3="SELECT name  from users where id=".$row['user_id'];//se selecciona la descripcion del producto
							$db_selected =mysql_select_db($dbname,$pg);
							$result3 = mysql_query($sql3, $pg);
							$row3=mysql_fetch_assoc($result3);
							
							
								
					$cad.= "<tr>\n";
						$cad.= "<td align=\"center\">".$i."</td>\n";
						$cad.= "<td align=\"center\">".strtr($row["prod_id"],$tabla)."</td>\n";
						$cad.="<td align=\"center\">".strtr($row2["description"],$tabla)."</td>\n";
						$cad.="<td align=\"center\">".$row['quantity']."</td>\n";
						$cad.="<td align=\"center\">".strtr($row3["name"],$tabla)."</td>\n";
						
						
					$cad.= "</tr>\n";
					
					
					
					
				}      
			
		$cad.= "</table></div>\n";}else{
		$cad='Empty position ';
		}

return utf8_encode($cad);
}

function dig_form_reports($pg)//aqui imprimimos los reportes necesarios
{	
	include('conf.php');
	$warehouse=$_SESSION['warehouse'];
	$user=$_SESSION['id_user'];						
	$cad='<div id="principal" align="center"><h1>Reports</h1>
		  
			';
		
		$cad.= "<table  align=\"center\">\n";	   
		$cad.='<tr>
			   <th><INPUT type="button" name="Inventory" value="Inventory" id="inventory" ';//genera inventario
	$cad.=' onClick="xajax_reporte_inventario();" />';
	$cad.='</th>
				 </tr>';
					$cad.='<tr>
			   <th><INPUT type="button" name="positions_product" value="Positions by Products" id="positions_product" ';//genera productos-locaciones
	$cad.=' onClick="xajax_reporte_posiciones();" />';
	$cad.='</th></tr>';
		$cad.='<tr>
			   <th><INPUT type="button" name="positions_REPEAT" value="Duplicate Registries" id="positions_REPEAT" ';//genera productos con la misma locacion y la misma cantidad y codigo
	$cad.=' onClick="xajax_position_repeat();" />';
	$cad.='</th></tr>';
	
	$cad.='<tr>
			   <th><INPUT type="button" name="positions_sage" value="Positions with SAGE DATE" id="positions_sage" ';//genera productos-locaciones
	$cad.=' onClick="xajax_position_sage();" />';
	$cad.='</th></tr>';
	
	$cad.='<tr>
			   <th><INPUT type="button" name="positions_sage_old" value="Positions with OLD SAGE DATE" id="positions_sage_old" ';//genera productos-locaciones viejas
	$cad.=' onClick="xajax_position_sage_old();" />';
	$cad.='</th></tr>';
	
	$cad.='<tr>
			   <th><INPUT type="button" name="Inventory_ftz" value="Inventory FTZ" id="inventory_ftz" ';//genera inventario FTZ
	$cad.=' onClick="xajax_reporte_inventario_ftz();" />';
	$cad.='</th>
				 </tr>';
    
		
		
			$cad.='<tr>
			   <th><INPUT type="button" name="empty" value="Empty Positions" id="empty" ';//genera lista de posiciones vacias
	$cad.=' onClick="xajax_reporte_empty();" />';
	$cad.='</th>
				 </tr></table></div><div id="reporte"></div>';
                 			
	return utf8_encode($cad);
}


function reporte_inventario(){
@session_start();
	include('conf.php');

$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
				$db_selected =mysql_select_db($dbname,$pg);
			
	$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el navegador
	
	$cad=reporte_inventario2();
	
	$objResponse->addScript('var win_plan = window.open("", "win", "width=900,height=650,scrollbars=yes");');
	$objResponse->addScript('var doc_plan = win_plan.document;');
	$objResponse->addScript('doc_plan.open("text/html", "replace");');
	
	//La ventana emergente hereda todas las caracteristicas de la ventana madre
	$objResponse->addScript('doc_plan.write("<HTML><HEAD ID=CONTENIDO_HEADER>"+document.getElementById("CONTENIDO_HEADER").innerHTML);');
	$objResponse->addScript('doc_plan.write("</HEAD><BODY >");');

		
		
	$objResponse->addScriptCall('doc_plan.write','<div id="operativo" STYLE="width:815px ;"><div id="logo"></div><div id="operativo" STYLE="width:100%;">');
	
	//Se escribe el formulario
	$objResponse->addScriptCall('doc_plan.write',$cad);
	

	$objResponse->addScriptCall('doc_plan.write','</div></div>');
	$objResponse->addScript('doc_plan.write("</BODY></HTML>");');
	$objResponse->addScript('doc_plan.close();');
	
	return $objResponse;
}





function reporte_inventario2(){

 
//SE SELECCIONAN TODOS LOS PRODUCTOS Y LUEGO SE SUMAN TODOS LOS DISPAROS DE TODAS LAS POSICIONES
@session_start();
		include('conf.php');
		$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
		$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el cliente

	//aqui debe ir la lista de lo que ya se ha cargado en esta posicion
		$sql="SELECT * FROM products, product_detail where STRCMP(products.id_product,product_detail.prod_id)=0 ";
		
		$db_selected =mysql_select_db($dbname,$pg);	
		$result = mysql_query($sql, $pg);
		$cad= '<h1>Inventory  </h1><form action="ficheroExcel.php" method="post" target="_blank" id="FormularioExportacion">
<p>Exportar a Excel  <img src="export_to_excel.gif" class="botonExcel" /></p>
<input type="hidden" id="datos_a_enviar" name="datos_a_enviar" />
</form><script language="javascript">
$(document).ready(function() {
     $(".botonExcel").click(function(event) {
     $("#datos_a_enviar").val( $("<div>").append( $("#Exportar_a_Excel").eq(0).clone()).html());
     $("#FormularioExportacion").submit();
});
});
</script>
<table  id="Exportar_a_Excel" align="center" border="1" >';
			
				$cad.= "<tr>\n";	
					
					$cad.= "<td align=\"center\"><b>Code</b></td>\n";
					$cad.= "<td align=\"center\"><b>Description</b></td>\n";
					$cad.= "<td align=\"center\"><b>Cost</b></td>\n";
					$cad.= "<td align=\"center\"><b>Old Quantity</b></td>\n";
					$cad.= "<td align=\"center\"><b>New Quantity</b></td>\n";
					
					
					
				$cad.= "</tr>\n";
		
		while ($row = mysql_fetch_assoc($result))
				{
 

$total=0;
$costo=$row["cost_price"];
$old=$row["old_quantity"];


$tabla = get_html_translation_table(HTML_ENTITIES);

//ahora se suman en el inventario todos los disparos de todas las posiciones con ese codigo

		$sql2="SELECT SUM(quantity) as total FROM inventory WHERE STRCMP(prod_id,'".$row['id_product']."')=0  ";
		//echo $sql2;
		$db_selected =mysql_select_db($dbname,$pg);	
		$result2 = mysql_query($sql2, $pg);
		$num= mysql_num_rows($result2);
		if($num>0){
		$row2 = mysql_fetch_assoc($result2);
		
	$total=$row2['total'];
	
		
		
		}
					
					$cad.= "<tr>\n";
						
						$cad.= "<td align=\"center\">".$row["id_product"]."</td>\n";
						$cad.= "<td align=\"center\">".strtr($row["description"],$tabla)."</td>\n";
						$cad.= "<td align=\"center\">".$costo."</td>\n";
						$cad.= "<td align=\"center\">".$old."</td>\n";
						
						$cad.="<td align=\"center\">".$total."</td>\n";
						
						
					$cad.= "</tr>\n";
					
		
}
$cad.='</table>';
 




return utf8_encode($cad);


}
function reporte_posiciones(){
@session_start();
	include('conf.php');

$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
				$db_selected =mysql_select_db($dbname,$pg);
			
	$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el navegador
	
	$cad=reporte_posiciones2();
	
	$objResponse->addScript('var win_plan = window.open("", "win", "width=900,height=650,scrollbars=yes");');
	$objResponse->addScript('var doc_plan = win_plan.document;');
	$objResponse->addScript('doc_plan.open("text/html", "replace");');
	
	//La ventana emergente hereda todas las caracteristicas de la ventana madre
	$objResponse->addScript('doc_plan.write("<HTML><HEAD ID=CONTENIDO_HEADER>"+document.getElementById("CONTENIDO_HEADER").innerHTML);');
	$objResponse->addScript('doc_plan.write("</HEAD><BODY >");');

		
		
	$objResponse->addScriptCall('doc_plan.write','<div id="operativo" STYLE="width:815px ;"><div id="logo"></div><div id="operativo" STYLE="width:100%;">');
	
	//Se escribe el formulario
	$objResponse->addScriptCall('doc_plan.write',$cad);
 
	
	//Se hace el llamado a la funcion que activa los calendarios

	$objResponse->addScriptCall('doc_plan.write','</div></div>');
	$objResponse->addScript('doc_plan.write("</BODY></HTML>");');
	$objResponse->addScript('doc_plan.close();');
	
	return $objResponse;
}





function reporte_posiciones2(){

 
//SE SELECCIONAN TODOS LOS PRODUCTOS Y LUEGO SE SUMAN TODOS LOS DISPAROS DE TODAS LAS POSICIONES
@session_start();
		include('conf.php');
		$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
		$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el cliente

	//aqui debe ir la lista de todos los productos
		$sql="SELECT  id_product  FROM products  order by id_product";
		
		$db_selected =mysql_select_db($dbname,$pg);	
		$result = mysql_query($sql, $pg);
		$cad= '<h1>Position per Product </h1><form action="ficheroExcel2.php" method="post" target="_blank" id="FormularioExportacion">
<p>Exportar a Excel  <img src="export_to_excel.gif" class="botonExcel" /></p>
<input type="hidden" id="datos_a_enviar" name="datos_a_enviar" />
</form><script language="javascript">
$(document).ready(function() {
     $(".botonExcel").click(function(event) {
     $("#datos_a_enviar").val( $("<div>").append( $("#Exportar_a_Excel").eq(0).clone()).html());
     $("#FormularioExportacion").submit();
});
});
</script>
<table  id="Exportar_a_Excel" align="center" border="1" >';
 $cad.= "<tr>\n";
$cad.= "<td align=\"center\"><b>Code</b></td>\n";
					
					$cad.= "<td align=\"center\"><b>Position</b></td>\n";
					
					
					
				$cad.= "</tr>\n";

			
			
		
		while ($row = mysql_fetch_assoc($result))
				{
				$cad.= "<tr>\n";
						
						$cad.= "<td lign=\"center\">".$row["id_product"]."</td>\n";
 //aqui va un segundo query que busca cuantas posiciones distintas tiene y en funcion de eso llena 
 
		$sql2="SELECT  DISTINCT pos_id  FROM inventory WHERE  STRCMP(id_warehouse,'".$_SESSION['warehouse']."')=0  and STRCMP(prod_id,'".$row['id_product']."')=0  order by pos_id";
		
		$db_selected =mysql_select_db($dbname,$pg);	
		$result2= mysql_query($sql2, $pg);	
					
while ($row2 = mysql_fetch_assoc($result2)){
				
				
					
						
						$cad.="<td align=\"center\">".$row2['pos_id']."</td>\n";
						}
						
						
					$cad.= "</tr>\n";
					
		
}
$cad.='</table>';
 




return $cad;


}
function reporte_empty(){
@session_start();
	include('conf.php');

$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
				$db_selected =mysql_select_db($dbname,$pg);
			
	$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el navegador
	
	$cad=reporte_empty2();
	
	$objResponse->addScript('var win_plan = window.open("", "win", "width=900,height=650,scrollbars=yes");');
	$objResponse->addScript('var doc_plan = win_plan.document;');
	$objResponse->addScript('doc_plan.open("text/html", "replace");');
	
	//La ventana emergente hereda todas las caracteristicas de la ventana madre
	$objResponse->addScript('doc_plan.write("<HTML><HEAD ID=CONTENIDO_HEADER>"+document.getElementById("CONTENIDO_HEADER").innerHTML);');
	$objResponse->addScript('doc_plan.write("</HEAD><BODY >");');

		
		
	$objResponse->addScriptCall('doc_plan.write','<div id="operativo" STYLE="width:815px ;"><div id="logo"></div><div id="operativo" STYLE="width:100%;">');
	
	//Se escribe el formulario
	$objResponse->addScriptCall('doc_plan.write',$cad);
	
	
	//Se hace el llamado a la funcion que activa los calendarios

	$objResponse->addScriptCall('doc_plan.write','</div></div>');
	$objResponse->addScript('doc_plan.write("</BODY></HTML>");');
	$objResponse->addScript('doc_plan.close();');
	
	return $objResponse;
}





function reporte_empty2(){

 
//SE SELECCIONAN TODOS LOS PRODUCTOS Y LUEGO SE SUMAN TODOS LOS DISPAROS DE TODAS LAS POSICIONES
@session_start();
		include('conf.php');
		$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
		$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el cliente
		
		
		//aqui debe ir la lista de las posiciones vacias
		$tabla = get_html_translation_table(HTML_ENTITIES);
		$cad= '<h1>Empty Positions </h1><form action="ficheroExcel3.php" method="post" target="_blank" id="FormularioExportacion">
<p>Exportar a Excel  <img src="export_to_excel.gif" class="botonExcel" /></p>
<input type="hidden" id="datos_a_enviar" name="datos_a_enviar" />
</form><script language="javascript">
$(document).ready(function() {
     $(".botonExcel").click(function(event) {
     $("#datos_a_enviar").val( $("<div>").append( $("#Exportar_a_Excel").eq(0).clone()).html());
     $("#FormularioExportacion").submit();
});
});
</script>
<table  id="Exportar_a_Excel" align="center" border="1" >';
			
				$cad.= "<tr>\n";
					$cad.= "<td align=\"center\"><b>Warehouse</b></td>\n";
					$cad.= "<td align=\"center\"><b>Position</b></td>\n";
					$cad.= "</tr>\n";
			
		$sql="SELECT id_warehouse, position FROM position WHERE close=0 order by id_warehouse, position";
		//echo $sql;
		$db_selected =mysql_select_db($dbname,$pg);	
		$result = mysql_query($sql, $pg);
		$num= mysql_num_rows($result);
		while ($row = mysql_fetch_assoc($result))//aqui revisamos si existe en el inventario
				{	
			$sql2="SELECT id_warehouse, pos_id FROM inventory WHERE STRCMP(pos_id,'".$row['position']."')=0 and STRCMP(id_warehouse,'".$row['id_warehouse']."')=0 order by id_warehouse, pos_id";
		//echo $sql2;
		$db_selected =mysql_select_db($dbname,$pg);	
		$result2 = mysql_query($sql2, $pg);
		$num2= mysql_num_rows($result2);
		if($num2==0){				
					$cad.= "<tr>\n";
						$cad.= "<td align=\"center\">".strtr($row["id_warehouse"],$tabla)."</td>\n";
						$cad.= "<td align=\"center\">".strtr($row["position"],$tabla)."</td>\n";
						
					$cad.= "</tr>\n";
					}      
			
		}
		$cad.= "</table></div>\n";
	
		
		



return $cad;


}
//reporte para verificar registros repetidos en una misma posicion
function position_repeat(){
@session_start();
	include('conf.php');
//echo 'entro';
$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
				$db_selected =mysql_select_db($dbname,$pg);
			
	$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el navegador
	
	$cad=position_repeat2();
	
	$objResponse->addScript('var win_plan = window.open("", "win", "width=900,height=650,scrollbars=yes");');
	$objResponse->addScript('var doc_plan = win_plan.document;');
	$objResponse->addScript('doc_plan.open("text/html", "replace");');
	
	//La ventana emergente hereda todas las caracteristicas de la ventana madre
	$objResponse->addScript('doc_plan.write("<HTML><HEAD ID=CONTENIDO_HEADER>"+document.getElementById("CONTENIDO_HEADER").innerHTML);');
	$objResponse->addScript('doc_plan.write("</HEAD><BODY >");');

		
		
	$objResponse->addScriptCall('doc_plan.write','<div id="operativo" STYLE="width:815px ;"><div id="logo"></div><div id="operativo" STYLE="width:100%;">');
	
	//Se escribe el formulario
	$objResponse->addScriptCall('doc_plan.write',$cad);
	
	
	//Se hace el llamado a la funcion que activa los calendarios

	$objResponse->addScriptCall('doc_plan.write','</div></div>');
	$objResponse->addScript('doc_plan.write("</BODY></HTML>");');
	$objResponse->addScript('doc_plan.close();');
	
	return $objResponse;
}





function position_repeat2(){

 
@session_start();
		include('conf.php');
		$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
		$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el cliente
		
		
		//aqui debe ir la lista de las posiciones vacias
		$tabla = get_html_translation_table(HTML_ENTITIES);
		$cad= '<h1>Position Repeat </h1>
<table  id="Exportar_a_Excel" align="center" border="1" >';
			
				$cad.= "<tr>\n";
				$cad.= "<td align=\"center\"><b>POSITION</b></td>\n";
					$cad.= "<td align=\"center\"><b>CODE</b></td>\n";
					$cad.= "<td align=\"center\"><b>BOXES</b></td>\n";
					$cad.= "<td align=\"center\"><b>QUANTITY X BOX</b></td>\n";
					$cad.= "<td align=\"center\"><b>TOTAL</b></td>\n";
					$cad.= "</tr>\n";
			
		$sql="SELECT * FROM INVENTORY  order by prod_id";
		//echo $sql;
		$db_selected =mysql_select_db($dbname,$pg);	
		$result = mysql_query($sql, $pg);
		$num= mysql_num_rows($result);
		while ($row = mysql_fetch_assoc($result))//aqui revisamos si existe en el inventario
				{	
			$sql2="SELECT * FROM inventory WHERE 
			STRCMP(prod_id,'".$row['prod_id']."')=0 
			and STRCMP(pos_id,'".$row['pos_id']."')=0 
			and boxes=".$row['boxes']." 
			and qtyxb=".$row['qtyxb']."
			and quantity=".$row['quantity']."
			and STRCMP(id_warehouse,'".$row['id_warehouse']."')=0
			and id !=".$row['id']."
			order by  prod_id, quantity";
		//echo $sql2;
		$db_selected =mysql_select_db($dbname,$pg);	
		$result2 = mysql_query($sql2, $pg);
		$num2= mysql_num_rows($result2);
		while ($row2 = mysql_fetch_assoc($result2))//aqui revisamos si existe en el inventario
				{					
					
						$cad.= "<tr>\n";
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td>\n";
						$cad.= "<td align=\"center\">".strtr($row2["prod_id"],$tabla)."</td>\n";
						//$cad.="<td align=\"center\">".strtr($row2["description"],$tabla)."</td>\n";
						$cad.="<td align=\"center\">".$row2['boxes']."</td>\n";
						$cad.="<td align=\"center\">".$row2['qtyxb']."</td>\n";
						$cad.="<td align=\"center\">".$row2['quantity']."</td>\n";
					$cad.= "</tr>\n";
					}      
			
		}
		$cad.= "</table></div>\n";
	
		
		



return $cad;


}
//reporte para verificar posiciones de inventario vs posiciones de Sage
function position_sage(){
@session_start();
	include('conf.php');
//echo 'entro';
$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
				$db_selected =mysql_select_db($dbname,$pg);
			
	$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el navegador
	
	$cad=position_sage2();
	
	$objResponse->addScript('var win_plan = window.open("", "win", "width=900,height=650,scrollbars=yes");');
	$objResponse->addScript('var doc_plan = win_plan.document;');
	$objResponse->addScript('doc_plan.open("text/html", "replace");');
	
	//La ventana emergente hereda todas las caracteristicas de la ventana madre
	$objResponse->addScript('doc_plan.write("<HTML><HEAD ID=CONTENIDO_HEADER>"+document.getElementById("CONTENIDO_HEADER").innerHTML);');
	$objResponse->addScript('doc_plan.write("</HEAD><BODY >");');

		
		
	$objResponse->addScriptCall('doc_plan.write','<div id="operativo" STYLE="width:815px ;"><div id="logo"></div><div id="operativo" STYLE="width:100%;">');
	
	//Se escribe el formulario
	$objResponse->addScriptCall('doc_plan.write',$cad);
	
	
	//Se hace el llamado a la funcion que activa los calendarios

	$objResponse->addScriptCall('doc_plan.write','</div></div>');
	$objResponse->addScript('doc_plan.write("</BODY></HTML>");');
	$objResponse->addScript('doc_plan.close();');
	
	return $objResponse;
}





function position_sage2(){

 
@session_start();
		include('conf.php');
		$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
		$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el cliente
		//aqui debe ir la lista de todos los productos
		$sql="SELECT  id_product  FROM products  order by id_product";
		
		$db_selected =mysql_select_db($dbname,$pg);	
		$result = mysql_query($sql, $pg);
		
		
		
		$tabla = get_html_translation_table(HTML_ENTITIES);
		$cad= '<h1>Positions x Product From Inventory adding Sage Date </h1><form action="ficheroExcel4.php" method="post" target="_blank" id="FormularioExportacion">
<p>Exportar a Excel  <img src="export_to_excel.gif" class="botonExcel" /></p>
<input type="hidden" id="datos_a_enviar" name="datos_a_enviar" />
</form><script language="javascript">
$(document).ready(function() {
     $(".botonExcel").click(function(event) {
     $("#datos_a_enviar").val( $("<div>").append( $("#Exportar_a_Excel").eq(0).clone()).html());
     $("#FormularioExportacion").submit();
});
});
</script>
<table  id="Exportar_a_Excel" align="center" border="1" >';
			
				$cad.= "<tr>\n";
				$cad.= "<td align=\"center\"><b>CODE</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_1</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_1</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_2</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_2</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_3</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_3</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_4</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_4</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_5</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_5</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_6</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_6</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_7</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_7</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_8</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_8</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_9</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_9</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_10</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_10</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_11</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_11</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_12</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_12</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_13</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_13</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_14</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_14</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_15</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_15</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_16</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_16</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_17</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_17</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_18</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_18</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_19</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_19</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_20</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_20</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_21</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_21</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_22</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_22</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_23</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_23</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_24</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_24</b></td>\n";
					
					$cad.= "</tr>\n";
			
	while ($row = mysql_fetch_assoc($result))//POR CADA PRODUCTO IMPRIMIMOS EL CODIGO
				{
				$cad.= "<tr>\n";
						
						$cad.= "<td align=\"center\">".$row["id_product"]."</td>\n";
 //aqui va un segundo query que busca cuantas posiciones distintas tiene en el inventario
 
		$sql2="SELECT  DISTINCT pos_id  FROM inventory WHERE   STRCMP(prod_id,'".$row['id_product']."')=0  order by pos_id";
		$db_selected =mysql_select_db($dbname,$pg);	
		$result2= mysql_query($sql2, $pg);	
		$n=mysql_num_rows($result2);//esto nos indica cuantas posiciones distintas tiene el producto			
while ($row2 = mysql_fetch_assoc($result2)){
	//IMPRIMIMOS EL CODIGO DEL PRODUCTO
	//$cad.= "<td align=\"center\">".$row2["pos_id"]."</td>\n";
		//ahora nos conectamos a Sage para validar si existe esa posicion para ese producto y si existe nos traemos la fecha, si no existe dejamos la fecha en blanco
		$conn = odbc_connect('test' ,$sage_user,$sage_pass);
				$query3 = "SELECT * FROM CI_Item WHERE ItemCode='".$row['id_product']."'";
				$result3 = odbc_exec($conn, $query3) or die(odbc_errormsg());
				$num=odbc_num_fields($result3);//esto nos dice cuantos campos tiene la tabla
				if (!$result3){
				$objResponse->addAlert("ERROR: ".$sql_item);
				}
				while ($row3 = odbc_fetch_array($result3)){
					set_time_limit(180);
				//aqui vamos a recorrer uno por uno cada uno de los campos de posiciones para ver si su valor es igual a la posicion que tomamos en el inventario
				if(strcmp($row3['UDF_LOCATION_1'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_1"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_2'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_2"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_3'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_3"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_4'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_4"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_5'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_5"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_6'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_6"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_7'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_7"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_8'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_8"],$tabla)."</td>";
				}
				
				if(strcmp($row3['UDF_LOCATION_9'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_9"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_10'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_10"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_11'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_11"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_12'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_12"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_13'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_13"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_14'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_14"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_15'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_15"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_16'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_16"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_17'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_17"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_18'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_18"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_19'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_19"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_20'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_20"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_21'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_21"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_22'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_22"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_23'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_23"],$tabla)."</td>";
				}
				if(strcmp($row3['UDF_LOCATION_24'],$row2['pos_id'])==0){
					
						$cad.= "<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_24"],$tabla)."</td>";
				}
				//Si no encontro en sage ninguna posicion
				if((strcmp($row3['UDF_LOCATION_1'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_2'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_3'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_4'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_5'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_6'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_7'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_8'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_9'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_10'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_11'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_12'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_13'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_14'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_15'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_16'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_17'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_18'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_19'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_20'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_21'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_22'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_23'],$row2['pos_id'])!=0)&&(strcmp($row3['UDF_LOCATION_24'],$row2['pos_id'])!=0)){
					$cad.="<td align=\"center\">".strtr($row2["pos_id"],$tabla)."</td><td align=\"center\"></td>";//Imprimimos solo la posicion
				}
				}//cierra el while de Sage
		

	}//cierra recorrido de posiciones
	$cad.= "</tr>";	
}//cierra recorrido de productos
$cad.= "</table></div>\n";

return $cad;


}

//reporte para obtener las posiciones Sage actuales
function position_sage_old(){
@session_start();
	include('conf.php');
//echo 'entro';
$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
				$db_selected =mysql_select_db($dbname,$pg);
			
	$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el navegador
	
	$cad=position_sage_old2();
	
	$objResponse->addScript('var win_plan = window.open("", "win", "width=900,height=650,scrollbars=yes");');
	$objResponse->addScript('var doc_plan = win_plan.document;');
	$objResponse->addScript('doc_plan.open("text/html", "replace");');
	
	//La ventana emergente hereda todas las caracteristicas de la ventana madre
	$objResponse->addScript('doc_plan.write("<HTML><HEAD ID=CONTENIDO_HEADER>"+document.getElementById("CONTENIDO_HEADER").innerHTML);');
	$objResponse->addScript('doc_plan.write("</HEAD><BODY >");');

		
		
	$objResponse->addScriptCall('doc_plan.write','<div id="operativo" STYLE="width:815px ;"><div id="logo"></div><div id="operativo" STYLE="width:100%;">');
	
	//Se escribe el formulario
	$objResponse->addScriptCall('doc_plan.write',$cad);
	
	
	//Se hace el llamado a la funcion que activa los calendarios

	$objResponse->addScriptCall('doc_plan.write','</div></div>');
	$objResponse->addScript('doc_plan.write("</BODY></HTML>");');
	$objResponse->addScript('doc_plan.close();');
	
	return $objResponse;
}





function position_sage_old2(){
@session_start();
		include('conf.php');
		
		$cad= '<h1>Old Locations Sage </h1><form action="ficheroExcel5.php" method="post" target="_blank" id="FormularioExportacion">
<p>Exportar a Excel  <img src="export_to_excel.gif" class="botonExcel" /></p>
<input type="hidden" id="datos_a_enviar" name="datos_a_enviar" />
</form><script language="javascript">
$(document).ready(function() {
     $(".botonExcel").click(function(event) {
     $("#datos_a_enviar").val( $("<div>").append( $("#Exportar_a_Excel").eq(0).clone()).html());
     $("#FormularioExportacion").submit();
});
});
</script>
<table  id="Exportar_a_Excel" align="center" border="1" >';
			
				$cad.= "<tr>\n";
				$cad.= "<td align=\"center\"><b>CODE</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_1</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_1</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_2</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_2</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_3</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_3</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_4</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_4</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_5</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_5</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_6</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_6</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_7</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_7</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_8</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_8</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_9</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_9</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_10</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_10</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_11</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_11</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_12</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_12</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_13</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_13</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_14</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_14</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_15</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_15</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_16</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_16</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_17</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_17</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_18</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_18</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_19</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_19</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_20</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_20</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_21</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_21</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_22</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_22</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_23</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_23</b></td>\n";
					$cad.= "<td align=\"center\"><b>LOCATION_24</b></td>\n";
					$cad.= "<td align=\"center\"><b>DATE_24</b></td>\n";
					
					$cad.= "</tr>\n";

				$cad.= "<tr>\n";
				$conn = odbc_connect('test' ,$sage_user,$sage_pass);
				$query3 = "SELECT * FROM CI_Item WHERE ItemType='1' order by ItemCode";
				$result3 = odbc_exec($conn, $query3) or die(odbc_errormsg());
				$tabla = get_html_translation_table(HTML_ENTITIES);
				if (!$result3){
				$objResponse->addAlert("ERROR: ".$query3);
				}
				while ($row3 = odbc_fetch_array($result3)){
						
						$cad.= "<td align=\"center\">".$row3["ItemCode"]."</td>\n";

		
					set_time_limit(180);
				
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_1"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_1"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_2"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_2"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_3"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_3"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_4"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_4"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_5"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_5"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_6"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_6"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_7"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_7"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_8"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_8"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_9"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_9"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_10"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_10"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_11"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_11"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_12"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_12"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_13"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_13"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_14"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_14"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_15"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_15"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_16"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_16"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_17"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_17"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_18"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_18"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_19"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_19"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_20"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_20"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_21"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_21"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_22"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_22"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_23"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_23"],$tabla)."</td>";
				$cad.= "<td align=\"center\">".strtr($row3["UDF_LOCATION_24"],$tabla)."</td><td align=\"center\">".strtr($row3["UDF_DATE_24"],$tabla)."</td>";
				$cad.= "</tr>";	
				}//cierra el while de Sage
		
$cad.= "</table>";

return $cad;


}
function reporte_inventario_ftz(){
@session_start();
	include('conf.php');

$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
				$db_selected =mysql_select_db($dbname,$pg);
			
	$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el navegador
	
	$cad=reporte_inventario_ftz2();
	
	$objResponse->addScript('var win_plan = window.open("", "win", "width=900,height=650,scrollbars=yes");');
	$objResponse->addScript('var doc_plan = win_plan.document;');
	$objResponse->addScript('doc_plan.open("text/html", "replace");');
	
	//La ventana emergente hereda todas las caracteristicas de la ventana madre
	$objResponse->addScript('doc_plan.write("<HTML><HEAD ID=CONTENIDO_HEADER>"+document.getElementById("CONTENIDO_HEADER").innerHTML);');
	$objResponse->addScript('doc_plan.write("</HEAD><BODY >");');

		
		
	$objResponse->addScriptCall('doc_plan.write','<div id="operativo" STYLE="width:815px ;"><div id="logo"></div><div id="operativo" STYLE="width:100%;">');
	
	//Se escribe el formulario
	$objResponse->addScriptCall('doc_plan.write',$cad);
	

	$objResponse->addScriptCall('doc_plan.write','</div></div>');
	$objResponse->addScript('doc_plan.write("</BODY></HTML>");');
	$objResponse->addScript('doc_plan.close();');
	
	return $objResponse;
}





function reporte_inventario_ftz2(){

 
//SE SELECCIONAN TODOS LOS PRODUCTOS Y LUEGO SE SUMAN TODOS LOS DISPAROS DE TODAS LAS POSICIONES dl FTZ
@session_start();
		include('conf.php');
		$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
		$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el cliente

	//aqui debe ir la lista de lo que ya se ha cargado en esta posicion
		$sql="SELECT * FROM products, product_detail where STRCMP(products.id_product,product_detail.prod_id)=0 order by id_product";
		
		$db_selected =mysql_select_db($dbname,$pg);	
		$result = mysql_query($sql, $pg);
		$cad= '<h1>Inventory FTZ  </h1><form action="ficheroExcel6.php" method="post" target="_blank" id="FormularioExportacion">
<p>Exportar a Excel  <img src="export_to_excel.gif" class="botonExcel" /></p>
<input type="hidden" id="datos_a_enviar" name="datos_a_enviar" />
</form><script language="javascript">
$(document).ready(function() {
     $(".botonExcel").click(function(event) {
     $("#datos_a_enviar").val( $("<div>").append( $("#Exportar_a_Excel").eq(0).clone()).html());
     $("#FormularioExportacion").submit();
});
});
</script>
<table  id="Exportar_a_Excel" align="center" border="1" >';
			
				$cad.= "<tr>\n";	
					
					$cad.= "<td align=\"center\"><b>Code</b></td>\n";
					$cad.= "<td align=\"center\"><b>Description</b></td>\n";
					$cad.= "<td align=\"center\"><b>Cost</b></td>\n";
					$cad.= "<td align=\"center\"><b>Old Quantity</b></td>\n";
					$cad.= "<td align=\"center\"><b>New Quantity</b></td>\n";
					
					
					
				$cad.= "</tr>\n";
		
		while ($row = mysql_fetch_assoc($result))
				{
 

$total=0;
$costo=$row["cost_price"];
$old=$row["old_quantity"];


$tabla = get_html_translation_table(HTML_ENTITIES);

//ahora se suman en el inventario todos los disparos de todas las posiciones con ese codigo

		$sql2="SELECT SUM(quantity) as total FROM inventory WHERE STRCMP(prod_id,'".$row['id_product']."')=0 and STRCMP(id_warehouse,'100')=0 ";
		//echo $sql2;
		$db_selected =mysql_select_db($dbname,$pg);	
		$result2 = mysql_query($sql2, $pg);
		$num= mysql_num_rows($result2);
		if($num>0){
		$row2 = mysql_fetch_assoc($result2);
		
	$total=$row2['total'];
	
		
		
		}
				if ($total!=0){	
					$cad.= "<tr>\n";
						
						$cad.= "<td lign=\"center\">".$row["id_product"]."</td>\n";
						$cad.= "<td lign=\"center\">".strtr($row["description"],$tabla)."</td>\n";
						$cad.= "<td lign=\"center\">".$costo."</td>\n";
						$cad.= "<td lign=\"center\">".$old."</td>\n";
						
						$cad.="<td align=\"center\">".$total."</td>\n";
						
						
					$cad.= "</tr>\n";
				}
		
}
$cad.='</table>';
 




return utf8_encode($cad);


}

?>