<?php
session_start();
if(empty($_SESSION['user']['smsgo']) || $_SESSION['user']['smsgo']==false){
	header('location: index.php');
	exit();
}
@include('rw_config_smsgo.php');
include('rw_db_connect.php');

if(!empty($_POST)){
	if(!empty($_POST['action']) && !empty($_POST['message_id'])){
		if($_POST['action']=='delete'){
			// on désactive ce message
			$query = '	update '.$rw_config['db_tables_prefix'].$rw_config['db_table_sms'].'
						set validation=1,
						display = 0
						where id='.$_POST['message_id'];
			$query_exe = mysql_query($query);
			echo $_POST['message_id'];
			exit();
		}elseif($_POST['action']=='display'){
			// on active ce message
			$query = '	update '.$rw_config['db_tables_prefix'].$rw_config['db_table_sms'].'
						set validation=1,
						display = 1
						where id='.$_POST['message_id'];
			$query_exe = mysql_query($query);
			echo $_POST['message_id'];
			exit();
		}
	}
	elseif (!empty($_POST['messages_validation'])){
		if(!empty($_POST['action'])){
			$query_where = ' validation=0 ';
			$query_order_by = '';
			if($_POST['action']=='deleted'){
				$query_where = ' validation=1 and display=0 ';
				$query_order_by = ' desc ';
			}elseif ($_POST['action']=='displayed'){
				$query_where = ' validation=1 and display=1 ';
				$query_order_by = ' desc ';
			}
			// récupération de la liste des sms envoyé et non validéss
			$page_actuel = 1;
			$start = 0;
			$end = $rw_config['nbr_message_per_page']; // nombre de messages affichés par page
			if(!empty($_POST['page'])){
				$start = ($_POST['page'] - 1)*$end;
				$page_actuel = $_POST['page'];
			}
			$query = '	select id, sms_content, date_create
						from '.$rw_config['db_tables_prefix'].$rw_config['db_table_sms'].'
						where '.$query_where.'
						order by date_create '.$query_order_by.'
						limit '.$start.','.$end;
			if($query_exe = mysql_query($query)){
				$nbr_messages = mysql_num_rows($query_exe);
			}
			$query2 = '	select count(*) as nbr_messages_total
						from '.$rw_config['db_tables_prefix'].$rw_config['db_table_sms'].'
						where '.$query_where;
			if($query_exe2 = mysql_query($query2)){
				$query_res2 = mysql_fetch_array($query_exe2);
			}
			$nbr_messages_totale = $query_res2['nbr_messages_total'];
			if($nbr_messages>0){
				while ($query_res = mysql_fetch_array($query_exe)){
					echo '<tr id="display_sms_'.$query_res['id'].'">';
					echo '<td>'.date("d-m-Y H\hi",strtotime($query_res['date_create'])).'</td>';
					echo '<td class="display_sms_validation_sms_content">'.$query_res['sms_content'].'</td>';
					if($_POST['action'] == 'deleted'){
						echo '<td><a href="" id="'.$query_res['id'].'" class="display">Afficher</a></td>';
					}elseif ($_POST['action']=='displayed'){
						echo '<td><a href="" id="'.$query_res['id'].'" class="delete">Refuser</a></td>';
					}else{
						echo '<td><a href="" id="'.$query_res['id'].'" class="display">Afficher</a> / <a href="" id="'.$query_res['id'].'" class="delete">Refuser</a></td>';
					}
					echo '<tr>';
				}
				if($nbr_messages_totale>$end){
					$nbr_pages = ceil($nbr_messages_totale/$end);
					echo '<tr><td colspan="3" id="pagination">';
					for ($page=1;$page<=$nbr_pages;$page++){
						$onclick = 'onclick="page_ask('.$page.');"';
						$style='';
						if($page==$page_actuel){
							$onclick = '';
							$style = ' style="background:#cccccc"';
						}
						echo '<a '.$onclick.$style.'>'.$page.'</a>';
					}
					echo '</td></tr>';
				}
			}else{
				echo '<tr><td colspan="3" align="center" style="color:red">Aucun message en attente</td></tr>';
			}
			exit();
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<head>
<title>SMS GO Dédicace Rentabiliweb : Administration</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" type="image/png" href="http://cluster.rentabiliweb.com/data/i/favicon.png" />
<!--[if lt IE 8]>
<link href="http://cluster.rentabiliweb.com/data/i/favicon.ico" rel="shortcut icon"/>
<![endif]-->
<link rel="stylesheet" type="text/css" href="css/style.css" />
<script type="text/javascript" src="http://data.rentabiliweb.com/js/smsgo/jquery-1.2.2.pack.js"></script>
<script type="text/javascript">
var current_page = 1;
$(document).ready(function(){
	get_messages_validation(true);
	$('input[@name=radio_message]').each(function(){
		$(this).click(function(){
			current_page = 1;
			get_messages_validation();
		});
	});
	$('#sms_go_add_button').click(function(){
		var message_content = $('#sms_go_add_text').val();
		$.post("rw_response.php", { sms: message_content, display : true },
		function(data){
			$('#sms_go_add_text').val('');
			get_messages_validation();
		}
		);
		return false;
	});
});
function page_ask(page){
	if(page>0){
		current_page = page;
	}
	get_messages_validation();
	return false;
}
function get_messages_validation(time){
	action = $('input[@name=radio_message]:checked').val();
	$.post("rw_admin_smsgo.php", { messages_validation: true, action:action, page:current_page},
	function(data){
		$('#display_sms_validation tbody').html(data);
		init_links();
	}
	);
	if(typeof time!= null && time==true){
		setTimeout('get_messages_validation(true);',15000);
	}
}
function init_links(){
	$('#display_sms_validation tbody a[id]').each(function(){
		if(this.className=='delete'){
			// on affiche pas ce message
			$(this).click(function(){
				$.post("rw_admin_smsgo.php", { message_id: this.id, action: "delete" },
				function(data){
					$('#display_sms_'+data).remove();
				}
				);
				return false;
			});
		}else{
			$(this).click(function(){
				// on affiche ce message
				$.post("rw_admin_smsgo.php", { message_id: this.id, action: "display" },
				function(data){
					$('#display_sms_'+data).remove();
				}
				);
				return false;
			});
		}
	});
}
</script>
</head>
<body>
<div id="header">
	<div>Outil SMS Go Dédicace</div>
</div>
<h1 style="margin-bottom:5px;">SMS Go Dédicace : Administration</h1>

<?php
if(!empty($_SESSION['user']['smsgo'])) echo "<input type='button' name='deconnexion' value='D&eacute;connexion' onclick='window.location.href=\"logout.php\"' style='margin-bottom: 40px; font-size: 12px; width: 100px; height: 25px;'/>";
?>



<p id="messages_get_actions">
	<label>
		<input type="radio" name="radio_message" id="radio_message_wait" value="wait" checked="checked" />
		Messages en attente de validation
	</label>
	<label>
		<input type="radio" name="radio_message" id="radio_message_display" value="displayed" />
		Messages validés
	</label>
	<label>
		<input type="radio" name="radio_message" id="radio_message_delete" value="deleted" />
		Messages refusés
	</label>
</p>
<table id="display_sms_validation" class="table">
	<caption>Messages actualisés toutes les 15 secondes</caption>
	<thead>
		<tr>
			<th>Date d'envoi</th>
			<th>Contenu du message</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
	</tbody>
	<tfoot>
		<tr>
			<td align="right">
				Ajouter un message*
			</td>
			<td colspan="2" align="center">
				<input size="60" type="text" maxlength="160" name="sms_go_add_text" value="" id="sms_go_add_text" />&nbsp;
				<input type="submit" value="OK" id="sms_go_add_button" />
			</td>
		</tr>
		<tr class="normal">
			<td colspan="3" style="font-size:0.8em" align="center">* ce message sera automatiquement valid&eacute; pour l'affichage</td>
		</tr>
	</tfoot>
</table>

</body>
</html>