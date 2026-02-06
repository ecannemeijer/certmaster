#!/bin/bash

# CertMaster Installation Script

echo "=================================="
echo "CertMaster Installation"
echo "=================================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if running as root for MySQL
echo -e "${YELLOW}Step 1: Creating Database${NC}"
sudo mysql -u root -e "CREATE DATABASE IF NOT EXISTS certmaster;" 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database 'certmaster' created${NC}"
else
    echo -e "${RED}✗ Failed to create database. Make sure MySQL is running.${NC}"
    exit 1
fi

# Import schema
echo -e "${YELLOW}Step 2: Importing Database Schema${NC}"
sudo mysql -u root certmaster < database.sql
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database schema imported${NC}"
else
    echo -e "${RED}✗ Failed to import schema${NC}"
    exit 1
fi

# Run migrations
echo -e "${YELLOW}Step 3: Running Migrations${NC}"
php spark migrate
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Migrations completed${NC}"
else
    echo -e "${RED}✗ Migration failed${NC}"
    exit 1
fi

# Set permissions
echo -e "${YELLOW}Step 4: Setting Permissions${NC}"
chmod -R 777 writable/
mkdir -p writable/uploads/certificates
mkdir -p writable/ssh_keys
chmod -R 777 writable/
echo -e "${GREEN}✓ Permissions set${NC}"

# Check Apache mod_rewrite
echo -e "${YELLOW}Step 5: Checking Apache Configuration${NC}"
if apache2ctl -M 2>/dev/null | grep -q "rewrite_module"; then
    echo -e "${GREEN}✓ mod_rewrite is enabled${NC}"
else
    echo -e "${YELLOW}! mod_rewrite not detected. Enable it with:${NC}"
    echo "  sudo a2enmod rewrite"
    echo "  sudo systemctl restart apache2"
fi

echo ""
echo -e "${GREEN}=================================="
echo "Installation Complete!"
echo "==================================${NC}"
echo ""
echo "Access the application at:"
echo "  http://localhost/certmaster/"
echo ""
echo "Default credentials:"
echo "  Username: admin"
echo "  Password: password"
echo ""
echo -e "${YELLOW}Security Note:${NC} Change the default password after first login!"
echo ""
