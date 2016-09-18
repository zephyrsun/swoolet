<?php
include "header.php";

list($class, $num, $class_name) = \Live\Lib\Utility::levelClass($user['lv']);

$html = <<<HTML
<span class="class" style="background-image:url(/static/img/class-bg-{$class}.png)">$class_name$num</span>
HTML;


?>
    <link href="/static/css/app-h5.css" rel="stylesheet">

    <div class="container container-phone">
        <div class="row header header-line">
            <div class="col-xs-6 text-center left">
                <img src="/static/img/kb.png" width="28px">
                <div>我的看币</div>
                <div class="b"><?php echo $balance; ?></div>
            </div>
            <div class="col-xs-6 text-center">
                <img src="/static/img/level.png" width="28px">
                <div>我的等级
                    <?php echo $html; ?>
                </div>
                <div class="progress">
                    <div class="progress-bar"
                         style="width: <?php echo (int)($user['exp'] / $user['next_exp'] * 100); ?>%;">
                    </div>
                    <div class="text">
                        <?php echo "{$user['exp']} / {$user['next_exp']}"; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $pay_style = 'margin:0 auto;width: 90%;';
        include 'pay_switch.php';
        ?>

        <div class="row" id="instruction">
            <div class="col-xs-12">
                <div class="b">充值奖励</div>
                <div>每次充值奖励<span class="pink">等额经验</span><span class="b">（例如充值30元获得30经验）</span>，送礼后可再得更多经验。</div>
                <div>充值累计满98元，奖励<span class="pink">15天会员</span><a href="/H5/buyVip?<?php echo $query; ?>">查看会员特权></a>
                </div>
                <div>充值累计满298元，奖励<span class="pink">45天会员</span><a href="/H5/buyVip?<?php echo $query; ?>">查看会员特权></a>
                </div>
                <div>充值累计满598元，奖励<span class="pink">90天会员</span><a href="/H5/buyVip?<?php echo $query; ?>">查看会员特权></a>
                </div>
            </div>
        </div>
    </div>
<?php include "footer.php" ?>