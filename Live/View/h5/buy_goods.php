<?php include "header.php" ?>
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
                <div>我的等级</div>
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
        <div class="row" id="buy-option">
            <?php
            foreach ($goods as $row) {
                echo <<<HTML
<div class="col-xs-4 text-center" id="{$row['id']}">
    <div class="b">{$row['coin']}看币</div>
    <div>¥ {$row['money']}</div>
</div>
HTML;
            }
            ?>
        </div>
        <div style="margin:0 auto;width: 90%;">
            <?php include 'pay_switch.php' ?>
        </div>

        <div class="row" id="instruction">
            <div class="col-xs-12">
                <div class="b">充值奖励</div>
                <div>每次充值奖励<span class="pink">10倍经验</span><span class="b">（例如充值30元获得300经验）</span></div>
                <div>充值累计满98元，奖励<span class="pink">15天会员</span><a href="/H5/buyVip?<?php echo $query; ?>">查看会员特权></a></div>
                <div>充值累计满298元，奖励<span class="pink">45天会员</span><a href="/H5/buyVip?<?php echo $query; ?>">查看会员特权></a>
                </div>
                <div>充值累计满598元，奖励<span class="pink">90天会员</span><a href="/H5/buyVip?<?php echo $query; ?>">查看会员特权></a>
                </div>
            </div>
        </div>
    </div>
    <script>

        var $active;
        $("#buy-option > div").click(function () {
            if ($active)
                $active.removeClass('active')

            $active = $(this).addClass('active')
        }).slice(0, 1).trigger('click')

        $("#pay a").click(function () {
            var id = $active[0].id

            this.href = "camhowsh://pay/?id=" + id
            return true;
        })

        $("#pay-type-hint").click(function () {
            $("#other-pay").toggle()
        })
    </script>
<?php include "footer.php" ?>