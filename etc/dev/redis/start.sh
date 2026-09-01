#!/bin/ash

# Main instance. Append-only so answers survive a container restart.
redis-server --port 6379 --dir /data --appendonly yes --daemonize yes
# Instance for tests (keep container alive). Kept in memory only.
redis-server --port 6380 --save '' --appendonly no
