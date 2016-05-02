<?php
session_start();
@include('rw_config_smsgo.php');
include('rw_db_connect.php');
$login = '';
$password = '';
// si le formulaire est soumis
if (!empty($_POST)) {
	$errors = array();
	(!empty($_POST['login']))?$login=$_POST['login']:$errors[] = 'Vous devez renseigner un login';
	(!empty($_POST['password']))?$password=$_POST['password']:$errors[] = 'Vous devez renseigner un mot de passe';
	if(empty($errors)){
		// vérifications des informations fournis par le formulaire
		($login==$rw_config['su_login'])?'':$errors[] = 'Votre login est incorrect';
		($password==$rw_config['su_password'])?'':$errors[] = 'Votre mot de passe est incorrect';
		if(empty($errors)){
			
			$_SESSION['user']['smsgo'] = true;
			$_SESSION['user']['login'] = $login;
			// identification réussie avec succès
			header('location: rw_admin_smsgo.php');
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<head>
<title>SMS GO Dédicace Rentabiliweb : identification</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" type="image/png" href="http://cluster.rentabiliweb.com/data/i/favicon.png" />
<!--[if lt IE 8]>
<link href="http://cluster.rentabiliweb.com/data/i/favicon.ico" rel="shortcut icon"/>
<![endif]-->
<link rel="stylesheet" type="text/css" href="css/style.css" />
</head>
<body>
<div id="header"><div>Outil SMS Go Dédicace</div></div>


<?php
if(!empty($_SESSION['user']['smsgo'])) 
{	
	echo '<h1 style="margin-bottom: 5px;">SMS Go : Administration</h1>';
	echo '<p style="margin:auto; color: red; font-size:12px; border: solid 1px red; background-color: #F6C9D1; width: 400px;">vous êtes toujours connecté avec le login <strong>'.$_SESSION['user']['login'].'</strong></p>';
	echo "<input type='button' name='deconnexion' value='D&eacute;connexion' onclick='window.location.href=\"logout.php\"' style='margin-top: 5px; margin-bottom: 30px; font-size: 12px; width: 100px; height: 25px;'/>";
}
else echo '<h1>SMS Go Dédicace : Administration</h1>';
?>
<?php
if(!@fopen('rw_config_smsgo.php','r') && @fopen('rw_install_smsgo.php','r')){
	echo '<p><a href="rw_install_smsgo.php">Installation SMS GO</a></p>';
}
?>
<?php
if(!empty($errors)){
	echo '<ul style="border:1px solid red; width:425px; margin:auto; margin-bottom: 20px; background-color: #CCCCCC; font-weight:bold;">';
	foreach ($errors as $error_msg){
		echo '<li style="color:red;">'.$error_msg.'</li>';
	}
	echo '</ul>';
}
?>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<table class="table">
	<caption>Identification</caption>
	
	<tr>
		<td>
			Login
		</td>
		<td>
			<input type="text" name="login" value="<?php echo $login; ?>" />
		</td>
	</tr>
	<tr>
		<td>
			Mot de passe
		</td>
		<td>
			<input type="password" name="password" value="<?php echo $password; ?>" />
		</td>
	</tr>
</table>
<div class="button2"><input type="image" src="images/button_envoi.png" alt="Valider" /></div>
		

</form>
<div class="notice">
	<span style="padding-top:10px;">Votre login et votre mot de passe de connexion à votre base de données ont changé ?</span><br />
	<a href="rw_modif_config_log_bdd_smsgo.php">Modifier les informations relatives à la connexion à la base de données</a><br /><br />
	<span style="font-style:italic;">Vous souhaitez modifier votre login et votre mot de passe de connexion à l'administration ?</span><br /> 
	<a style="padding-bottom:10px;" href='rw_modif_config_log_admin_smsgo.php'>Modifier les informations relatives à la connexion à l'administration</a>
</div>	

</body>
</html>