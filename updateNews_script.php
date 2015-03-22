<?php
$link = mysql_connect('localhost', 'root', 'root');

if (!$link) {
	die('Could not connect: ' . mysql_error());
}
echo 'Connected successfully';
echo "</br>";

mysql_select_db ("bm_stage");

$query = "select i.id as ID, i.title as TITLE, concat(left(i.introtext, 50),'...') as SUMMARY, 
			i.alias as ALIAS, i.introtext as CONTENT, i.created as CREATED
		  from atjyw_k2_items i 
		  inner join atjyw_k2_categories c on i.catid = c.id
		  where c.name = 'news'
		  order by i.created desc limit 4;";

mysql_query("SET NAMES UTF8");

$result = mysql_query($query);
if (!$result) {
	echo 'Could not run query: ' . mysql_error();
	exit;
}
if (mysql_num_rows($result) > 0) {
	/// qui mi compongo il mio testo.....
	$contenuto = "";
	$contenuto .= "<h1 class=\"uk-heading-large tm-margin-bottom-large\">News dal mondo evangelico</h1>";
	$contenuto .= " <div class=\"uk-grid\" data-uk-grid-margin=\"\" data-uk-grid-match=\"{target:'.uk-panel'}\">";
	
	while ($row = mysql_fetch_assoc($result)) {
		$contenuto .= "	<div class=\"uk-width-medium-1-2 uk-width-large-1-4\" data-uk-scrollspy=\"{cls:'uk-animation-scale-up', delay: 150}\">";
		$contenuto .= "<div class=\"uk-overlay tm-overlay uk-width-1-1\">";
		$contenuto .= "		<div class=\"uk-panel uk-panel-box\"> ";
		$contenuto .= "				<img src=\"images/yootheme/home_news_01.png\" alt=\"".$row['TITLE']."\" width=\"220\" height=\"220\" />";
		$contenuto .= "			<h2 class=\"uk-panel-title uk-margin-top\">".$row['TITLE']."</h2>
				<p class=\"tm-panel-subtitle\">A modern, slim CMS and web
					application framework</p>
			</div>
			<div class=\"uk-overlay-area\">
				<div class=\"uk-overlay-area-content tm-primary\">
					<p>".$row["SUMMARY"]."</p>
					<a href=\"".addslashes("http://bm:8888/bm_stage/index.php?option=com_k2&view=item&id=".$row['ID'])."\" target=\"_blank\"
						class=\"uk-button uk-margin-small-top\">Leggi tutto...</a>
				</div>
			</div>
		</div>
	</div>";
		
		
	//echo $row['TITLE']."</br>";
	//print_r($row);
	}
	$contenuto .=" </div>";
	
	//$contenuto = addslashes("index.php?option=com_content&view=article&id=2");
	//$contenuto ="MMMMMMMMMM";
	
	
	$contenuto = mysql_real_escape_string($contenuto);
}





//// qui faccio l'update del testo nel DB!

//$contenuto="pippo";

$updateQ = "UPDATE atjyw_modules 
			SET content='".$contenuto. 
			"' WHERE id = 107";
$result = mysql_query($updateQ);
if(!$result) {
	echo 'Could not run UPDATE query: '.mysql_error();
	exit;	
}
echo "Update avvenuto con successo!";

/*
select i.id, i.title, concat(left(i.introtext, 50),'...'), i.alias, i.introtext, i.created 
from atjyw_k2_items i 
inner join atjyw_k2_categories c on i.catid = c.id
where c.name = 'news'
order by i.created desc limit 4;

*/

mysql_close($link);
?>
