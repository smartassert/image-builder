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

@test "$script_name: PULL_REQUESTS missing fails with exit code 3" {
  REPO="owner/package" \
  COMMENT="comment content" \
  run main

  assert_failure "3"
  assert_output ""
}

@test "$script_name: REPO missing fails with exit code 3" {
  PULL_REQUESTS='[{"id": 100000001,"number": 1}]' \
  COMMENT="comment content" \
  run main

  assert_failure "3"
  assert_output ""
}

@test "$script_name: COMMENT missing fails with exit code 3" {
  PULL_REQUESTS='[{"id": 100000001,"number": 1}]' \
  REPO="owner/package" \
  run main

  assert_failure "3"
  assert_output ""
}

@test "$script_name: empty pull request set does nothing" {
  PULL_REQUESTS='[]' \
  REPO="owner/package" \
  COMMENT="comment content" \
  run main

  assert_success
  assert_output ""
}

@test "$script_name: one pull request calls 'gh' once" {
  function gh() {
    echo "$1 $2 $3 $4 $5 $6 $7"
  }

  export -f gh

  PULL_REQUESTS='[{"id": 100000001,"number": 1}]' \
  REPO="owner/package" \
  COMMENT="comment content" \
  run main

  assert_success
  assert_output "pr comment 1 --repo owner/package --body comment content"
}

@test "$script_name: two pull requests calls 'gh' twice" {
  function gh() {
    echo "$1 $2 $3 $4 $5 $6 $7"
  }

  export -f gh

  PULL_REQUESTS='[{"id": 100000001,"number": 1},{"id": 100000002,"number": 2}]' \
  REPO="owner/package" \
  COMMENT="comment content" \
  run main

  assert_success
  assert_output "pr comment 1 --repo owner/package --body comment content
pr comment 2 --repo owner/package --body comment content"
}
