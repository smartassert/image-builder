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

@test "$script_name: no arguments outputs 'false'" {
  run main

  assert_success
  assert_output "false"
}

@test "$script_name: MAXIMUM_DURATION set, DURATION missing  outputs 'false'" {
  MAXIMUM_DURATION="10" \
  run main

  assert_success
  assert_output "false"
}

@test "$script_name: DURATION set, MAXIMUM_DURATION missing  outputs 'false'" {
  DURATION="20" \
  run main

  assert_success
  assert_output "false"
}

@test "$script_name: DURATION less than MAXIMUM_DURATION outputs 'true'" {
  DURATION="10" \
  MAXIMUM_DURATION="11" \
  run main

  assert_success
  assert_output "true"
}

@test "$script_name: DURATION equals MAXIMUM_DURATION outputs 'true'" {
  DURATION="11" \
  MAXIMUM_DURATION="11" \
  run main

  assert_success
  assert_output "true"
}

@test "$script_name: DURATION greater than MAXIMUM_DURATION outputs 'false'" {
  DURATION="12" \
  MAXIMUM_DURATION="11" \
  run main

  assert_success
  assert_output "false"
}
