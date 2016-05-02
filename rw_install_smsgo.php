<?php

error_reporting(E_ALL);
    @ini_set('display_errors', 1);

// si l'installation a déjà été effectué alors on quitte l'install
@include('rw_config_smsgo.php');
if(!empty($rw_config) && !empty($rw_config['install_ok'])){

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
		<title>SMS GO Dédicace Rentabiliweb : Installation</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="icon" type="image/png" href="http://cluster.rentabiliweb.com/data/i/favicon.png" />
		<!--[if lt IE 8]>
		<link href="http://cluster.rentabiliweb.com/data/i/favicon.ico" rel="shortcut icon"/>
		<![endif]-->
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		</head>
		<body>
		<div id="header"><div>Outil SMS Go Dédicace</div>

		</div>
		<h1 style="margin-bottom:20px;">Application Rentabiliweb SMS Go Dédicace déjà installée</h1>
		<a style="color:white" href="index.php">Identification</a>
		</body>
		</html>';


	exit();
}
// installation de l'application
$errors = array();//tableau de message d'erreurs de saisie (initialisation)
$db_connect_error = '';//message d'erreur de connexion (initialisation)
$db_error = '';//message d'erreur de BDD (initialisation)

if(!empty($_POST)){
	(!empty($_POST['rw_db_host']))?$rw_db_host=trim($_POST['rw_db_host']):$errors[]='Vous devez renseigner votre serveur';
	(!empty($_POST['rw_db_name']))?$rw_db_name=trim($_POST['rw_db_name']):$errors[]='Vous devez renseigner le nom de votre base de données';
	(!empty($_POST['rw_db_login']))?$rw_db_login=trim($_POST['rw_db_login']):$errors[]='Vous devez renseigner le login de connexion à votre base de données';
	(!empty($_POST['rw_db_pass']))?$rw_db_pass=trim($_POST['rw_db_pass']):$errors[]='Vous devez renseigner le mot de passe de connexion à votre base de données';
	(!empty($_POST['rw_db_table_prefixe']))?$rw_db_table_prefixe=trim($_POST['rw_db_table_prefixe']):$rw_db_table_prefixe='';
	(!empty($_POST['rw_admin_login']))?$rw_admin_login=trim($_POST['rw_admin_login']):$errors[]='Vous devez renseigner un login de connexion à votre administration';
	(!empty($_POST['rw_admin_pass']))?$rw_admin_pass=trim($_POST['rw_admin_pass']):$errors[]='Vous devez renseigner un mot de passe de connexion à votre administration';
	(!empty($_POST['rw_mot_cle']))?$rw_mot_cle=trim($_POST['rw_mot_cle']):$errors[]='Vous devez renseigner un mot clé pour sms go';
	if(empty($errors))
	{
		//Pour éviter les Warnings intempestifs
		error_reporting(0);

		//test de connexion au serveur que le client configure
		if(!mysql_connect(trim($_POST['rw_db_host']),trim($_POST['rw_db_login']),trim($_POST['rw_db_pass'])))
		{
			//pb de connexion au serveur, on récupère le numéro de l'erreur
			$errorCode = mysql_errno();
			//traitement selon le numéro de l'erreur
			if($errorCode==1045) $db_connect_error='Le login de connexion à la BDD<br />et/ou<br />le mot de passe de connexion à la BDD est erroné';
			else $db_connect_error='Le nom du serveur est erroné';
		}
		//test de connexion à la base de données que le client configure
		elseif(!mysql_select_db(trim($_POST['rw_db_name'])))
		{
			//pb de connexion à la base de données
			$db_error='Le nom de votre base de données est invalide';
		}
		else
		{
			//on ferme la connexion sql généré pdt le test au dessus
			mysql_close();

			// si on a aucune erreur, on procède à l'installation
			// génère le fichier de configuration qui sera ecrit en dur
			$rw_config_file = fopen('rw_config_smsgo.php','w');
			$rw_config_file_content =
"<?php
// tableau de la configuration de l'application
". '$rw_config'." = array (
// serveur mysql
'db_host' => '".$rw_db_host."',
// login de connexion
'db_login' => '".$rw_db_login."',
// mot de passe de connexion
'db_password' => '".$rw_db_pass."',
// nom de la base de données
'db_name' => '".$rw_db_name."',
// préfixe des tables
'db_tables_prefix' => '".$rw_db_table_prefixe."',
// table des sms go
'db_table_sms' => 'smsgo_live_message',
// login pour la page d'administration
'su_login' => '".$rw_admin_login."',
// mot de passe pour la page d'administration
'su_password' => '".$rw_admin_pass."',
// votre mot clé pour sms go configuré dans l'administration du site rentabiliweb.com pour sms go
'rw_mot_cle' => '".strtoupper($rw_mot_cle)."',
// nombre de message par page afficher dans l'administration
'nbr_message_per_page' => '10',
// installation réussie
'install_ok' => true,
);
?>";
			fwrite($rw_config_file,$rw_config_file_content);
			fclose($rw_config_file);
			// on load le fichier qu'on vient de créer pour les configs
			include('rw_config_smsgo.php');
			// connexion à la base de données
			include('rw_db_connect.php');

			$query1 = "	CREATE TABLE `".$rw_config['db_tables_prefix'].$rw_config['db_table_sms']."` (
						  `id` int(10) NOT NULL auto_increment,
						  `sms_content` varchar(200) NOT NULL default '',
						  `date_create` datetime NOT NULL default '000-00-00 00:00',
						  `validation` int(1) NOT NULL default '0',
						  `display` int(1) NOT NULL default '0',
						  PRIMARY KEY  (`id`)
						) TYPE=MyISAM;";
			$query1_exe = mysql_query($query1);
			if($query1_exe){
				// table créée avec succès
				header('location: index.php');
			}
		}
	}
}
// affichage des informations à fournir par le client pour créer la bdd (en gardant ceux éventuellement saisi par le client)
if(isset($_POST['rw_db_host']))	$rw_db_host = $_POST['rw_db_host'];
if(isset($_POST['rw_db_name'])) $rw_db_name = $_POST['rw_db_name'];
if(isset($_POST['rw_db_login'])) $rw_db_login = $_POST['rw_db_login'];
if(isset($_POST['rw_db_pass'])) $rw_db_pass = $_POST['rw_db_pass'];
if(isset($_POST['rw_db_table_prefixe']))
{
	$rw_db_table_prefixe = $_POST['rw_db_table_prefixe'];
}
else $rw_db_table_prefixe = 'rw_';

