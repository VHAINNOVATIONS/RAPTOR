#!/bin/bash
echo "Starting services as background processes..."
cd /opt/ewdjs
sudo nohup node startFederator > /var/log/raptor/federatorCPM.log 2>&1 &
sudo nohup node ewdStart-raptor > /var/log/raptor/ewdjsCPM.log 2>&1 &

