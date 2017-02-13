#!/bin/sh

ps aux | grep -ie node | awk '{print "process: " $2}'
pkill -f node
ps aux | grep -ie node | awk '{print "process: " $2}'

