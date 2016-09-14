<?php include "header.php" ?>
    <link href="/static/css/app-h5.css" rel="stylesheet">
    <style>

        body {
            font-size: 14px !important;
            background-color: #f5f5f5;
        }

        .container {
            background-color: #fff;
        }

        #result {
            padding-top: 100px;
            padding-bottom: 60px;
        }

        #result .b {
            color: #f33781;
        }

        #result img {
            padding-bottom: 25px;
        }

        #value {
            color: #3a3a3a;
            padding-top: 20px;
            padding-bottom: 20px;
            width: 212px;
            margin-left: auto;
            margin-right: auto;
        }

        #value .b {
            font-size: 18px;
            margin-right: 12px;
        }

        #value .g {
            color: #8e8e8e;
        }

        .wave {
            background: url("/static/img/wave.png") repeat-x;
            background-size: 14px 6px;
            content: " ";
            line-height: 1;
        }

    </style>
    <div class="container container-phone">
        <div class="row" id="result">
            <div class="col-xs-12 text-center">
                <?php
                if ($goods['type'] == 2) {
echo <<<HTML
<img src="/static/img/vip_success.png" width="93px">
<div>恭喜您成为<span class="b">尊贵会员</span>！</div>
HTML;
                } else {
                    echo <<<HTML
<img src="/static/img/tick.png" width="49px">
<div>成功付款 <span class="b">{$goods['money']}</span> 元</div>
HTML;

                }

                ?>

            </div>
        </div>
        <div style="border-bottom: dotted 1px #c3c2c2;">
        </div>
        <div id="value">
            <div style="<?php if (!$goods['coin']) echo "display:none"; ?>">
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <div class="pull-left">看币</div>
                        <div class="pull-right">
                            <span class="b">+ <?php echo $goods['coin']; ?></span>
                            <span class="g">总共 <?php echo (int)$coin; ?> 看币</span>
                        </div>
                    </div>
                </div>
                <div style="border-bottom: dotted 1px #e4e4e4;margin-top: 10px;margin-bottom: 10px">
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 text-center">
                    <div class="pull-left">经验</div>
                    <div class="pull-right">
                        <span class="b">+ <?php echo $goods['exp']; ?></span>
                        <span class="g">总共 <?php echo $exp; ?> 经验</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container container-phone wave">
        &nbsp;
    </div>
<?php include "footer.php" ?>