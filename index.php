<?php

// Include this function on your pages


// At the beginning of each page call these two functions
ob_start();
ob_implicit_flush(0);
// Then do everything you want to do on the page

 $cache_expire = 60*60*24*365;
 header("Pragma: public");
 header("Cache-Control: max-age=".$cache_expire);
 header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$cache_expire) . ' GMT');
 header('X-Frame-Options: GOFORIT'); 
 
 function print_gzipped_page() {

    global $HTTP_ACCEPT_ENCODING;
    if( headers_sent() ){
        $encoding = false;
    }elseif( strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false ){
        $encoding = 'x-gzip';
    }elseif( strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false ){
        $encoding = 'gzip';
    }else{
        $encoding = false;
    }

    if( $encoding ){
        $contents = ob_get_contents();
        ob_end_clean();
        header('Content-Encoding: '.$encoding);
        print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
        $size = strlen($contents);
        $contents = gzcompress($contents, 9);
        $contents = substr($contents, 0, $size);
        print($contents);
        exit();
    }else{
        ob_end_flush();
        exit();
    }
}
 
function GoogleClientLogin($username, $password, $service) {
	// Check that we have all the parameters
	if(!$username || !$password || !$service) {
		throw new Exception("You must provide a username, password, and service when creating a new GoogleClientLogin.");
	}
	
	// Set up the post body
	$body = "accountType=GOOGLE &Email=$username&Passwd=$password&service=$service";
	
	// Set up the cURL
	$c = curl_init ("https://www.google.com/accounts/ClientLogin");
	curl_setopt($c, CURLOPT_POST, true);
	curl_setopt($c, CURLOPT_POSTFIELDS, $body);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($c);
	
	// Parse the response to obtain just the Auth token
	// Basically, we remove everything before the "Auth="
	return preg_replace("/[\s\S]*Auth=/", "", $response);
}

class FusionTable {
	var $token;
	
	function FusionTable($token) {
		if (!$token) {
			throw new Exception("You must provide a token when creating a new FusionTable.");		
		}
		$this->token = $token;
	}
	
	function query($query) {
		if(!$query) {
			throw new Exception("query method requires a query.");
		}
		// Check to see if we have a query that will retrieve data
		if(preg_match("/^select|^show tables|^describe/i", $query)) {
			$request_url = "http://tables.googlelabs.com/api/query?sql=" . urlencode($query);
			$c = curl_init ($request_url);
			curl_setopt($c, CURLOPT_HTTPHEADER, array("Authorization: GoogleLogin auth=" . $this->token));
			curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
			
			// Place the lines of the output into an array
			
			//$results = preg_split("/\n/", curl_exec ($c));
			$results = curl_exec ($c);
			$results = str_replace('\n',' ',$results);
			$results = str_replace('<br />',' ',$results);
			$results = str_replace('href','link',$results);
			//$results = trim( preg_replace( '/\s+/', ' ', $results ) );
			$results = preg_split("/\n/", $results);
			//$results = preg_split(",", $results);
			//echo("<br/>It was me<br/><pre>");
			print_r($results);
			//echo("</pre><br/>");
			// If we got an error, raise it
			if(curl_getinfo($c, CURLINFO_HTTP_CODE) != 200) {
				return $this->output_error($results);
			}

			// Drop the last (empty) array value
			array_pop($results);
			
			// Parse the output
			return $this->parse_output($results);
		}
		// Otherwise we are going to be updating the table, so we need to the POST method
		else if(preg_match("/^update|^delete|^insert/i", $query)) {
			// Set up the cURL
			$body = "sql=" . urlencode($query);
			$c = curl_init ("http://tables.googlelabs.com/api/query");
			curl_setopt($c, CURLOPT_POST, true);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($c, CURLOPT_HTTPHEADER, array(
				"Content-length: " . strlen($body),
				"Content-type: application/x-www-form-urlencoded",
				"Authorization: GoogleLogin auth=" . $this->token . " "		// I don't know why, but unless I add extra characters after the token, I get this error: Syntax error near line 1:1: unexpected token: null
			));
			curl_setopt($c, CURLOPT_POSTFIELDS, $body);
			
			// Place the lines of the output into an array
			$results = preg_split("/\n/", curl_exec ($c));
			
			// If we got an error, raise it
			if(curl_getinfo($c, CURLINFO_HTTP_CODE) != 200) {
				return $this->output_error($results);
			}

			// Drop the last (empty) array value
			array_pop($results);
			
			return $this->parse_output($results);
		}
		else {
			throw new Exception("Unknown SQL query submitted.");
		}
	}
	
