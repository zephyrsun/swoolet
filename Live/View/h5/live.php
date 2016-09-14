<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $video['title']; ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
    <link href="/static/css/main.css" rel="stylesheet">
    <link href="//cdn.bootcss.com/plyr/2.0.7/plyr.css" rel="stylesheet">
</head>
<style>
    body {
        color: #3a3a3a;
        background-color: #000;
    }

    footer {
        height: 50px;
        position: fixed;
        bottom: 0;
        background-color: #fff;
        border-bottom: 1px solid #cfcfcf;
    }

    footer .avatar {
        width: 34px;
        border-radius: 17px;
        margin-top: 8px;
        margin-right: 10px;
    }

    footer .title {
        color: #8e8e8e;
        font-size: 10px;
    }

    footer .nickname {
        margin-top: 10px;
        font-size: 12px;
    }

    footer > div {
        float: left;
    }

    #follow {
        padding-top: 9px;
        float: right !important;
    }

    #follow a {
        color: #f33781;
        border: solid 1px #f33781;
        border-radius: 16px;
        font-size: 12px;
        padding: 6px 22px;
    }

    video {
        width: 100%;
        box-sizing: content-box;
        display: block;
        min-height: 0px;
        transition: opacity 0.25s ease;
    }

    #btn-play {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        -webkit-transform: translate(-50%, -50%);
        width: 55px;
        z-index: 10;
    }

    #top {
        background-color: #000;
        opacity: 0.8;
        padding: 6px;
        color: #fff;
        position: absolute;
        width: 100%;
        left: 0;
        top: 0;
        text-align: right;
    }

    #top img {
        width: 16px;
    }

    #top a {
        padding: 0px 6px;
    }
</style>
<body>
<div id="top">
    <a href=""><img src="/static/img/logo-s.png"></a>
</div>

<img id="btn-play" src="/static/img/live_play.png">
<video id="player" x-webkit-airplay="allow" crossorigin webkit-playsinline
       preload="<?php echo $video['cover']; ?>"
       src="http://content.jwplatform.com/manifests/vM7nH0Kl.m3u8">
</video>
<script src="//cdn.bootcss.com/plyr/2.0.7/plyr.js"></script>
<script src="//cdn.bootcss.com/hls.js/0.6.2-6/hls.min.js"></script>
<script src="//cdn.bootcss.com/jquery/3.1.0/jquery.min.js"></script>
<script>
    (function () {
        var $video = $('#player')

        $video.height($(window).height())

        var video = $video[0]
        if (Hls.isSupported()) {
            var hls = new Hls()
            hls.loadSource(video.src)
            hls.attachMedia(video)
//            hls.on(Hls.Events.MANIFEST_PARSED, function () {
//                video.play()
//            })
        }

        plyr.setup(video)

        var $play = $('#btn-play').click(function () {
            video.play()
            $(this).hide()
        })
    })()
</script>
<footer class="container container-phone">
    <div>
        <img class="avatar" src="<?php echo $user['avatar']; ?>">
    </div>
    <div>
        <div class="nickname"><?php echo $user['nickname']; ?></div>
        <div class="title">主播号：<?php echo $user['uid']; ?></div>
    </div>
    <div id="follow">
        <a class="btn" href="">下载看好直播</a>
    </div>
</footer>

</body>
</html>