<?php require_once('../../connections/crm.php');  /// conecta a base de datos ?>
<?php
// Función para el cierre de sesión 
$logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")){
  $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
    //Para que el usuario salga completamente se limpian todas las variables
  $_SESSION['MM_Username'] = NULL;
  $_SESSION['MM_UserGroup'] = NULL;
  $_SESSION['PrevUrl'] = NULL;
  unset($_SESSION['MM_Username']);
  unset($_SESSION['MM_UserGroup']);
  unset($_SESSION['PrevUrl']);
	
  $logoutGoTo = "../../logincrm.php";//Envía a la página de inicio de sesión de administrador
  if ($logoutGoTo) {
    header("Location: $logoutGoTo");
    exit;
  }
}

ini_set( 'session.cookie_httponly', 1 );

//función para cierre inactivo de sesión y limpieza de cookies
 function tiempo() {  
   if (isset($_SESSION['LAST_LOGIN']) && (time() - $_SESSION['LAST_LOGIN'] > 1800)) {  
     if (isset($_COOKIE[session_name()])) {  
       setcookie(session_name(), "", time() - 3600, "/");  
       //limpiamos completamente el array superglobal    
       session_unset();  
       //Eliminamos la sesión (archivo) del servidor   
       session_destroy();  
     }  
     header("../../logincrm.php"); //redirigir al punto de partida para identificarse  
     exit;  
   }  
   //...  
 }  



// *** Restringe el acceso a la pagina sin login previamente hecho
if (!isset($_SESSION)) {
    session_start();
}

//Identifica tipo de string que estan dentro del formulario//
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

//Busqueda datos de usuario
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_crm, $crm);
$query_usuario = sprintf("SELECT * FROM Asesores WHERE identificacion = %s", GetSQLValueString($colname_usuario, "int"));
$usuario = mysql_query($query_usuario, $crm) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);

if($row_usuario['Rol']!='HABILITADO'){//Validar si el usuario se encuentra habilitado para acceder a CRM
$MM_authorizedUsers = "usuariovalido";//Se asigna nombre de sesion de usuario
$MM_donotCheckaccess = "false";//Se deshabilita el checking de acceso	
}
else
{
	header("Location:../../noautorizado.php");
}

function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) {
	// Por seguridad, no se inicia si el usuario no esta autorizado

  $isValid = False; 
  //Cuando el usuario tiene ingreso, la variable Session MM_Username es igual a la de acceso del usuario, es decir su número de identificación.
  //Por otro lado, se sabe que el usario no esta con Login si la variable esta en blanco.
  if (!empty($UserName)) { 
    //Se puede restringir el acceso a ciertos ID, mediante el login accedido.
    // Convierte los strings restringidos en arrays.
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "logincrm.php";// Si el usuario no esta autorizado a ver esta pagina lo devolvera a la pagina principal de acceso a login
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
  
  //*** Termina validación usuario ***//  
}
//Identifica tipo de string que estan dentro del formulario//


if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

//Busqueda datos usuario CRM
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_crm, $crm);
$query_usuario = sprintf("SELECT * FROM Asesores WHERE identificacion = %s", GetSQLValueString($colname_usuario, "int"));
$usuario= mysql_query($query_usuario, $crm) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);

//Busqueda datos cuenta


if (isset($_GET['cuenta'])) {
  $colname_cuenta=$_GET['cuenta'];
}


if (isset($_GET['cuenta'])) {
							  $colname_cuenta=$_GET['cuenta'];
							}
							
							mysql_select_db($database_crm, $crm);
							$query_cuenta = sprintf("SELECT * FROM Cuentas WHERE idCuentas = %s", GetSQLValueString($colname_cuenta, "text"));
							$cuenta= mysql_query($query_cuenta, $crm) or die(mysql_error());
							$row_cuenta = mysql_fetch_assoc($cuenta);
							$totalRows_cuenta = mysql_num_rows($cuenta);


mysql_select_db($database_crm, $crm);
$query_cuenta = sprintf("SELECT * FROM Cuentas WHERE idCuentas = %s", GetSQLValueString($colname_cuenta, "text"));
$cuenta= mysql_query($query_cuenta, $crm) or die(mysql_error());
$row_cuenta = mysql_fetch_assoc($cuenta);
$totalRows_cuenta = mysql_num_rows($cuenta);

