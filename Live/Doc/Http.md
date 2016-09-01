#常量说明

#服务器接口
开发环境地址:
> http://192.168.0.85:8080

测试环境地址:
> http://test.camhow.com.cn/


##登录

###发送验证码
```
接口:
/Login/sendSms

参数:
mobile //手机号码

返回:
{"code":"123456","c":0}

```

###手机登录
```
接口:
/Login/mobile

参数:
mobile //手机号码
code //验证码
city //城市

返回:
{"uid":2,"avatar":"","birthday":"0000-00-00","full":false,"token":"xxxxx","c":0}

user 用户对象
full true直接进入直播间,false需要更新用户资料,进入更新资料的界面
```

###获取用户信息
```
接口:
/User/getUserInfo

参数:
token 
uid //查看谁

返回:
{"uid":2,"nickname":"15921258182","height":0,"birthday":"0000-00-00","sign":"","avatar":"","lv":0,"income":"50","sent":"50","follow":1,"fan":0,"c":0}
```

###增加房管
```
接口:
/RoomAdmin/add

参数:
token 
uid //增加谁

返回:
无

房间消息(t值参考`房间消息类型`):
{"t":6,"user":{"uid":"10001","nickname":"nickname10001"},"msg":":nickname被任命为管理员"}

{nickname}需要被user.nickname替换
```

###删除房管
```
接口:
/RoomAdmin/del

参数:
token 
uid //删除谁

返回:
无
```

###禁言
```
接口:
/RoomAdmin/silenceUser

参数:
token 
room_id //房间号,如果是主播传主播uid
uid //禁言谁

返回:
无
```

###取消禁言
```
接口:
/RoomAdmin/stopSilenceUser

参数:
token 
room_id //房间号,如果是主播传主播uid
uid //禁言谁

返回:
无
```

###上传头像
```
接口:
/Upload/avatar

参数:
token 
表单图片流

返回:
{"img":"http:\/\/obs24956g.bkt.clouddn.com\/1_1470988982.jpg","c":0}
```
###上传封面
```
接口:
/Upload/cover

参数:
token 
表单图片流

返回:
{"img":"http:\/\/obs24956g.bkt.clouddn.com\/1_1470988982.jpg","c":0}
```
###坐标获取城市信息
```
接口:
/Map/getCity

参数:
token
location 39.983424,116.322987

返回:
{"city":"北京市","c":0}
```

###举报
```
接口:
/RoomAdmin/reportUser

参数:
token
reason 举报理由, 举报理由有5种可选:广告欺诈、淫秽色情、骚扰谩骂、反动政治、其他内容
uid 被举报人的uid

返回:
{"msg":"感谢您的举报，我们将尽快处理","c":0}
```

###关注
```
接口:
/User/follow

参数:
token
uid 被关注人的uid

返回:
{"msg":"关注成功","c":0}
```

###取消关注
```
接口:
/User/unfollow

参数:
token
uid 被关注人的uid

返回:
{"msg":"取消关注成功","c":0}
```

###关注列表
```
接口:
/My/follows

参数:
token
key 翻页用,返回列表里的最后一个数据项的key值,第一次传0

返回:
{"list":[{"uid":1,"nickname":"nickname1","avatar":"","key":1}],"c":0}
```

###粉丝列表
```
接口:
/My/fans

参数:
token
key 翻页用,返回列表里的最后一个数据项的key值,第一次传0

返回:
{"list":[{"uid":1,"nickname":"nickname1","avatar":"","key":1}],"c":0}
```

###首页热门列表
```
接口:
/Home/hot

参数:
token
key 翻页用,返回列表里的最后一个数据项的key值,第一次传0

返回:
{"list":[{"uid":2,"title":"","city":"","cover":"http:\/\/obzd2fzvx.bkt.clouddn.com\/cover\/2_1471316268.jpg","play_url":"rtmp:\/\/pili-live-rtmp.camhow.com.cn\/kanhao\/test_2_1470980684","nickname":"15921258182","zodiac":"0","key":0}],"c":0}
```
###开播信息,获取封面
```
接口:
/My/castInfo

参数:
token

返回:
{"0":"user","1":{"uid":2,"nickname":"15921258182","height":0,"birthday":"0000-00-00","zodiac":null,"sign":"","avatar":""},"c":0,"m":null}
```


