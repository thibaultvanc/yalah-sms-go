<?php

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
		$error_new_infos="";//message d'erreur pour les nouvelles infos de config pour la connexion à la BDD (initialisation)
		$modifOK=false;
		(!empty($_POST['rw_db_login']))?$rw_db_login=trim($_POST['rw_db_login']):$errors[]='Vous devez renseigner un login de connexion &agrave; votre base de donn&eacute;es';
		(!empty($_POST['rw_db_pass']))?$rw_db_pass=trim($_POST['rw_db_pass']):$errors[]='Vous devez renseigner un mot de passe de connexion &agrave; votre base de donn&eacute;es';
		(!empty($_POST['new_rw_db_login']))?$new_rw_db_login=trim($_POST['new_rw_db_login']):$errors[]='Vous devez renseigner un nouveau login de connexion &agrave; votre base de donn&eacute;es';
		(!empty($_POST['new_rw_db_pass']))?$new_rw_db_pass=trim($_POST['new_rw_db_pass']):$errors[]='Vous devez renseigner un nouveau mot de passe de connexion &agrave; votre base de donn&eacute;es';
		if(empty($errors))
		{
			
			
			$ancien_login_BDD_definition='\'db_login\'';
			$ancien_MP_BDD_definition='\'db_password\'';
			$ancienne_ligne_login_BDD='';
			$ancienne_ligne_MP_BDD='';
			
			//définition du fichier de config
			$fichier = 'rw_config_smsgo.php';
			
			//ouverture du fichier de config en lecture et modif
			$rw_config_file = fopen($fichier,'r');
			
			while (!feof ($rw_config_file))// tant que pas en fin de fichier
			{
				$buffer = fgets($rw_config_file);//lecture du fichier
				$donnee = explode(",",$buffer);  // parsing des données basé sur ","
				$ligne = explode(' => ',$donnee[0]);//parsing des données basé sur " => "
				
				if($ligne[0]==$ancien_login_BDD_definition)//on cherche la ligne de l'ancien loggin
				{
					$ancien_loggin_saisi='\''.trim($_POST['rw_db_login']).'\'';
					if($ligne[1]==$ancien_loggin_saisi)//si l'ancien loggin saisi correspond à celui du fichier
					{
						$ancienne_ligne_login_BDD=''.$ancien_login_BDD_definition.' => '.$ligne[1].'';
						$nouveau_loggin_bdd='\''.trim($_POST['new_rw_db_login']).'\'';
						$nouvelle_ligne__login_BDD=''.$ancien_login_BDD_definition.' => '.$nouveau_loggin_bdd.'';
					}
					else
					{
						$errors_log[]="Votre ancien login de connexion &agrave; la base de donn&eacute;es est incorrect";
						$error_style1='color:red;';
					}
				}
				
				if($ligne[0]==$ancien_MP_BDD_definition)//on cherche la ligne de l'ancien mdp
				{
					$ancien_MP_saisi='\''.trim($_POST['rw_db_pass']).'\'';
					if($ligne[1]==$ancien_MP_saisi)//si l'ancien MP saisi correspond à celui du fichier
					{
						$ancienne_ligne_MP_BDD=''.$ancien_MP_BDD_definition.' => '.$ligne[1].'';
						$nouveau_MP_BDD = '\''.trim($_POST['new_rw_db_pass']).'\'';
						$nouvelle_ligne_MP_BDD = ''.$ancien_MP_BDD_definition.' => '.$nouveau_MP_BDD.'';
					}
					else
					{
						$errors_log[]="Votre ancien mot de passe de connexion &agrave; la base de donn&eacute;es est incorrect";
						$error_style2='color:red;';
					}
				}
			}
			//fermeture du fichier en lecture
			fclose($rw_config_file);
			
			//Pour éviter les Warnings intempestifs
			error_reporting(0);
			
			//on ferme la connexion sql généré pdt le test au dessus
			mysql_close();
				
			if(empty($errors_log))//pas d'erreur sur le loggin et mdp anciens
			{
				//Test pour savoir si les nouvelles infos de connexion sont bonnes et permettent d'accéder à la base de donn�es
				if(mysql_connect(trim($_POST['rw_db_host']),trim($_POST['new_rw_db_login']),trim($_POST['new_rw_db_pass'])))
				{
					//obtenir le contenu du fichier de config en texte
					$contenu = file_get_contents($fichier);
					//ouverture du fichier en lecture
					$rw_config_file = fopen($fichier,'r');
					//modif de la ligne login
					$logginBddMod = str_replace($ancienne_ligne_login_BDD, $nouvelle_ligne__login_BDD, $contenu);
					//fermeture du fichier
					fclose($rw_config_file);
					//ouverture du fichier de config en r��criture
					$rw_config_file2=fopen($fichier,'w+');
					//modification de la ligne concern�e
					fwrite($rw_config_file2,$logginBddMod);
					//fermeture du fichier en r��criture
					fclose($rw_config_file2);
					
					
					//obtenir le contenu du fichier de config en texte
					$contenu2 = file_get_contents($fichier);
					//ouverture du fichier en lecture
					$rw_config_file = fopen($fichier,'r');
					//modif de la ligne pass
					$MdpBddMod = str_replace($ancienne_ligne_MP_BDD, $nouvelle_ligne_MP_BDD, $contenu2);
					//fermeture du fichier
					fclose($rw_config_file);
					//ouverture du fichier de config en réécriture
					$rw_config_file3=fopen($fichier,'w+');
					//modification de la concern�e
					fwrite($rw_config_file3,$MdpBddMod);
					//fermeture du fichier en réécriture
					fclose($rw_config_file3);
					//les modifs sont faites
					$modifOK=true;
				}
				else
				{
					//on ferme la connexion sql générée pdt le test au dessus
					mysql_close();
					//message d'erreur pour les nouvelles infos de config pour la connexion à la BDD
					$error_new_infos='Le nouveau login<br />et/ou<br />le nouveau mot de passe de connexion &agrave; la base de donn&eacute;es<br />
					ne semble(nt) pas &ecirc;tre correct(s)<br />Erreur sql '.mysql_errno().' : '.mysql_error().'';
					$error_style='color:red;';
				}
			}
		}
	}
