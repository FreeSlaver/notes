<?php

//$youtubeUrl='hello';
$youtubeUrl = $_['youtubeUrl'];
echo  $youtubeUrl;

$command = 'youtube-dl --proxy socks5://localhost:1080/ -F '+$youtubeUrl;
exec($command, $out, $status);
echo implode(" ",$out);
echo implode(" ",$status);
?>