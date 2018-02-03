<?php require_once('../../connections/crm.php');  /// conecta a base de datos ?>
<?php
header("Content-Type: text/html;charset=utf-8");
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
     header("../../logincrm.html"); //redirigir al punto de partida para identificarse  
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

// Se invoca la accion editFormaAction para realizar las operaciones que de aqu� en adelante se programan que se incluyen dentro del formularios//
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

// se insertan valores segun lo editado por el usuario. Estos valores bajo inyecci�n SQL se van a la tabla de la base de datos//
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "Correo")) {// Al momento de oprimir el boton continuar verifica el nombre del formulario y ejecuta los comandos sql
  $insertSQL = sprintf("INSERT INTO  Correo(Asunto,Oportunidad_idOportunidad, Asesores_idAsesor) VALUES (%s,%s,%s)",
                       GetSQLValueString($_POST['Asunto'], "text"),// Ingresa el numero de identificaci�n de la oportunidad con el cual el usuario esta llenando la encuesta                     
                       GetSQLValueString($_POST['Oportunidad_idOportunidad'], "text"),
					   GetSQLValueString($_POST['Asesores_idAsesor'], "text"),
					   GetSQLValueString($_POST['Cuenta_idCuenta'], "text"));
					   
  mysql_select_db($database_crm, $crm);// vinculaci�n de la base de datos para inyeccion sql
  $Result1 = mysql_query($insertSQL, $crm) or die(mysql_error());// Acción a realizar en  la base de datos para inyeccion sql

  $insertGoTo = "perfiloportunidadcorreo.php";// envía a la siguiente página despues de oprimir el botón continuar
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

//Busqueda datos cuentas y asesor

if (isset($_GET['oportunidad'])) {
  $colname_oportunidad=$_GET['oportunidad'];
}
mysql_select_db($database_crm, $crm);
$query_oportunidad = sprintf("SELECT * FROM Oportunidad WHERE idOportunidad = %s", GetSQLValueString($colname_oportunidad, "text"));
$oportunidad= mysql_query($query_oportunidad, $crm) or die(mysql_error());
$row_oportunidad = mysql_fetch_assoc($oportunidad);
$totalRows_oportunidad = mysql_num_rows($oportunidad);

mysql_select_db($database_crm, $crm);

$query_asesor = sprintf("SELECT * FROM Oportunidad as Op INNER JOIN Asesores as ase ON  Op.Asesores_idAsesor=ase.IdAsesor WHERE Op.Asesores_idAsesor=%s", GetSQLValueString($row_oportunidad['Asesores_idAsesor'],"text"));
$asesor= mysql_query($query_asesor, $crm) or die(mysql_error());
$row_asesor = mysql_fetch_assoc($asesor);
$totalRows_asesor = mysql_num_rows($asesor);

$query_cuenta = sprintf("SELECT * FROM Oportunidad as Op INNER JOIN Cuentas as ct ON  Op.Cuentas_idCuentas=ct.idCuentas WHERE Op.Cuentas_idCuentas=%s", GetSQLValueString($row_oportunidad['Cuentas_idCuentas'],"text"));
$cuenta= mysql_query($query_cuenta, $crm) or die(mysql_error());
$row_cuenta = mysql_fetch_assoc($cuenta);
$totalRows_cuenta = mysql_num_rows($cuenta);

mysql_select_db($database_crm, $crm);
$query_contacto = sprintf("SELECT * FROM Oportunidad as Op INNER JOIN Contactos as Cn ON  Op.Contacto_idContacto=Cn.idContacto WHERE Op.Contacto_idContacto=%s", GetSQLValueString($row_oportunidad['Contacto_idContacto'],"text"));
$contacto= mysql_query($query_contacto, $crm) or die(mysql_error());
$row_contacto = mysql_fetch_assoc($contacto);
$totalRows_contacto = mysql_num_rows($contacto);

// Mostrar próximo autoconsecutivo
$sql = "SHOW TABLE STATUS LIKE 'Correo'"; 
$result = mysql_query($sql); 
$row = mysql_fetch_array($result); 
$next_id = $row['Auto_increment']; //esta es la columna que contiene el dato (requiere privilegios de admin) 

if(isset($_POST['email'])&&isset($_POST['Asunto'])&&isset($_POST['comentario']) ){


//Incluimos la función
require_once('../../includes/class.phpmailer.php');
//Creamos la instancia de la clase PHPMailer y configuramos la cuenta

$archivo = $_FILES['fichero'];// Si hay archivos adjuntos
$mail=new PHPMailer();
$mail->Mailer="smtp";
$mail->Helo = "www.gennco.com.co"; //Muy importante para que llegue a hotmail y otros
$mail->SMTPAuth=true;
$mail->Host="mail.gennco.com.co";
$mail->Port=26; //depende de lo que te indique tu ISP. El default es 25, pero nuestro ISP lo tiene puesto al 26
$mail->Username=$row_usuario['email'];
$mail->Password=$row_usuario['contrasena'];
$mail->From=$row_usuario['email'];
$mail->FromName=$row_usuario['nombreAsesor'];
$mail->Timeout=60;
$mail->IsHTML(true);
//Enviamos el correo
$mail->AddAddress($_POST['email']); //Cualquier destino, por default trae el de la base de datos con relacion a la propuesta
$mail->AddCC($_POST['copia']);
$mail->AddBCC($row_usuario['email']);
$mail->Subject=($_POST['Asunto']);
$mail->Body=($_POST['comentario']);
$mail->AddAttachment($archivo['tmp_name'], $archivo['name']);
$exito = $mail->Send();
if($exito){
     $mail->ClearAddresses();
     echo "Mensaje enviado exitosamente";
}  
}

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
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>CRM GENNCO | Oportunidad</title>
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
<!-- Script para validación de textos-->
<script src="../../SpryAssets/SpryValidationTextarea.js" type="text/javascript"></script>
<link href="../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet" type="text/css"/>

<!-- Script para validación de campos textos-->
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css"/>
<script src="../../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

<style type="text/css">
.nombre {
	font-weight: bold;
	font-family: Verdana, Geneva, sans-serif;
}
.colorfirma {
	color: #999;
}
.rayacolor {
	color: #666;
	font-weight: bold;
}
.TAMAÑORAYITA {
	font-size: 36px;
}
.tamañoletra {
	font-size: 18px;
	font-family: Arial, Helvetica, sans-serif;
}
.prueb {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 16px;
}
.estasies {
	font-family: Arial, Helvetica, sans-serif;
	color: #666;
}
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <header class="main-header"> 
    <!-- Logo --> 
    <a href="../../inicio.php" class="logo"> 
    <!-- mini logo for sidebar mini 50x50 pixels --> 
    <!-- mini logo for sidebar mini 50x50 pixels --> 
    <span class="logo-mini"><b>
    <image src="../../dist/img/logocrm.png" style=" width: 50px; height: 50px">
    </b></span> 
    <!-- logo for regular state and mobile devices --> 
    <span class="logo-lg">
    <image src="../../dist/img/logocrm.png" style="width:50px; height: 50px" >
    <b>CRM</b>GENNCO </span> 
    <!-- Header Navbar: style can be found in header.less --> 
    </a>
    <nav class="navbar navbar-static-top"> 
      <!-- Sidebar toggle button--> 
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button"> <span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </a>
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- Messages: style can be found in dropdown.less-->
          <li class="dropdown messages-menu"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <i class="fa fa-envelope-o"></i> <span class="label label-success"></span> </a>
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
          <li class="dropdown tasks-menu"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <i class="fa fa-flag-o"></i> <span class="label label-danger"></span> </a>
            <ul class="dropdown-menu">
            </ul>
          </li>
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <img src="../../<?php echo $row_usuario['foto'] ?>" class="user-image" alt="User Image"> <span class="hidden-xs"><?php echo $row_usuario['nombreAsesor']?></span> </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header"> <img src="../../<?php echo $row_usuario['foto'] ?>" class="img-circle" alt="User Image">
                <p> <?php echo $row_usuario['cargoAsesor']?> </p>
              </li>
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-right"> <a href="<?php echo $logoutAction ?>" class="btn btn-default btn-flat">Cerrar Sesión</a> </div>
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
        <div class="pull-left image"> <img src="../../<?php echo $row_usuario['foto'] ?>" class="img-circle" alt="User Image"> </div>
        <div class="pull-left info">
          <p><?php echo $row_usuario['nombreAsesor']?></p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a> </div>
      </div>
      
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">
        <li class="header">MEN&Uacute; PRINCIPAL</li>
        <li class="treeview"> <a href="../../inicio.php"> <i class="fa fa-dashboard"></i> <span>Pagina Principal</span> </a> </li>
        <li class="treeview"> <a href="#"> <i class="fa fa-edit"></i> <span>Cuentas</span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
          <ul class="treeview-menu">
            <li ><a href="nuevoregistro.php" class="active"><i class="fa fa-circle-o"></i> Crear cuentas</a></li>
            <li><a href="cuentascreadas.php" ><i class="fa fa-circle-o"></i> Cuentas Creadas</a></li>
          </ul>
        </li>
        <li class="treeview active"> <a href="#"> <i class="fa fa-edit"></i> <span>Oportunidades</span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
          <ul class="treeview-menu">
            <li ><a href="nuevaoportunidad.php"><i class="fa fa-circle-o"></i> Crear Nueva oportunidad</a></li>
            <li class="active"><a href="oportunidadescreadas.php"><i class="fa fa-circle-o"></i> Oportunidades Creadas</a></li>
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
      <h1> Crear Correo</h1>
      <ol class="breadcrumb">
        <li><a href="../../inicio.php"><i class="fa fa-dashboard"></i>Inicio</a></li>
        <li><a href="#">Oportunidad</a>ades</li>
        <li><a href="oportunidadescreadas.php">Oportunidadades Creadas</a></li>
        <li><a href="perfiloportunidad.php">Detalles Oportunidad</a></li>
        <li class="active">Crear correo </li>
      </ol>
    </section>
    
    <!-- Main content -->
    <section class="content">
    <div class="row">
      <div class="col-md-3"> 
        
        <!-- Profile Image -->
        <div class="box box-primary">
          <div class="box-body box-profile"> <img class="profile-user-img img-responsive img-circle" src="../../<?php echo $row_cuenta['logoCuenta']?>" alt="User profile picture">
            <h3 class="profile-username text-center">Oportunidad</h3>
            <p class="text-muted text-center"><?php echo $row_oportunidad['idOportunidad']?> </p>
            <ul class="list-group list-group-unbordered">
              <li class="list-group-item"> <b>Nombre empresa:</b> <a class="pull-right" href="perfilcuenta.php?cuenta=<?php echo $row_cuenta['idCuentas']?>"><?php echo $row_cuenta['nombreEmpresa']?></a> </li>
              <li class="list-group-item"> <b>Estado de la oportunidad:</b> <a class="pull-right"><?php echo $row_oportunidad['estadoOportunidad']?></a> </li>
              <li class="list-group-item"> <b>Creada desde:</b> <a class="pull-right"><?php echo $row_oportunidad['fechaCreacion']?></a> </li>
            </ul>
            <a href="oportunidadescreadas.php" class="btn btn-primary btn-block"><b>Volver a listado oportunidades</b></a> </div>
          <!-- /.box-body --> 
        </div>
        <!-- /.box --> 
        
        <!-- About Me Box -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Información Oportunidad</h3>
          </div>
          <!-- /.box-header -->
          <div class="box-body"> <strong><i class="fa fa-book margin-r-5 ion-social-usd "></i> Monto</strong>
            <p class="text-muted"> <?php echo number_format($row_oportunidad['montoOportunidad'],0,',','.')?></p>
            <hr>
            <strong><i class="fa fa-tag margin-r-5"></i>Tipo Oportunidad</strong>
              <p class="text-muted"> <?php echo $row_oportunidad['tipoOportunidad']?></p>
              <hr>
            <strong><i class="fa fa-map-marker margin-r-5 ion-person"></i>Creada Por: </strong>
            <p class="text-muted"><?php echo $row_asesor['nombreAsesor']?></p>
            <hr>
          </div>
          
          <!-- /.box-body --> 
        </div>
        <!-- /.box --> 
      </div>
      <!-- /.col -->
      <div class="col-md-9">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li ><a href="perfiloportunidad.php? oportunidad=<?php echo $row_oportunidad['idOportunidad']?>">Historial Oportunidad</a></li>
            <li ><a href="perfiloportunidadme.php? oportunidad=<?php echo $row_oportunidad['idOportunidad']?>">Modificar Estado</a></li>
            <li ><a href="perfiloportunidadllamada.php? oportunidad=<?php echo $row_oportunidad['idOportunidad']?>">Crear Llamada</a></li>
            <li class="active"><a href="#">Crear Correo</a></li>
            <li><a href="perfiloportunidadvisita.php? oportunidad=<?php echo $row_oportunidad['idOportunidad']?>">Crear Visita</a></li>
            <li><a href="perfiloportunidadactividad.php? oportunidad=<?php echo $row_oportunidad['idOportunidad']?>">Crear Tarea</a></li>
            <li><a href="perfilhistorialtarea.php? oportunidad=<?php echo $row_oportunidad['idOportunidad']?>">Reporte de Tareas</a></li>
          </ul>
          <div class="tab-content">
            <div class=" active tab-pane" id="activity3">
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Crear un nuevo correo</h3>
                </div>
                <!-- /.box-header -->
                
                <div class="box-body">
                <form role="form" action="<?php $editFormAction ?>" method="post" name="Correo" enctype="multipart/form-data">
                  <div class="form-group">
                    <button class="bg-blue-active" disabled>Correo N° <?php echo $next_id?> </button>
                  </div>
                  <div class="form-group"> <span id="sprytextfield1">
                    <input class="form-control" placeholder="Para:" name="email" value="<?php echo $row_contacto['correoContacto']?>">
                    <span class="textfieldRequiredMsg">Debe digitar una dirección de correo electrónico</span></span> <br>
                    <input  type="email" class="form-control" placeholder="CC:" name="copia" >
                  </div>
                  <div class="form-group"> <span id="sprytextfield2">
                    <input class="form-control" placeholder="Asunto:" name="Asunto">
                    <span class="textfieldRequiredMsg">Debe digitar el asunto del correo </span></span> </div>
                  <div class="form-group"> <span id="sprytextarea1">
                    <textarea name="comentario" id="editor1" class="form-control" style="height: 300px">
                    <table style="box-shadow:8px 6px 10px #CCC; padding-left:10px;" width="700px" cellpadding="0" cellspacing="8" border="0">
                          <tr>
                            <td colspan="4"><img img style="background:#fff; border-radius:5px;" width="73" height="73" src="http://www.gennco.com.co/publicimages/images/logo.png" /><span class="rayacolor5" style="font-size: 18px">     <span style="font-family: Arial, Helvetica, sans-serif; color: #333; font-weight: bold; font-size: 18px;"> GENNCO LTDA</span></td>
                          </tr>
                          <tr>
                            <td class="nombre"><?php echo $row_usuario['nombreAsesor'] ?></td>
                            <td><span class="rayacolor" style="font-size:18px; margin-left:-300px;">|&nbsp;&nbsp; <span style="font-family: Arial, Helvetica, sans-serif"><?php echo $row_usuario['cargoAsesor']?></span></td>
                          </tr>
                          <tr>
                            <td colspan="4"><span class="rayacolor" style="font-size: 18px"><span style="font-family: Arial, Helvetica, sans-serif"><img src="http://www.gennco.com.co/publicimages/images/imagencorreo.jpg" alt="" width="45" height="28" /><a style="text-decoration:none; color:#06F; text-decoration:blink;"  href="mailto:<?php echo $row_usuario['email']?>"><?php echo $row_usuario['email']?></a>&nbsp;<img src="http://www.gennco.com.co/publicimages/images/imagentelefono.png"  width="28" height="28" /> +057 - <?php echo $row_usuario['celular']?>  </span></span></td>
                          </tr>
                          <tr>
                            <td width="30%"><span class="rayacolor1" style="font-size: 16px"><span style="font-family: Arial, Helvetica, sans-serif; color: #333; font-weight: bold;"><img src="http://www.gennco.com.co/publicimages/images/imagenubicacion.jpg" width="23" height="28" /> Cll 101 No 70-50</span></span></td>
                            <td width="35%"><span style="font-family: Arial, Helvetica, sans-serif"><span class="rayacolor1" style="font-size: 16px">| <span style="font-family: Arial, Helvetica, sans-serif; color: #333; font-weight: bold;">&nbsp;&nbsp;&nbsp;<img src="http://www.gennco.com.co/publicimages/images/imagentelefono.png" width="28" height="28" /> +057(1) - 3837809</span></span></td>
                          
                          </tr>
                          <tr>
                            <td colspan="4"><img src="http://www.gennco.com.co/publicimages/images/imagencorreo.jpg" alt="" width="45" height="28" /><a style="text-decoration:none; color:#F00; text-decoration:blink;"  href="mailto:gentecompetitiva@gmail.com"> <span class="rayacolor13" style="font-size: 18px"><span style="font-family: Arial, Helvetica, sans-serif; color: #06F; font-weight: bold;">gentecompetitiva@gmail.com</span>  <img src="http://www.gennco.com.co/publicimages/images/imagenglobo.jpg" width="28" height="28" /> <span style="font-family: Arial, Helvetica, sans-serif"><span class="rayacolor1" style="font-size: 16px"><span style="font-family: Arial, Helvetica, sans-serif; color: #333; font-weight: bold;">www.gennco.com.co</span></a></td>
                          </tr>
                          <tr>
                          <td colspan="4" align="left" valign="top" style="border-top-width:2px; border-top-style:solid; border-top-color:#dbeae7; padding-top:15px;">
                           <p align="center"><font face="Georgia, Times New Roman, Times, serif" color="#a6aeac"  style="font-size:14px;"><img src="http://www.firmasdecorreo.com/media/img-firmas/ico-eco.gif" alt="eco" width="14" height="14" align="absmiddle" /> No me imprimas si no es necesario. Protejamos el medio ambiente</font></p>     </td>
                         </tr>
					</textarea>
                    <span class="textareaRequiredMsg">El mensaje no debe estar en blanco, digite algunas frases.</span></span> </div>
                  <div class="form-group">
                    <div class="btn btn-default btn-file"> <i class="fa fa-paperclip"> Adjuntar</i>
                      <input type="file" name="fichero">
                    </div>
                    <p class="help-block">Max. 10MB</p>
                  </div>
                  </div>
                  <!-- /.box-body -->
                  <div class="box-footer">
                  <div class="pull-right">
                  <button type="reset" class="btn btn-default"onClick="document.getElementById('Correo').reset()"><i class="fa fa-pencil"></i>Borrar</button>
                  <input type="hidden" name="Oportunidad_idOportunidad" value="<?php echo $row_oportunidad['idOportunidad']?>" />
                  <input type="hidden"  name="Asesores_idAsesor" readonly value="<?php echo $row_usuario['idAsesor']?>">
                  <input type="hidden"  name="MM_insert" value="Correo">
                  <button type="submit" name="enviar" class="btn btn-primary"><i class="fa fa-envelope-o"></i> Enviar</button>
                </form>
              </div>
            </div>
            <!-- /.box-footer --> 
          </div>
          <!-- /. box --> 
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
  reserved. </footer>
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
<!-- Select2 --> 
<script src="../../plugins/select2/select2.full.min.js"></script> 
<!-- InputMask --> 
<script src="../../plugins/input-mask/jquery.inputmask.js"></script> 
<script src="../../plugins/input-mask/jquery.inputmask.date.extensions.js"></script> 
<script src="../../plugins/input-mask/jquery.inputmask.extensions.js"></script> 
<!-- Validación de campos de textos --> 
<script>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1","email");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
var sprytextarea1 = new Spry.Widget.ValidationTextarea ("sprytextarea1",{minChars:10, maxChars:5000});
</script> 
<!-- CK Editor --> 
<script src="https://cdn.ckeditor.com/4.5.7/standard/ckeditor.js"></script> 
<script>
  $(function () {
    // Replace the <textarea id="editor1"> with a CKEditor
    // instance, using default configuration.
    CKEDITOR.replace('editor1');
    //bootstrap WYSIHTML5 - text editor
    $(".textarea").wysihtml5();
  });
</script>
</body>
</html>