//Busqueda datos propuesta

mysql_select_db($database_crm, $crm);
$query_oportunidad = sprintf("SELECT * FROM Oportunidad WHERE Cuentas_idCuentas = %s", GetSQLValueString($colname_cuenta, "text"));
$oportunidad= mysql_query($query_oportunidad, $crm) or die(mysql_error());
$row_oportunidad = mysql_fetch_assoc($oportunidad);
$totalRows_oportunidad = mysql_num_rows($oportunidad);

//Busqueda datos contacto

mysql_select_db($database_crm, $crm);
$query_contacto = sprintf("SELECT * FROM Contactos WHERE Cuentas_idCuentas = %s AND Estado='Activo'", GetSQLValueString($colname_cuenta, "text"));
$contacto= mysql_query($query_contacto, $crm) or die(mysql_error());
$row_contacto = mysql_fetch_assoc($contacto);
$totalRows_contacto = mysql_num_rows($contacto);

//Busqueda datos de Oportunidades 
mysql_select_db($database_crm, $crm);
$query_asesorn = sprintf("SELECT * FROM Oportunidad as Op INNER JOIN Asesores as ase ON  Op.Asesores_idAsesor=ase.idAsesor WHERE Op.Cuentas_idCuentas=%s", GetSQLValueString($colname_cuenta,"text"));
$asesorn= mysql_query($query_asesorn, $crm) or die(mysql_error());
$row_asesorn = mysql_fetch_assoc($asesorn);
$totalRows_asesorn = mysql_num_rows($asesorn);		


//Conteo de las propuestas
mysql_select_db($database_crm, $crm);
$query_conteo = sprintf("SELECT COUNT(*) FROM Oportunidad WHERE Oportunidad.Cuentas_idCuentas  = %s AND Oportunidad.estadoOportunidad LIKE '%%Abierta%%' ", GetSQLValueString($colname_cuenta, "text"));
$conteo = mysql_query($query_conteo, $crm) or die(mysql_error());
$row_conteo = mysql_fetch_assoc($conteo);
$totalRows_conteo = mysql_num_rows($conteo);

mysql_select_db($database_crm, $crm);
$query_conteo1 = sprintf("SELECT COUNT(*) FROM Oportunidad WHERE Oportunidad.Cuentas_idCuentas = %s AND Oportunidad.estadoOportunidad LIKE '%%Positiva%%' ", GetSQLValueString($colname_cuenta, "text"));
$conteo1 = mysql_query($query_conteo1, $crm) or die(mysql_error());
$row_conteo1 = mysql_fetch_assoc($conteo1);
$totalRows_conteo1 = mysql_num_rows($conteo1);	

mysql_select_db($database_crm, $crm);
$query_conteo2 = sprintf("SELECT COUNT(*) FROM Oportunidad WHERE Oportunidad.Cuentas_idCuentas = %s AND Oportunidad.estadoOportunidad LIKE '%%Negativa%%' ", GetSQLValueString($colname_cuenta, "text"));
$conteo2 = mysql_query($query_conteo2, $crm) or die(mysql_error());
$row_conteo2 = mysql_fetch_assoc($conteo2);
$totalRows_conteo2 = mysql_num_rows($conteo2);	

mysql_select_db($database_crm, $crm);
$query_conteo3 = sprintf("SELECT COUNT(*) FROM Oportunidad WHERE Oportunidad.Cuentas_idCuentas = %s ", GetSQLValueString($colname_cuenta, "text"));
$conteo3 = mysql_query($query_conteo3, $crm) or die(mysql_error());
$row_conteo3 = mysql_fetch_assoc($conteo3);
$totalRows_conteo3 = mysql_num_rows($conteo3);		

