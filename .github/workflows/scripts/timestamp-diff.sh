#!/usr/bin/env bash

TIMESTAMP1=$(date --date $DATE1 +%s)
TIMESTAMP2=$(date --date $DATE2 +%s)
DIFF=$(($TIMESTAMP2 - $TIMESTAMP1))
echo $DIFF
