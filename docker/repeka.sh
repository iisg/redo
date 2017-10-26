#!/usr/bin/env bash

cd "$(dirname "$0")"

ln -s ./../var/config/docker.env .env >/dev/null 2>&1

if [ ! -f .env ]; then
  echo "Could not read the docker.env configuration file."
  exit
fi

export UID=$(id -u) >/dev/null 2>&1
export GID=$(id -g) >/dev/null 2>&1

source .env >/dev/null 2>&1

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# remove \r at the end of the env, if exists
CONTAINER_NAME="$(echo -e "${COMPOSE_PROJECT_NAME}" | sed -e 's/\r$//')"

if [ "$1" = "start" ]; then
  echo -e "${GREEN}Starting Repeka containers${NC}"

  docker-compose up --build -d

  sleep 1
  docker exec -it -u www-data "$CONTAINER_NAME" php bin/console repeka:initialize && echo -e "${GREEN}Repeka containers have been started.${NC}"

elif [ "$1" = "stop" ]; then
  echo -e "${GREEN}Stopping Repeka containers${NC}"
  docker-compose stop && echo -e "${GREEN}Repeka containers have been stopped.${NC}"

elif [ "$1" = "restart" ]; then
  "./$(basename "$0")" stop
  sleep 1
  "./$(basename "$0")" start

else
  echo -e "${RED}Usage: $0 start|stop|restart${NC}"

fi
