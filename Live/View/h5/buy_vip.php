<?php include "header.php" ?>
    <link href="/static/css/app-h5.css" rel="stylesheet">
    <style>
        .h3 {
            font-size: 18px;
            color: #efbf38;
            font-weight: bold;
            padding-top: 15px;
            padding-bottom: 15px;
        }

        .h1 {
            font-size: 26px;
            color: #efbf38;
            font-weight: bold;
            font-style: italic;
            margin-right: 10px;
        }

        .intro {
            font-size: 14px;
            color: #3a3a3a;
        }

        .intro .g {
            color: #8e8e8e;
        }


    </style>
    <div class="container container-phone">
        <div class="row header">
            <div class="col-xs-12 text-center">
                <img src="/static/img/vip_card.png" width="338px">
            </div>
        </div>
        <div style="margin:0 auto;width: 150px;">
            <?php include 'pay_switch.php' ?>
        </div>
        <div class="row gap"></div>
        <div class="row intro">
            <div class="col-xs-12">
                <div class="h3">会员特权</div>
                <div>
                    <span class="h1">1</span>会员每日签到可获得5看币和20经验，<span class="g">(普通用户没有看币，只有10经验)</span>。另外，会员每逢单数日签到可额外获得最高100经验。
                </div>
            </div>
        </div>
    </div>
<?php include "footer.php" ?>