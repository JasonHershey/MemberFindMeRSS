# MemberFindMeRSS
Create an RSS feed from MemberFindMe REST API

### Background
This is a small PHP project I did for our local [chamber of commerce](http://duvallchamberofcommerce.com).
The chamber's [web designer and admin](http://cascadevalleydesigns.com) asked for some help with automating part of an email compaign.
The email campaign will email subscribers a list of upcoming events in the local area during the next week.
Like many websites, the chamber website is based on Wordpress. The events are managed through MemberFind.me, which has a plug-in for Wordpress. 
We both knew about MailChimp's ability to create a campaign based on an RSS feed.  

**But**, MemberFind.me doesn't provide an option for an RSS feed.  Their community-based support suggested looking at the API calls
in their plug-in code, and creating our own custom feed.  That is where I came in, and this project is the result.

## MemberFind.me REST API
MemberFind.me doesn't have documentation for their APIs.  But as long as you have an example or two, the APIs are accessible,
and you know how to read the results, it isn't hard.

I found a couple of example calls, like this one:

``` php
https://api.memberfind.me/v1/evt?sdp=1468738800&edp=1475996400&org=12918&all&Z=1468964198&SF=NnJKmzY2vzAWvAMM02ITXG0blJlCeMKadwY2yMjS8Ft~#.$set['org']."&wee=1&grp=".$instance['grp']."&cnt=".$instance['cnt']."&sdp=".time()
```

I used the attached Windows PowerShell script, memberfindmerss.ps1 to help me figure out more information about the call and what it returned.
It returns json and the data fields map pretty directly to the parameters passed.
Looking at the data returned, the event descriptions on the site, and some common sense, and it isn't hard to figure the data out. 
I did learn that the data that is returned varies on how you call the API. If you call it in a way that returns a list of events,
you get a more limited set of data.
Here are the fields returned when asking for a list of events, for example with the call:

```
https://api.memberfind.me/v1/evt?szp=1468738800&edp=1475996400&org=12918&all&Z=1468964198
```

| Field | Description |
|-------|-------------|
| _id   | Event ID    |
| ttl   | Event title |
| sdp   | Start datetime as Unix timestamp |
| szp   | Start datetime in RFC 822 format |
| grp   | Group or category ID |
| url   | relative URL to event on WordPress site |
| cal   | Calendar -- daily, recurring, etc. |
| edp   | End datetime as Unix timestamp |
| ezp   | End datetime in RFS 822 format |
| adn   | Event location |
| lgo   | Is there a logo? _Boolean_ |
| _re   | Unknown |

If you call a specific event, like this:

```
https://api.memberfind.me/v1/evt?org=12918&url=2016/8/15/oxbow-farm-summer-camp
```

the following **additional** fields are returned:

| Field | Description |
|-------|-------------|
| col   | Collection? |
| adr   | Address with data as an array |
| dtl   | Detailed description as an HTML blob |
| org   | Org or Customer ID for MemberFind.me customer |
| cur   | Currency |
| cap   | Capacity limit? Unknown, but returns true or false |
| _ct   | Creation date as Unix datetime |
| tpl   | Unknown |


As you can see, I did not try and sort out what all the fields meant. I was really only looking for those we needed for the RSS feed.

After that building the RSS feed was pretty simple, with a quick visit to a couple of websites [I liked this one](http://cyber.law.harvard.edu/rss/rss.html) to verify the RSS XML format/schema.
A basic RSS file looks like this:

``` xml
<rss version='2.0'>
  <channel>
    <title>Channel title</title>
    <link>Link to RSS file</link>
    <description>Description of the RSS channel</description>
      <item>
        <item>RSS item</item>
        <link>Link to the item referenced</link>
        <category>A category for grouping or filtering items</category>
        <description>Detailed description. Can contain HTML, but must escape it or create CDATA</description>
        <enclosure>A file attachment or image</enclosure>
        <pubDate>Date the item was published</pubDate>
      </item>
      <item>
      ....
      </item>
  </channel>
</rss>
```
  
And in our case we mapped fields from MemberFind.me to RSS like this:

| RSS element | MemberFind.me field |
|---------|-------------|
| title   | ttl |
| link    | _Base URL to chamber website_ + url |
| category | grp |
| description | Built from other data **see below ** |
| enclosure | _Base URL to logo location where MemmberFind.me stores it_ + _id |
| pubDate | Generated date **see below** |

### RSS description element
The description was the most complex to build, because we wanted the description to have a complete summary of the event, much like it appears on the chamber website.
So, we built it up using multiple fields and additional text and HTML.
We created an HTML table, and the resulting HTML was:

``` html
<table>
	<tr><td>Venue: </td><td colspan='3'>[adn field]</td></tr>
	<tr><td>Event start: </td><td> [szp field] </td><td>Event end: </td><td> [ezp field]</td></tr>
	<tr><td><img width='150px' src='https://d1tif55lvfk8gc.cloudfront.net/[_id field].jpg'/></td><td colspan='3'>[dtl field]</td></tr>
	</table>
```

### RSS pubDate element
We also had to artificially create a pubDate element value.  MailChimp looks at the pubDate when deciding what RSS items to include in a mailing.  It looks likes for items published in the last week, or whatever time period is associated with the mail campaign. At first we thought to use the create date, _ct field.  But the events could be created months in advance. So instead, we create the pubdate based on the event date. We decided thathe pubDate would simply be 1 week before the event date.  This does give us some odd results where the pubDate can be 'in the future'.

### Filtering events and making the REST calls
In our case, we want to make sure that only events coming up in the next week are returned for the feed.  So, when we call the REST API, we build the calling URL using calculated dates, using the **strtotime** function.  Our calling URL to get the list of events is:

```
https://api.memberfind.me/v1/evt?org=12918&all&sdp=".strtotime("next Monday")."&edp=".strtotime("next Monday + 1 week")."&Z=".time()."&grp=".$_GET['categoryid']
```

You will notice we support passing a category ID in the RSS url so that our MailChimp campaign can excluse some events, like City Council meetings.

Also, later in the code, we call a slightly different URL, for each event, in order to get those other fields we mentioned earlier... like the **dtl** field that has the detailed descriptio of the event for when we build our table.

## Add-to-calendar updates

The solution has been updated to add links that will add events to a calendar. There are links that will create a .ics or .vcs file, or add an event to a users Google calendar.

The files are calitem.php and calvcs.php. Creation of these files was based on a variety of solutions available on stackoverflow.

Take a look at the solution and let me know what you think.

> **Note on using Windows PowerShell**  I found using PowerShell to be very handy for this project. It was a nice interactive way to call and review the results of a REST call.  I could quickly and easily play around to see what calls worked and see the results.  Afterwards, I could move things to PHP knowing that once added to the site, the only problems were likely with PHP syntax, not in the actual REST call.
