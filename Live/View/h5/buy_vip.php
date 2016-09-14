<?php include "header.php" ?>
    <link href="/static/css/app-h5.css" rel="stylesheet">
    <style>
        .h3 {
            font-size: 18px;
            color: #efbf38;
            font-weight: bold;
            padding-top: 15px;
        }

        .intro > div > div {
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
        <?php
        $option_style = 'display:none';
        $pay_style = 'margin:0 auto;width: 150px;';
        include 'pay_switch.php';
        ?>
        <div class="row gap"></div>
        <div class="row intro">
            <div class="col-xs-12">
                <div class="h3">会员特权</div>
                <div>
                    <span class="h1">1</span>会员每日签到可获得5看币和20经验，<span class="g">普通用户0看币，10经验</span>。会员每逢双数日签到可额外获得成长经验，最高5倍。
                </div>
                <div>
                    <span class="h1">2</span>可免费无限私信主播，<span class="g">普通用户每条私信需要20看币</span>。
                </div>
                <div>
                    <span class="h1">3</span>绚丽进房特效，会员专属特权。
                </div>
                <div>
                    <span class="h1">4</span>尊贵会员身份标志，与众不同。
                </div>
                <div>
                    <span class="h1">5</span>会员可参与主播线下活动，参与自制节目现场录制。
                </div>
            </div>
        </div>
    </div>
<?php include "footer.php" ?>