<?php 


function dig_form_position($pg)
{	
	include('conf.php');
	$warehouse=$_SESSION['warehouse'];
	
	$user=$_SESSION['id_user'];						
	$cad='<div id="principal" align="center"><h1>NEW POSITION</h1><form id="FormPosition" name="FormPosition" method="post" action="" onSubmit="return false;"  >
		  <table>
			<tr>
			 
			   <th ><h2>Position:</h2> </th>
			 <th >
					 <input name="position" type="text" id="position" size="20" onChange="xajax_guarda_posicion(xajax.getFormValues(\'FormPosition\'),'.$warehouse.','.$user.');" />
			  </th>
			
			 
			  </tr>
			  
			  
			';
			
		$cad.= "</table>\n<br><br><br>";
		$cad.= "<table  align=\"center\">\n";	   
		$cad.='<tr>
			   <th><INPUT type="button" name="opcion" value="Save" id="opcion" ';
	$cad.=' onClick="xajax_guarda_posicion(xajax.getFormValues(\'FormPosition\'),'.$warehouse.','.$user.');" />';
	$cad.='</th>
				 </tr>';
		$cad.= "</table>\n </form></div>";
	
				
	return utf8_encode($cad);
}

function guarda_posicion($datos,$warehouse,$user)

{
	@session_start();
	include('conf.php');

	$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
	$db_selected =mysql_select_db($dbname,$pg);
			
	$warehouse=$_SESSION['warehouse'];	
	
	$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el navegador
		

	$sql="SELECT position FROM position WHERE ";
	$sql.="STRCMP(position,'".$datos['position'];
	$sql.="')=0 and STRCMP(id_warehouse,'".$warehouse;
	$sql.="')=0 and close=0";
		//echo $sql;
			$result = mysql_query($sql, $pg);
			$n = mysql_num_rows($result);
			
			if($n==1){
			
	//Si la posicion existe se debe cargar el inventario
	$cad=dig_form_inventory($pg,$datos,$warehouse,$user);
	$objResponse->addAssign("operativo", "innerHTML", $cad);
	$script="code.focus();";
	$objResponse->addScript($script);
	
  
	
	}else{
	$objResponse->addAlert("This position does not exists or is close");
	
	}	
	return $objResponse;
}

