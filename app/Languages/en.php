<?php
/**
 * English language file.
 * IMPORTANT!: Text enclosed in double curly brackets (e.g., {{ITAGS}}) are "placeholders" and should NOT be translated!
 */
return array(
    // Meta Data - (app/Views/helpers)
    'website_title' => 'Tube Media Converter - Very Fast Tube 2 MP3 Converter',
    'website_description' => 'Very Fast Tube to MP3 Converter up to 320 kbps and Video Downloader',
    'website_keywords' => 'Tube, Converter, fast, download, video, audio, music, downloader, MP3, MP4, WEBM, 3GP, FLV, M4A',
    'search_description' => 'Download %s as MP3, MP4, WEBM, M4A, and 3GP on %s',

    // Navbar - (app/Views/elements)
    'navbar_home' => 'Home',
    'navbar_faq' => 'FAQ',
    'navbar_api' => 'API',
    'navbar_contact' => 'Contact',
    'navbar_error 403 - forbidden' => 'Error 403 - Forbidden',
    'navbar_error 404 - not found' => 'Error 404 - Not Found',

    // Header - (app/Templates/xeon/header.php)
    'search_something' => 'Search something you love.',

    // Search Form - (app/Views/elements)
    'searchform_placeholder' => 'Enter a valid YouTube URL or Search Term',
    'searchfrom_info_1' => 'Supported YouTube URLs:',
    'searchfrom_info_2' => 'Just the Youtube VideoID:',
    'searchfrom_info_3' => 'Playlist URLs:',
    'searchfrom_info_4' => 'Search Term:',

    // Result - (app/Views/pages)
    'top' => 'Top',
    'music' => 'Music',
    'videos' => 'Videos',
    'load' => 'Load',
    'more_videos' => 'more videos',
    'no_videos_found' => 'No videos were found! Please, try again.',
    'download' => 'Download',
    'video_button' => 'Video',
    'by' => 'by',
    'on' => 'on',
    'tags' => 'Tags',
    'users' => 'users',
    'load_more_msg' => 'Please wait, loading more videos!',
    'preparing_msg' => 'One Moment please, Preparing your download links!',
    'preparing_msg2' => 'Almost there! Thanks for your patience.',
    'video_streams' => 'Video Streams',
    'video_only' => 'Video only',
    'audio_only' => 'Audio only',
    'audio_play_button' => 'Play Audio',
    'video_preview_button' => 'Preview Video',
    'share_button' => 'Share This',
    'share_modal_header' => 'Share <b>%s</b> with your friends!',
    'share_modal_text' => 'Download',
    'share_modal_close' => 'Close',

    //Grab - (app/Views/pages)
    'time_frame' => 'Time Frame:',
    'start' => 'Start',
    'end' => 'End',
    'highspeed_label' => 'Highspeed Mode',
    'highspeed_text' => 'Highspeed Mode allows you to Download Video and Audio directly from YouTube.<br/ >If the download fails, please uncheck the checkbox and try again.',

    //FAQ - (app/Views/pages)
    'question_1' => 'How can i convert a Video?',
    'question_2' => 'What is the maximum allowed Duration of a Video?',
    'question_3' => 'Which Video Pages are Supported?',
    'answer_1' => 'Enter a Search Term or a YouTube Video / Playlist URL.<br/>Click the File type button you want and the Download begins.',
    'answer_2' => 'There is no limit.',
    'answer_3' => 'Currently we only support YouTube.',

    //API - (app/Views/pages)
    'api_method' => 'Method #',
    'api_json' => 'JSON REST API',
    'api_button' => 'Button/Iframe API',
    'api_search' => 'JSON "Search" REST API',
    'api_request' => 'Send an HTTP request to:',
    'api_src' => 'Set the "src" attribute of an HTML iframe to:',
    'api_where' => '...where ',
    'api_following' => ' is one of the following:',
    'api_for' => 'for ',
    'api_results' => ' results',
    'api_and' => '...and ',
    'api_validId' => ' is any valid YouTube video ID.',
    'api_validSearchTerm' => ' is any valid YouTube search term.',
    'api_json_eg' => 'The API request and corresponding JSON response should look like one of the following, e.g.:',
    'api_button_eg' => 'The resulting iframe code and corresponding output should look like one of the following, e.g.:',
    'api_mp3' => 'MP3',
    'api_videos' => 'Video w/Audio',
    'api_mergedstreams' => 'Merged Video + Audio',
    'api_videostreams' => 'Video-Only',
    'api_audiostreams' => 'Audio-Only',
    'api_generates' => '...generates this:',

    //Download/Conversion Errors - (app/Views/pages)
    'download_urls' => 'Error: Could not generate download URLs. Please, try again.',
    'invalid_itags' => 'Error: Could not generate download URLs for the following itags: {{ITAGS}}. Please, try again.',
    'selected_format' => 'Error: No Format Selected!',
    'no_streams' => 'Error: No Streams found!',
    'blocked_video' => 'Sorry, this video is blocked at the request of the copyright holder.',
    'search_scraping' => 'Error: Could not extract search results!',

    //Index "No-JS" content - (app/Views/pages)
    'nojs_vid_duration' => 'Video Duration:',
    'nojs_vid_uploader' => 'Video uploaded by:',
    'nojs_vid_date' => 'Video release date:',
    'nojs_vid_views' => 'Video views:',
    'nojs_vid_likes' => 'Video likes:',
    'nojs_vid_dislikes' => 'Video dislikes:',

    //Index "Charts-Alt" content - (app/Views/pages)
    'charts_alt_title' => 'Welcome to the Site!',
    'charts_alt_summary' => 'It\'s never been easier to find your favorite YouTube videos, watch/listen to them online, and download/convert them to popular formats like MP3, MP4, and more! Just follow these simple steps:',
    'charts_alt_card1_title' => '1. Search for your favorite YouTube videos',
    'charts_alt_card1_body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
    'charts_alt_card2_title' => '2. Watch or Listen to any videos that you find',
    'charts_alt_card2_body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
    'charts_alt_card3_title' => '3. Download and Convert videos to MP3, MP4, and more',
    'charts_alt_card3_body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'
);
