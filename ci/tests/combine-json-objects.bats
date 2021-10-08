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

@test "$script_name: first argument is non-json" {
  export ARG1="non-json value"
  export ARG2="[]"

  run main

  echo "${output}"

  assert_failure "1"
  assert_line --index 0 --regexp "parse error: Invalid literal at line [0-9]+, column [0-9]+"
  assert_line --index 1 "Invalid (non-json)"
  assert_line --index 2 "ARG1: $ARG1"
  assert_line --index 3 "ARG2: $ARG2"
  assert_line --index 4 "jq exit code: 4"
}

@test "$script_name: second argument is non-json" {
  export ARG1="{}"
  export ARG2="non-json value"

  run main

  echo "${output}"

  assert_failure "1"
  assert_line --index 0 --regexp "parse error: Invalid literal at line [0-9]+, column [0-9]+"
  assert_line --index 1 "Invalid (non-json)"
  assert_line --index 2 "ARG1: $ARG1"
  assert_line --index 3 "ARG2: $ARG2"
  assert_line --index 4 "jq exit code: 4"
}

@test "$script_name: two empty objects combine into an empty object" {
  export ARG1="{}"
  export ARG2="{}"

  run main

  echo "${output}"

  assert_success
  assert_output "{}"
}

@test "$script_name: two empty arrays combine into an empty array" {
  export ARG1="[]"
  export ARG2="[]"

  run main

  echo "${output}"

  assert_success
  assert_output "[]"
}

@test "$script_name: empty first argument and empty object combine into empty object" {
  export ARG1=""
  export ARG2="{}"

  run main

  echo "${output}"

  assert_success
  assert_output "{}"
}

@test "$script_name: two objects without overlapping keys combine" {
  export ARG1='{"key1": "value1"}'
  export ARG2='{"key2": "value2"}'

  run main

  echo "${output}"

  assert_success
  assert_output "{
  \"key1\": \"value1\",
  \"key2\": \"value2\"
}"
}

@test "$script_name: two objects with overlapping keys combine; second arg overrides first" {
  export ARG1='{"field1": 0, "label": "first label"}'
  export ARG2='{"field2": 1, "label": "second label"}'

  run main

  echo "${output}"

  assert_success
  assert_output "{
  \"field1\": 0,
  \"label\": \"second label\",
  \"field2\": 1
}"
}
