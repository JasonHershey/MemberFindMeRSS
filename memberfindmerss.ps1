$url="https://api.memberfind.me/v1/evt?szp=1468738800&edp=1475996400&org=12918&all&Z=1468964198"

#https://api.memberfind.me/v1/evt?sdp=1468738800&edp=1475996400&org=12918&all&Z=1468964198&SF=NnJKmzY2vzAWvAMM02ITXG0blJlCeMKadwY2yMjS8Ft~

#https://api.memberfind.me/v1/evt?sdp=1468738800&edp=1475996400&org=12918&all&Z=1468964198&SF=NnJKmzY2vzAWvAMM02ITXG0blJlCeMKadwY2yMjS8Ft~

#.$set['org']."&wee=1&grp=".$instance['grp']."&cnt=".$instance['cnt']."&sdp=".time()

$stuff = Invoke-RestMethod -Uri $url -Method Get;




$details="https://api.memberfind.me/v1/evt?org=12918&url=2016/8/15/oxbow-farm-summer-camp"



$description= Invoke-RestMethod -Uri $details -Method Get;

$description


$stuff
