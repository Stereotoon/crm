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
// modifica el valor de la oportunidad en la tabla Oportunidad y en el respectivo campo
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "modificaValor")) { // Al momento de oprimir el boton continuar verifica el nombre del formulario y ejecuta los comandos sql
  $updateSQL = sprintf("UPDATE Oportunidad SET montoOportunidad=%s WHERE idOportunidad=%s",
                       GetSQLValueString($_POST['montoOportunidad'], "int"),
                       GetSQLValueString($_POST['idOportunidad'], "text"));

  mysql_select_db($database_crm, $crm);// envía a la siguiente página despues de oprimir el botón continuar
  $Result1 = mysql_query($updateSQL, $crm) or die(mysql_error());

  $updateGoTo = "perfiloportunidad.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
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

if($row_oportunidad['estadoOportunidad']=='Negativa' || $row_oportunidad['estadoOportunidad']=='Positiva'){
header("Location:../../pages/forms/oportunidadcerrada.php?oportunidad=".$row_oportunidad['idOportunidad']."");
}


mysql_select_db($database_crm, $crm);
$query_asesor = sprintf("SELECT * FROM Oportunidad as Op INNER JOIN Asesores as ase ON  Op.Asesores_idAsesor=ase.IdAsesor WHERE Op.Asesores_idAsesor=%s", GetSQLValueString($row_oportunidad['Asesores_idAsesor'],"text"));
$asesor= mysql_query($query_asesor, $crm) or die(mysql_error());
$row_asesor = mysql_fetch_assoc($asesor);
$totalRows_asesor = mysql_num_rows($asesor);

mysql_select_db($database_crm, $crm);
$query_cuenta = sprintf("SELECT * FROM Oportunidad as Op INNER JOIN Cuentas as ct ON  Op.Cuentas_idCuentas=ct.idCuentas WHERE Op.Cuentas_idCuentas=%s", GetSQLValueString($row_oportunidad['Cuentas_idCuentas'],"text"));
$cuenta= mysql_query($query_cuenta, $crm) or die(mysql_error());
$row_cuenta = mysql_fetch_assoc($cuenta);
$totalRows_cuenta = mysql_num_rows($cuenta);


//Busqueda de historial de oportunidad de las actividades realizadas en cada una
mysql_select_db($database_crm, $crm);
$query_historial= sprintf("SELECT * FROM relacionHistorial as con INNER JOIN Asesores as ase ON  ase.idAsesor=con.Asesores_idAsesor  WHERE con.Oportunidad_idOportunidad=%s ORDER BY con.fechaActualizacion DESC", GetSQLValueString($row_oportunidad['idOportunidad'],"text"));
$historial= mysql_query($query_historial, $crm) or die(mysql_error());
$row_historial = mysql_fetch_assoc($historial);
$totalRows_historial= mysql_num_rows($historial);



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
<title>CRM GENNCO | Oportunidades</title>
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
<!-- daterange picker -->
  <link rel="stylesheet" href="../../plugins/daterangepicker/daterangepicker.css">
  <!-- bootstrap datepicker -->
  <link rel="stylesheet" href="../../plugins/datepicker/datepicker3.css">
<!-- DataTables -->
  <link rel="stylesheet" href="../../plugins/datatables/dataTables.bootstrap.css">

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <header class="main-header">
    <!-- Logo -->

   <a href="../../inicio.php" class="logo">
       <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b><image src="../../dist/img/logocrm.png" style=" width: 50px; height: 50px"></b></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><image src="../../dist/img/logocrm.png" style="width:50px; height: 50px" ><b>CRM</b>GENNCO </span>
    </a>
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
        <li class="header">MEN&Uacute; PRINCIPAL</li>
        <li class="treeview">
          <a href="../../inicio.php">
            <i class="fa fa-dashboard"></i> <span>Pagina Principal</span>
          </a>

          </li>


                <li class="treeview">
          <a href="#">
            <i class="fa fa-edit"></i> <span>Cuentas</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li ><a href="nuevoregistro.php" class="active"><i class="fa fa-circle-o"></i> Crear cuentas</a></li>
             <li><a href="cuentascreadas.php" ><i class="fa fa-circle-o"></i> Cuentas Creadas</a></li>
          </ul>
        </li>
           <li class="treeview active">
          <a href="#">
            <i class="fa fa-edit"></i> <span>Oportunidades</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
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
      <h1> Detalles de la oportunidad </h1>
      <ol class="breadcrumb">
        <li><a href="../../inicio.php"><i class="fa fa-dashboard"></i>Inicio</a></li>
        <li><a href="#">Oportunidad</a>ades</li>
        <li><a href="oportunidadescreadas.php">Oportunidadades Creadas</a></li>
        <li class="active">Detalles Oportunidad </li>
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

              <form action="<? echo $editFormAction?>" method="post" name="modificaValor">
              <p class="text-muted"> <?php echo number_format($row_oportunidad['montoOportunidad'],0,",",".")?> </p>&nbsp; &nbsp; <button type="button" class="btn btn-primary" style="float:right" onclick="Habilitar()">Modificar Valor<br>
              </button>
              <p id="activar">
                <script>
					function Habilitar() {
						document.getElementById("activar").innerHTML = " <br> <input type='hidden' name='idOportunidad' value='<?php echo $row_oportunidad['idOportunidad']?>'/>   <input type='hidden' name='MM_update' value='modificaValor'/> <input type='number' class='form-control'  name='montoOportunidad' placeholder='Digite el nuevo valor' required> <br><input type='submit' class='btn btn-primary' style='float:right' value='Modificar'> <br>"
					}
				</script></p>
              </form>
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
              <li class="active"><a href="#">Historial Oportunidad</a></li>
              <li><a href="perfiloportunidadme.php? oportunidad=<?php echo $row_oportunidad['idOportunidad']?>">Modificar Estado</a></li>
              <li ><a href="perfiloportunidadllamada.php? oportunidad=<?php echo $row_oportunidad['idOportunidad']?>">Crear Llamada</a></li>
              <li><a href="perfiloportunidadcorreo.php? oportunidad=<?php echo $row_oportunidad['idOportunidad']?>">Crear Correo</a></li>
              <li><a href="perfiloportunidadvisita.php? oportunidad=<?php echo $row_oportunidad['idOportunidad']?>">Crear Visita</a></li>
              <li><a href="perfiloportunidadactividad.php? oportunidad=<?php echo $row_oportunidad['idOportunidad']?>">Crear Tarea</a></li>
              <li><a href="perfilhistorialtarea.php? oportunidad=<?php echo $row_oportunidad['idOportunidad']?>">Reporte de Tareas</a></li>
            </ul>
            <div class="tab-content">
              <div class="active tab-pane" id="settings1">
                <div class="tab-pane" id="settings1">
                <!-- The timeline -->
                <ul class="timeline timeline-inverse">
                <li>
                    <i class="fa fa-clock-o bg-gray"></i>
                  </li>
                  <br>

                  <!-- timeline item: Por cada fila de la consulta hecha en el historial se creara una de estas etiquetas segun el tipo de actividad realizada-->

                     <?php

					    do{
					  	 if ($row_historial['TipoActividad']== "Llamada"){


				       	  echo" <li>";
                          echo "<i class='fa fa-envelope bg-blue ion-android-call'></i>";
						  echo"<div class='timeline-item'>";
						  echo "<span class='time'><i class='fa fa-clock-o'></i><b>".$row_historial['fechaActualizacion']."</b></span>";
						  echo"<h3 class='timeline-header'><a href='#'>".$row_historial['nombreAsesor']." </a> ha llamado y ha documentado lo siguiente:</h3>";
						  echo"<div class='timeline-body'>";
						  echo $row_historial['actividad'];
						  echo"</div>";
						  echo"</li>";
					   }

				       else if ($row_historial['TipoActividad']== "Correo"){


				       	  echo"<li>";
                          echo "<i class='fa fa-envelope bg-orange ion-android-mail'></i>";
						  echo"<div class='timeline-item'>";
						  echo "<span class='time'><i class='fa fa-clock-o'></i><b>".$row_historial['fechaActualizacion']."</b></span>";
						  echo"<h3 class='timeline-header'><a href='#'>".$row_historial['nombreAsesor']." </a> ha enviado un correo con el siguiente asunto:</h3>";
						  echo"<div class='timeline-body'>";
						   echo $row_historial['Asunto'];
						  echo"</div>";
						   echo"</li>";
						}

	                   else if ($row_historial['TipoActividad']== "Visita"){

				       	  echo" <li>";
                          echo "<i class='fa fa-envelope bg-purple ion-android-people'></i>";
						  echo"<div class='timeline-item'>";
						  echo "<span class='time'><i class='fa fa-clock-o'></i><b>".$row_historial['fechaActualizacion']."</b></span>";
						  echo"<h3 class='timeline-header'><a href='#'>".$row_historial['nombreAsesor']." </a> ha concretado una visita :</h3>";
						  echo"<div class='timeline-body'>";
						  echo "El día: <b>".$row_historial['fechaVisita']."</b> a las <b>".$row_historial['horaVisita']."</b> <br> En la dirección: ".$row_historial['direccionEvento']."<br>";
              echo "<b>Con las siguientes actividades:</b> ".$row_historial['detalleEvento'];
						  echo"</div>";
						  echo"</li>";
					   }

					} while ($row_historial=mysql_fetch_array($historial));

				    ?>
                     <li>
                    <i class="fa fa-envelope bg-green ion-plus-circled"></i>

                    <div class="timeline-item ">
                      <span class="time"><i class="fa fa-clock-o"></i><b><?php echo $row_oportunidad['fechaCreacion']?></b></span>

                      <h3 class="timeline-header"><a href="#">Inicio </a> se ha creado una oportunidad</h3>

                      <div class="timeline-body">
                       Se ha creado la oportunidad con exito para respectivos procesos de gestion.
                      </div>
                      </div>
                    </li>

                  </li>
                  <!-- END timeline item -->
                    <!-- timeline time label -->
                  <li class="time-label">
                        <span class="bg-red">
                          <?php echo $row_oportunidad['fechaCreacion']?>
                        </span>
                  </li>
                  <!-- /.timeline-label -->

                </ul>
              </div>
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


<script>
  $(function () {
    $("#example1").DataTable();
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false
    });
  });
</script>



</body>
</html>
