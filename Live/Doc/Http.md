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
uid //禁言谁

返回:
无