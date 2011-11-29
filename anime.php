<link rel="stylesheet" href="http://aagmjc5n.facebook.joyent.us/anime.anonymous/animeanonymousstyle.v1.0.css" type="text/css" />

<div id="ftdata">Loading Anime Info...<img border="0" height="21" src="http://1.bp.blogspot.com/-_jr8U-tayi0/Tm-PG9zwqAI/AAAAAAAAATM/xkxNHb_R7Gs/s400/indicator-u.gif" width="21" /></div>

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
//http://google-plus-blogger-template.blogspot.com/
google.load('visualization', '1');


	var title;
	var anime_type;
	var episodes;
	var synopsis;
	var animeImg;
	var titleURL;
        var cleanSynopsis; 
var animeID;

function getData() {
if(getParameterByName("id")){
 		var whereClause = "";
		whereClause = "WHERE mal_id = " + getParameterByName("id");		 
		var queryText = encodeURIComponent("SELECT 'title', 'anime_type', 'episodes', 'synopsis', 'mal_score', 'mal_image','mal_id','start_date','end_date','synonyms' FROM 1409399 " + whereClause);
		var query = new google.visualization.Query('http://www.google.com/fusiontables/gvizdata?tq='  + queryText);
		query.send(getAnime);
		//FBCore();
}else{
  var whereClause = "";
  //whereClause = " LIMIT 15";
  whereClause = "ORDER BY mal_score DESC LIMIT 100";
  //whereClause = " WHERE 'rank' > '200'";
  //var queryText = encodeURIComponent("SELECT 'title', 'anime_type', 'episodes', 'synopsis', 'mal_score', 'mal_image' FROM 1409399 ");
  var queryText = encodeURIComponent("SELECT 'title', 'anime_type', 'episodes', 'synopsis', 'mal_score', 'mal_image','mal_id' FROM 1409399 " + whereClause);
  var query = new google.visualization.Query('http://www.google.com/fusiontables/gvizdata?tq='  + queryText);
  query.send(getRows);
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
var fusiontabledata = "";
fusiontabledata += '<h2 style="font-size: 20px;">'+title+'<div><fb:like href="http://apps.facebook.com/anime-anonymous/anime.php?id='+mal_id +'" send="true" width="700" show_faces="false" font=""></fb:like></div></h2>';
fusiontabledata += '<div><div><table border="0" width="100%" cellspacing="3" style="float: left">';
fusiontabledata += '<tr><td align="left" valign="top" colspan="2"><div id="leftbody"></div>';
fusiontabledata += '</td></tr><tr><td width="210" align="left" valign="top"><table border="0" width="100%" cellspacing="3" cellpadding="3"><tr><td style="border-style: solid; border-width: 0px" bordercolor="#f7f7f7">';
fusiontabledata += '<div class="picSurround"><img border="0" src="'+ imgstr +'.jpg"></div>';

fusiontabledata += '<div><a href="#addtolistanchor" onclick=" getFavorites("watched");">Show Favorites</a></div>';
fusiontabledata += '<div><a href="#addtolistanchor" onclick="Add2Favorite("watched",'+mal_id +');">Add to Favorites</a></div>';

fusiontabledata += '<h2>Anime Rating</h2>';
fusiontabledata += '<div class="rw-ui-container rw-urid-'+ mal_id +'"></div>';

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


fusiontabledata += '<tr><td>getfans</td></tr>';
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
fusiontabledata += '<fb:comments href="anime.php?id='+mal_id  +'" num_posts="2" width="500" xid="'+mal_id+'"_anime></fb:comments>';
fusiontabledata += '</td></tr></table>';
fusiontabledata += '</tr></table></td></tr></table>';

document.getElementById('ftdata').innerHTML = fusiontabledata;

//var mytitle = '<a href="http://animedom.blogspot.com/2011/09/animelist.html">'+title+'</a>';
//document.getElementsByTagName('h3')[0].innerHTML = ""; //Clear blogger title
try
  {
fbinit();
RW_Async_Init();

//alert("Wish me luck");
var i, refAttr;
var metaTags = document.getElementsByTagName('meta');
//alert("we did it"+metaTags.length);
}catch(er){
//nothing to see here
//alert(er.description);
}
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
}

function getRows(response) {
  numRows = response.getDataTable().getNumberOfRows();
  numCols = response.getDataTable().getNumberOfColumns();
  var fusiontabledata = "";
  var evenx = false;
  var apath = ""; // "anime.php"
  //for(i = 0; i < numCols; i++) {
  //fusiontabledata += response.getDataTable().getColumnLabel(i) + ",";
  //}
  fusiontabledata += '<br /><table><tr valign="top"><td align="left" valign="top"><table border="0" cellpadding="5" cellspacing="0">';
  fusiontabledata += '<tr valign="top">';
  for(i = 0; i < numRows; i++) {
    //for(j = 0; j < numCols-1; j++) {
 var q = i+1;
 var pq = 30;
 var title = response.getDataTable().getValue(i, 0);
 var anime_type = response.getDataTable().getValue(i, 1);
 var episodes = response.getDataTable().getValue(i, 2);
 var synopsis = response.getDataTable().getValue(i, 3);
 synopsis = synopsis.replace("\n"," ");
 synopsis = synopsis.replace("&lt;br /&gt;"," ");
 synopsis = synopsis.substr(0,150);
 var mal_score = response.getDataTable().getValue(i, 4);
 var imgstr = response.getDataTable().getValue(i, 5);
 imgstr = imgstr.substr(0,(imgstr.length-4));
 
 var crc_code = response.getDataTable().getValue(i, 6);
 
 fusiontabledata += '<td class="borderClass" style="border-width: 0 0 1px 0;" align="center" valign="top" width="'+pq+'">';
 fusiontabledata += '<span style="font-weight: bold; font-size: 24px;" class="lightLink">'+q+'</span>';
 fusiontabledata += '</td><td class="borderClass" align="center" valign="top" width="50">';
 fusiontabledata += '<div class="picSurround">';
 fusiontabledata += '<a href="'+apath+'?id='+crc_code+'" class="hoverinfo_trigger" id="#area5114" rel="#info5114">';
 fusiontabledata += '<img src="'+imgstr+'t.jpg" border="0">';
 fusiontabledata += '</a>';
 fusiontabledata += '</div>';
 fusiontabledata += '</td>';
 fusiontabledata += '<td class="borderClass" align="left" valign="top">';
 fusiontabledata += '<div id="area5114"> <div id="info5114" rel="a5114" class="hoverinfo"> </div> </div>';
 fusiontabledata += '<a href="'+apath+'?id='+crc_code+'" class="hoverinfo_trigger" id="#area5114" rel="#info5114">';
 fusiontabledata += '<strong>'+title+'</strong> </a> <div class="spaceit_pad"> ' + synopsis ;
 fusiontabledata += '<a href="'+apath+'?id='+ crc_code + '">';
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
   
   //var mytitle = '<a href="http://animedom.blogspot.com/2011/09/animelist.html">Top 100 Anime</a>';

 document.getElementById('ftdata').innerHTML = fusiontabledata;

 document.getElementsByTagName('h3')[0].innerHTML = ""; //mytitle; Clear blogger Title
}
</script>
<body onload="getData();"/>