###更新用户信息
```
接口:
/user/updateUserInfo

参数:
token

可选参数:
nickname
height
birthday
zodiac
sign

返回:
{"user":{"uid":2,"nickname":"15921258182","height":0,"birthday":"0000-00-00","zodiac":null,"sign":"111","avatar":""},"c":0}
```

###签到
```
接口:
/My/signIn

参数:
token

返回:
{"money":2,"exp":20,"c":0}
```

###我的管理员
```
接口:
/My/admins

参数:
token

返回:
{"list":[{"uid":3,"nickname":"15951208387","avatar":"","lv":1}],"c":0}
```

###上传相册照片
```
接口:
/Upload/photo

参数:
token
表单图片流

返回:
{"img":"http:\/\/obs24956g.bkt.clouddn.com\/1_1470988982.jpg","c":0}
```

###查看别人的个人主页
```
接口:
/User/home

参数:
token
uid 查看谁

返回:
{"user":{"uid":1,"nickname":"15921258181","sex":"男","height":0,"birthday":"0000-00-00","zodiac":null,"city":"看好星球","sign":"","avatar":"","is_vip":true,"is_tycoon":false,"lv":27,"income":"40","sent":0,"follow":0,"fan":1,"is_follow":false},"visit":[],"album":[],"replay":[{"key":1,"title":0,"cover":"","play_url":"http:\/\/pili-media.camhow.com.cn\/recordings\/z1.kanhao.test-1\/test-1_1471494338.m3u8"},{"key":2,"title":0,"cover":"","play_url":"http:\/\/pili-media.camhow.com.cn\/recordings\/z1.kanhao.test-1\/test-1_1471496232.m3u8"},{"key":3,"title":0,"cover":"","play_url":"http:\/\/pili-media.camhow.com.cn\/recordings\/z1.kanhao.test-1\/test-1_1471496232.m3u8"},{"key":4,"title":0,"cover":"","play_url":"http:\/\/pili-media.camhow.com.cn\/recordings\/z1.kanhao.test-1\/test-1_1471496232.m3u8"}],"c":0}
```


###查看更多访客
```
接口:
/User/getVisit

参数:
token
uid 查看谁
key 最后一个数据的key值

返回:
```

###查看更多相册
```
接口:
/User/getAlbum

参数:
token
uid 查看谁
key 最后一个数据的key值

返回:
```

###查看更多回放
```
接口:
/User/getReplay

参数:
token
uid 查看谁
key 最后一个数据的key值

返回:
```


###拉取聊天消息
```
接口:
/ChatMsg/get

拉去后请调用/ChatMsg/markAsRead,标记为已读

参数:
token

返回:
{"msg":[{"id":5,"from_uid":1,"msg":"chat2","ts":1472124073},{"id":4,"from_uid":1,"msg":"chat2","ts":1472124073}],"c":0}
```

###标记聊天消息为已读
```
接口:
/ChatMsg/markAsRead

参数:
token

返回:
{"msg":"ok","c":0}
```

###获取支付商品
```
接口:
/Charge/getGoods

参数:
pf: 平台 ios或android
channel: 目前传1
type: 1:普通充值 2:开通会员

返回:
{"list":[{"id":1,"coin":42,"money":6,"exp":60},{"id":2,"coin":147,"money":30,"exp":300},{"id":3,"coin":686,"money":98,"exp":980},{"id":4,"coin":2086,"money":298,"exp":2980},{"id":5,"coin":4116,"money":588,"exp":5880},{"id":6,"coin":11186,"money":1598,"exp":15980},{"id":7,"coin":27916,"money":3988,"exp":39880},{"id":8,"coin":55216,"money":7888,"exp":78880}],"c":0}
```

###绑定手机接口
```
接口:
/My/BindMobile

参数:
token
mobile: 手机号

返回:
{"msg":"ok","c":0}
```