	private function parse_output($results) {
		$headers = false;
		$output = array();
		foreach($results as $row) {
			// Get the headers
			if(!$headers) {
				$headers = $this->parse_row($row);
			}
			else {
				// Create a new row for the array
				$newrow = array();
				$values = $this->parse_row($row);
				
				// Build an associative array, using the headers for the association
				foreach($headers as $index => $header) {
					$newrow[$header] = $values[$index];
				}
				
				// Add the new array to the output array
				array_push($output, $newrow);
			}
		}
		
		// Return the output
		return $output;
	}
	/*
	private function parse_row($row) {
		// Split the comma delimted row
		$cells = preg_split("/,/", $row);
		
		// Go through each cell and see if we encounter a double quote
		foreach($cells as $index => $value) {
			// When we encounter a double quote at the start of a cell, we've got a quoted string
			if(preg_match("/^\"/", $value)) {
				// Concatenate the value with the next cell and remove the double quotes
				$cells[$index] = preg_replace("/^\"|\"$/", "", $cells[$index] . $cells[$index+1]);
				
				// Drop the next cell from the array
				array_splice($cells, $index+1, 1);
			}
		}
		return $cells;
	}
	*/
	
	
	 private function parse_row($row)
    {
        // Split the comma delimted row
        $cells = preg_split("/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/", $row);
        // Loop over each column and remove ending double-quotes and white-space
        foreach( $cells as $k => $v )
            $cells[$k] = trim($v, '"');
            
        return $cells;
    }
	
