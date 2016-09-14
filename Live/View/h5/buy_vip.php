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
            padding-bottom: 20px;
            line-height: 22px;
        }

        .h1 {
            font-size: 26px;
            color: #efbf38;
            font-weight: bold;
            font-style: italic;
            margin-right: 10px;
        }

        .intro {
            font-size: 12px;
            color: #3a3a3a;
        }

        .intro .g {
            color: #8e8e8e;
        }

        #vip-card {
            color: #fff;
            width: 250px;
            margin-left: auto;
            margin-right: auto;
        }

        #vip-card .card-top {
            width: 250px;
            position: absolute;
            top: 45px;
        }

        #vip-card .card-top .b {
            color: inherit;
            font-size: 20px;
        }

        #vip-card .card-bottom {
            width: 250px;
            position: absolute;
            bottom: 14px;
            padding-right: 14px;
            color: #fff;
            font-size: 9px;
            text-align: right;
        }

    </style>
    <div class="container container-phone">
        <div class="row header">
            <div class="col-xs-12 text-center">
                <div id="vip-card">
                    <img src="/static/img/vip_card.png" width="100%">
                    <div class="card-top">
                        <div class="b">看好会员年卡</div>
                    </div>
                    <div class="card-bottom">No. <?php echo $uid; ?></div>
                </div>
            </div>
        </div>
        <?php
        $option_style = 'display:none';
        $pay_style = 'margin:0 auto;width: 150px;';
        include 'pay_switch.php';
        ?>
        <div class="row text-center" style="margin-bottom: 20px">
            <div class="b">现价：¥98.00，日均：¥0.27，省262</div>
            <div>原价：¥360.00，日均：¥0.99</div>
        </div>
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
                    <span class="h1">3</span>可自定义主页背景图，彰显个性，<span class="g">普通用户没有</span>。
                </div>
                <div>
                    <span class="h1">4</span>绚丽进房特效，会员专属特权，<span class="g">普通用户没有</span>。
                </div>
                <div>
                    <span class="h1">5</span>尊贵会员身份标志，<span class="g">普通用户没有</span>。
                </div>
                <div>
                    <span class="h1">6</span>会员可参与主播线下活动，参与自制节目现场录制，<span class="g">普通用户没有</span>。
                </div>
            </div>
        </div>
    </div>
<?php include "footer.php" ?>