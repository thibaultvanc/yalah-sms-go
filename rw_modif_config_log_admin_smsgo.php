<?php
session_start();
@include('rw_config_smsgo.php');
if(!empty($rw_config) && !empty($rw_config['install_ok']))
{
	//variables pour la couleur des messages d'erreur (dans les champs de saisie)
	$error_style='';//nouveau loggin / mdp
	$error_style1='';//ancien loggin
	$error_style2='';//ancien mdp
	//initit de la variable de modif
	$modifOK=false;
	if(!empty($_POST))
	{
		$errors = array();//tableau de message d'erreurs de saisie (initialisation)
		$errors_log = array();//tableau de messages d'erreurs sur le loggin et/ou mdp (initialisation)
		$modifOK=false;
		(!empty($_POST['rw_admin_login']))?$rw_admin_login=trim($_POST['rw_admin_login']):$errors[]='Vous devez renseigner un login de connexion &agrave; votre administration';
		(!empty($_POST['rw_admin_pass']))?$rw_admin_pass=trim($_POST['rw_admin_pass']):$errors[]='Vous devez renseigner un mot de passe de connexion &agrave; votre administration';
		(!empty($_POST['new_rw_admin_login']))?$new_rw_admin_login=trim($_POST['new_rw_admin_login']):$errors[]='Vous devez renseigner un nouveau login de connexion &agrave; votre administration';
		(!empty($_POST['new_rw_admin_pass']))?$new_rw_admin_pass=trim($_POST['new_rw_admin_pass']):$errors[]='Vous devez renseigner un nouveau mot de passe de connexion &agrave; votre administration';
		if(empty($errors))
		{
			//variables pour la couleur des messages d'erreur (dans les champs de saisie)
			$error_style1='';
			$error_style2='';
			
			$ancienne_ligne_login='';
			$ancienne_ligne_MP='';
			$ancien_login_definition='\'su_login\'';
			$ancien_MP_definition='\'su_password\'';
			
			//d�finition du fichier de config
			$fichier = 'rw_config_smsgo.php';
			
			//ouverture du fichier de config en lecture et modif
			$rw_config_file = fopen($fichier,'r');
			while (!feof ($rw_config_file))// tant que pas en fin de fichier
			{
				$buffer = fgets($rw_config_file);//lecture du fichier
				$donnee = explode(",",$buffer);  // parsing des donn�es bas� sur ","
				$ligne = explode(' => ',$donnee[0]);//parsing des donn�es bas� sur " => "
	
				if($ligne[0]==$ancien_login_definition)//on cherche la ligne de l'ancien loggin
				{
					$ancien_loggin_saisi='\''.trim($_POST['rw_admin_login']).'\'';
					if($ligne[1]==$ancien_loggin_saisi)//si l'ancien loggin saisi correspond � celui du fichier
					{
						$ancienne_ligne_login=''.$ancien_login_definition.' => '.$ligne[1].'';
						$nouveau_loggin_admin='\''.trim($_POST['new_rw_admin_login']).'\'';
						$nouvelle_ligne_login=''.$ancien_login_definition.' => '.$nouveau_loggin_admin.'';
					}
					else
					{
						$errors_log[]="Votre ancien login de connexion &agrave; l'administration est incorrect";
						$error_style1='color:red;';
					}
				}
				
				 if($ligne[0]==$ancien_MP_definition)//on cherche la ligne de l'ancien mdp
				{
					$ancien_MP_saisi='\''.trim($_POST['rw_admin_pass']).'\'';
					if($ligne[1]==$ancien_MP_saisi)//si l'ancien MP saisi correspond � celui du fichier
					{
						$ancienne_ligne_MP=''.$ancien_MP_definition.' => '.$ligne[1].'';
						$_nouveau_MP_admin = '\''.trim($_POST['new_rw_admin_pass']).'\'';
						$nouvelle_ligne_MP=''.$ancien_MP_definition.' => '.$_nouveau_MP_admin.'';
					}
					else
					{
						$errors_log[]="Votre ancien mot de passe de connexion &agrave; l'administration est incorrect";
						$error_style2='color:red;';
					}
				}
			}
			//fermeture du fichier en lecture
			fclose($rw_config_file);
			
			
			if(empty($errors_log))//pas d'erreur sur le loggin et mdp anciens
			{
				//obtenir le contenu du fichier de config en texte
				$contenu = file_get_contents($fichier);
				//ouverture du fichier en lecture
				$rw_config_file = fopen($fichier,'r');
				//modif de la ligne login
				$logginAdmMod = str_replace($ancienne_ligne_login, $nouvelle_ligne_login, $contenu);
				//fermeture du fichier
				fclose($rw_config_file);
				//ouverture du fichier de config en r��criture
				$rw_config_file2=fopen($fichier,'w+');
				//modification dans le fichier de la ligne concern�e
				fwrite($rw_config_file2,$logginAdmMod);
				//fermeture du fichier en r��criture
				fclose($rw_config_file2);
				
				
				//obtenir le contenu du fichier de config en texte
				$contenu2 = file_get_contents($fichier);
				//ouverture du fichier en lecture
				$rw_config_file = fopen($fichier,'r');
				//modif de la ligne mdp
				$MdpAdminMod = str_replace($ancienne_ligne_MP, $nouvelle_ligne_MP, $contenu2);
				//fermeture du fichier en r��criture
				fclose($rw_config_file);
				//ouverture du fichier de config en r��criture
				$rw_config_file3=fopen($fichier,'w+');
				//modification dans le fichier de la ligne concern�e
				fwrite($rw_config_file3,$MdpAdminMod);
				//fermeture du fichier en r��criture
				fclose($rw_config_file3);
				//les modifs sont faites
				$modifOK=true;
			}
		}
	}
//conservation des donn�es post�es
if(isset($_POST['rw_admin_login'])) {$rw_admin_login = $_POST['rw_admin_login'];} else {$rw_admin_login = "";}
if(isset($_POST['rw_admin_pass'])) {$rw_admin_pass = $_POST['rw_admin_pass'];} else {$rw_admin_pass	= "";}
if(isset($_POST['new_rw_admin_login'])) {$new_rw_admin_login = $_POST['new_rw_admin_login'];} else {$new_rw_admin_login = "";}
if(isset($_POST['new_rw_admin_pass'])) {$new_rw_admin_pass = $_POST['new_rw_admin_pass'];} else {$new_rw_admin_pass = "";}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Rentabiliweb : SMS GO Modification des données admin</title>
	<link rel="icon" type="image/png" href="http://cluster.rentabiliweb.com/data/i/favicon.png" />
<!--[if lt IE 8]>
<link href="http://cluster.rentabiliweb.com/data/i/favicon.ico" rel="shortcut icon"/>
<![endif]-->
<link rel="stylesheet" type="text/css" href="css/style.css" />
</head>
<body>
<div id="header"><div>Outil SMS Go Dédicace</div>
<div><a href="index.php">Retour &agrave; l'identification</a></div>
</div>
	<h1>Modification des informations de connexion &agrave; l'administration</h1>
	
<?php
//variable php de la div contenant les instructions
$div_instructions='<div style="margin:auto; text-align:justify; font-style:italic; height:70px; width:500px; margin-bottom:15px;">
	<p style="margin:0; font-size:14px;">Vous pouvez modifier ci-dessous votre login et/ou votre de passe de connexion &agrave; l\'administration.<span style="font-weight: bold;"> Comme tous les champs sont obligatoires</span>,
	pour ne modifier que l\'un ou l\'autre, saisissez la valeur que vous ne voulez pas modifier (l\'ancienne) dans le champs r&eacute;serv&eacute; &agrave; la nouvelle valeur.
	</p>
	</div>';
//variable php du formulaire de modif des infos relatives � l'admin
$form_modif_adminLog='<form action='.$_SERVER["REQUEST_URI"].' method="post">
		<table class="table">
			<tr>
				<td>Ancien login de connexion &agrave; l\'administration*</td>
				<td><input type="text" name="rw_admin_login" value="'.$rw_admin_login.'" style="'.$error_style1.'" onfocus="this.style.color=\'black\'; this.select()"/></td>
			</tr>
			<tr>
				<td>Ancien mot de passe de connexion &agrave; l\'administration*</td>
				<td><input type="text" name="rw_admin_pass" value="'.$rw_admin_pass.'" style="'.$error_style2.'" onfocus="this.style.color=\'black\'; this.select()"/></td>
			</tr>
			<tr>
				<td>Nouveau login de connexion &agrave; l\'administration*</td>
				<td><input type="text" name="new_rw_admin_login" value="'.$new_rw_admin_login.'" /></td>
			</tr>
			<tr>
				<td>Nouveau mot de passe de connexion &agrave; l\'administration*</td>
				<td><input type="text" name="new_rw_admin_pass" value="'.$new_rw_admin_pass.'" /></td>
			</tr>
			<tr class="normal">
				<td colspan="2" align="center" style="color:red;">* Champs obligatoires</td>
			</tr>
		</table>
		<div class="button2"><input type="image" src="images/button_envoi.png" alt="Valider" /></div>
	</form>';
//Test de la modification (soit on arrive sur la page, soit il existe des erreurs � la soumission du form)
if(!$modifOK)
{
	echo $div_instructions;//affichage des instructions
	if(!empty($errors))//affichage des erreurs de saisies (infos manquantes)
	{
		echo '<ul>';
		foreach ($errors as $msg_error)
		{
			echo '<li>'.$msg_error.'</li>';
		}
		echo '</ul>';
	}
	if(!empty($errors_log))//affichage des erreurs de saisies (infos du log erronnées)
	{
		echo '<ul>';
		foreach ($errors_log as $msg_error_log)
		{
			echo '<li>'.$msg_error_log.'</li>';
		}
		echo '</ul>';
	}
	echo $form_modif_adminLog;//affichage du form
}
else//la modification a �t� effectu�e, on rappelle au client son nouveau loggin et mdp, on lui donne un lien pour retourner à l'admin
{
	echo '<div><p><strong>Modification r&eacute;ussie du fichier de configuration</strong><br />
	Votre nouveau login de connexion &agrave; l\'administration : <strong>'.$new_rw_admin_login.'</strong><br />
	Votre nouveau mot de passe de connexion &agrave; l\'administration : <strong>'.$new_rw_admin_pass.'</strong></p></div>
	<div class="button"><a class="button_medium" href="index.php" title="retour à l\'identification">Identification</a></div>';
	if(!empty($_SESSION['user']['smsgo']))
	{
		// On écrase le tableau de session
		$_SESSION = array();

		// On détruit la session
		session_destroy();
	}
}

?>	
</body>
</html>