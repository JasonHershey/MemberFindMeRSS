<?php
header('Content-Type: text/xml'); //Creating an XML file
echo '<?xml version="1.0" standalone="yes"?>';
?>

<?php 
// Calling the event URL. In this case, we are looking for all events starting next week, so next Monday up to 1 week later. 
// We are also filtering by category ID passed in page URL
$event_url="https://api.memberfind.me/v1/evt?org=12918&all&sdp=".strtotime("next Monday")."&edp=".strtotime("next Monday + 1 week")."&Z=".time()."&grp=".$_GET['categoryid'];
?>

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

// we build the description with a common format and inserting data from the json		
	$description = "<table>
	<tr><td>Venue: </td><td colspan='3'>".$event->adn."</td></tr>
	<tr><td>Event start: </td><td>".$event->szp."</td><td>Event end: </td><td>".$event->ezp."</td></tr>
	<tr><td><img width='150px' src='https://d1tif55lvfk8gc.cloudfront.net/".$event->_id.".jpg'/></td><td colspan='3'>".$event_detail->dtl." </td></tr>
	</table>";

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