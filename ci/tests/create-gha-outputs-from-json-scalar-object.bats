#!/usr/bin/env bats

script_name=$(basename "$BATS_TEST_FILENAME" | sed 's/bats/sh/g')
export script_name

setup() {
  load 'node_modules/bats-support/load'
  load 'node_modules/bats-assert/load'
}

main() {
  bash "${BATS_TEST_DIRNAME}/../scripts/$script_name" "$ARG1"
}

@test "$script_name: non-json fails" {
  ARG1='non-json value' \
  run main

  assert_failure
}

@test "$script_name: non-scalar value fails" {
  ARG1='{"key1":"value1", "key2":"{"key3":"value3"}"}' \
  run main

  assert_failure
}

@test "$script_name: empty" {
  ARG1='' \
  run main

  assert_success
  assert_output ""
}

@test "$script_name: single key:value" {
  ARG1='{"key1":"value1"}' \
  run main

  assert_success
  assert_output "::set-output name=key1::value1"
}

@test "$script_name: multiple key:value" {
  ARG1='{"key1":"value1", "key2":"value2", "key3":"value3"}' \
  run main

  assert_success
  assert_output "::set-output name=key1::value1
::set-output name=key2::value2
::set-output name=key3::value3"
}