function dig_form_inventory($pg,$datos,$warehouse,$user)
{	
	include('conf.php');			

	$position=$datos['position'];
				
	$cad='<div id="principal" align="center"><h1>POSITION '.$datos['position'].'</h1><form id="FormInventory" name="FormInventory"  method="post" onSubmit="return false;"  >
		  <table>
			<tr>
			 
			   <th ><h2>Code:</h2> </th>
			 <th  align="left">
					 <input name="code" type="text" id="code" size="20" onchange= "xajax_valida_codigo(code.value);"/>
			  </th>
			
			 
			  </tr>
			  <tr>
			 
			   <th  ><h2>Description:</h2> </th>
			 <th align="left">
					 <input name="description" type="text" id="description" size="50" readonly />
			  </th>
			
			 
			  </tr>
			  <tr>
			 
			   <th ><h2>Quantity:</h2>  </th>
			 <th align="left">
					 <input name="quantity" type="text" id="quantity" size="10" onchange= "xajax_valida_cantidad( \''.$position.'\' , code.value, description.value, quantity.value, '.$user.', \''.$warehouse.'\');" />
			  </th>
			
			 
			  </tr>
			  
			  
			';
			
		$cad.='</table><br><br><br></form><div id="inventory">';
			$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);

	//aqui debe ir la lista de lo que ya se ha cargado en esta posicion
		$sql="SELECT * FROM inventory WHERE STRCMP(pos_id,'".$datos['position']."')=0 and user_id=".$user." and STRCMP(id_warehouse,'".$warehouse."')=0 order by id DESC";
		//	echo $sql;
		$db_selected =mysql_select_db($dbname,$pg);	
		$result = mysql_query($sql, $pg);
		$num= mysql_num_rows($result);
		if($num>0){
				$tabla = get_html_translation_table(HTML_ENTITIES);
		
		$cad.= '<h1>Inventory </h1><table  align="center" border="1">';
			
				$cad.= "<tr>\n";	
					$cad.= "<td align=\"center\"><b>Number</b></td>\n";
					$cad.= "<td align=\"center\"><b>Code</b></td>\n";
					$cad.= "<td align=\"center\"><b>Description</b></td>\n";
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
						$cad.="<td align=\"center\">".$row['quantity']."</td>\n";
						$cad.= "<td align=\"center\"><a onClick=\"xajax_elimina_inventario(".$row['id'].",'".$datos['position']."' ,".$user.");\">X</a></td>\n";
						
					$cad.= "</tr>\n";
					
					
					
					
				}      
			
		$cad.= "</table><input name=\"close\" type=\"button\" value=\"Close\" onclick=\"xajax_close_position('".$datos['position']."', '".$warehouse."');\" /></div>\n";}else{
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
				
				
				$objResponse->addAssign("description", "value", $row['description']);
				$objResponse->addScript('quantity.focus()');
			
			$objResponse->addScript('mouseoversound.playclip();');

				
				}else {
				$objResponse->addAssign("description", "value", ''); }	
				
				
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
		
		$cad.= '<h1>Closed Positions </h1><table  align="center" border="1" >';
			
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
		$cad.='Empty closed positions</br></div>';
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
						$cad.= "<td align=\"center\"><a onClick=\"xajax_close_position('".$row['position']."','".$row['id_warehouse']."');\">CLOSE</a></td>\n";
						
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
		
		$cad= '<h1>Inventory </h1><table  align="center" border="1">';
			
				$cad.= "<tr>\n";	
					$cad.= "<td align=\"center\" ><b>Number</b></td>\n";
					$cad.= "<td align=\"center\" ><b>Code</b></td>\n";
					$cad.= "<td align=\"center\" ><b>Description</b></td>\n";
					$cad.= "<td align=\"center\" ><b>Quantity</b></td>\n";
					
					$cad.= "<td class=\"no_imp\"><b>X</b></td>\n";
					
				$cad.= "</tr>\n";
			
			$i=0;
				
				while ($row = mysql_fetch_assoc($result))
				{	
				$i++;
				
				$sql2="SELECT description as description from products where STRCMP(id_product,'".$row['prod_id']."')=0";//se selecciona la descripcion del producto
							$db_selected =mysql_select_db($dbname,$pg);
							$result2 = mysql_query($sql2, $pg);
							$row2=mysql_fetch_assoc($result2);
							
							
								
					$cad.= "<tr>\n";
						$cad.= "<td align=\"center\">".$i."</td>\n";
						$cad.= "<td align=\"center\">".strtr($row["prod_id"],$tabla)."</td>\n";
						$cad.="<td align=\"center\">".strtr($row2["description"],$tabla)."</td>\n";
						$cad.="<td align=\"center\">".$row['quantity']."</td>\n";
						$cad.= "<td align=\"center\"><a onClick=\"xajax_elimina_inventario(".$row['id'].",'".$position."' ,".$user.");\"><b>X</b></a></td>\n";
						
					$cad.= "</tr>\n";
					
					
					
					
				}      
			
		$cad.= "</table><input name=\"close\" type=\"button\" value=\"Close\" onclick=\"xajax_close_position('".$position."'".", '".$_SESSION['warehouse']."');\" /></div>\n";}else{
		$cad.='Empty position for this group';
		}
		$objResponse->addAssign("inventory", "innerHTML", $cad);

return $objResponse;

}


