
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