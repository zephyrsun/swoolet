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

返回:
{"uid":2,"avatar":"","birthday":"0000-00-00","full":false,"token":"xxxxx","c":0}

user 用户对象
full true直接进入直播间,false需要更新用户资料,进入更新资料的界面
```

###获取用户信息
接口:
/User/getUserInfo

参数:
token 
uid //查看谁

返回:
{"uid":2,"nickname":"15921258182","height":0,"birthday":"0000-00-00","sign":"","avatar":"","lv":0,"income":"50","sent":"50","follow":1,"fan":0,"c":0}

###增加房管
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


###删除房管
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

###上传头像
接口:
/Upload/avatar

参数:
token 
表单图片流

返回:
{"img":"http:\/\/obs24956g.bkt.clouddn.com\/1_1470988982.jpg","c":0}

###上传封面
接口:
/Upload/cover

参数:
token 
表单图片流

返回:
{"img":"http:\/\/obs24956g.bkt.clouddn.com\/1_1470988982.jpg","c":0}

###坐标获取城市信息
接口:
/Map/getCity

参数:
token
location 39.983424,116.322987

返回:
{"city":"北京市","c":0}


###举报
接口:
/RoomAdmin/reportUser

参数:
token
reason 举报理由, 举报理由有5种可选:广告欺诈、淫秽色情、骚扰谩骂、反动政治、其他内容
uid 被举报人的uid

返回:
{"msg":"感谢您的举报，我们将尽快处理","c":0}


###关注
接口:
/User/follow

参数:
token
uid 被关注人的uid

返回:
{"msg":"关注成功","c":0}

###取消关注
接口:
/User/unfollow

参数:
token
uid 被关注人的uid

返回:
{"msg":"取消关注成功","c":0}


###关注列表
接口:
/My/follows

参数:
token
key 翻页用,返回列表里的最后一个数据项的key值,第一次传0

返回:
{"list":[{"uid":1,"nickname":"nickname1","avatar":"","key":1}],"c":0}

###粉丝列表
接口:
/My/fans

参数:
token
key 翻页用,返回列表里的最后一个数据项的key值,第一次传0

返回:
{"list":[{"uid":1,"nickname":"nickname1","avatar":"","key":1}],"c":0}