###获取banner列表
```
接口:
/Banner/index

参数:
token

返回:
{"list":[{"id":1,"title":"测试","content":"测试内容","img":"http:\/\/img.mp.itc.cn\/upload\/20160829\/cbdee074ee1a4d31a9518fa7c8beb693_th.jpeg","ts":1}],"c":0}```
```

###获取支付宝param
```
接口:
/Alipay/createOrder

参数:
token
goods_id 商品id
pf: 平台 ios或android

返回:
{"param":{"_input_charset":"utf-8","body":"充值看币_42_1","notify_url":"\/Alipay\/notify","out_trade_no":"2016082965","partner":"2088221665307615","payment_type":"1","return_url":"\/Alipay\/callback","seller_id":"2088221665307615","service":"create_direct_pay_by_user","subject":"充值42看币","total_fee":6,"sign":"Tx8jWUT2hnOUUipbCoo1z7dRn1iJSy60qqtSkMJwyxl+AmSaRPN7Rbjp\/Ps6FPjXNrlV83YkpIF7D3PdynkLGxOhFsb0z91tZTfKd6bAAa9r2gmYlzlWbVyFNbIPJoYbrWOr3W8QoJvPlUU3ioAQS5LFCsomtpQcAHUkP6p5JJA=","sign_type":"RSA"},"c":0}
```

###苹果支付接口
```
接口:
/AppleIAP/verifyReceipt

参数:
token
receipt 

返回:
```

###房间排行接口
```
接口:
/Rank/roomSent

参数:
token
room_id 

返回:
{"rank":[{"uid":2,"nickname":"nii😀","avatar":"","lv":5,"money":700},{"uid":1,"nickname":"15921258181","avatar":"","lv":27,"money":0}],"c":0}
```

###开屏页接口
```
接口:
/Banner/splash

参数:
pf 平台 ios或android
ch 渠道特定值,默认传1

返回:
{"img":"http:\/\/cdn.duitang.com\/uploads\/item\/201308\/20\/20130820124935_kQQLU.thumb.600_0.jpeg","c":0}
```

###回放
```
接口:
/Replay/view

参数:
uid
id 回放id

返回:
参考进入房间
replay.view_num 是观看人数
```

###回放消息
```
接口:
/Replay/getRoomMsg

参数:
uid
id 回放id
ts 由前一个数据返回

返回:
参考进入房间
数据中有ts字段,是个时间差,表示这条信息在第几秒播放,拉取下一组数据时传入上一次最后一条的ts
```

###礼物列表
```
接口:
/Gift/getGift

不要每次进直播间请求,放在应用启动的时候

参数:
token
v app版本号

返回:
{"list":[{"id":10,"name":"礼物1","money":2,"exp":2,"remark":"+2经验","sort":0,"status":1},{"id":1,"name":"礼物2","money":10,"exp":10,"remark":"+10经验","sort":1,"status":1},{"id":2,"name":"礼物3","money":20,"exp":20,"remark":"+20经验","sort":2,"status":1},{"id":3,"name":"礼物4","money":50,"exp":50,"remark":"+50经验","sort":3,"status":1},{"id":4,"name":"礼物5","money":100,"exp":100,"remark":"+100经验","sort":4,"status":1},{"id":5,"name":"礼物6","money":520,"exp":520,"remark":"+520经验","sort":5,"status":1},{"id":6,"name":"礼物7","money":999,"exp":999,"remark":"+999经验","sort":6,"status":1},{"id":7,"name":"礼物8","money":1888,"exp":1888,"remark":"+1888经验","sort":7,"status":1},{"id":9,"name":"礼物9","money":6666,"exp":6666,"remark":"+6666经验","sort":8,"status":1},{"id":8,"name":"礼物10","money":8888,"exp":8888,"remark":"+8000经验","sort":9,"status":1}],"c":0}
```


###首页关注列表
```
接口:
/Live/follow

参数:
token
key 翻页用,返回列表里的最后一个数据项的key值,第一次传0

返回:
{"list":[{"uid":2,"title":"","city":"","cover":"http:\/\/obzd2fzvx.bkt.clouddn.com\/cover\/2_1471316268.jpg","play_url":"rtmp:\/\/pili-live-rtmp.camhow.com.cn\/kanhao\/test_2_1470980684","nickname":"15921258182","zodiac":"0","key":0}],"c":0}
```