//Busqueda de tareas pendientes a punto de vencerse
mysql_select_db($database_crm, $crm);
$query_alerta = sprintf("SELECT Cuentas_idCuentas,Oportunidad_idOportunidad, nombreEmpresa, TIMESTAMPDIFF( DAY ,fechaProgramada, CURRENT_DATE()) as diferencia FROM Tarea as tr INNER JOIN Cuentas as ct ON tr.Cuentas_idCuentas=ct.idCuentas WHERE tr.Asesores_idAsesor=%s AND tr.Realizada='Programada' AND (TIMESTAMPDIFF(DAY ,fechaProgramada, CURRENT_DATE())>-2)", GetSQLValueString($row_usuario['idAsesor'], "text"));
$alerta = mysql_query($query_alerta, $crm) or die(mysql_error());
$row_alerta = mysql_fetch_assoc($alerta);
$totalRows_alerta = mysql_num_rows($alerta);				
							
							
 ?>
<!DOCTYPE html>
<html>
<head>
<meta charset http-equiv="content-type" content="text/html;charset= utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>CRM GENNCO| Cuentas</title>
<link rel="shortcut icon" type="image/x-icon" href="../../dist/img/favicon.ico">
<!-- Tell the browser to be responsive to screen width -->
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<!-- Bootstrap 3.3.6 -->
<link rel="stylesheet" href="../../bootstrap/css/bootstrap.min.css">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
<!-- Ionicons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
<!-- Theme style -->
<link rel="stylesheet" href="../../dist/css/AdminLTE.min.css">
<!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
<link rel="stylesheet" href="../../dist/css/skins/_all-skins.min.css">
<!-- DataTables -->
  <link rel="stylesheet" href="../../plugins/datatables/dataTables.bootstrap.css">
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <!-- Script para bloquear caracteres, solo para que se puedan escribir numeros -->
 <script type="text/javascript"> function controltag(e) {
        tecla = (document.all) ? e.keyCode : e.which;
        if (tecla==8) return true;
        else if (tecla==0||tecla==9)  return true;
       // patron =/[0-9\s]/;// -> solo letras
        patron =/[0-9\s]/;// -> solo numeros
        te = String.fromCharCode(tecla);
        return patron.test(te);
    }
	</script>
<script>
    
 $(document).ready(function() {
    $('#example1').DataTable();
} );   

	</script>
    
</head>

<body class="hold-transition skin-blue sidebar-mini">