function valida_cantidad($position, $code , $descri, $quantity, $user, $warehouse){
//echo'entro';
		include('conf.php');
		//$warehouse=$_SESSION['warehouse'];
		$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
		$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el cliente
		//validamos que sea un producto existente
		if($descri==''){
		$objResponse->addAlert("Please insert a valid product");
		$objResponse->addAssign("quantity", "value", '');
		$objResponse->addScript('code.focus();');
		
		}else{
		
		if(is_numeric($quantity)==1){
		
		
		//entonces salvamos el producto en el inventario
		$sql="INSERT INTO inventory(pos_id, prod_id, quantity, user_id, id_warehouse) VALUES( ";
		$sql.="'".$position."', ";
		$sql.="UPPER('".$code."'), ";
		$sql.=$quantity.", ";
		$sql.=$user.", ";
		$sql.="'".$warehouse."'); ";
		
	//echo $sql;
		$db_selected =mysql_select_db($dbname,$pg);	
		$result=mysql_query($sql,$pg);
			//echo $result.'<-result';
		
			
		//despues de agregar se vuelve a llamar a la funcion para agregar nuevas cajas a la posicion
		$objResponse->addAssign("description", "value", '');
		$objResponse->addAssign("code", "value", '');
		$objResponse->addAssign("quantity", "value", '');
		$script="code.focus();";
		$objResponse->addScript($script);
		$objResponse->addScript('xajax_dig_form_inventory_location(\''.$position.'\', '.$user.', \''.$warehouse.'\');');
		$objResponse->addScript('mouseoversound.playclip();');
		
		
		
		}else{
		$objResponse->addAlert("Please insert an Integer value for Quantity");
		$objResponse->addScript('quantity.focus();');
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
	
	//$objResponse->addAlert("This element was delete from the inventory");
	$objResponse->addScript('xajax_dig_form_inventory_location(\''.$position.'\', '.$user.',\''.$_SESSION['warehouse'].'\');');
	$script="code.focus();";
		$objResponse->addScript($script);
	
	
	
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
	
	$objResponse->addAlert("This position was close succesfully!");
	$objResponse->addScript("xajax_busca_opcion('position')");
	$objResponse->addScript("position.focus();')");
	
	
	
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
	$objResponse->addScriptCall('doc_plan.write',"<script>$script</script>");
	
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
		$cad.='Empty position ';
		}

return $cad;
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
					$cad.='<tr
			   <th><INPUT type="button" name="positions_product" value="Positions by Products" id="positions_product" ';//genera productos-locaciones
	$cad.=' onClick="xajax_reporte_posiciones();" />';
	$cad.='</th>
				 </tr>';
		$cad.= "</table>\n </form></div>";
		
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
	$objResponse->addScriptCall('doc_plan.write',"<script>$script</script>");
	
	//Se hace el llamado a la funcion que activa los calendarios

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
						
						$cad.= "<td lign=\"center\">".$row["id_product"]."</td>\n";
						$cad.= "<td lign=\"center\">".strtr($row["description"],$tabla)."</td>\n";
						$cad.= "<td lign=\"center\">".$costo."</td>\n";
						$cad.= "<td lign=\"center\">".$old."</td>\n";
						
						$cad.="<td align=\"center\">".$total."</td>\n";
						
						
					$cad.= "</tr>\n";
					
		
}
$cad.='</table>';
 




return $cad;


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
	$objResponse->addScriptCall('doc_plan.write',"<script>$script</script>");
	
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

	//aqui debe ir la lista de lo que ya se ha cargado en esta posicion
		$sql="SELECT DISTINCT pos_id , prod_id FROM inventory WHERE  STRCMP(id_warehouse,'".$_SESSION['warehouse']."')=0 order by pos_id";
		
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
						
						$cad.= "<td lign=\"center\">".$row["prod_id"]."</td>\n";
						
						$cad.="<td align=\"center\">".$row['pos_id']."</td>\n";
						
						
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
	$objResponse->addScriptCall('doc_plan.write',"<script>$script</script>");
	
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


?>