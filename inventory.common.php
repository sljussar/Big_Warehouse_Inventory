<?php 
require_once ("../xajax/xajax.inc.php");
$xajax = new xajax("inventory.server.php");

/*Funciones generales del sistema*/

$xajax->registerFunction("busca_opcion");//
$xajax->registerFunction("sync_products");//
$xajax->registerFunction("salir");//logout 

//POSICIONES
$xajax->registerFunction("guarda_posicion");//verifica que una posicion exista para agregarle inventario
$xajax->registerFunction("close_position");//cierra una posicion abierta
$xajax->registerFunction("open_position");//abre una posicion cerrada
$xajax->registerFunction("detalla_posicion");//nos da los renglones en una posicion


//INVENTARIO
$xajax->registerFunction("valida_codigo");//
$xajax->registerFunction("valida_cantidad");//
$xajax->registerFunction("calcula_cantidad");//
$xajax->registerFunction("dig_form_inventory_location");//nos da los renglones en una posicion y permite eliminarlos
$xajax->registerFunction("elimina_inventario");//quita un renglon de una posicion especifica

//REPORTES
$xajax->registerFunction("reporte_inventario");//Nos da el codigo de producto y cantidad total de cada uno
$xajax->registerFunction("reporte_posiciones");//Nos da las posiciones en las que esta cada producto
$xajax->registerFunction("reporte_empty");//Nos da las posiciones que estan sin mercancia asignada
$xajax->registerFunction("position_repeat");//nos da registros de inventario exactamente iguales
$xajax->registerFunction("position_sage");//permite comparar posiciones de inventario vs las posiciones de Sage con su fecha
$xajax->registerFunction("position_sage_old");//trae las posiciones viejas de Sage con su fecha 
$xajax->registerFunction("reporte_inventario_ftz");//inventario del FTZ
?>
