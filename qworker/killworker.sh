#!/bin/sh

for pid in $(ps -ef | grep "worker" | awk '{print $2}'); do kill -9 $pid; done

exit(0)