#!/usr/bin/env bash

ASSERT_URL=$(echo $ASSERT_URL_TEMPLATE | sed "s@{{ version }}@$VERSION@g")
ASSERT_URL_STATUS_CODE=$(curl -L -s -o /dev/null -w "%{http_code}" $ASSERT_URL)

if [ "200" != "$ASSERT_URL_STATUS_CODE" ]; then
  SOURCE_URL=$(echo $SOURCE_URL_TEMPLATE | sed "s@{{ version }}@$VERSION@g")
  SOURCE_URL_STATUS_CODE=$(curl -L -s -o /dev/null -w "%{http_code}" $SOURCE_URL)

  if [ "200" != "$SOURCE_URL_STATUS_CODE" ]; then
    exit 1
  else
    URL=$SOURCE_URL
  fi

else
  URL=$ASSERT_URL
fi

echo "$URL"
