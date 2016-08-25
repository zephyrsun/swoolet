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

:nickname需要被user.nickname替换
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

###禁言
接口:
/RoomAdmin/silenceUser

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