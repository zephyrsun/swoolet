#!/bin/bash
#set -x

rsync -av --port=8733 ~/workspace/wwwroot/swoolet root@192.168.0.159::www  --exclude=.*