if(isset($_POST['rw_admin_login'])) $rw_admin_login = $_POST['rw_admin_login'];
if(isset($_POST['rw_admin_pass'])) $rw_admin_pass = $_POST['rw_admin_pass'];
if(isset($_POST['rw_mot_cle'])) $rw_mot_cle = $_POST['rw_mot_cle'];

//variable pour la couleur des messages d'erreur (dans les champs de saisie)
$error_style = 'color:red;';
?>
<?php
$url=explode('/', $_SERVER['REQUEST_URI']);
$nb_de_bouts=count($url);

$partie_a_remplacer=$url[$nb_de_bouts-1];
$remplace='rw_response.php';

$url_response = str_replace($partie_a_remplacer,$remplace,$_SERVER['REQUEST_URI']);
$url_response_full = 'http://'.$_SERVER['SERVER_NAME'].''.$url_response.'';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<title>SMS GO Dédicace Rentabiliweb : Installation</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" type="image/png" href="http://cluster.rentabiliweb.com/data/i/favicon.png" />
<!--[if lt IE 8]>
<link href="http://cluster.rentabiliweb.com/data/i/favicon.ico" rel="shortcut icon"/>
<![endif]-->
<link rel="stylesheet" type="text/css" href="css/style.css" />
</head>
<body>
<div id="header"><div>Outil SMS Go Dédicace</div></div>
<h1>SMS Go Dédicace : Installation</h1>
<?php
//affichage des erreurs de saisies (infos manquantes)
if(!empty($errors))
{
	echo '<ul>';
	foreach ($errors as $msg_error)
	{
		echo '<li>'.$msg_error.'</li>';
	}
	echo '</ul>';
}
?>
<?php
//affichage des erreurs de saisies (infos de connexion au serveur érronnées)
if(!empty($db_connect_error))
{
	echo '<ul><li>'.$db_connect_error.'</li></ul>';
}
?>
<?php
//affichage des erreurs de saisies (mauvaise BDD)
if(!empty($db_error))
{
	echo '<ul><li>'.$db_error.'</li></ul>';
}
?>

