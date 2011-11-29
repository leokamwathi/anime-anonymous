<link rel="stylesheet" href="http://aagmjc5n.facebook.joyent.us/anime.anonymous/animeanonymousstyle.v1.0.css" type="text/css" />

<div id="ftdata">Loading Anime List...<img border="0" height="21" src="http://1.bp.blogspot.com/-_jr8U-tayi0/Tm-PG9zwqAI/AAAAAAAAATM/xkxNHb_R7Gs/s400/indicator-u.gif" width="21" /></div>

<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<div id="fb-root"></div>
<script type="text/javascript" id="fbscript">
function fbinit(){
  window.fbAsyncInit = function() {
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
 window.location.href = "anime.php?id="+getParameterByName("id");
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
 fusiontabledata += '<td class="borderClass" align="center" valign="top" width="50">';
 fusiontabledata += '<div class="picSurround">';
 fusiontabledata += '<a href="anime.php?id='+crc_code+'&name='+titleURL+'" class="hoverinfo_trigger" id="#area5114" rel="#info5114">';
 fusiontabledata += '<img src="'+imgstr+'t.jpg" border="0">';
 fusiontabledata += '</a>';
 fusiontabledata += '</div>';
 fusiontabledata += '</td>';
 fusiontabledata += '<td class="borderClass" align="left" valign="top">';
 fusiontabledata += '<div id="area5114"> <div id="info5114" rel="a5114" class="hoverinfo"> </div> </div>';
 fusiontabledata += '<a href="anime.php?id='+crc_code+'&name='+titleURL +'" class="hoverinfo_trigger" id="#area5114" rel="#info5114">';
 fusiontabledata += '<strong>'+title+'</strong> </a> <div class="spaceit_pad"> ' + synopsis ;
 fusiontabledata += '<a href="anime.php?id='+ crc_code + '&name='+titleURL +'">';
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
</script>
<body onload="getData();"/>