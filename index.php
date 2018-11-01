<?php
	session_start();
	require_once('conf.php');
?>
<?php

	if ((!isset($_SESSION['id_user']))&&(!isset($_POST['login']))) {
		include('forminicio.php');
	}
	else {
	
		if(isset($_POST['login'])){
		$login=$_POST['login'];
		$clave=md5($_POST['clave']);
		
		
	
		$pg=mysql_connect($pghost.':'.$pgport, $db_user, $db_pass);
		
		
		if($pg){
		
			$sql="select * from users where login='$login' and pass='$clave' ;";
			//echo $sql;
			
			$db_selected =mysql_select_db($dbname,$pg);
			
			$result = mysql_query($sql, $pg);
			
			$n = mysql_num_rows($result);
			if($n==1){
				$row = mysql_fetch_assoc($result);
				$_SESSION['login']=$row['login'];
				$_SESSION['pass']=md5($_POST['clave']);
				$_SESSION['name']=$row['name'];
				$_SESSION['id_user']=$row['id'];
				$_SESSION['warehouse']=$_POST['warehouse'];
			}
			else{
				echo "<p>The user does not exists in the Data Base</p><hr>";
				include('forminicio.php');
			}
		}
		else{
			echo "<p>Can't connect to the Data Base</p><hr>";
			include('forminicio.php');
			}
		}
}
if(isset($_SESSION['id_user'])){
	require_once('inicio.php');
}
?>
	