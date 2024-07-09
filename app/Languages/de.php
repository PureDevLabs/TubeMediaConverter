<?php
/**
 * German language file.
 * IMPORTANT!: Text enclosed in double curly brackets (e.g., {{ITAGS}}) are "placeholders" and should NOT be translated!
 */
return array(
    // Meta Data - (app/Views/helpers)
    'website_title' => 'Tube Media Converter - Der wirklich schnellste Tube 2 MP3 Converter',
    'website_description' => 'Der wirklich schnellste Tube Video Downloader und MP3 Converter bis zu 320 kbit/s',
    'website_keywords' => 'Tube, Converter, schnell, fast, download, video, audio, music, musik, downloader, MP3, MP4, WEBM, 3GP, FLV, M4A',
    'search_description' => 'Download %s als MP3, MP4, WEBM, M4A, und 3GP von %s',

    // Navbar - (app/Views/elements)
    'navbar_home' => 'Home',
    'navbar_faq' => 'FAQ',
    'navbar_api' => 'API',
    'navbar_contact' => 'Kontakt',

    // Header - (app/Templates/xeon)
    'search_something' => 'Suche etwas das du magst.',

    // Searchform - (app/Views/elements)
    'searchform_placeholder' => 'YouTube URL oder Suchbegriff hier eingeben.',
    'searchfrom_info_1' => 'Unterstützte YouTube URLs:',
    'searchfrom_info_2' => 'Nur die Youtube VideoID:',
    'searchfrom_info_3' => 'Playlist URLs:',
    'searchfrom_info_4' => 'Suchbegriff:',

    // Result - (app/Views/pages)
    'top' => 'Top',
    'music' => 'Musik',
    'videos' => 'Videos',
    'load' => 'Lade',
    'more_videos' => 'weitere Videos',
    'no_videos_found' => 'Keine Videos gefunden! Bitte versuche es erneut.',
    'download' => 'Download',
    'video_button' => 'Video',
    'by' => 'von',
    'on' => 'am',
    'tags' => 'Tags',
    'users' => 'Benutzer',
    'load_more_msg' => 'Bitte warten, lade mehr Videos!',
    'preparing_msg' => 'Einen Moment bitte, Download links werden vorbereitet!',
    'preparing_msg2' => 'Fast geschafft. Danke für deine Geduld!',
    'video_streams' => 'Video Streams',
    'video_only' => 'Nur Video',
    'audio_only' => 'Nur Audio',

    //Grab - (app/Views/pages)
    'time_frame' => 'Zeitspanne:',
    'start' => 'Start',
    'end' => 'Ende',
    'highspeed_label' => 'Highspeed Mode',
    'highspeed_text' => 'Highspeed Mode erlaubt dir die Video oder Audio Datei direkt von YouTube runterzuladen.<br/ >Wenn der download fehlschlägt, bitte checkbox deaktivieren und erneut versuchen.',

    //FAQ - (app/Views/pages)
    'question_1' => 'Wie kann ich ein Video umwandeln?',
    'question_2' => 'Was ist die maximal erlaube Videolänge?',
    'question_3' => 'Welche Videoportale werden unterstützt?',
    'answer_1' => 'Gebe ein Suchbegriff oder eine YouTube Video / Playlist URL ein.<br/>Klicke auf den Dateityp den du möchtest und der Download beginnt.',
    'answer_2' => 'Es gibt kein Limit.',
    'answer_3' => 'Zur Zeit unterstützen wir nur YouTube.',

    //API - (app/Views/pages)
    'api_method' => 'Methode #',
    'api_json' => 'JSON REST API',
    'api_button' => 'Button/Iframe API',
    'api_search' => 'JSON "Search" REST API',    
    'api_request' => 'Sende eine HTTP anfrage an:',
    'api_src' => 'Setze das "src" Attribut eines HTML iframe zu:',
    'api_where' => '...wobei',
    'api_following' => ' duch eines der folgenden Parameter ersetzt wird:',
    'api_for' => 'für ',
    'api_results' => ' ergebnisse',
    'api_and' => '...und ',
    'api_validId' => ' durch eine gültige YouTube-Video-ID.',
    'api_validSearchTerm' => ' ein gültiger YouTube-Suchbegriff ist.',
    'api_json_eg' => 'Die API Anfrage und die entsprechende JSON-Antwort sollten wie folgt aussehen:',
    'api_button_eg' => 'Der daraus resultierende Iframe-Code und die entsprechende Ausgabe sollten wie folgt aussehen:',
    'api_mp3' => 'MP3',
    'api_videos' => 'Video mit Audio',
    'api_mergedstreams' => 'Zusammengeführtes Video + Audio',
    'api_videostreams' => 'Nur Video',
    'api_audiostreams' => 'Nur Audio',
    'api_generates' => '...Erzeugt dies:',

	//Download/Conversion Errors - (app/Views/pages)
    'download_urls' => 'Fehler: Konnte keine Download URLs generieren. Bitte versuche es erneut.',
    'invalid_itags' => 'Fehler: Download-URLs konnte für folgende itags nicht generiert werden: {{ITAGS}}. Bitte versuche es erneut.',
    'selected_format' => 'Fehler: Kein Format ausgewählt!',
    'no_streams' => 'Fehler: keine Streams gefunden!',
    'blocked_video' => 'Sorry, dieses Video ist auf Wunsch des Urheberrechtinhabers gesperrt.',
    'search_scraping' => 'Fehler: Konnte keine Suchergebnisse extrahieren.',

    //Index "No-JS" content - (app/Views/pages)
    'nojs_vid_duration' => 'Video Laufzeit:',
    'nojs_vid_uploader' => 'Video hochgeladen von:',
    'nojs_vid_date' => 'Video veröffentlicht am:',
    'nojs_vid_views' => 'Video Aufrufe:',
    'nojs_vid_likes' => 'Video likes:',
    'nojs_vid_dislikes' => 'Video dislikes:',

    //Index "Charts-Alt" content - (app/Views/pages)
    'charts_alt_title' => 'Willkommen auf der Seite!',
    'charts_alt_summary' => 'Es war noch nie einfacher, Ihre Lieblings-YouTube-Videos zu finden, sie online anzusehen/anzuhören und sie in beliebte Formate wie MP3, MP4 und mehr herunterzuladen/konvertieren! Befolgen Sie einfach diese einfachen Schritte:',
    'charts_alt_card1_title' => '1. Suchen Sie nach Ihren Lieblings-YouTube-Videos',
    'charts_alt_card1_body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
    'charts_alt_card2_title' => '2. Sehen oder hören Sie sich die gefundenen Videos an',
    'charts_alt_card2_body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
    'charts_alt_card3_title' => '3. Videos herunterladen und in MP3, MP4 und mehr konvertieren',
    'charts_alt_card3_body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'    
);
