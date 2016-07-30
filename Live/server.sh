#!/bin/bash

#step=1 #间隔的秒数，不能大于60

#for (( i = 0; i < 60; i=(i+step) )); do
    /opt/app/php/bin/php  /opt/wwwroot/sihuo/App/$1 "?c=push&a=chat" > /opt/wwwroot/sihuo/App/logs/async_chat_queue.log
#    sleep $step
#done

exit 0


php http_server.php
php websocket_server.php