	private function output_error($err) {
		$err = implode("", $err);
		
		// Remove everything outside of the H1 tag
		$err = preg_replace("/[\s\S]*<H1>|<\/H1>[\s\S]*/i", "", $err);
		
		// Return the error
		return $err;
		
		// Eventually we'll just throw the error rather than return the error output
		throw new Exception($err);
	}
}
?>
<html>
<head>
   <meta charset="utf-8"/>
    <?php
	if (is_string($_REQUEST['id'])){
	$whereClause = "WHERE mal_id = '".$_REQUEST['id']."'";	
	//$sql="SELECT 'anime_type', 'episodes', 'synopsis', 'mal_score', 'mal_image','mal_id','start_date','end_date','synonyms','title' FROM 1409399 ".$whereClause;
	$sql="SELECT anime_type, episodes, synopsis, mal_score, mal_image,mal_id,start_date,end_date,synonyms,title FROM 1409399 ".$whereClause;
	$token = GoogleClientLogin("labwax@gmail.com", "xdexters231###", "fusiontables"); 
	$ft = new FusionTable($token);
	$output = $ft->query($sql);
	echo('<!--'.htmlentities(print_r($output)).'-->');
	if($output){
	echo('<meta property="og:title" content="'.$output[0]['title'].'"/>
    <meta property="og:type" content="tv_show"/>
    <meta property="og:url" content="http://apps.facebook.com/anime-anonymous/?id='.$_REQUEST['id'].'"/>
	<meta property="og:site_url" content="http://apps.facebook.com/anime-anonymous/?id='.$_REQUEST['id'].'"/>
    <meta property="og:image" content="'.$output[0]['mal_image'].'"/>
    <meta property="og:site_name" content="Anime Anonymous"/>
	<meta property="fb:app_id" content="39732531101"/>
	<meta property="fb:admins" content="505625483"/>
	<meta property="fb:admin" content="505625483"/>
    <meta property="og:description" content="'.htmlentities($output[0]['synopsis']).'"/>');
	}else{
	echo('<meta property="og:title" content="Anime Anonymous"/>
    <meta property="og:type" content="tv_show"/>
    <meta property="og:url" content="http://apps.facebook.com/anime-anonymous/"/>
	<meta property="og:site_url" content="http://apps.facebook.com/anime-anonymous/"/>
    <meta property="og:image" content="https://fbcdn-photos-a.akamaihd.net/photos-ak-snc1/v43/53/39732531101/app_1_39732531101_48.gif"/>
    <meta property="og:site_name" content="Anime Anonymous"/>
	<meta property="fb:app_id" content="39732531101"/>
	<meta property="fb:admins" content="505625483"/>
	<meta property="fb:admin" content="505625483"/>
    <meta property="og:description" content="With a database of over 3,000+ anime & pictures. Create a list of your favorite anime and share it with your friends.This is what every anime fan has been waiting for."/>');
	}
	}else{
echo('<meta property="og:title" content="Anime Anonymous"/>
    <meta property="og:type" content="tv_show"/>
    <meta property="og:url" content="http://apps.facebook.com/anime-anonymous/"/>
	<meta property="og:site_url" content="http://apps.facebook.com/anime-anonymous/"/>
    <meta property="og:image" content="https://fbcdn-photos-a.akamaihd.net/photos-ak-snc1/v43/53/39732531101/app_1_39732531101_48.gif"/>
    <meta property="og:site_name" content="Anime Anonymous"/>
	<meta property="fb:app_id" content="39732531101"/>
	<meta property="fb:admins" content="505625483"/>
	<meta property="fb:admin" content="505625483"/>
    <meta property="og:description" content="With a database of over 3,000+ anime & pictures. Create a list of your favorite anime and share it with your friends.This is what every anime fan has been waiting for."/>');
	}
	?>
<link rel="stylesheet" href="http://aagmjc5n.facebook.joyent.us/anime.anonymous/animeanonymousstyle.v1.0.css" type="text/css" />
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript" id="fbscript">
function fbinit(){
  window.fbAsyncInit = function() {
	FB.Canvas.setAutoResize();
    FB.init({appId: '39732531101', status: true, cookie: true, xfbml: true});
	FB.Event.subscribe('edge.create', function(response) {
		//alert('You liked the URL: ' + response);

//publishing

/*
title = response.getDataTable().getValue(0, 0);
	anime_type = response.getDataTable().getValue(0, 1);
	episodes = response.getDataTable().getValue(0, 2);
	synopsis = response.getDataTable().getValue(0, 3);
	titleURL = encodeURIComponent(title);
        cleanSynopsis = synopsis; 

*/
var anipath = 'http://apps.facebook.com/anime-anonymous/?id='+animeID+'&name='+titleURL; 
FB.ui(
  {
    method: 'feed',
    name: title,
    link: anipath ,
    picture: animeImg+'t.jpg',
    caption: title,
    description: synopsis
  },
  function(response) {
    if (response && response.post_id) {
     // alert('Post was published.');
    } else {
     // alert('Post was not published.');
    }
  }
);

//end pub

	});
	
  };
  (function() {
    var e = document.createElement('script'); e.async = true;
    e.src = document.location.protocol +'//connect.facebook.net/en_US/all.js';
    document.getElementById('fb-root').appendChild(e);
  }());
}
</script>
<script type="text/javascript" id="script">

google.load('visualization', '1');
var counter = false;
var mytitle = '<h2><div style="float: right;"></div>TOP 100 Anime</h2>';
//mytitle = 'TOP 100 Anime List';

function getData() {
if(getParameterByName("id")){
 //window.location.href = "?id="+getParameterByName("id");
	var whereClause = "";
	whereClause = "WHERE mal_id = " + getParameterByName("id");		 
	var queryText = encodeURIComponent("SELECT 'title', 'anime_type', 'episodes', 'synopsis', 'mal_score', 'mal_image','mal_id','start_date','end_date','synonyms' FROM 1409399 " + whereClause);
	var query = new google.visualization.Query('http://www.google.com/fusiontables/gvizdata?tq='  + queryText);
	query.send(getAnime);
}else{

if(getParameterByName("ani")){

var findchar = getParameterByName("ani");
  var whereClause = "";
mytitle = findchar+' Animelist';
if (findchar=="TOP"){
  whereClause = "ORDER BY mal_score DESC LIMIT 100";
  //mytitle = '<h2><div style="float: right;"></div>TOP 100 Anime</h2>';
  mytitle = 'TOP 100 Anime List';
  counter = true;
}else if (findchar=="0-9"){
  whereClause = "Where title >='0' and title < 'A' ORDER BY title DESC LIMIT 500";
}else if (findchar=="A"){
  whereClause = "Where title >='A' and title < 'B' ORDER BY title DESC LIMIT 500";
}else if (findchar=="B"){
  whereClause = "Where title >='B' and title < 'C' ORDER BY title DESC LIMIT 500";
}else if (findchar=="C"){
  whereClause = "Where title >='C' and title < 'D' ORDER BY title DESC LIMIT 500";
}else if (findchar=="D"){
  whereClause = "Where title >='D' and title < 'E' ORDER BY title DESC LIMIT 500";
}else if (findchar=="E"){
  whereClause = "Where title >='E' and title < 'F' ORDER BY title DESC LIMIT 500";
}else if (findchar=="F"){
  whereClause = "Where title >='F' and title < 'G' ORDER BY title DESC LIMIT 500";
}else if (findchar=="G"){
  whereClause = "Where title >='G' and title < 'H' ORDER BY title DESC LIMIT 500";
}else if (findchar=="H"){
  whereClause = "Where title >='H' and title < 'I' ORDER BY title DESC LIMIT 500";
}else if (findchar=="I"){
  whereClause = "Where title >='I' and title < 'J' ORDER BY title DESC LIMIT 500";
}else if (findchar=="J"){
  whereClause = "Where title >='J' and title < 'K' ORDER BY title DESC LIMIT 500";
}else if (findchar=="K"){
  whereClause = "Where title >='K' and title < 'L' ORDER BY title DESC LIMIT 500";
}else if (findchar=="L"){
  whereClause = "Where title >='L' and title < 'M' ORDER BY title DESC LIMIT 500";
}else if (findchar=="M"){
  whereClause = "Where title >='M' and title < 'N' ORDER BY title DESC LIMIT 500";
}else if (findchar=="N"){
  whereClause = "Where title >='N' and title < 'O' ORDER BY title DESC LIMIT 500";
}else if (findchar=="O"){
  whereClause = "Where title >='O' and title < 'P' ORDER BY title DESC LIMIT 500";
}else if (findchar=="P"){
  whereClause = "Where title >='P' and title < 'Q' ORDER BY title DESC LIMIT 500";
}else if (findchar=="Q"){
  whereClause = "Where title >='Q' and title < 'R' ORDER BY title DESC LIMIT 500";
}else if (findchar=="R"){
  whereClause = "Where title >='R' and title < 'S' ORDER BY title DESC LIMIT 500";
}else if (findchar=="S"){
  whereClause = "Where title >='S' and title < 'T' ORDER BY title DESC LIMIT 500";
}else if (findchar=="T"){
  whereClause = "Where title >='T' and title < 'U' ORDER BY title DESC LIMIT 500";
}else if (findchar=="U"){
  whereClause = "Where title >='U' and title < 'V' ORDER BY title DESC LIMIT 500";
}else if (findchar=="V"){
  whereClause = "Where title >='V' and title < 'W' ORDER BY title DESC LIMIT 500";
}else if (findchar=="W"){
  whereClause = "Where title >='W' and title < 'X' ORDER BY title DESC LIMIT 500";
}else if (findchar=="X"){
  whereClause = "Where title >='X' and title < 'Y' ORDER BY title DESC LIMIT 500";
}else if (findchar=="Y"){
  whereClause = "Where title >='Y' and title < 'Z' ORDER BY title DESC LIMIT 500";
}else if (findchar=="Z"){
  whereClause = "Where title >='Z' and title < 'a' ORDER BY title DESC LIMIT 500";
}else{
   whereClause = "ORDER BY mal_score DESC LIMIT 100";
   mytitle = 'TOP 100 Anime List';
  counter = true;
}
}else{
   whereClause = "ORDER BY mal_score DESC LIMIT 100";
   mytitle = 'TOP 100 Anime List';
  counter = true;
}
  var queryText = encodeURIComponent("SELECT 'title', 'anime_type', 'episodes', 'synopsis', 'mal_score', 'mal_image','mal_id' FROM 1409399 " + whereClause);
  var query = new google.visualization.Query('http://www.google.com/fusiontables/gvizdata?tq='  + queryText);
  query.send(getRows);
  fbinit();
  }
}

function getParameterByName(name)
{
  name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
  var regexS = "[\\?&]" + name + "=([^&#]*)";
  var regex = new RegExp(regexS);
  var results = regex.exec(window.location.href);
  if(results == null)
    return "";
  else
    return decodeURIComponent(results[1].replace(/\+/g, " "));
}

function getRows(response) {
  numRows = response.getDataTable().getNumberOfRows();
  numCols = response.getDataTable().getNumberOfColumns();
  var fusiontabledata = "";
  var evenx = false;
  
  //for(i = 0; i < numCols; i++) {
  //fusiontabledata += response.getDataTable().getColumnLabel(i) + ",";
  //}

  var topList = ""

topList  += '<div id="horiznav_nav" style="margin: 5px 0 10px 0;">';
topList  += '<ul style="margin-right: 0; padding-right: 0;">';
topList  += '<li><a href="?ani=top" class="horiznav_active">TOP</a></li>';
topList  += '<li><a href="?ani=0-9">0-9</a></li>';
topList  += '<li><a href="?ani=A">A</a></li>';
topList  += '<li><a href="?ani=B">B</a></li>';
topList  += '<li><a href="?ani=C">C</a></li>';
topList  += '<li><a href="?ani=D">D</a></li>';
topList  += '<li><a href="?ani=E">E</a></li>';
topList  += '<li><a href="?ani=F">F</a></li>';
topList  += '<li><a href="?ani=G">G</a></li>';
topList  += '<li><a href="?ani=H">H</a></li>';
topList  += '<li><a href="?ani=I">I</a></li>';
topList  += '<li><a href="?ani=J">J</a></li>';
topList  += '<li><a href="?ani=K">K</a></li>';
topList  += '<li><a href="?ani=L">L</a></li>';
topList  += '<li><a href="?ani=M">M</a></li>';
topList  += '<li><a href="?ani=N">N</a></li>';
topList  += '<li><a href="?ani=O">O</a></li>';
topList  += '<li><a href="?ani=P">P</a></li>';
topList  += '<li><a href="?ani=Q">Q</a></li>';
topList  += '<li><a href="?ani=R">R</a></li>';
topList  += '<li><a href="?ani=S">S</a></li>';
topList  += '<li><a href="?ani=T">T</a></li>';
topList  += '<li><a href="?ani=U">U</a></li>';
topList  += '<li><a href="?ani=V">V</a></li>';
topList  += '<li><a href="?ani=W">W</a></li>';
topList  += '<li><a href="?ani=X">X</a></li>';
topList  += '<li><a href="?ani=Y">Y</a></li>';
topList  += '<li><a href="?ani=Z">Z</a></li>';
topList  += '</ul>';
topList  += '</div>';


  fusiontabledata += topList  + '<h2 style="font-size: 24px;">'+mytitle+'</h2>';
  fusiontabledata += '<table><tr valign="top"><td align="left" valign="top"><table border="0" cellpadding="5" cellspacing="0">';
  fusiontabledata += '<tr valign="top">';
  for(i = 0; i < numRows; i++) {
    //for(j = 0; j < numCols-1; j++) {
 var q = i+1;
 var pq = 30;
 var title = response.getDataTable().getValue(i, 0);
 var anime_type = response.getDataTable().getValue(i, 1);
 var episodes = response.getDataTable().getValue(i, 2);
 var synopsis = response.getDataTable().getValue(i, 3);
 var titleURL = encodeURIComponent(title);
 var stx = synopsis;
while(synopsis==stx){

synopsis = synopsis.replace("&lt;","<"); 
synopsis = synopsis.replace("&gt;",">");
synopsis = synopsis.replace("\n","<br/>");

if(synopsis ==stx){
stx = "macmendex";
}else{
stx = synopsis ;
}
}

 synopsis = synopsis.substr(0,150);

 var mal_score = response.getDataTable().getValue(i, 4);
 var imgstr = response.getDataTable().getValue(i, 5);
 imgstr = imgstr.substr(0,(imgstr.length-4));
 
 var crc_code = response.getDataTable().getValue(i, 6);
 if(counter){
 fusiontabledata += '<td class="borderClass" style="border-width: 0 0 1px 0;" align="center" valign="top" width="'+pq+'">';
 fusiontabledata += '<span style="font-weight: bold; font-size: 24px;" class="lightLink">'+q+'</span></td>';
 }
 fusiontabledata += '<td class="borderClass" align="center" valign="top" width="50px">';
 fusiontabledata += '<div class="picSurround">';
 fusiontabledata += '<a href="?id='+crc_code+'&name='+titleURL+'" class="hoverinfo_trigger" id="#area5114" rel="#info5114">';
 fusiontabledata += '<img src="'+imgstr+'t.jpg" border="0">';
 //fusiontabledata += '<img src="'+imgstr+'.jpg" width="50%" height="50%" border="0">';
 fusiontabledata += '</a>';
 fusiontabledata += '</div>';
 fusiontabledata += '</td>';
 fusiontabledata += '<td class="borderClass" align="left" valign="top">';
 fusiontabledata += '<div id="area5114"> <div id="info5114" rel="a5114" class="hoverinfo"> </div> </div>';
 fusiontabledata += '<a href="?id='+crc_code+'&name='+titleURL +'" class="hoverinfo_trigger" id="#area5114" rel="#info5114">';
 fusiontabledata += '<strong>'+title+'</strong> </a> <div class="spaceit_pad"> ' + synopsis ;
 fusiontabledata += '<a href="?id='+ crc_code + '&name='+titleURL +'">';
 fusiontabledata += '<i>... read more</i></a><br><span class="lightLink">';
 fusiontabledata += anime_type + ',' + episodes + ' eps , scored ' + mal_score;
 fusiontabledata += '</span> </div> </td>';
 if(evenx){
     fusiontabledata += '</tr><tr valign="top">';
  evenx = false;
 }else{
  evenx = true;
 }
  }

  fusiontabledata += '</table></td><td align="left" valign="top"><table border="0" cellpadding="5" cellspacing="0">';
   fusiontabledata += "</table></td></tr></table><br/>";
   
  document.getElementById('ftdata').innerHTML = fusiontabledata+mytitle;
 fbinit(); 
  //var mytitle = '<h2><div style="float: right;"></div>TOP 100 Anime</h2>';
  //document.getElementsByTagName('h3')[0].innerHTML = "";
}


function getAnime(response) {

	title = response.getDataTable().getValue(0, 0);
	anime_type = response.getDataTable().getValue(0, 1);
	episodes = response.getDataTable().getValue(0, 2);
	synopsis = response.getDataTable().getValue(0, 3);
	titleURL = encodeURIComponent(title);
        cleanSynopsis = synopsis; 
        
	
	
var stx = cleanSynopsis;
while(cleanSynopsis==stx){

cleanSynopsis = synopsis.replace("<","&lt;"); 
cleanSynopsis = synopsis.replace(">","&gt;");


if(cleanSynopsis==stx){
stx = "macmendex";
}else{
stx = cleanSynopsis;
}
}
	
stx = synopsis ;
while(synopsis ==stx){

synopsis = synopsis.replace("&lt;","<"); 
synopsis = synopsis.replace("&gt;",">");
synopsis = synopsis.replace("\n","<br/>");
//synopsis = synopsis.replace(" br/","br/");
//synopsis = synopsis.replace("br/ ","br/");

if(synopsis ==stx){
stx = "macmendex";
}else{
stx = synopsis ;
}
}
	
	var mal_score = response.getDataTable().getValue(0, 4);
	var imgstr = response.getDataTable().getValue(0, 5);
	imgstr = imgstr.substr(0,(imgstr.length-4));
        animeImg = imgstr;
	thumbImg = imgstr+"t.jpg";
	var mal_id = response.getDataTable().getValue(0, 6);
	var sdate = response.getDataTable().getValue(0, 7);
	var edate = response.getDataTable().getValue(0, 8);
	var synonyms = title; + ' , ' + response.getDataTable().getValue(0, 9);
	animeID = mal_id;
	if (sdate){
		var start_date = new Date(sdate).toDateString();
	}else{
		var start_date = "";
	}
	
	if (edate){
		var end_date =new Date(edate).toDateString();
	}else{
		var end_date = "";
	}
	
	  var topList = ""

topList  += '<div id="horiznav_nav" style="margin: 5px 0 10px 0;">';
topList  += '<ul style="margin-right: 0; padding-right: 0;">';
topList  += '<li><a href="?ani=top" class="horiznav_active">TOP</a></li>';
topList  += '<li><a href="?ani=0-9">0-9</a></li>';
topList  += '<li><a href="?ani=A">A</a></li>';
topList  += '<li><a href="?ani=B">B</a></li>';
topList  += '<li><a href="?ani=C">C</a></li>';
topList  += '<li><a href="?ani=D">D</a></li>';
topList  += '<li><a href="?ani=E">E</a></li>';
topList  += '<li><a href="?ani=F">F</a></li>';
topList  += '<li><a href="?ani=G">G</a></li>';
topList  += '<li><a href="?ani=H">H</a></li>';
topList  += '<li><a href="?ani=I">I</a></li>';
topList  += '<li><a href="?ani=J">J</a></li>';
topList  += '<li><a href="?ani=K">K</a></li>';
topList  += '<li><a href="?ani=L">L</a></li>';
topList  += '<li><a href="?ani=M">M</a></li>';
topList  += '<li><a href="?ani=N">N</a></li>';
topList  += '<li><a href="?ani=O">O</a></li>';
topList  += '<li><a href="?ani=P">P</a></li>';
topList  += '<li><a href="?ani=Q">Q</a></li>';
topList  += '<li><a href="?ani=R">R</a></li>';
topList  += '<li><a href="?ani=S">S</a></li>';
topList  += '<li><a href="?ani=T">T</a></li>';
topList  += '<li><a href="?ani=U">U</a></li>';
topList  += '<li><a href="?ani=V">V</a></li>';
topList  += '<li><a href="?ani=W">W</a></li>';
topList  += '<li><a href="?ani=X">X</a></li>';
topList  += '<li><a href="?ani=Y">Y</a></li>';
topList  += '<li><a href="?ani=Z">Z</a></li>';
topList  += '</ul>';
topList  += '</div>';
	
var fusiontabledata = "";
fusiontabledata += topList+'<br/><h2 style="font-size: 20px;">'+title+'<div><fb:like href="http://apps.facebook.com/anime-anonymous/?id='+mal_id +'" send="true" width="700" show_faces="false" font=""></fb:like></div></h2>';
fusiontabledata += '<div><div><table border="0" width="100%" cellspacing="3" style="float: left">';
fusiontabledata += '<tr><td align="left" valign="top" colspan="2"><div id="leftbody"></div>';
fusiontabledata += '</td></tr><tr><td width="210" align="left" valign="top"><table border="0" width="100%" cellspacing="3" cellpadding="3"><tr><td style="border-style: solid; border-width: 0px" bordercolor="#f7f7f7">';
fusiontabledata += '<div class="picSurround"><img border="0" src="'+ imgstr +'.jpg"></div>';

//fusiontabledata += '<div><a href="#addtolistanchor" onclick=" getFavorites("watched");">Show Favorites</a></div>';
//fusiontabledata += '<div><a href="#addtolistanchor" onclick="Add2Favorite("watched",'+mal_id +');">Add to Favorites</a></div>';

fusiontabledata += '<h2>Anime Rating</h2>';
fusiontabledata += '<div class="spaceit"><span class="dark_text" style="font-size:20px">'+mal_score+'</span></div>';
//fusiontabledata += '<div class="rw-ui-container rw-urid-'+ mal_id +'"></div>';

/*
fusiontabledata += '<div id="sidebar-wrapper"><div class="sidebar section" id="sidebar"><div class="widget HTML" id="HTML3"><div class="widget-content">';
fusiontabledata += '<div id="profileRows"><a href="#addtolistanchor" onclick=" getFavorites("watched");">Show Favorites</a><a href="javascript:void(0);" onclick="Add2Favorite("watched",'+mal_id +');" style="font-weight: normal;"><span id="favOutput">Add to Favorites</span></a><a href="javascript:doedit(721);">Edit Anime Information</a></div>';
*/

fusiontabledata += '<h2>Alternative Titles</h2>';
fusiontabledata += '<div class="spaceit"><span class="dark_text">English:</span> '+synonyms+'</div>';

fusiontabledata += '<h2>Information</h2>';
fusiontabledata += '<div class="spaceit"><span class="dark_text">Type:</span> '+anime_type+'</div>';
fusiontabledata += '<div class="spaceit"><span class="dark_text">Episodes:</span> '+episodes+'</div>';
fusiontabledata += '<div class="spaceit"><span class="dark_text">Aired:</span> '+start_date+' to '+end_date+'</div>';

/*
fusiontabledata += '<h2>Statistics</h2>';
fusiontabledata += '<div class="spaceit"><span class="dark_text">Score:</span> '+mal_score+'</div>';
*/

fusiontabledata += '</div></div></div></td></tr>';


//fusiontabledata += '<tr><td>getfans</td></tr>'; //find a way to get a list of people who like a link. 
fusiontabledata += '</td></tr></table>';
fusiontabledata += '</td><td align="left" valign="top"><table border="0" width="100%" cellspacing="3" cellpadding="3" style="float: left"><tr><td class="label" align="left" colspan="2"><h2>Synopsis</h2></td>';
fusiontabledata += '</tr><tr><td width="100%">';
fusiontabledata += synopsis;
fusiontabledata += '</td></tr><tr><td class="label" align="left" colspan="2">&nbsp;</td></tr><tr><td class="label" align="left" colspan="2">';

/*
fusiontabledata += '<h2>Reletaed Anime</h2>';
fusiontabledata += 'side_story<br/>';
fusiontabledata += 'prequel<br/>';
fusiontabledata += 'sequel<br/>';
fusiontabledata += '<h2>'+title+' Fans</h2>';
fusiontabledata += '%Get fans List%';
*/

fusiontabledata += '<h2>Facebook Comments</h2>';
fusiontabledata += '<fb:comments href="http://apps.facebook.com/anime-anonymous/?id='+mal_id  +'" num_posts="2" width="500" xid="'+mal_id+'"_anime></fb:comments>';
fusiontabledata += '</td></tr></table>';
fusiontabledata += '</tr></table></td></tr></table>';

document.getElementById('ftdata').innerHTML = fusiontabledata;

//var mytitle = '<a href="http://animedom.blogspot.com/2011/09/animelist.html">'+title+'</a>';
//document.getElementsByTagName('h3')[0].innerHTML = ""; //Clear blogger title
try
  {
fbinit();

//alert("Wish me luck");

//alert("we did it"+metaTags.length);
}catch(er){
//nothing to see here
//alert(er.description);
}
/*
var i, refAttr;
var metaTags = document.getElementsByTagName('meta');
for (i in metaTags) {
try
  {
	refAttr = metaTags[i].getAttribute("property");

//    alert(refAttr+" : "+metaTags[i].getAttribute("content"));

    if( refAttr == 'og:image') {
		metaTags[i].setAttribute("content",thumbImg) ;
             //   alert(refAttr+" : "+metaTags[i].getAttribute("content"));
    }
	if( refAttr == 'og:description') {
		metaTags[i].setAttribute("content",cleanSynopsis) ;
              //  alert(refAttr+" : "+metaTags[i].getAttribute("content"));
    }
	if( refAttr == 'description') {
		metaTags[i].setAttribute("content",cleanSynopsis) ;
             //   alert(refAttr+" : "+metaTags[i].getAttribute("content"));
    }
}catch(er){
//nothing to see here
//alert(er.message);
}	
}
*/
}

</script>
</head>
<body onload="getData();">
<div id="fb-root"></div>
<div id="ftdata">Loading Anime List...<img border="0" height="21" src="http://1.bp.blogspot.com/-_jr8U-tayi0/Tm-PG9zwqAI/AAAAAAAAATM/xkxNHb_R7Gs/s400/indicator-u.gif" width="21" /></div>

</body>
</html>