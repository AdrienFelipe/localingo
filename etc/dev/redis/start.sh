#!/bin/ash

# Main instance.
redis-server --port 6379 --daemonize yes
# Instance for tests (keep container alive).
redis-server --port 6380
