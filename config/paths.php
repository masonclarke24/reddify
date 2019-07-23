<?php

return[
    
    'audio' => './audio',
    'images' => './images',
    'videos' => './video',
    'font' => '/fonts/Raleway-Black.ttf',
    'ffmpeg' => "..".DIRECTORY_SEPARATOR."FFmpeg" .DIRECTORY_SEPARATOR. "ffmpeg.exe",
    'cloudCredentials' => env('GCLOUD_CREDENTIALS')
];