//conservation des données postées
if(isset($_POST['rw_db_login'])) {$rw_db_login = $_POST['rw_db_login'];} else {$rw_db_login="";}
if(isset($_POST['rw_db_pass'])) {$rw_db_pass = $_POST['rw_db_pass'];} else{$rw_db_pass="";}	
if (isset($_POST['new_rw_db_login'])) {$new_rw_db_login = $_POST['new_rw_db_login'];}else{$new_rw_db_login="";}
if(isset($_POST['new_rw_db_pass'])) {$new_rw_db_pass = $_POST['new_rw_db_pass'];} else {$new_rw_db_pass="";}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Rentabiliweb : SMS GO Modification des données bdd</title>
	<link rel="icon" type="image/png" href="http://cluster.rentabiliweb.com/data/i/favicon.png" />
<!--[if lt IE 8]>
<link href="http://cluster.rentabiliweb.com/data/i/favicon.ico" rel="shortcut icon"/>
<![endif]-->
<link rel="stylesheet" type="text/css" href="css/style.css" />
</head>
<body onunload="request.getSession().invalidate();">
<div id="header"><div>Outil SMS Go Dédicace</div>
<div><a href="index.php">Retour &agrave; l'identification</a></div>
</div>
<h1>Modification des informations de connexion &agrave; la base de donn&eacute;es</h1>

<?php
//variable php de la div contenant les instructions
$div_instructions='<div style="margin:auto; text-align:justify; font-style:italic; height:70px; width:500px; margin-bottom:15px;">
	<p style="margin:0; font-size:14px;">Vous pouvez modifier ci-dessous votre login et/ou votre de passe de connexion &agrave; la base de donn&eacute;es.<span style="font-weight: bold;"> Comme tous les champs sont obligatoires</span>,
	pour ne modifier que l\'un ou l\'autre, saisissez la valeur que vous ne voulez pas modifier (l\'ancienne) dans le champs r&eacute;serv&eacute; &agrave; la nouvelle valeur.
	</p>
	</div>';
//variable php du formulaire de modif des infos relatives à l'admin
$form_modif_adminLog='<form action='.$_SERVER["REQUEST_URI"].' method="post">
		<table class="table">
			<tr>
				<td>Ancien login de connexion &agrave; votre BDD*</td>
				<td><input type="text" name="rw_db_login" value="'.$rw_db_login.'" style="'.$error_style1.'" onfocus="this.style.color=\'black\'; this.select()"/></td>
			</tr>
			<tr>
				<td>Ancien mot de passe de connexion &agrave; votre BDD*</td>
				<td><input type="text" name="rw_db_pass" value="'.$rw_db_pass.'" style="'.$error_style2.'" onfocus="this.style.color=\'black\'; this.select()"/></td>
			</tr>
			<tr>
				<td>Nouveau login de connexion &agrave; votre BDD*</td>
				<td><input type="text" name="new_rw_db_login" value="'.$new_rw_db_login.'" style="'.$error_style.'" onfocus="this.style.color=\'black\'; this.select()"/></td>
			</tr>
			<tr>
				<td>Nouveau mot de passe de connexion &agrave; votre BDD*</td>
				<td><input type="text" name="new_rw_db_pass" value="'.$new_rw_db_pass.'" style="'.$error_style.'" onfocus="this.style.color=\'black\'; this.select()"/></td>
			</tr>
			
			<tr class="normal">
				<td colspan="2" align="center" style="color:red;">* Champs obligatoires</td>
			</tr>
		</table>
		<div class="button2"><input type="image" src="images/button_envoi.png" alt="Valider" /></div>
	</form>';
//Test de la modification (soit on arrive sur la page, soit il existe des erreurs à la soumission du form)
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
	if(empty($errors_log) && !empty($error_new_infos)) echo '<p style="font-size:12px; font-weight:bold; color:red; border:1px solid red; width:520px; margin:auto; margin-bottom: 20px; background-color: #CCCCCC;">'.$error_new_infos.'</p>';
	echo $form_modif_adminLog;//affichage du form
}
else//la modification a été effectuée, on rappelle au client son nouveau loggin et mdp, on lui donne un lien pour retourner à l'admin
{
	echo '<div><p><strong>Modification r&eacute;ussie du fichier de configuration</strong><br />
	Nouveau login de connexion &agrave; la base de donn&eacute;es : <strong>'.$new_rw_db_login.'</strong><br />
	Votre nouveau mot de passe de connexion &agrave; la base de donn&eacute;es : <strong>'.$new_rw_db_pass.'</strong></p></div>
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