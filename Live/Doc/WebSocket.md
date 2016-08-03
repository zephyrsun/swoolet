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


##直播间

###进入直播间
```
请求:
{"m":"room_enter","room_id":"1","token":"xxxxxxxx"}

返回:
{"msg":"登陆成功","c":0}

房间消息(t值参考`房间消息类型`):
{"t":4,"uid":"10001","nickname":"nickname10001"}
```

###发消息
```
请求:
{"m":"room_sendMsg","msg":"一条消息"}

返回:
{"msg":"发送成功","c":0}

房间消息(t值参考`房间消息类型`):
{"t":4,"uid":"10001","nickname":"nickname10001","msg":"一条消息"}
```

###退出直播间
```
请求:
{"m":"room_quit"}

返回:
无

房间消息(t值参考`房间消息类型`):
无
```

###点赞
```
请求:
{"m":"room_parise"}

返回:
无

房间消息(t值参考`房间消息类型`):
{"t":5,"n":1}
```

###关注主播
```
请求:
{"m":"room_follow"}

返回:
无

房间消息(t值参考`房间消息类型`):
{"t":3,"uid":"10001","nickname":"nickname10001","msg":"关注了主播"}
```

###送礼
```
请求:
{"m":"room_sendGift","gift_id":1}

返回:
无

房间消息(t值参考`房间消息类型`):
{"t":3,"uid":"10001","nickname":"nickname10001","msg":"送给主播","gift_id":1}
```

###开播