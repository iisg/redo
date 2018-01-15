#!/usr/bin/env bash

cd "$(dirname "$0")"

if [ "$(expr substr $(uname -s) 1 5)" == "MINGW" ]; then
    export SUBJECT="//C=PL\ST=REPEKA\L=REPEKA\O=Dis\CN=REPEKA"
else
    export SUBJECT="/C=PL/ST=REPEKA/L=REPEKA/O=Dis/CN=REPEKA"
fi

openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout server.key -out server.crt -subj $SUBJECT
