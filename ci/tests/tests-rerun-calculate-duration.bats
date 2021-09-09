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

@test "$script_name: no arguments fails with exit code 3" {
  run main

  assert_failure "3"
  assert_output ""
}

@test "$script_name: UPDATED_AT set, CREATED_AT missing fails with exit code 3" {
  UPDATED_AT="2021-09-08T16:56:07Z" \
  run main

  assert_failure "3"
  assert_output ""
}

@test "$script_name: CREATED_AT set, UPDATED_AT missing fails with exit code 4" {
  CREATED_AT="2021-09-08T16:56:07Z" \
  run main

  assert_failure "4"
  assert_output ""
}

@test "$script_name: CREATED_AT cannot be parsed as a date fails with exit code 5" {
  CREATED_AT="non-date string" \
  UPDATED_AT="2021-09-08T17:56:07Z" \
  run main

  assert_failure "5"
  assert_output ""
}

@test "$script_name: UPDATED_AT cannot be parsed as a date fails with exit code 6" {
  CREATED_AT="2021-09-08T17:56:07Z" \
  UPDATED_AT="non-date string" \
  run main

  assert_failure "6"
  assert_output ""
}

@test "$script_name: UPDATED_AT set at 37 seconds ahead of CREATED_AT outputs 37" {
  CREATED_AT="2021-09-08T16:00:00Z" \
  UPDATED_AT="2021-09-08T16:00:37Z" \
  run main

  assert_success
  assert_output "37"
  assert_output "37"
}

@test "$script_name: UPDATED_AT set at one hour ahead of CREATED_AT outputs 3600" {
  CREATED_AT="2021-09-08T16:56:07Z" \
  UPDATED_AT="2021-09-08T17:56:07Z" \
  run main

  assert_success
  assert_output "3600"
}
