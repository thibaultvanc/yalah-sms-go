<?php
$reponse = 'NON';

@include('rw_config_smsgo.php');
@include('rw_db_connect.php');
if(empty($rw_config)){
	echo $reponse;
	exit();
}
if(!empty($_REQUEST) && !empty($_REQUEST['sms']) && !empty($_REQUEST['display']) && $_REQUEST['display']==true){
	
	$sms_complet = $_REQUEST['sms'];
	$search = "'";
	$replace = "\'";
	$sms_complet = str_replace($search, $replace, $sms_complet);
	
	$query = '	insert into '.$rw_config['db_tables_prefix'].$rw_config['db_table_sms'].'
				(sms_content, date_create, validation, display)
				Values
				(\''.$sms_complet.'\',\''.date('Y-m-d H:i:s').'\',1,1)';
	$query_exe = mysql_query($query);
	echo mysql_insert_id();
	exit();
}
// traitement à réaliser lors de l'appel de ce fichier par le serveur rentabiliweb lors de l'envoi d'un sms par le client
$errors = array();
(!empty($_REQUEST['sms']))?$sms_complet = urldecode($_REQUEST['sms']):$errors[]='Le message reçu est vide';
if(empty($errors)){
	// mot trouvé
	//$sms_complet = strtoupper($sms_complet);
	$sms_complet_tab = explode(' ',$sms_complet);
	$sms_complet_tab[0] = strtoupper($sms_complet_tab[0]);
	if($sms_complet_tab[0]==$rw_config['rw_mot_cle'])
	{
		if(!empty($sms_complet_tab[1])){
			$keyword_long = strlen($rw_config['rw_mot_cle']);
			$sms_complet_content = trim(substr($sms_complet,$keyword_long));
			$sms_complet_content = urldecode($sms_complet_content);
			$search = "'";
			$replace = "\'";
			$sms_complet_content = str_replace($search, $replace, $sms_complet_content);
			$sms_complet_content=utf8_encode($sms_complet_content);
						
			$query = 'insert into '.$rw_config['db_tables_prefix'].$rw_config['db_table_sms'].'
				(sms_content, date_create)
				Values
				(\''.$sms_complet_content.'\',\''.date('Y-m-d H:i:s').'\')';
			if(@$query_exe = mysql_query($query)){
				$reponse = 'OUI';
			}
		}
		unset($rw_config);
		$rw_config = null;
	}
	
}
echo $reponse;
?>