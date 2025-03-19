#!/bin/bash

# Define colors for messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # Reset

REMOTE_USER="tava6855"
REMOTE_HOST="sharp.o2switch.net"
REMOTE_DIR="/home/tava6855/public_html/wp-content/plugins/batch-id"
INSECTO_V4_PATH="/home/$(whoami)/Code/clients/smartlife/smartlifebiosciences.local/wp-content/plugins/batch-id"
RSYNC_IGNORE="$INSECTO_V4_PATH/.rsyncignore"

# Check if the source directory exists
if [ ! -d "$INSECTO_V4_PATH" ]; then
  echo -e "${RED}Error: Source directory does not exist: $INSECTO_V4_PATH${NC}"
  exit 1
fi

# Check if the .rsyncignore file exists, otherwise exit
if [ ! -f "$RSYNC_IGNORE" ]; then
  echo -e "${RED}Error: No .rsyncignore file found.${NC}"
  exit 1
fi

cd "$INSECTO_V4_PATH" || exit

# Add a test mode (dry-run)
DRY_RUN=""
if [ "$1" == "--dry-run" ]; then
  DRY_RUN="--dry-run"
  echo -e "${YELLOW}Test mode activated: No actual changes will be made.${NC}"
fi

# Check SSH connection before running rsync
ssh -q "$REMOTE_USER@$REMOTE_HOST" exit
if [ $? -ne 0 ]; then
  echo -e "${RED}Error: Unable to connect to $REMOTE_HOST via SSH.${NC}"
  exit 1
fi

# Launch rsync
rsync -avz $DRY_RUN --exclude-from="$RSYNC_IGNORE" ./ "$REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR" || {
  echo -e "${RED}Error: Synchronization failed.${NC}"
  exit 1
}

echo -e "${GREEN}Synchronization completed with the remote server.${NC}"
