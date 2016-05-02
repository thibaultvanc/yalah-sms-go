<?php
if(!empty($rw_config)){
	$rw_db_link = mysql_connect($rw_config['db_host'],$rw_config['db_login'],$rw_config['db_password']);
	if (!$rw_db_link) {
		die('Connexion au serveur sql impossible : erreur n° '.mysql_errno().', ' . mysql_error());
	}
	$rw_db_connect = mysql_select_db($rw_config['db_name'], $rw_db_link);
	if (!$rw_db_connect){
		die('Impossible de sélectionner la base de données : ' . mysql_error());
	}
	mysql_query('SET NAMES \'UTF8\' ');
}else{
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
			<title>Outil SMS GO Rentabiliweb : Intro</title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<link rel="icon" type="image/png" href="http://cluster.rentabiliweb.com/data/i/favicon.png" />
			<!--[if lt IE 8]>
			<link href="http://cluster.rentabiliweb.com/data/i/favicon.ico" rel="shortcut icon"/>
			<![endif]-->
			<link rel="stylesheet" type="text/css" href="css/style.css" />
			</head>
			<body>
			<div id="header"><div>Outil SMS Go Dédicace</div></div>';
	
	echo '<h1>SMS Go Dédicace : Installation non effectuée</h1>';
	if(@fopen('rw_install_smsgo.php','r')){
		echo '<div class="button"><a class="button_medium" href="rw_install_smsgo.php">Installer l\'outil</a></div>';
	
	}
	exit();
}
// supprimer les accents
function rw_strip_accents($texte){
//    $texte = mb_strtolower($texte, 'UTF-8');
    $texte = str_replace(
        array(
            'à', 'â', 'ä', 'á', 'ã', 'å',
            'î', 'ï', 'ì', 'í', 
            'ô', 'ö', 'ò', 'ó', 'õ', 'ø', 
            'ù', 'û', 'ü', 'ú', 
            'é', 'è', 'ê', 'ë', 
            'ç', 'ÿ', 'ñ', 
        ),
        array(
            'a', 'a', 'a', 'a', 'a', 'a', 
            'i', 'i', 'i', 'i', 
            'o', 'o', 'o', 'o', 'o', 'o', 
            'u', 'u', 'u', 'u', 
            'e', 'e', 'e', 'e', 
            'c', 'y', 'n', 
        ),
        $texte
    );
    return $texte;        
}
?>