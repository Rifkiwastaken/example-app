#!/bin/bash

# Script untuk setup development environment untuk testing migrasi
# SIBESTI - Custom ID Migration Testing

echo "=========================================="
echo "SETUP DEVELOPMENT ENVIRONMENT"
echo "=========================================="
echo ""

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Konfigurasi
PROD_DB="sibesti"
DEV_DB="sibesti_dev_migration_test"
BACKUP_DIR="database/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo -e "${YELLOW}Step 1: Membuat backup database production...${NC}"
echo "Database: $PROD_DB"
echo "Backup akan disimpan di: $BACKUP_DIR"
echo ""

# Buat direktori backup jika belum ada
mkdir -p $BACKUP_DIR

# Backup database production
BACKUP_FILE="$BACKUP_DIR/sibesti_before_migration_${TIMESTAMP}.sql"
echo "Membuat backup: $BACKUP_FILE"

# Gunakan mysqldump (sesuaikan credentials)
read -p "MySQL Username: " MYSQL_USER
read -sp "MySQL Password: " MYSQL_PASS
echo ""

mysqldump -u $MYSQL_USER -p$MYSQL_PASS $PROD_DB > $BACKUP_FILE

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Backup berhasil dibuat!${NC}"
    echo "File: $BACKUP_FILE"
    echo "Size: $(du -h $BACKUP_FILE | cut -f1)"
else
    echo -e "${RED}✗ Backup gagal!${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}Step 2: Membuat database development...${NC}"

# Drop database development jika sudah ada
mysql -u $MYSQL_USER -p$MYSQL_PASS -e "DROP DATABASE IF EXISTS $DEV_DB;"

# Buat database development baru
mysql -u $MYSQL_USER -p$MYSQL_PASS -e "CREATE DATABASE $DEV_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database development berhasil dibuat!${NC}"
else
    echo -e "${RED}✗ Gagal membuat database development!${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}Step 3: Clone data dari production ke development...${NC}"

# Import backup ke database development
mysql -u $MYSQL_USER -p$MYSQL_PASS $DEV_DB < $BACKUP_FILE

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Data berhasil di-clone ke development!${NC}"
else
    echo -e "${RED}✗ Gagal clone data!${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}Step 4: Update .env untuk development testing...${NC}"

# Backup .env original
cp .env .env.backup_${TIMESTAMP}
echo -e "${GREEN}✓ .env original di-backup ke .env.backup_${TIMESTAMP}${NC}"

# Buat .env.dev untuk testing
cat > .env.dev << EOF
APP_NAME=SIBESTI-DEV-MIGRATION-TEST
APP_ENV=local
APP_KEY=$(grep APP_KEY .env | cut -d '=' -f2)
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$DEV_DB
DB_USERNAME=$MYSQL_USER
DB_PASSWORD=$MYSQL_PASS

# Copy settings lainnya dari .env original
$(grep -v "^DB_\|^APP_NAME\|^APP_ENV" .env | grep -v "^#" | grep -v "^$")
EOF

echo -e "${GREEN}✓ .env.dev berhasil dibuat!${NC}"

echo ""
echo "=========================================="
echo -e "${GREEN}SETUP SELESAI!${NC}"
echo "=========================================="
echo ""
echo "Database Development: $DEV_DB"
echo "Backup Production: $BACKUP_FILE"
echo ""
echo -e "${YELLOW}NEXT STEPS:${NC}"
echo "1. Gunakan .env.dev untuk testing:"
echo "   cp .env.dev .env"
echo ""
echo "2. Jalankan migrasi fase 1-3 di development"
echo ""
echo "3. Test aplikasi dengan database development"
echo ""
echo "4. Jika berhasil, restore .env original:"
echo "   cp .env.backup_${TIMESTAMP} .env"
echo ""
echo "5. Jalankan migrasi di production"
echo ""
echo -e "${RED}PENTING:${NC} Jangan lupa restore .env setelah testing!"
echo ""
