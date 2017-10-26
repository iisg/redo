#!/usr/bin/env bash

cd "$(dirname "$0")"

mkdir -p \
  metrics/data/whisper \
  metrics/data/elasticsearch \
  metrics/data/grafana \
  metrics/log/graphite/webapp \
  metrics/log/elasticsearch \
  postgres
