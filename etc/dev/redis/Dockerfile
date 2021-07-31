FROM redis:6-alpine

# Use a "start" script to run multiple redis instances.
COPY --chmod=755 start.sh start.sh
CMD ["./start.sh"]
