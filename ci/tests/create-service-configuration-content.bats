#!/usr/bin/env bats

script_name=$(basename "$BATS_TEST_FILENAME" | sed 's/bats/sh/g')
export script_name

setup() {
  load 'node_modules/bats-support/load'
  load 'node_modules/bats-assert/load'
}

main() {
  bash "${BATS_TEST_DIRNAME}/../scripts/$script_name"
}

@test "$script_name: content is created from all variables being set" {
  IMAGE_ID="123456789" \
  STATE_URL="/" \
  HEALTH_CHECK_URL="/health-check" \
  run main

  assert_success
  assert_output "IMAGE_ID=123456789
STATE_URL=/
HEALTH_CHECK_URL=/health-check"
}

@test "$script_name: content is created with empty values when variables are not set" {
  IMAGE_ID="123456789" \
  run main

  assert_success
  assert_output "IMAGE_ID=123456789
STATE_URL=
HEALTH_CHECK_URL="
}