<div class="wrapper">
  <header class="main-header"> 
    <!-- Logo --> 
 <a href="../../inicio.php" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
       <span class="logo-mini"><b><image src="../../dist/img/logocrm.png" style=" width: 50px; height: 50px"></b></span>	
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><image src="../../dist/img/logocrm.png" style=" width: 50px; height: 50px" ><b>CRM</b>GENNCO </span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
   <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- Messages: style can be found in dropdown.less-->
          <li class="dropdown messages-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-envelope-o"></i>
              <span class="label label-success"></span>
            </a>
            <ul class="dropdown-menu">
         </ul>
          </li>
               <!-- Notifications: style can be found in dropdown.less -->
          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-bell-o"></i>
              <span class="label label-warning"><?php if($totalRows_alerta > 0){echo $totalRows_alerta;}?></span>
            </a>
            <ul class="dropdown-menu">
             <?php if($totalRows_alerta > 0){?>
              <li class="header">Tiene <?php echo $totalRows_alerta?> tarea(s) por vencerse</li>
              <li>
                <!-- inner menu: contains the actual data -->
                <ul class="menu">
                <?php
				do{
                  echo"<li>
                    <a href='pages/forms/perfiloportunidad.php? oportunidad=".$row_alerta['Oportunidad_idOportunidad']."'>
                      <i class='fa fa-calendar text-red'></i> La oportunidad ".$row_alerta['Oportunidad_idOportunidad']." de <br> la empresa ".$row_alerta['nombreEmpresa'].".<br> Tiene una tarea por vencerse.
                    </a>
                  </li>";
				} while ($row_alerta = mysql_fetch_assoc($alerta));
				 ?>
                 </ul>
              </li>
              <li class="footer"><a href="#"></a></li>
              <?php } ?>
            </ul>
          </li>
          <!-- Tasks: style can be found in dropdown.less -->
          <li class="dropdown tasks-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-flag-o"></i>
              <span class="label label-danger"></span>
            </a>
            <ul class="dropdown-menu">
            </ul>
          </li>
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="../../<?php echo $row_usuario['foto'] ?>" class="user-image" alt="User Image">
              <span class="hidden-xs"><?php echo $row_usuario['nombreAsesor']?></span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <img src="../../<?php echo $row_usuario['foto'] ?>" class="img-circle" alt="User Image">

                <p>
                <?php echo $row_usuario['cargoAsesor']?>
                </p>
              </li>  
              <!-- Menu Footer-->
              <li class="user-footer">
               
                <div class="pull-right">
                  <a href="<?php echo $logoutAction ?>" class="btn btn-default btn-flat">Cerrar Sesión</a>
                </div>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>
  </header>
  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar"> 
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar"> 
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="../../<?php echo $row_usuario['foto'] ?>" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p><?php echo $row_usuario['nombreAsesor']?></p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
        <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">
        <li class="header">MENÚ PRINCIPAL</li>
        <li class="treeview">
          <a href="../../inicio.php">
            <i class="fa fa-dashboard"></i> <span>Pagina Principal</span>
          </a>  
       
       
          <ul class="treeview-menu">
            <li><a href="../../inicio.php"><i class="fa fa-circle-o"></i> Pagina Principal</a></li>
          </ul>
       
      
                <li class="treeview active">
          <a href="#">
            <i class="fa fa-edit"></i> <span>Cuentas</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li ><a href="nuevoregistro.php"><i class="fa fa-circle-o"></i> Crear cuentas</a></li>
             <li ><a href="cuentascreadas.php"><i class="fa fa-circle-o"></i> Cuentas Creadas</a></li>
          </ul>
        </li>
           <li class="treeview">
          <a href="#">
            <i class="fa fa-edit"></i> <span>Oportunidades</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li ><a href="nuevaoportunidad.php"><i class="fa fa-circle-o"></i> Crear Nueva oportunidad</a></li>
             <li><a href="oportunidadescreadas.php"><i class="fa fa-circle-o"></i> Oportunidades Creadas</a></li>
          </ul>
        </li>
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>
  
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper"> 
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1> Perfil de la cuenta </h1>
      <ol class="breadcrumb">
        <li><a href="../../inicio.php"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li><a href="#">Cuentas</a></li>
        <li><a href="cuentascreadas.php">Cuentas Creadas</a></li>
        <li class="active">Perfil Cuentas</li>
      </ol>
    </section>
    
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-3"> 
          
          <!-- Profile Image -->
          <div class="box box-primary">
            <div class="box-body box-profile"> <img class="profile-user-img img-responsive img-circle" src="../../<?php echo $row_cuenta['logoCuenta']?>" alt="User profile picture">
              <h3 class="profile-username text-center"><?php echo $row_cuenta['nombreEmpresa']?></h3>
              <p class="text-muted text-center">Nit:<?php echo $row_cuenta['Nit']?></p>
              <ul class="list-group list-group-unbordered">
                <li class="list-group-item"> <b>Propuestas Creadas</b> <a class="pull-right"><?php echo $row_conteo3['COUNT(*)']; ?></a> </li>
                <li class="list-group-item"> <b>Propuestas Abiertas</b> <a class="pull-right"><?php echo $row_conteo['COUNT(*)']; ?></a> </li>
                <li class="list-group-item"> <b>Propuestas Cerradas Positivamente</b> <a class="pull-right"><?php echo $row_conteo1['COUNT(*)']; ?></a> </li>
                <li class="list-group-item"> <b>Propuestas Cerradas Negativamente</b> <a class="pull-right"><?php echo $row_conteo2['COUNT(*)']; ?></a> </li>
              </ul>
              <a href="perfilcuentaedita.php?cuenta=<?php echo $row_cuenta['idCuentas']?>" class="btn btn-primary btn-block"><b>Editar datos de la Cuenta</b></a> 
              <a href="cuentascreadas.php" class="btn btn-primary btn-block"><b>Volver a listado cuentas</b></a>             
            </div>
            <!-- /.box-body --> 
          </div>
          <!-- /.box --> 
          
          <!-- About Me Box -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Información empresa</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body"> <strong><i class="fa fa-book margin-r-5"></i> Codigo CRM</strong>
              <p class="text-muted"> <?php echo $row_cuenta["idCuentas"]?> </p>
              <hr>
              <strong><i class="fa fa-map-marker margin-r-5"></i> Dirección</strong>
              <p class="text-muted"><?php echo $row_cuenta['direccionCuenta']?></p>
              <hr>
              <strong><i class="margin-r-5 ion-ios-email"></i>Correo</strong>
              <p> <?php echo $row_cuenta['correoCuenta']?></p>
              <hr>
              <strong><i class="fa fa-file-text-o margin-r-5 ion-android-call"></i> Teléfonos</strong>
              <p class="ion-iphone">  Celular: <?php echo $row_cuenta['celularCuenta']?></p> 
              <p class="ion-ios-telephone">  Fijo: <?php echo $row_cuenta['telefonoCuenta']?></p>
               <hr>
              <strong><i class="fa fa-file-text-o margin-r-5"></i> Creado desde:</strong>
              <p><?php echo $row_cuenta['fechaCreacion']?></p>
            
            </div>
            
            <!-- /.box-body --> 
          </div>
          <!-- /.box --> 
        </div>
        <!-- /.col -->
        <div class="col-md-9">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#activity2" data-toggle="tab">Contactos Creados</a></li>
              <li ><a href="perfilcuentanc.php?cuenta=<?php echo $row_cuenta['idCuentas']?>">Nuevo contacto</a></li>
            </ul>
            <div class="tab-content">
            
              <div class="active tab-pane" id="activity2">
                <div class="box">
                  <div class="box-header">
                    <h3 class="box-title">Listado de Contactos Creados</h3>
                  </div>
                  <!-- /.box-header -->
                  <div class="box-body">
                    
                     
                        <?php
							 	
						 if($totalRows_contacto > 0){
							 echo"<table id='example1' class='table table-bordered table-striped'>";
								echo"<thead>";
								echo "<tr>";
								echo "<th>Codigo CRM</th>";
								echo "<th>Nombre contacto</th>";
								echo "<th>Cargo</th>";
								echo"<th>Telefono</th>";
								echo"<th>Correo</th>";
								echo"<th>Modificar</th>";
								echo"</tr>";
								echo"</thead>";
						
							 
                         do{
									    echo "<tr>";
										 echo "<td>".$row_contacto['idContacto']."</td>";
										  echo "<td>".$row_contacto['nombreContacto']."</td>";
										  echo "<td>".$row_contacto['cargoContacto']."</td>";
										  echo "<td>".$row_contacto['telefonoContacto']."</td>";
										  echo "<td>".$row_contacto['correoContacto']."</td>";
										  echo "<td> <a href=perfilcuentamodifica.php?contacto=".$row_contacto['idContacto']."&cuenta=".$row_cuenta['idCuentas']."><button type='submit' class='btn btn-primary'/>Modificar</button></a></td>";		
										  echo "</tr>";
									
								  }while ($row_contacto = mysql_fetch_assoc($contacto));
						    	 echo"</tbody>";
								 echo "<tfoot>";
								 echo "<tr>";
								 echo "<th>Codigo CRM</th>";
							     echo "<th>Nombre contacto</th>";
								 echo"<th>Cargo</th>";
								 echo"<th>Telefono</th>";
							     echo"<th>Correo</th>";
							     echo"<th>Modificar</th>";
							     echo "</tr>";
								 echo "</tfoot>";
							     echo"</table>";
					  }
   						else{ 
						      echo "<tr>";
							  echo"<a href='nuevaoportunidad.php'><button type='button' class='btn btn-block btn-danger btn-lg ion-alert-circled'> No hay contactos que mostrar</button></a>";
				              echo "</tr>";
						 }
						
                        ?>
                  </div>
                  <!-- /.box-body --> 
                </div>
                <!-- /.box --> 
              </div>
              <!-- /.tab-pane --> 
            </div>
            <!-- /.tab-content --> 
          </div>
          <!-- /.nav-tabs-custom --> 
        
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#activity" data-toggle="tab">Oportunidades Activas</a></li>
            </ul>
            <div class="tab-content">
              <div class="active tab-pane" id="activity">
                <div class="box">
                  <div class="box-header">
                    <h3 class="box-title">Listado de oportunidades activas</h3>
                  </div>
                  <!-- /.box-header -->
                  <div class="box-body">
             <?php 
		       if($totalRows_oportunidad >0){
							
							echo"<table id='example1' class='table table-bordered table-striped'>";
							echo"<thead>";
							echo "<tr>";
						    echo"<th>Propuesta N° </th>";
					        echo "<th>Asesor</th>";
							echo"<th>Monto</th>";
							echo"<th>Fecha Inicio</th>";
							echo"<th>Estado</th>";
							echo" <th>Detalles</th>";
						    echo"</tr>";
							echo"</thead>";
							echo"<tbody>";
						    echo"<tr>";
                 
						   do{
							  echo "<tr>";
							  echo "<td>".$row_asesorn['idOportunidad']."</td>";
							  echo "<td>".$row_asesorn['nombreAsesor']."</td>";
							  echo "<td>".$row_asesorn['montoOportunidad']."</td>";
							  echo "<td>".$row_asesorn['fechaCreacion']."</td>";
							  echo "<td>".$row_asesorn['estadoOportunidad']."</td>";
							  echo "<td> <a href=perfiloportunidad.php?oportunidad=".$row_asesorn['idOportunidad']."><button type='submit' class='btn btn-primary'/>Ver Oportunidad</button></a></td>";		
							  echo "<tr>";
						} while ($row_asesorn = mysql_fetch_assoc($asesorn));	   
							
						   echo"</tr>"; 
						  echo"</tbody>";
						  echo"<tfoot>";
							echo"<tr>";
							  echo"<th>Propuesta N°</th>";
							  echo"<th>Asesor</th>";
							  echo"<th>Monto</th>";
							  echo"<th>Fecha Inicio</th>";
							  echo"<th>Estado</th>";
							  echo"<th>Detalles</th>";
							echo"</tr>";
						  echo "</tfoot>";
						echo"</table>";
					   
                    }
   					else{ 
						      echo "<tr>";
							  echo"<button type='button' class='btn btn-block btn-danger btn-lg ion-alert-circled'> No hay oportunidades que mostrar</button>";
				              echo "</tr>";
					 }  
				 ?>			                  
                  </div>
                  <!-- /.box-body --> 
                </div>
                <!-- /.box --> 
              </div>
              <!-- /.tab-pane -->
             
            </div>
            <!-- /.tab-content --> 
          </div>
          <!-- /.nav-tabs-custom --> 
        </div>
        <!-- /.col --> 
      </div>
      <!-- /.row --> 
      
    </section>
    <!-- /.content --> 
  </div>
  <!-- /.content-wrapper -->
  <footer class="main-footer">
    <div class="pull-right hidden-xs"> <b>Version</b> 2.3.6 </div>
    <strong>Copyright &copy; 2014-2016 <a href="http://almsaeedstudio.com">Almsaeed Studio</a>.</strong> All rights
    reserved. 
    </footer>
</div>
<!-- ./wrapper --> 

<!-- jQuery 2.2.3 --> 
<script src="../../plugins/jQuery/jquery-2.2.3.min.js"></script> 
<!-- Bootstrap 3.3.6 --> 
<script src="../../bootstrap/js/bootstrap.min.js"></script> 
<!-- DataTables --> 
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script> 
<script src="../../plugins/datatables/dataTables.bootstrap.min.js"></script> 
<!-- SlimScroll --> 
<script src="../../plugins/slimScroll/jquery.slimscroll.min.js"></script> 
<!-- FastClick --> 
<script src="../../plugins/fastclick/fastclick.js"></script> 
<!-- AdminLTE App --> 
<script src="../../dist/js/app.min.js"></script> 
<!-- AdminLTE for demo purposes --> 
<script src="../../dist/js/demo.js"></script> 
<!-- page script --> 
<script>
    
 $(document).ready(function() {
    $('#example1').DataTable();
	
} );   

	</script>




</body>
</html>