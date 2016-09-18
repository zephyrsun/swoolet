<?php include "header.php" ?>
    <link href="/static/css/app-h5.css" rel="stylesheet">
    <style>
        #logo {
            padding-top: 100px;
        }

        #version {
            color: #3a3a3a;
            font-size: 15px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        #team {
            color: #c3c3c3;
            text-align: center;
            font-size: 10px;
            height: 140px;
        }

        #team .person {
            color: #3a3a3a;
            margin-bottom: 10px;
            font-size: 12px;
        }

    </style>
    <div class="container container-phone">
        <div class="row text-center">
            <div id="logo">
                <img src="/static/img/logo.png" width="98px">
            </div>
            <div id="version">
                版本号：<?php echo $v; ?>
            </div>
            <div>
                <marquee id="team" direction="up" scrollamount="2">
                    <div>产品 & 后端 & H5</div>
                    <div class="person">孙政华</div>
                    <div>iOS</div>
                    <div class="person">宋乃银&nbsp;&nbsp;&nbsp;&nbsp;李静莹</div>
                    <div>Android</div>
                    <div class="person">崔国</div>
                    <div>测试</div>
                    <div class="person">吉翔</div>
                    <div>UI</div>
                    <div class="person">祝玉婉</div>
                    <div>联系邮箱</div>
                    <div class="person"><a href="mailto:sunzhenghua@camhow.live">sunzhenghua@camhow.live</a></div>
                </marquee>
            </div>
        </div>
    </div>
<?php include "footer.php" ?>