#常量说明

##房间消息类型
```
const TYPE_MESSAGE = 1;//普通消息
const TYPE_HORN = 2;//广播喇叭
const TYPE_FOLLOW = 3;//关注主播
const TYPE_ENTER = 4;//进入房间
const TYPE_PRAISE = 5;//点赞
const TYPE_GIFT = 10;//送礼
```

#服务器接口
开发环境地址:
> ws://192.168.0.85:9512


##进入房间

请求:
> {"m":"room_enter","room_id":"1","uid":"10001"}

返回:
> {"msg":"登陆成功","c":0}

房间消息(`t`值参考`房间消息类型`):
> {"t":4,"uid":"10001","nickname":"nickname10001"}
