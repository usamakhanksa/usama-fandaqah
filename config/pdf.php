<?php

return [
	'mode'                  => 'utf-8',
	'format'                => 'A4',
	'author'                => '',
	'subject'               => '',
	'keywords'              => '',
	'creator'               => 'Fandaqah App',
	'display_mode'          => 'fullpage',
	'tempDir'               => base_path('/storage/app/'),
//    'font_path' => storage_path('pdf'),
    'font_path' => base_path('resources/fonts/'),
    'font_data' => [
        "xbriyaz" => array(
            'R' => "XB Riyaz.ttf",
            'B' => "XB RiyazBd.ttf",
            'I' => "XB RiyazIt.ttf",
            'BI' => "XB RiyazBdIt.ttf",
            'useOTL' => 0xFF,
            'useKashida' => 75,
        ),

    ]
];
