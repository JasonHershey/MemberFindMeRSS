<?php
header('Content-Type: text/xml'); //Creating an XML file
echo '<?xml version="1.0" standalone="yes"?>';
?>

<?php 
// Calling the event URL. In this case, we are looking for all events starting next week, so next Monday up to 1 week later. 
// We are also filtering by category ID passed in page URL
// updated to handle when no category is passed
if ($_GET['categoryid']>'')
{
$event_url="https://api.memberfind.me/v1/evt?org=12918&all&sdp=".strtotime("next Monday")."&edp=".strtotime("next Monday + 1 week")."&Z=".time()."&grp=".$_GET['categoryid'];
}
else
{
$event_url="https://api.memberfind.me/v1/evt?org=12918&all&sdp=".strtotime("next Monday")."&edp=".strtotime("next Monday + 1 week")."&Z=".time();

}?>

<!-- This is the beginning of our RSS feed -->
<rss version= "2.0"> 
<channel>
<title>Duvall Chamber of Commerce Upcoming Events</title> 
<link>http://duvallchamberofcommerce.com/rss</link> 
<description>A list of upcoming events from the Duvall chamber of commerce</description>


<?php

//We are now handling the json that was returned

$event_json = file_get_contents($event_url);
$events = json_decode($event_json);

//For each event returned, we are going to build the data for the RSS entry

foreach($events as $event){

// for each event, we now call the URL to get the event details, and process that json
	$event_details = file_get_contents("https://api.memberfind.me/v1/evt?org=12918&url=".$event->url);
	$event_detail=json_decode($event_details);

// Added to handling for Google calendar when the end time does not exist (for an all-day event)
// Url changes based on if edp value exists
if ($event->edp==''){
$googleurl="'http://www.google.com/calendar/event?action=TEMPLATE&text=".$event->ttl."&dates=".date('Ymd',$event->sdp)."T".date('His',$event->sdp)."/".date('Ymd',$event->sdp)."T".date('His',$event->sdp)."&details=For details on this and other events, visit the Duvall Chamber of Commerce event calendar at http://duvallchamberofcommerce.com/duvall-events/&location=".$event->adn."'";
}
else{   
$googleurl="'http://www.google.com/calendar/event?action=TEMPLATE&text=".$event->ttl."&dates=".date('Ymd',$event->sdp)."T".date('His',$event->sdp)."/".date('Ymd',$event->edp)."T".date('His',$event->edp)."&details=For details on this and other events, visit the Duvall Chamber of Commerce event calendar at http://duvallchamberofcommerce.com/duvall-events/&location=".$event->adn."'";
}

// we build the description with a common format and inserting data from the json		
// updated format of description to work better with mobile. Uses design defined by a designer
// also stripping out some html from the details of the event
// also added add-to-calendar links

	$description = "<table>
    <tr><td><img width='150px' src='https://d1tif55lvfk8gc.cloudfront.net/".$event->_id.".jpg'/></td></tr>
    </table>
    <table>
    <tr><td><h2>" .$event->ttl."</h2></td></tr>
    </table>
    <table>
    <tr><td>
    <h3><span style='font-size:12.0pt;line-height:125%;font-family:'Helvetica',sans-serif;color:firebrick'>---------------------------------------</span></h3>
    <span style='font-size:12.0pt;line-height:125%;font-family:'Helvetica',sans-serif;color:firebrick'>When: </span><span style='font-size:12.0pt;line-height:125%;font-family:'Helvetica',sans-serif;color:#303030'>".$event->szp." to ".$event->ezp."</span>
    <br/>
	<span style='font-size:12.0pt;line-height:125%;font-family:'Helvetica',sans-serif;color:firebrick'>Where: </span><span style='font-size:12.0pt;line-height:125%;font-family:'Helvetica',sans-serif;color:#303030'>".$event->adn."</span>
	<h3><span style='font-family:'Helvetica',sans-serif;color:firebrick'>---------------------------------------</span></h3>
    <span style='font-family:'Helvetica',sans-serif;color:#202020'>".strip_tags($event_detail->dtl,'<p><a><h1><h2><h3><table><td><tr><th><img><label>')."</span>
    </td></tr>
    </table>
    <table>
    <tr>
    <td>
    <p>Add to calendar:</p>
    <p><a href='./calitem.php?start=".$event->sdp."&amp;end=".$event->edp."&amp;desc=".$event->url."&amp;sub=".$event->ttl."&amp;loc=".$event->adn."' target=_new>Outlook/iPhone</a>
    -<a href='./calvcs.php?start=".$event->sdp."&amp;end=".$event->edp."&amp;desc=".$event->url."&amp;sub=".$event->ttl."&amp;loc=".$event->adn."' target=_new>Android</a>
    -<a href=".$googleurl." target=_new>Google</a></p>


 </td>
    </tr>
    </table>
    ";

// We are using the RSS with mail chimp which uses pubdate to decide what RSS items to mail out.
// So we artificially create the pubdate by subtracting 1 week from the start date for the event
    $pubdate = new DateTime($event->szp);
    $pubdate->sub(new DateInterval('P1W'));
    
    // and now we create the actual RSS entry
    echo "<item>";
	echo "<title>" .$event->ttl."</title>";
	echo "<link>https://duvallchamberofcommerce.com/duvall-events/#!event/".$event->url."</link>";
	echo "<category>".$event->grp."</category>";
	echo "<description>".htmlspecialchars($description)."</description>";
	echo "<enclosure>https://d1tif55lvfk8gc.cloudfront.net/".$event->_id.".jpg</enclosure>";
    echo "<pubDate>".date_format($pubdate,'r')."</pubDate>";	
echo"</item>";
};

?>

<!-- and we wrap up the XML for our feed -->
</channel>
</rss>