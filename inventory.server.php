<?php
	function busca_opcion($opcion) 
	{
	@session_start();
	@include('conf.php');
	
	
	//se establece la conexion con la base de datos
	$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
		
	$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el cliente
		switch($opcion)
		{
			case 'position':
				$cad=dig_form_position($pg);
				$objResponse->addAssign("operativo", "innerHTML", $cad);
				//$objResponse->addScript('FormPosition.position.focus();');
				$objResponse->addScript('setTimeout(function() { document.getElementById(\'position\').focus(); }, 60);');
				
			break;
			case 'reports':
				$cad=dig_form_reports($pg);
				$objResponse->addAssign("operativo", "innerHTML", $cad);
				
				
			break;
			case 'positions':
				$cad=dig_form_positions($pg);
				$objResponse->addAssign("operativo", "innerHTML", $cad);
				//$objResponse->addScript('FormPosition.position.focus();');
				$objResponse->addScript('document.getElementById("position").focus();');
			break;
			
			default:
			$cad="..En construccion";
			$objResponse->addAssign("operativo", "innerHTML", $cad);
		}
		return $objResponse;
	}
	
	function salir(){
	@session_start();
	
	
	unset($_SESSION['login']);
	unset($_SESSION['pass']);
	unset($_SESSION['name']);
	unset($_SESSION['id_user']);
	unset($_SESSION['warehouse']);
	$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el cliente
  	$objResponse->addScript("document.location='forminicio.php';");
  	return $objResponse;
			
} 
function sync_products(){
	@session_start();
	include('conf.php');
	$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
	$conn = odbc_connect('test' ,$sage_user,$sage_pass);
	$objResponse = new xajaxResponse(); //se inicia la comunicacion asincrona con el cliente
	//Delete Clients and Products
	$db_selected =mysql_select_db($dbname,$pg);
	$sql="DELETE from products";
	$result = mysql_query($sql, $pg);
	$sql2="DELETE from product_detail";
	$result6 = mysql_query($sql2, $pg);
	if ((!$result)||(!$result6)){
		$objResponse->addAlert("ERROR: " .$sql.' '.$sql2);
		}else{
			//INSERT PRODUCTS
			$query = "SELECT UDF_DESC_ENGLISH, itemCode  FROM CI_Item WHERE ItemType='1'";
			$result2 = odbc_exec($conn, $query) or die(odbc_errormsg());
			while ($row = odbc_fetch_array($result2)){
				$descri=str_replace("'","\'",$row['UDF_DESC_ENGLISH']);
				$sql_item="INSERT INTO products(id_product,description)VALUES('".$row['itemCode']."','".$descri."');";
				$result3 =mysql_query($sql_item, $pg);
				if (!$result3){
				$objResponse->addAlert("ERROR: ".$sql_item);
				}
			}
			
			//INSERT PRODUCTS_DETAILS
			$query2 = "SELECT itemCode, AverageUnitCost, TotalQuantityOnHand FROM CI_Item WHERE ItemType='1'";
			$result4 = odbc_exec($conn, $query2) or die(odbc_errormsg());
			while ($row2 = odbc_fetch_array($result4)){
				
				$sql_client= "INSERT INTO product_detail(prod_id, old_quantity, cost_price)VALUES('".$row2['itemCode']."',".$row2['TotalQuantityOnHand'].",'".$row2['AverageUnitCost']."');";
				$result5 =  mysql_query($sql_client, $pg);
				if (!$result5){
				$objResponse->addAlert("ERROR: ".$sql_client);
				}
			}
			if(($result3)&&($result5)){
			$objResponse->addAlert("The Synchronization Finished Successfully");
			}
		}	
		
		odbc_close($conn);
		
 return $objResponse;
}
 
	
	require("inventory.php");
	
	require("inventory.common.php");
	$xajax->processRequests();
	
?>
