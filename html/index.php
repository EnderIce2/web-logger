<!DOCTYPE html>
<?php
// PHP Code

// Configuration
$file = 'ip.txt';
$requests_file = 'url.txt';
$owner_ip = "192.168.0.1";

function GetIPAddress()
{
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $result = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $result = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $result = $_SERVER['REMOTE_ADDR'];
            }
        }
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR")) {
            $result = getenv("HTTP_X_FORWARDED_FOR");
        } else {
            if (getenv("HTTP_CLIENT_IP")) {
                $result = getenv("HTTP_CLIENT_IP");
            } else {
                $result = getenv("REMOTE_ADDR");
            }
        }
    }
    return $result;
}

$user_agent     =   $_SERVER['HTTP_USER_AGENT'];

function GetWebBrowser()
{
    global $user_agent;
    $browsers  =   array(
        '/msie/i'       =>  'Internet Explorer',
        '/Firefox/i'    =>  'Firefox', // not working
        '/Mozilla/i'    =>  'Mozilla',
        '/Mozilla/5.0/i' => 'Mozilla',
        '/safari/i'     =>  'Safari',
        '/chrome/i'     =>  'Chrome',
        '/edge/i'       =>  'Edge',
        '/opera/i'      =>  'Opera',
        '/OPR/i'        =>  'Opera',
        '/netscape/i'   =>  'Netscape',
        '/maxthon/i'    =>  'Maxthon',
        '/konqueror/i'  =>  'Konqueror',
        '/Bot/i'        =>    'BOT Browser',
        '/Valve Steam GameOverlay/i'  =>  'Steam',
        '/mobile/i'     =>  'Handheld Browser'
    );
    $result        =   "Unknown";
    foreach ($browsers as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $result    =   $value;
        }
    }
    return $result;
}

function GetOperatingSystem()
{
    global $user_agent;
    $operating_systems       =   array(
        '/windows nt 10/i'     =>  'Windows 10',
        '/windows nt 6.3/i'     =>  'Windows 8.1',
        '/windows nt 6.2/i'     =>  'Windows 8',
        '/windows nt 6.1/i'     =>  'Windows 7',
        '/windows nt 6.0/i'     =>  'Windows Vista',
        '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
        '/windows nt 5.1/i'     =>  'Windows XP',
        '/windows xp/i'         =>  'Windows XP',
        '/windows nt 5.0/i'     =>  'Windows 2000',
        '/windows me/i'         =>  'Windows ME',
        '/win98/i'              =>  'Windows 98',
        '/win95/i'              =>  'Windows 95',
        '/win16/i'              =>  'Windows 3.11',
        '/macintosh|mac os x/i' =>  'Mac OS X',
        '/mac_powerpc/i'        =>  'Mac OS 9',
        '/linux/i'              =>  'Linux',
        '/kalilinux/i'          =>  'KaliLinux',
        '/ubuntu/i'             =>  'Ubuntu',
        '/iphone/i'             =>  'iPhone',
        '/ipod/i'               =>  'iPod',
        '/ipad/i'               =>  'iPad',
        '/android/i'            =>  'Android',
        '/blackberry/i'         =>  'BlackBerry',
        '/webos/i'              =>  'Mobile',
        '/Windows Phone/i'      =>  'Windows Phone'
    );
    $result    =   "Unknown";
    foreach ($operating_systems as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $result    =   $value;
        }
    }
    return $result;
}

$user_os        = GetOperatingSystem();
$ip             = GetIPAddress();
$user_browser   = GetWebBrowser();

$site_referer = $_SERVER['HTTP_REFERER'];
if ($site_referer == "") {
    $site = "direct";
} else {
    $site = $site_referer;
}

if ($ip == $owner_ip) {
    $ip = "Owner";
    $country = "Home";
    $city = "Home";
} else {
    $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}"));
    $country = $details->country;
    $city = $details->city;
}
$url = "$_SERVER[REQUEST_URI]";

$current = file_get_contents($file);
$current .= "\n\n====" . date("Y-m-d - H:i:s") . "====";
$current .= "\nIP: " . $ip;
$current .= "\nCountry: " . $country;
$current .= "\nCity: " . $city;
$current .= "\nOS: " . $user_os;
$current .= "\nBrowser: " . $user_browser;
$current .= "\nWeb Referer: " . $site;
$current .= "\nURL request: " . $url;
$current .= "\nUser Agent: " . $user_agent;
$current .= "\n=============================";
file_put_contents($file, $current);

if ($url != "/") {
    $current_url_req = file_get_contents($requests_file);
    $current_url_req .= $url . "\n";
    file_put_contents($requests_file, $current_url_req);

    // For now this should do the trick, but in the future maybe I will make something more efficient
    $lines = file($requests_file);
    $lines = array_unique($lines);
    file_put_contents($requests_file, implode($lines));
}
header("HTTP/1.0 403 Forbidden"); // Error code
// HTML Code
?>
<html>

<head>
	<title>403 Forbidden</title>
</head>

<body>
	<h1>Forbidden</h1>
	<p>You don't have permission to access this resource.</p>
</body>

</html>
