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