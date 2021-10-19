#!/usr/bin/env bats

script_name=$(basename "$BATS_TEST_FILENAME" | sed 's/bats/sh/g')
export script_name

setup() {
  load 'node_modules/bats-support/load'
  load 'node_modules/bats-assert/load'
}

main() {
  bash "${BATS_TEST_DIRNAME}/../scripts/$script_name" "$ARG1" "$ARG2"
}

@test "$script_name: no arguments" {
  run main

  assert_failure "1"
  assert_output "Argument 1 (service id) not given"
}

@test "$script_name: first argument only" {
  ARG1="foo-service" \
  run main

  assert_failure "2"
  assert_output "Argument 2 (configuration path) not given"
}

@test "$script_name: not found" {
  ARG1="foo-service" \
  ARG2="./services/foo-service/configuration.env"
  run main

  assert_failure "3"
  assert_output "Configuration for service foo-service not found: ./services/foo-service/configuration.env"
}

@test "$script_name: found" {
  ARG1="foo-service" \
  ARG2="./ci/tests/fixtures/foo-service-configuration.env"
  run main

  assert_success
  assert_output <<< "./ci/tests/fixtures/foo-service-configuration.env"
}
