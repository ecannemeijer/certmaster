#!/bin/bash

# CertMaster System Check

echo "=================================="
echo "CertMaster System Check"
echo "=================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check PHP
echo -n "Checking PHP... "
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1)
    if [ "$PHP_VERSION" -ge 8 ]; then
        echo -e "${GREEN}✓ PHP $PHP_VERSION installed${NC}"
    else
        echo -e "${RED}✗ PHP 8+ required${NC}"
    fi
else
    echo -e "${RED}✗ PHP not found${NC}"
fi

# Check MySQL
echo -n "Checking MySQL... "
if command -v mysql &> /dev/null; then
    echo -e "${GREEN}✓ MySQL installed${NC}"
else
    echo -e "${RED}✗ MySQL not found${NC}"
fi

# Check Database
echo -n "Checking Database... "
if sudo mysql -u root -e "USE certmaster;" 2>/dev/null; then
    echo -e "${GREEN}✓ Database 'certmaster' exists${NC}"
else
    echo -e "${RED}✗ Database not found. Run install.sh${NC}"
fi

# Check Apache
echo -n "Checking Apache... "
if command -v apache2 &> /dev/null; then
    echo -e "${GREEN}✓ Apache installed${NC}"
else
    echo -e "${RED}✗ Apache not found${NC}"
fi

# Check mod_rewrite
echo -n "Checking mod_rewrite... "
if apache2ctl -M 2>/dev/null | grep -q "rewrite_module"; then
    echo -e "${GREEN}✓ mod_rewrite enabled${NC}"
else
    echo -e "${YELLOW}! mod_rewrite not enabled${NC}"
fi

# Check Composer
echo -n "Checking Composer... "
if command -v composer &> /dev/null; then
    echo -e "${GREEN}✓ Composer installed${NC}"
else
    echo -e "${RED}✗ Composer not found${NC}"
fi

# Check writable directory
echo -n "Checking permissions... "
if [ -w "writable/" ]; then
    echo -e "${GREEN}✓ writable/ is writable${NC}"
else
    echo -e "${RED}✗ writable/ not writable. Run: chmod -R 777 writable/${NC}"
fi

# Check .env
echo -n "Checking .env file... "
if [ -f ".env" ]; then
    echo -e "${GREEN}✓ .env exists${NC}"
else
    echo -e "${RED}✗ .env not found. Copy from env${NC}"
fi

# Check Controllers
echo -n "Checking Controllers... "
CONTROLLERS=("Auth.php" "Dashboard.php" "Servers.php" "Certificates.php")
ALL_EXIST=true
for controller in "${CONTROLLERS[@]}"; do
    if [ ! -f "app/Controllers/$controller" ]; then
        ALL_EXIST=false
        break
    fi
done

if [ "$ALL_EXIST" = true ]; then
    echo -e "${GREEN}✓ All controllers present${NC}"
else
    echo -e "${RED}✗ Missing controllers${NC}"
fi

# Check Models
echo -n "Checking Models... "
MODELS=("UserModel.php" "ServerModel.php" "CertificateModel.php" "SshKeyModel.php" "DeployLogModel.php")
ALL_EXIST=true
for model in "${MODELS[@]}"; do
    if [ ! -f "app/Models/$model" ]; then
        ALL_EXIST=false
        break
    fi
done

if [ "$ALL_EXIST" = true ]; then
    echo -e "${GREEN}✓ All models present${NC}"
else
    echo -e "${RED}✗ Missing models${NC}"
fi

# Check Views
echo -n "Checking Views... "
VIEWS=("auth/login.php" "dashboard/index.php" "servers/index.php" "servers/create.php" "servers/edit.php" "certificates/upload.php")
ALL_EXIST=true
for view in "${VIEWS[@]}"; do
    if [ ! -f "app/Views/$view" ]; then
        ALL_EXIST=false
        break
    fi
done

if [ "$ALL_EXIST" = true ]; then
    echo -e "${GREEN}✓ All views present${NC}"
else
    echo -e "${RED}✗ Missing views${NC}"
fi

# Check OpenSSL
echo -n "Checking OpenSSL... "
if command -v openssl &> /dev/null; then
    echo -e "${GREEN}✓ OpenSSL installed${NC}"
else
    echo -e "${RED}✗ OpenSSL not found${NC}"
fi

# Check SSH
echo -n "Checking SSH... "
if command -v ssh &> /dev/null; then
    echo -e "${GREEN}✓ SSH client installed${NC}"
else
    echo -e "${RED}✗ SSH not found${NC}"
fi

echo ""
echo "=================================="
echo "System Check Complete"
echo "=================================="
echo ""
echo "Application URL: http://localhost/certmaster/"
echo "Default Login: admin / password"
echo ""
