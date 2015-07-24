#!/bin/bash
echo "Starting services as background processes..."
cd /opt/ewdjs
sudo nohup node startFederator > /var/log/raptor/federator.log 2>&1 &
sudo nohup node ewdStart-raptor > /var/log/raptor/ewdjs.log 2>&1 &

