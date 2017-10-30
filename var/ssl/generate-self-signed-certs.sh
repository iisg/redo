#!/usr/bin/env bash

cd "$(dirname "$0")"

openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout server.key -out server.crt -subj "/C=PL/ST=Repeka/L=Repeka/O=Dis/CN=repeka"
