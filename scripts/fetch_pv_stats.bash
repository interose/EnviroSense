#!/bin/bash
result=$(curl --digest -u admin:5pGwtWZguv4zbQnIHcF9 http://192.168.77.199/rpc/Shelly.GetStatus)
response=$(curl -i -X POST "https://home.meirose.info/api/photovoltaic" -H 'Content-Type: application/json' -H 'Authorization: Basic aW90OlEzOGlTZ1lUeEJRNjU3djViV3pH' --data "$result")
echo "$response"
