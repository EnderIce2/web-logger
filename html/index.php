<!DOCTYPE html>
<?php
// PHP Code

// Configuration (leave empty if you want to disable a feature)

/*
 local file for ip grabber file
e.g. https://i.imgur.com/JpM6hUf.png
*/
$file = "";

/*
 requests file
e.g. https://i.imgur.com/sqlUZwR.png
*/
$requests_file = "";

/*
 your ip (it will be shown as Owner and location Home)
e.g. https://i.imgur.com/cdIQEWf.png
*/
$owner_ip = "";

/*
 Webhook if you prefer discord webhooks instead.
e.g. https://i.imgur.com/b1B9Ins.png
*/
$discord_webhook = "";

/*
 If you want to use discord webhook for logging file/folder requests.
e.g. https://i.imgur.com/YJ44F8j.png
*/
$discord_request_file_webhook = "";

function GetIPAddress()
{
    if (null !== filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_SANITIZE_STRING)) {
        $result = filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_SANITIZE_STRING);
    } else {
        if (null !== filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_SANITIZE_STRING)) {
            $result = filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_SANITIZE_STRING);
        } else {
            $result = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_STRING);
        }
    }
    return $result;
}

$user_agent     =   filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING);

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

$site_referer = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_STRING);
if ($site_referer == "") {
    $site = "direct";
} else {
    $site = $site_referer;
}

if ($ip == $owner_ip) {
    $ip = "Owner";
    $country = "Home";
    $city = "Home";
    $region = "Home";
    $org = "My router";
} else {
    $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}"));
    $country = $details->country;
    $city = $details->city;
    $region = $details->region;
    $org = $details->org;
}
$url = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING);

if ($file != "") {
    $current = file_get_contents($file);
    $current .= "\n\n====" . date("Y-m-d - H:i:s") . "====";
    $current .= "\nIP: " . $ip;
    $current .= "\nLocation: " . $country . ", " . $region . ", " . $city;
    $current .= "\nOrganization: " . $org;
    $current .= "\nOS: " . $user_os;
    $current .= "\nBrowser: " . $user_browser;
    $current .= "\nWeb Referer: " . $site;
    $current .= "\nURL request: " . $url;
    $current .= "\nUser Agent: " . $user_agent;
    $current .= "\n=============================";
    file_put_contents($file, $current);
}

if ($url != "/" && $requests_file != "") {
    $current_url_req = file_get_contents($requests_file);
    $current_url_req .= $url . "\n";
    file_put_contents($requests_file, $current_url_req);

    // For now this should do the trick, but in the future maybe I will make something more efficient
    $lines = file($requests_file);
    $lines = array_unique($lines);
    file_put_contents($requests_file, implode($lines));
}

$timestamp = date("c", strtotime("now"));

if ($discord_webhook != "") {
    // You can uncomment if you need to customize the embed
    $discord_embed_json = json_encode([
        // "content" => "",
        "username" => "IP Logger",
        "tts" => false,
        "embeds" => [
            [
                "title" => "IP Log",
                "type" => "rich",
                "description" => "Received log from " . $ip,
                "url" => "https://github.com/EnderIce2/web-logger",
                "timestamp" => $timestamp,
                "color" => hexdec("330075"),
                // "footer" => [
                //     "text" => ""
                //     "icon_url" => ""
                // ],
                // "thumbnail" => [
                //    "url" => ""
                // ],

                "fields" => [
                    [
                        "name" => "IP",
                        "value" => $ip,
                        "inline" => false
                    ],
                    [
                        "name" => "Location",
                        "value" => ":flag_" . strtolower($country) . ": " . $country . ", " . $region . ", " . $city,
                        "inline" => false
                    ],
                    [
                        "name" => "Organization",
                        "value" => $org,
                        "inline" => false
                    ],
                    [
                        "name" => "Operating System",
                        "value" => $user_os,
                        "inline" => false
                    ],
                    [
                        "name" => "Web Browser",
                        "value" => $user_browser,
                        "inline" => false
                    ],
                    [
                        "name" => "Web Refer",
                        "value" => $site,
                        "inline" => false
                    ],
                    [
                        "name" => "URL Request",
                        "value" => $url,
                        "inline" => false
                    ],
                    [
                        "name" => "User Agent",
                        "value" => $user_agent,
                        "inline" => false
                    ]
                ]
            ]
        ]

    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    // chrome will send two requests; one for favicon and another one for the webpage
    if ($url != "/favicon.ico") {
        $curl_webhook = curl_init($discord_webhook);
        curl_setopt($curl_webhook, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($curl_webhook, CURLOPT_POST, 1);
        curl_setopt($curl_webhook, CURLOPT_POSTFIELDS, $discord_embed_json);
        curl_setopt($curl_webhook, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl_webhook, CURLOPT_HEADER, 0);
        curl_setopt($curl_webhook, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl_webhook);
        // echo $response; // debugging
        curl_close($curl_webhook);
    }
}

if ($discord_request_file_webhook != "" && $url != "/") {
    $request_discord_embed_json = json_encode([
        "content" => $url,
        "username" => "IP URL Request Logger",
        "tts" => false,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    // chrome will send two requests; one for favicon and another one for the webpage
    if ($url != "/favicon.ico") {
        $request_curl_webhook = curl_init($discord_request_file_webhook);
        curl_setopt($request_curl_webhook, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($request_curl_webhook, CURLOPT_POST, 1);
        curl_setopt($request_curl_webhook, CURLOPT_POSTFIELDS, $request_discord_embed_json);
        curl_setopt($request_curl_webhook, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($request_curl_webhook, CURLOPT_HEADER, 0);
        curl_setopt($request_curl_webhook, CURLOPT_RETURNTRANSFER, 1);
        $request_response = curl_exec($request_curl_webhook);
        // echo $request_response; // debugging
        curl_close($request_curl_webhook);
    }
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
