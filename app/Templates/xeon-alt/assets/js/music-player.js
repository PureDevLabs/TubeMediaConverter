var iframeApiLoading = true,
musicPlayer = null,
musicPlayerTimer,
videoInfo,
time_update_interval = 0;

function loadIframeAPI()
{
    $("#audio-player").fadeIn(1200);
    var ytapi = document.createElement('script');
    ytapi.src = "https://www.youtube.com/iframe_api";
    var ytapiScript = document.getElementsByTagName('script')[0];
    ytapiScript.parentNode.insertBefore(ytapi, ytapiScript);
}

function onYouTubeIframeAPIReady(vidId) 
{
    musicPlayer = new YT.Player('video', {
        width: 390,
        height: 640,
        videoId: videoInfo.videoID,
        events: {
            'onReady': startPlayer,
            'onStateChange': playerStateChange,
            'onError': showError
        }
    });
}

function playerStateChange(e)
{
    //console.log("player state change: " + e.data);
    if (e.data === YT.PlayerState.ENDED)
    {
        $("#pause").hide();
        $("#play").show(); 
    }
}

function showError(e)
{
    //console.log("player error: " + e.data);
    $('#playerError').show();
    $('#musicControls').hide();
}

function startPlayer()
{
    $('#playerError').hide();
    $('#musicControls').show();
    updateTimerDisplay();
    updateProgressBar();
    musicPlayer.setVolume(50);
    clearInterval(time_update_interval);
    time_update_interval = setInterval(function(){
        updateTimerDisplay();
        updateProgressBar();
    }, 1000);
    $('#volume-input').val(50);
    loadIframeVideo();
}

function updateTimerDisplay()
{
    $('#current-time').text(formatTime(musicPlayer.getCurrentTime()));
    $('#duration').text(formatTime(musicPlayer.getDuration()));
}

function updateProgressBar()
{
    $('#progress-bar').val((musicPlayer.getCurrentTime() / musicPlayer.getDuration()) * 100);
}

function formatTime(time)
{
    time = Math.round(time);
    var minutes = Math.floor(time / 60),
        seconds = time - minutes * 60;
    seconds = (seconds < 10) ? '0' + seconds : seconds;
    return minutes + ":" + seconds;
}

function loadIframeVideo()
{
    musicPlayer.cueVideoById(videoInfo.videoID);
    $isVisible = $('#audio-player').css('display') != "none";
    if (!$isVisible)
    {
        $("#audio-player").fadeIn("slow", function(){
            musicPlayer.playVideo();
            $("#play").hide();
            $("#pause").show();
            $("#playerError").hide();
            $("#musicControls").show();
            $("#videoTitle").html(videoInfo.videoTitle);
        });
    }
    else
    {
        var checkPlayer = function(){
            //console.log(musicPlayer.getPlayerState());
            if (musicPlayer.getPlayerState() == 5) 
            {
                if (!iframeApiLoading) clearInterval(musicPlayerTimer);
                musicPlayer.playVideo();
                $("#play").hide();
                $("#pause").show();
                $("#playerError").hide();
                $("#musicControls").show();
                $("#videoTitle").html(videoInfo.videoTitle);
            }
            if (iframeApiLoading && musicPlayer.getPlayerState() == 1) 
            {
                clearInterval(musicPlayerTimer);
                iframeApiLoading = false;
            } 
        };
        musicPlayerTimer = setInterval(checkPlayer, 100);
    }
}

$(document).on("click", ".playMusic", function(e)
{
    var getVideoID = $(this).closest('[data-video-id]');
    videoInfo = {"videoID": getVideoID.attr('data-video-id'), "videoTitle": getVideoID.attr('data-video-title')};
    if (musicPlayer == null) 
    {
        loadIframeAPI();
    }
    else
    {
        loadIframeVideo();
    }
});

$(document).ready(function(){
    $('#playerError').hide(), $('#pause').hide(), $('#audio-player').hide(), $('#musicControls').hide();
    $('#progress-bar').on('mouseup touchend', function(e){
        var newTime = musicPlayer.getDuration() * (e.target.value / 100);
        musicPlayer.seekTo(newTime);
    });
    $('#play').on('click', function(){
        musicPlayer.playVideo();
        $('#play').hide();
        $('#pause').show();
    });
    $('#pause').on('click', function(){
        musicPlayer.pauseVideo();
        $('#pause').hide();
        $('#play').show();
    });
    $('#mute-toggle').on('click', function(){
        var mute_toggle = $(this);
        if (musicPlayer.isMuted())
        {
            musicPlayer.unMute();
            mute_toggle.text('volume_up');
        }
        else
        {
            musicPlayer.mute();
            mute_toggle.text('volume_off');
        }
    });
    $('#volume-input').on('change', function(){
        musicPlayer.setVolume($(this).val());
    });
    $(".close").click(function(){
        $('#audio-player').fadeOut('slow');
        musicPlayer.pauseVideo();
    });
});