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
			$fields = 'anime_type,episodes,synopsis,mal_score,mal_image,mal_id,start_date,end_date,synonyms,title';
			$str = curl_exec ($c);
			//$str = substr($str,strlen($fields));
			//$str = str_replace('\n',' ',$str);
			//$str = str_replace('<br />',' ',$str);
			//$str = str_replace('href','link',$str);
			//$str = trim( preg_replace( '/\s+/', ' ', $str ) );
			//$str = $fields."\n".$str;
			//$results = preg_split("/\n/", $str);
			//print_r($results);
			$results = preg_split("/\n/", $fields."\n".preg_replace( '/\s+/', ' ',trim(substr($str,strlen($fields))))."\n");
			//$results = preg_split("/\n/", $fields."\n".str_replace('\n',' ',trim(substr($str,strlen($fields))))."\n");
			//$results = preg_split(",", $results);
			//echo("<br/>It was me<br/><pre>");
			//print_r($str);
			//print_r($results);
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
				$fields = 'anime_type,episodes,synopsis,mal_score,mal_image,mal_id,start_date,end_date,synonyms,title';
			$results = curl_exec ($c);
			$results = substr($results,strlen($fields));
			$results = str_replace('&amp;','&',$results);
			$results = str_replace('\n',' ',$results);
			$results = str_replace('<br />',' ',$results);
			$results = str_replace('http://myanimelist.net/anime/','',$results); //http://anime-anonymous.herokuapp.com/%22http://myanimelist.net/anime/263/Hajime_no_Ippo%22
			$results = str_replace('href="','href="http://apps.facebook.com/anime-anonymous/?q=',$results);
			$results = str_replace('href ="','href="http://apps.facebook.com/anime-anonymous/?q=',$results);
			$results = str_replace('href = "','href="http://apps.facebook.com/anime-anonymous/?q=',$results);
			$results = str_replace('href= "','href="http://apps.facebook.com/anime-anonymous/?q=',$results);
			$results = trim( preg_replace( '/\s+/', ' ', $results ) );
			$results = $fields."\n".$results;
			$results = preg_split("/\n/", $results);
			//$results = preg_split(",", $results);
			//echo("<br/>It was me<br/><pre>");
			print_r($results);
			//$results = preg_split("/\n/", curl_exec ($c));
			
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
<html xmlns:fb="http://ogp.me/ns/fb#">
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
	//echo('<!--'.htmlentities(print_r($output)).'-->');
	if($output){
	echo('<meta property="og:title" content="'.str_replace('<',' ',str_replace('>',' ',$output[0]['title'])).'"/>
    <meta property="og:type" content="tv_show"/>
    <meta property="og:url" content="http://apps.facebook.com/anime-anonymous/?id='.$_REQUEST['id'].'"/>
	<meta property="og:site_url" content="http://apps.facebook.com/anime-anonymous/?id='.$_REQUEST['id'].'"/>
    <meta property="og:image" content="'.$output[0]['mal_image'].'"/>
    <meta property="og:site_name" content="Anime Anonymous"/>
	<meta property="fb:app_id" content="39732531101"/>
	<meta property="fb:admins" content="505625483"/>
	<meta property="fb:admin" content="505625483"/>
    <meta property="og:description" content="'.str_replace('<',' ',str_replace('>',' ',str_replace('&amp;','&',$output[0]['synopsis']))).'"/>');
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
<link rel="stylesheet" href="stylesheets/animeanonymousstyle.v1.0.css" type="text/css" />
<script type="text/javascript" src="http://www.google.com/jsapi"></script>

<script type="text/javascript" id="fbscript">
 window.location.hash="topLink";
 window.scroll(0,0);
var myUID;
var myName;
var myImg;
var title;
var anime_type;
var episodes;
var synopsis;
var titleURL;
var mal_score;
var imgstr;
var mal_id;
var sdate;
var edate;
var synonyms;
var findstr;
var app_id;
var redirect_path;
var base_path;
var app_path;
var def_path;
function fbinit(){
  window.fbAsyncInit = function() {
	FB.Canvas.setAutoResize();
    FB.init({appId: '39732531101', status: true, cookie: true, xfbml: true});
	app_id = "39732531101";
	base_path = "http://anime-anonymous.herokuapp.com/";
	app_path = "http://apps.facebook.com/anime-anonymous/";
	//?id='+mal_id+'
	
	
	if(window.location != window.parent.location){
		//its an iframe
		def_path = app_path;
	}else{
		//home path
		def_path = base_path;
	}
	
	if(getParameterByName("id")){
		redirect_path = base_path+"?id="+getParameterByName("id");
	}else if(getParameterByName("ani")){
		redirect_path = base_path+"?ani="+getParameterByName("ani");
	}else{
		redirect_path = base_path;
	}
		//alert(redirect_path);
	
				FB.getLoginStatus(function(response) {
					if (response.session) {
						//alert('one piece');
						FB.login(function(response) {}, {scope:'email,publish_stream,publish_actions'});
					}else{
							if(getParameterByName("code")){
								//alert('code gease');
							}else{
								//alert('Bleach');
								FB.login(function(response) {}, {scope:'email,publish_stream,publish_actions'});
								//top.location=window.location="http://www.facebook.com/dialog/oauth/?scope=email,publish_stream,publish_actions&client_id="+ app_id +"&redirect_uri="+ redirect_path +"&response_type=code";
							}
					}
				});
				FB.Event.subscribe('edge.create', function(response) {
					becomeFan();
				});
				FB.api('/me', function(response) {
				//if(getParameterByName("debug")){
				console.log(response);
				myUID = response.id;
				myName = response.name;
				myImg = "https://graph.facebook.com/"+ myUID +"/picture?type=normal";
				//}
				/*
			    var query = FB.Data.query('select uid, name, hometown_location, sex, pic_square from user where uid={0}', response.id);
				query.wait(function(rows) {
				 myUID = rows[0].uid ;
				 myName = rows[0].name ;
				 myImg = rows[0].pic_square ;
				 //document.getElementById('login').style.display = "block";
				 //document.getElementById('login').innerHTML = response.name + " succsessfully logged in! -> " + myUID;
				
			 });
			 */
		});
  };
  (function() {
    var e = document.createElement('script'); e.async = true;
    e.src = document.location.protocol +'//connect.facebook.net/en_US/all.js';
    document.getElementById('fb-root').appendChild(e);
	
	if(window.location == window.parent.location){
	/*
		var ee = document.createElement('script'); ee.async = true;
		ee.src = 'http://static.ak.fbcdn.net/connect.php/js/FB.Share';
		document.getElementById('fb-share-root').appendChild(ee);
	*/
	}

  }());
}
</script>
<script type="text/javascript" id="script">

google.load('visualization', '1');
var counter = false;
var mytitle = '<h2><div style="float: right;"></div>TOP 100 Anime</h2>';
//mytitle = 'TOP 100 Anime List';
function becomeFan(){
	var anipath = 'http://apps.facebook.com/anime-anonymous/?id='+mal_id+'&name='+titleURL; 
FB.ui(
  {
    method: 'feed',
    name:  myname+" is now a fan of "+title,
    link: anipath ,
    picture: animeImg+'t.jpg',
    caption: myname+" become a fan of "+title,
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
}
function getData() {
 window.location.hash="topLink";
 window.scroll(0,0);
if(getParameterByName("id")){
 //window.location.href = "?id="+getParameterByName("id");
	var whereClause = "";
	whereClause = "WHERE mal_id = " + getParameterByName("id");		 
	var queryText = encodeURIComponent("SELECT 'title', 'anime_type', 'episodes', 'synopsis', 'mal_score', 'mal_image','mal_id','start_date','end_date','synonyms' FROM 1409399 " + whereClause);
	var query = new google.visualization.Query('http://www.google.com/fusiontables/gvizdata?tq='  + queryText);
	query.send(getAnime);
}else if(getParameterByName("q")){
	findstr = getParameterByName("q");
	var qArray = findstr.split("/");
	if(qArray.length>1){
		findstr = qArray[1];
	}
	//findstr = myReplace(findstr,"/"," ");
	//findstr = myReplace(findstr,"-"," ");
	findstr = myReplace(findstr,"_"," ");
	//findstr = myReplace(findstr,"  "," ");
	//var slash = findstr.indexOf("/");
	//findstr = findstr.substr();
	//var whereClause = "Where title like '"+findstr+"' ORDER BY title ASC LIMIT 100";
	//alert(findstr);
	var whereClause = "Where title CONTAINS '"+findstr+"' ORDER BY title ASC LIMIT 100";
	var queryText = encodeURIComponent("SELECT 'title', 'anime_type', 'episodes', 'synopsis', 'mal_score', 'mal_image','mal_id','start_date','end_date','synonyms' FROM 1409399 " + whereClause);
	var query = new google.visualization.Query('http://www.google.com/fusiontables/gvizdata?tq='  + queryText);
	if(qArray.length>1){
		query.send(getAnime);
	}else{
		query.send(getSearch);
	}
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
  whereClause = "Where title >='0' and title < 'A' ORDER BY title ASC LIMIT 500";
}else if (findchar=="A"){
  whereClause = "Where title >='A' and title < 'B' ORDER BY title ASC LIMIT 500";
}else if (findchar=="B"){
  whereClause = "Where title >='B' and title < 'C' ORDER BY title ASC LIMIT 500";
}else if (findchar=="C"){
  whereClause = "Where title >='C' and title < 'D' ORDER BY title ASC LIMIT 500";
}else if (findchar=="D"){
  whereClause = "Where title >='D' and title < 'E' ORDER BY title ASC LIMIT 500";
}else if (findchar=="E"){
  whereClause = "Where title >='E' and title < 'F' ORDER BY title ASC LIMIT 500";
}else if (findchar=="F"){
  whereClause = "Where title >='F' and title < 'G' ORDER BY title ASC LIMIT 500";
}else if (findchar=="G"){
  whereClause = "Where title >='G' and title < 'H' ORDER BY title ASC LIMIT 500";
}else if (findchar=="H"){
  whereClause = "Where title >='H' and title < 'I' ORDER BY title ASC LIMIT 500";
}else if (findchar=="I"){
  whereClause = "Where title >='I' and title < 'J' ORDER BY title ASC LIMIT 500";
}else if (findchar=="J"){
  whereClause = "Where title >='J' and title < 'K' ORDER BY title ASC LIMIT 500";
}else if (findchar=="K"){
  whereClause = "Where title >='K' and title < 'L' ORDER BY title ASC LIMIT 500";
}else if (findchar=="L"){
  whereClause = "Where title >='L' and title < 'M' ORDER BY title ASC LIMIT 500";
}else if (findchar=="M"){
  whereClause = "Where title >='M' and title < 'N' ORDER BY title ASC LIMIT 500";
}else if (findchar=="N"){
  whereClause = "Where title >='N' and title < 'O' ORDER BY title ASC LIMIT 500";
}else if (findchar=="O"){
  whereClause = "Where title >='O' and title < 'P' ORDER BY title ASC LIMIT 500";
}else if (findchar=="P"){
  whereClause = "Where title >='P' and title < 'Q' ORDER BY title ASC LIMIT 500";
}else if (findchar=="Q"){
  whereClause = "Where title >='Q' and title < 'R' ORDER BY title ASC LIMIT 500";
}else if (findchar=="R"){
  whereClause = "Where title >='R' and title < 'S' ORDER BY title ASC LIMIT 500";
}else if (findchar=="S"){
  whereClause = "Where title >='S' and title < 'T' ORDER BY title ASC LIMIT 500";
}else if (findchar=="T"){
  whereClause = "Where title >='T' and title < 'U' ORDER BY title ASC LIMIT 500";
}else if (findchar=="U"){
  whereClause = "Where title >='U' and title < 'V' ORDER BY title ASC LIMIT 500";
}else if (findchar=="V"){
  whereClause = "Where title >='V' and title < 'W' ORDER BY title ASC LIMIT 500";
}else if (findchar=="W"){
  whereClause = "Where title >='W' and title < 'X' ORDER BY title ASC LIMIT 500";
}else if (findchar=="X"){
  whereClause = "Where title >='X' and title < 'Y' ORDER BY title ASC LIMIT 500";
}else if (findchar=="Y"){
  whereClause = "Where title >='Y' and title < 'Z' ORDER BY title ASC LIMIT 500";
}else if (findchar=="Z"){
  whereClause = "Where title >='Z' and title < 'a' ORDER BY title ASC LIMIT 500";
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
  }
  fbinit();
  window.location.hash="topLink";
  window.scroll(0,0);
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
 title = response.getDataTable().getValue(i, 0);
 anime_type = response.getDataTable().getValue(i, 1);
 episodes = response.getDataTable().getValue(i, 2);
 synopsis = response.getDataTable().getValue(i, 3);
 titleURL = encodeURIComponent(title);
 var stx = synopsis;
 synopsis = synopsis.nl2br('<br/>');
while(synopsis==stx){

synopsis = synopsis.replace("&lt;","<"); 
synopsis = synopsis.replace("&gt;",">");
synopsis = synopsis.replace("&amp;","&");
synopsis = synopsis.replace("\n","<br/>");
synopsis = synopsis.replace("\r","<br/>");
//synopsis = synopsis.replace("\g","<br/>");
synopsis = synopsis.replace(/\n/,"<br/>");
synopsis = synopsis.replace(/\r/,"<br/>");
//synopsis = synopsis.replace(/\g/,"<br/>");
synopsis = synopsis.replace("&quot;",'"');
synopsis = synopsis.replace('http://myanimelist.net/anime/',''); //http://anime-anonymous.herokuapp.com/%22http://myanimelist.net/anime/263/Hajime_no_Ippo%22
synopsis = synopsis.replace('href="','href  =  "http://anime-anonymous.herokuapp.com/?q=');
synopsis = synopsis.replace('href ="','href  =  "http://anime-anonymous.herokuapp.com/?q=');
synopsis = synopsis.replace('href = "','href  =  "http://anime-anonymous.herokuapp.com/?q=');
synopsis = synopsis.replace('href= "','href  =  "http://anime-anonymous.herokuapp.com/?q=');
//synopsis = synopsis.replace("\n","<br/>");
synopsis = synopsis.replace( /\r\n|\r|\n/g, "<br/>");


if(synopsis ==stx){
stx = "macmendex";
}else{
stx = synopsis ;
}
}

 synopsis = synopsis.substr(0,150);

 mal_score = response.getDataTable().getValue(i, 4);
 imgstr = response.getDataTable().getValue(i, 5);
 imgstr = imgstr.substr(0,(imgstr.length-4));
 
 var crc_code = response.getDataTable().getValue(i, 6);
 if(counter){
 fusiontabledata += '<td class="borderClass" style="border-width: 0 0 1px 0;" align="center" valign="top" width="'+pq+'">';
 fusiontabledata += '<span style="font-weight: bold; font-size: 24px;" class="lightLink">'+q+'</span></td>';
 }
 fusiontabledata += '<td class="borderClass" align="center" valign="top" width="50px">';
 fusiontabledata += '<div class="picSurround">';
 fusiontabledata += '<a href="?id='+crc_code+'&name='+titleURL+'" class="hoverinfo_trigger" id="#area5114" rel="#info5114" target="_self">';
 fusiontabledata += '<img src="'+imgstr+'t.jpg" border="0">';
 //fusiontabledata += '<img src="'+imgstr+'.jpg" width="50%" height="50%" border="0">';
 fusiontabledata += '</a>';
 fusiontabledata += '</div>';
 fusiontabledata += '</td>';
 fusiontabledata += '<td class="borderClass" align="left" valign="top">';
 fusiontabledata += '<div id="area5114"> <div id="info5114" rel="a5114" class="hoverinfo"> </div> </div>';
 fusiontabledata += '<a href="?id='+crc_code+'&name='+titleURL +'" class="hoverinfo_trigger" id="#area5114" rel="#info5114" target="_self">';
 fusiontabledata += '<strong>'+title+'</strong> </a> <div class="spaceit_pad"> ' + synopsis ;
 fusiontabledata += '<a href="?id='+ crc_code + '&name='+titleURL +'" target="_self">';
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


function getSearch(response) {
  numRows = response.getDataTable().getNumberOfRows();
  numCols = response.getDataTable().getNumberOfColumns();
  var fusiontabledata = "";
  var evenx = false;
  mytitle = "Searching for '"+findstr+"' ("+numRows+" records found)";
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
 title = response.getDataTable().getValue(i, 0);
 anime_type = response.getDataTable().getValue(i, 1);
 episodes = response.getDataTable().getValue(i, 2);
 synopsis = response.getDataTable().getValue(i, 3);
 titleURL = encodeURIComponent(title);
 var stx = synopsis;
 synopsis = synopsis.nl2br('<br/>');
while(synopsis==stx){

synopsis = synopsis.replace("&lt;","<"); 
synopsis = synopsis.replace("&gt;",">");
synopsis = synopsis.replace("&amp;","&");
synopsis = synopsis.replace("\n","<br/>");
synopsis = synopsis.replace("\r","<br/>");
//synopsis = synopsis.replace("\g","<br/>");
synopsis = synopsis.replace(/\n/,"<br/>");
synopsis = synopsis.replace(/\r/,"<br/>");
//synopsis = synopsis.replace(/\g/,"<br/>");
synopsis = synopsis.replace("&quot;",'"');
synopsis = synopsis.replace('http://myanimelist.net/anime/',''); //http://anime-anonymous.herokuapp.com/%22http://myanimelist.net/anime/263/Hajime_no_Ippo%22
synopsis = synopsis.replace('href="','href  =  "http://anime-anonymous.herokuapp.com/?q=');
synopsis = synopsis.replace('href ="','href  =  "http://anime-anonymous.herokuapp.com/?q=');
synopsis = synopsis.replace('href = "','href  =  "http://anime-anonymous.herokuapp.com/?q=');
synopsis = synopsis.replace('href= "','href  =  "http://anime-anonymous.herokuapp.com/?q=');
//synopsis = synopsis.replace("\n","<br/>");
synopsis = synopsis.replace( /\r\n|\r|\n/g, "<br/>");


if(synopsis ==stx){
stx = "macmendex";
}else{
stx = synopsis ;
}
}

 synopsis = synopsis.substr(0,150);

 mal_score = response.getDataTable().getValue(i, 4);
 imgstr = response.getDataTable().getValue(i, 5);
 imgstr = imgstr.substr(0,(imgstr.length-4));
 
 var crc_code = response.getDataTable().getValue(i, 6);
 if(counter){
 fusiontabledata += '<td class="borderClass" style="border-width: 0 0 1px 0;" align="center" valign="top" width="'+pq+'">';
 fusiontabledata += '<span style="font-weight: bold; font-size: 24px;" class="lightLink">'+q+'</span></td>';
 }
 fusiontabledata += '<td class="borderClass" align="center" valign="top" width="50px">';
 fusiontabledata += '<div class="picSurround">';
 fusiontabledata += '<a href="?id='+crc_code+'&name='+titleURL+'" class="hoverinfo_trigger" id="#area5114" rel="#info5114" target="_self">';
 fusiontabledata += '<img src="'+imgstr+'t.jpg" border="0">';
 //fusiontabledata += '<img src="'+imgstr+'.jpg" width="50%" height="50%" border="0">';
 fusiontabledata += '</a>';
 fusiontabledata += '</div>';
 fusiontabledata += '</td>';
 fusiontabledata += '<td class="borderClass" align="left" valign="top">';
 fusiontabledata += '<div id="area5114"> <div id="info5114" rel="a5114" class="hoverinfo"> </div> </div>';
 fusiontabledata += '<a href="?id='+crc_code+'&name='+titleURL +'" class="hoverinfo_trigger" id="#area5114" rel="#info5114" target="_self">';
 fusiontabledata += '<strong>'+title+'</strong> </a> <div class="spaceit_pad"> ' + synopsis ;
 fusiontabledata += '<a href="?id='+ crc_code + '&name='+titleURL +'" target="_self">';
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
synopsis = synopsis.nl2br('<br/>');
while(synopsis ==stx){
//synopsis = synopsis.replace("&lt;","<"); 
//synopsis = synopsis.replace("&gt;",">");
synopsis = synopsis.replace("&lt;","<"); 
synopsis = synopsis.replace("&gt;",">");
synopsis = synopsis.replace("&amp;","&");
synopsis = synopsis.replace("\n","<br/>");
synopsis = synopsis.replace("\r","<br/>");
//synopsis = synopsis.replace("\g","<br/>");
synopsis = synopsis.replace(/\n/,"<br/>");
synopsis = synopsis.replace(/\r/,"<br/>");
//synopsis = synopsis.replace(/\g/,"<br/>");
synopsis = synopsis.replace("&quot;",'"');
synopsis = synopsis.replace('http://myanimelist.net/anime/',''); //http://anime-anonymous.herokuapp.com/%22http://myanimelist.net/anime/263/Hajime_no_Ippo%22
synopsis = synopsis.replace('href="','href  =  "http://anime-anonymous.herokuapp.com/?q=');
synopsis = synopsis.replace('href ="','href  =  "http://anime-anonymous.herokuapp.com/?q=');
synopsis = synopsis.replace('href = "','href  =  "http://anime-anonymous.herokuapp.com/?q=');
synopsis = synopsis.replace('href= "','href  =  "http://anime-anonymous.herokuapp.com/?q=');
synopsis = synopsis.replace( /\r\n|\r|\n/g, "<br/>");
//synopsis = synopsis.replace(" br/","br/");
//synopsis = synopsis.replace("br/ ","br/");

if(synopsis ==stx){
stx = "macmendex";
}else{
stx = synopsis ;
}
}
	
	 mal_score = response.getDataTable().getValue(0, 4);
	 imgstr = response.getDataTable().getValue(0, 5);
	imgstr = imgstr.substr(0,(imgstr.length-4));
        animeImg = imgstr;
	thumbImg = imgstr+"t.jpg";
	mal_id = response.getDataTable().getValue(0, 6);
	sdate = response.getDataTable().getValue(0, 7);
	edate = response.getDataTable().getValue(0, 8);
	synonyms = title; + ' , ' + response.getDataTable().getValue(0, 9);
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

//<a name="fb_share" type="button" share_url="http://apps.facebook.com/anime-anonymous/?id='+mal_id+'"></a>


//
//fusiontabledata += topList+'<br/><div class="animeTitle" >'+title+'</div><div><br/></div><div style="float: left;"><a name="fb_share" type="button_count" ></a><fb:add-to-timeline show-faces="false" width="100"  mode="button"></fb:add-to-timeline><fb:like href="http://apps.facebook.com/anime-anonymous/?id='+mal_id+'" send="true" width="400" show_faces="false" font=""></fb:like><fb:login-button show-faces="false" width="100" max-rows="0" scope="email,publish_stream,publish_actions"></fb:login-button></div>';
fusiontabledata += topList+'<br/><div class="animeTitle" >'+title+'</div><div><br/></div><div style="float: left;"><table cellspacing = 2 cellpadding = 2><tr><td><div style="float: left;"><a name="fb_share" type="button_count" ></a></td><td><div style="float: left;"><fb:like type="button_count" href="http://apps.facebook.com/anime-anonymous/?id='+mal_id+'" send="true" width="300" show_faces="false" font=""></fb:like></td><td><div style="float: left;"><fb:add-to-timeline show-faces="false" width="80"  mode="button"></fb:add-to-timeline></td><td><div style="float: right;"><fb:login-button show-faces="false" width="100" max-rows="0" scope="email,publish_stream,publish_actions"></fb:login-button></div></td></tr></table></div>';
//<div><br/></div><div style="float: left;"><table cellspacing = 2 cellpadding = 2><tr><td><div style="float: left;"><a name="fb_share" type="button_count" ></a></td><td><div style="float: left;"><fb:like href="http://apps.facebook.com/anime-anonymous/?id='+mal_id+'" send="true" width="400" show_faces="false" font=""></fb:like></td><td><div style="float: left;"><fb:add-to-timeline show-faces="false" width="80"  mode="button"></fb:add-to-timeline></td><td><div style="float: right;"><fb:login-button show-faces="false" width="100" max-rows="0" scope="email,publish_stream,publish_actions"></fb:login-button></div></td></tr></table></div>';
fusiontabledata += '<div><div><table border="0" width="100%" cellspacing="3" style="float: left;">';
fusiontabledata += '<tr><td align="left" valign="top" colspan="2"><div id="leftbody"></div>';
fusiontabledata += '</td></tr><tr><td width="210" align="left" valign="top"><table border="0" width="100%" cellspacing="3" cellpadding="3"><tr><td style="border-style: solid; border-width: 0px" bordercolor="#f7f7f7">';
fusiontabledata += '<div class="picSurround"><img border="0" src="'+ imgstr +'.jpg"></div>';
var fbLink = encodeURIComponent('http://apps.facebook.com/anime-anonymous/?id='+mal_id);
var fbSharePath = 'http://www.facebook.com/sharer.php?u='+fbLink+'&t='+encodeURIComponent(title)+'';
var fbsharelink = '<a href="#" name="fb_share" class="buttonLinx" type="button_count" share_url="http://apps.facebook.com/anime-anonymous/?id='+mal_id+'" onclick="window.open('+fbSharePath+');">Share on facebook</a>';
//fusiontabledata += '<br/><div>'+fbsharelink+'</div>';
fusiontabledata += '<br/><div><a href="#" class="buttonLinx" onclick="recommend();">Recommend To Friends</a></div>';
//fusiontabledata += '<br/><div><fb:add-to-timeline show-faces="false" mode="box"></fb:add-to-timeline></div>';

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


//fusiontabledata += '<tr><td><h2>Fans</h2><br/><fb:facepile href="http://apps.facebook.com/anime-anonymous/?id='+mal_id+'" width="200" max_rows="8"></fb:facepile></td></tr>'; //find a way to get a list of people who like a link. 
fusiontabledata += '<tr><td><h2>Fans</h2><br/><fb:like-box href="http://apps.facebook.com/anime-anonymous/?id='+mal_id+'" width="200" show_faces="true" stream="false" header="false"></fb:like-box></td></tr>'; //find a way to get a list of people who like a link. 
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

function recommend() {
FB.getLoginStatus(function(response) {
var myMessage = myName + " has recommended you to check out "+title;
var myData = mal_id;
var myTitle = "Recommend "+title+" to your friends"
if(getParameterByName("debug")){
			console.log(response);
		}
  if (response.session) {
FB.ui({method: 'apprequests', display: 'iframe', message: myMessage.substr(0,254), data: myData,title:myTitle.substr(0,49)});
  } else {
	//send to permission page
	FB.login(function(response) {}, {scope:'email,publish_stream,publish_actions'});
	FB.getLoginStatus(function(response) {
		if (response.session) {
			FB.ui({method: 'apprequests', display: 'iframe', message: myMessage.substr(0,254), data: myData,title:myTitle.substr(0,49)});
		} else {
			alert("You must be logged on to Facebook to do that. Click the login button and try again or try refreshing the page.");
		}
	});
  }
});
}

function myReplace(str,fstr,rstr){
	var strx = str;
	try{
		while(str==strx){
			str = str.replace(fstr,rstr); 
			if(str ==strx){
				strx = "macmendex";
			}else{
				strx = str;
			}
		}
		return str;
	}catch(err){
		return "";
	}
}

String.prototype.nl2br = function() {
	var br;
	if( typeof arguments[0] != 'undefined' ) {
		br = arguments[0];
	}
	else {
		br = '<br />';
	}
	return this.replace( /\r\n|\r|\n/g, br );
}
 
String.prototype.br2nl = function() {
	var nl;
	if( typeof arguments[0] != 'undefined' ) {
		nl = arguments[0];
	} 
	else {
		nl = '\r\n';
	}
	return this.replace( /\<br(\s*\/|)\>/g, nl );
}
 window.location.hash="topLink";
 window.scroll(0,0);
</script>
<style>
#wrap { 
  width: 900px; 
  /*overflow-x:hidden;*/
  margin: 0 auto; 
  /*overflow-y:auto;*/
  /*overflow-x:auto;*/
  /*height:100%;*/
}
</style>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-3637395-7']);
  _gaq.push(['_setDomainName', 'herokuapp.com']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>
<body onload="getData();" style="background-color: #f7f7f7;" >

<a name="topLink"><div/></a>
<div id="fb-root"></div>
<div id="fb-share-root"></div>
<div id="wrap"  style="background-color: #ffffff;" >
<?php 
  if (is_string($_REQUEST['idx'])){
  ?>
<!-- floating page sharers Start -->
<style>
#pageshare {position:fixed; top:350px; margin-left:-60px; float:left; border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;background-color:#eee;border:1px solid #DDD;padding:0 0 2px 0;z-index:10;}
#pageshare .sbutton {float:left;clear:both;margin:5px 5px 0 5px;}
.fb_share_count_top {width:48px !important;}
.fb_share_count_top, .fb_share_count_inner {-moz-border-radius:3px;-webkit-border-radius:3px;}
.FBConnectButton_Small, .FBConnectButton_RTL_Small {width:49px !important; -moz-border-radius:3px;/*bs-fsmsb*/-webkit-border-radius:3px;}
.FBConnectButton_Small .FBConnectButton_Text {padding:2px 2px 3px !important;-moz-border-radius:3px;-webkit-border-radius:3px;font-size:8px;}
</style>
<div id='pageshare'>
<div class='sbutton' id='fb'>
<fb:like href='http://apps.facebook.com/anime-anonymous/?id=<?php echo($_REQUEST['id']);?>' layout='box_count' send='true' show_faces='true' width='50'/>
</fb:like></div>
<div class='sbutton' id='fb'>
<a name="fb_share" type="box_count">Share</a>
</a>
</div>

<?php
/*
	echo('
	<div class='sbutton' id='fb'>
	<fb:share-button class="meta">
	<meta name="title" content="'.str_replace('<',' ',str_replace('>',' ',$output[0]['title'])).'"/>
	<meta name="description" content="'.str_replace('<',' ',str_replace('>',' ',str_replace('&amp;','&',$output[0]['synopsis']))).'"/>
	<link rel="image_src" href="'.$output[0]['mal_image'].'"/>
	<link rel="target_url" href="http://apps.facebook.com/anime-anonymous/?id='.$_REQUEST['id'].'"/>
	</fb:share-button>
	</div>
    ');
	*/
?>

<div class='sbutton' id='rt'>
<script src="http://tweetmeme.com/i/scripts/button.js" type='text/javascript'></script>
</div>
<div class='sbutton' id='su'>
<script src="http://www.stumbleupon.com/hostedbadge.php?s=5"></script>
</div>
<div class='sbutton' id='digg' style='margin-left:3px;width:48px'>
<script src='http://widgets.digg.com/buttons.js' type='text/javascript'></script>
<a class="DiggThisButton DiggMedium"></a>
</div>
<div class='sbutton' id='gplusone'>
<script type="text/javascript" src="http://apis.google.com/js/plusone.js"></script>
<g:plusone size="tall"></g:plusone>
</div>
<div style="clear: both;font-size: 9px;text-align:center;"><a href="http://socialchatrooms.blogspot.com/">Live Chat</a></div>
</div>
<!-- floating page sharers End -->
<?php
// href="http://www.facebook.com/sharer.php"
}
?>
<?php
$myRnd = rand(0, 15);
if($myRnd>9){
$imgNo = "0".$myRnd;
}else{
$imgNo = "00".$myRnd;
}
$bannerImage = "images/".$imgNo.".png";
echo('<div style="background-position: left top; position:relative; background-image:url('."'".$bannerImage."'".'); background-repeat:no-repeat"><img border="0" src="images/anime-anonymous-banner-3.png" width="900" height="200"/></div>');
//<script src="xhttp://static.ak.fbcdn.net/connect.php/js/FB.Sharex" type="text/javascript"></script>

?>

<div id="ftdata" style="background-color: #ffffff;">Loading Anime List...<img border="0" height="21" src="http://1.bp.blogspot.com/-_jr8U-tayi0/Tm-PG9zwqAI/AAAAAAAAATM/xkxNHb_R7Gs/s400/indicator-u.gif" width="21" /></div>
</div>
</body>
</html>