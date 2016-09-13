<div class="row" id="buy-option" style="<?php echo $option_style; ?>">
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
<div style="<?php echo $pay_style; ?>">
    <div id="pay">
        <?php
        if ($pf == 'ios') {
            echo <<<HTML
<div class="row text-center">
    <a class="btn btn-pay" href="">立即购买</a>
</div>
HTML;
        } else {
            echo <<<HTML
<div class="row text-center">
    <a class="btn btn-alipay" href="">支付宝购买</a>
</div>
<div class="row text-center" id="pay-type-hint">
    其他支付方式
</div>
<div class="row text-center" id="other-pay" style="display:none;">
    <a class="btn btn-pay btn-wxpay" href="">微信购买</a>
</div>
HTML;
        }
        ?>
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