<form name="install_smsgo" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<table class="table">
	<caption>Informations d'installation SMS GO Dédicace</caption>
	<tr>
		<td>Votre serveur*</td>
		<td><input type="text" name="rw_db_host" value="<?php if(isset($_POST['rw_db_host'])) echo $rw_db_host; ?>" style="<?php if(!empty($db_connect_error) && $errorCode==2002) echo $error_style; ?>" onfocus="this.style.color='black'; this.select()"/></td>
	</tr>
	<tr>
		<td>Nom de votre base de données*</td>
		<td><input type="text" name="rw_db_name" value="<?php if(isset($_POST['rw_db_name'])) echo $rw_db_name; ?>" style="<?php if(!empty($db_error)) echo $error_style; ?>" onfocus="this.style.color='black'; this.select()"/></td>
	</tr>
	<tr>
		<td>Login de connexion à votre BDD*</td>
		<td><input type="text" name="rw_db_login" value="<?php if(isset($_POST['rw_db_login'])) echo $rw_db_login; ?>" style="<?php if(!empty($db_connect_error) && $errorCode==1045) echo $error_style; ?>" onfocus="this.style.color='black'; this.select()"/></td>
	</tr>
	<tr>
		<td>Mot de passe de connexion à votre BDD*</td>
		<td><input type="text" name="rw_db_pass" value="<?php if(isset($_POST['rw_db_pass'])) echo $rw_db_pass; ?>" style="<?php if(!empty($db_connect_error) && $errorCode==1045) echo $error_style; ?>" onfocus="this.style.color='black'; this.select()"/></td>
	</tr>
	<tr>
		<td>Préfixe pour la table sms go</td>
		<td><input type="text" name="rw_db_table_prefixe" value="<?php echo $rw_db_table_prefixe; ?>" /></td>
	</tr>
	<tr>
		<td>Login de connexion à l'administration*</td>
		<td><input type="text" name="rw_admin_login" value="<?php if(isset($_POST['rw_admin_login'])) echo $rw_admin_login; ?>" /></td>
	</tr>
	<tr>
		<td>Mot de passe de connexion à l'administration*</td>
		<td><input type="text" name="rw_admin_pass" value="<?php if(isset($_POST['rw_admin_pass'])) echo $rw_admin_pass; ?>" /></td>
	</tr>
	<tr>
		<td>Votre mot clé pour sms Go*</td>
		<td><input type="text" name="rw_mot_cle" value="<?php if(isset($_POST['rw_mot_cle'])) echo $rw_mot_cle; ?>" /></td>
	</tr>
	<tr class="normal">
		<td colspan="2" align="center" style="color:red;">* Champs obligatoires</td>
	</tr>
	<tr class="normal">
		<td colspan="2" align="center"><span style="font-size:22px; font-weight:bold; text-decoration:underline;">INFORMATION IMPORTANTE</span><br /><span style="font-weight:bold;">Voici l'url de réponse que vous devez spécifier <br />dans votre espace admin
		de Rentabiliweb :</span><br />
		<?php echo $url_response_full; ?></td>
	</tr>
</table>
<div class="button2"><input type="image" src="images/button_envoi.png" alt="Valider" /></div>
</form>
</body>
</html>
