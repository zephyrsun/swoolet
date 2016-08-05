#!/bin/bash
#set -x

rsync -av --port=8733 ~/workspace/wwwroot/swoolet root@101.200.220.22::www  --exclude=.*