<?php include "header.php" ?>
    <link href="/static/css/app-h5.css" rel="stylesheet">
    <style>
        #faq .faq-cell {
            font-size: 14px;
            border-bottom: solid 1px #f5f5f5;
        }

        #faq .faq-q {
            color: #3a3a3a;
            cursor: pointer;
            background: url("/static/img/arr-down.png") no-repeat right;
            background-size: 14px 8px;
            margin-top: 15px;
            margin-bottom: 15px;
        }

        #faq .faq-q.up {
            background-image: url("/static/img/arr-up.png");
        }

        #faq .faq-a {
            display: none;
            margin-bottom: 15px;
        }
    </style>
    <div class="container container-phone">
        <div class="row">
            <div class="col-xs-12" id="faq">
                <!--账号-->
                <div class="faq-cell">
                    <div class="faq-q">
                        昵称、性别、生日、星座的修改规则是？
                    </div>
                    <div class="faq-a">
                        昵称、性别、生日，每30天可修改一次。星座根据生日自动算出，无法修改。
                    </div>
                </div>
                <div class="faq-cell">
                    <div class="faq-q">
                        提示登陆过期怎么办？
                    </div>
                    <div class="faq-a">
                        ①一般账号长期未登陆会提示登陆过期，重新登陆即可。②账号违法国家相关规定，此时请联系客服。
                    </div>
                </div>
                <div class="faq-cell">
                    <div class="faq-q">
                        如何提升等级？
                    </div>
                    <div class="faq-a">
                        通过每日登录、分享、送礼物，可以获得经验值。充值以及充值后送礼物均能产生大量经验。详细规则查看我的->等级。
                    </div>
                </div>
                <div class="faq-cell">
                    <div class="faq-q">
                        关注达到上限怎么办？
                    </div>
                    <div class="faq-a">
                        目前普通用户最多可以关注200人。 会员可以关注500人。
                    </div>
                </div>
                <div class="faq-cell">
                    <div class="faq-q">
                        违规禁播的规则是什么？
                    </div>
                    <div class="faq-a">
                        响应国家号召，提倡绿色直播，凡头像、昵称、签名、直播标题、内容涉及政治、恐怖暴力及黄赌毒等违规内容直接禁播处理，网警24小时巡查！
                    </div>
                </div>

                <!--直播-->
                <div class="faq-cell">
                    <div class="faq-q">
                        观众人数少怎么办？
                    </div>
                    <div class="faq-a">
                        多直播，与观众混脸熟，人是感情动物。学习其他主播，取长补短，建立自己的直播特色。建立自己的粉丝群，多互动。最后，拉朋友来暖场，朋友永远是最好的第一批观众。
                    </div>
                </div>
                <div class="faq-cell">
                    <div class="faq-q">
                        看不到字幕怎么办？
                    </div>
                    <div class="faq-a">
                        切换网络、重新打开APP试试，网络不好的时候可能会看不到。
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function () {
            var $q;

            $('#faq .faq-q').click(function () {
                if ($q) {
                    $q.next().hide()
                    $q.removeClass('up')
                }

                $q = $(this)
                var $a = $q.next()

                if ($a.is(':hidden')) {
                    $a.show()
                    $q.addClass('up')
                } else {
                    $a.hide()
                    $q.removeClass('up')
                }
            })
        })()
    </script>
<?php include "footer.php" ?>