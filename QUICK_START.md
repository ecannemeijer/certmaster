# CertMaster - Quick Start Guide

## 🚀 Snel Starten

### 1. Installatie (Eerste keer)
```bash
cd /var/www/html/certmaster
./install.sh
```

### 2. Toegang tot de Applicatie
Open je browser en ga naar:
```
http://localhost/certmaster/
```

### 3. Inloggen
```
Username: admin
Password: password
```

## 📖 Basis Tutorial

### Stap 1: Eerste Server Toevoegen

1. Na het inloggen, klik op **"Add New Server"**
2. Vul de gegevens in:
   - **Server Name**: Bijv. "Production Web Server"
   - **Hostname**: Bijv. "webserver.example.com"
   - **IP Address**: Bijv. "192.168.1.100"
   - **SSH Port**: 22 (standaard)
   - **SSH Username**: root (of een andere gebruiker)
   - **Certificate Path**: /etc/apache2/ssl (waar de certs komen)
   - **Apache Restart Command**: sudo systemctl restart apache2
3. Klik op **"Add Server"**

### Stap 2: SSH Key Genereren

1. Ga naar **"Servers"** in het menu
2. Klik op het **sleutel icoon** (🔑) bij je server
3. Er wordt een RSA 4096-bit key pair gegenereerd
4. **Kopieer de public key** die wordt getoond
5. Log in op je remote server en voeg de key toe:
   ```bash
   ssh user@192.168.1.100
   nano ~/.ssh/authorized_keys
   # Plak de public key onderaan
   # Sla op en sluit af
   chmod 600 ~/.ssh/authorized_keys
   ```

### Stap 3: Certificaat Uploaden

1. Ga terug naar het **Dashboard**
2. Klik op het **upload icoon** (📤) bij je server
3. Upload twee bestanden:
   - **PEM/CRT File**: Je certificaat bestand
   - **KEY File**: Je private key bestand
4. Klik op **"Upload Certificate"**

Het systeem leest automatisch:
- Common Name (CN)
- Valid From datum
- Valid Until datum

### Stap 4: Certificaat Deployen

1. Op het **Dashboard**, zie je nu je server met certificaat info
2. Klik op het **raket icoon** (🚀) bij je server
3. Bevestig de deployment
4. Het systeem zal:
   - Via SSH verbinding maken met de server
   - De certificaat bestanden uploaden naar het geconfigureerde pad
   - Apache herstarten
   - De status loggen

### Stap 5: Status Monitoren

Het Dashboard toont:
- **🟢 Groen**: Certificaat geldig (meer dan 30 dagen)
- **🟡 Geel**: Certificaat verloopt binnenkort (minder dan 30 dagen)
- **🔴 Rood**: Certificaat verlopen
- **⚪ Grijs**: Geen certificaat

## 🔧 Test Setup (Lokaal)

Als je lokaal wilt testen zonder een echte remote server:

### Lokale SSH Server Opzetten
```bash
# Installeer SSH server
sudo apt install openssh-server

# Start SSH
sudo systemctl start ssh

# Test verbinding
ssh localhost
```

### Test Server Toevoegen
- **Hostname**: localhost
- **IP Address**: 127.0.0.1
- **SSH Port**: 22
- **SSH Username**: [je eigen username]
- **Certificate Path**: /tmp/test-certs
- **Apache Restart Command**: echo "Apache restart test"

### Test Certificaat Maken
```bash
# Maak een test certificaat
openssl req -x509 -newkey rsa:4096 -keyout test.key -out test.pem -days 365 -nodes \
  -subj "/C=NL/ST=Test/L=Test/O=Test/CN=test.example.com"

# Nu heb je:
# test.pem (certificaat)
# test.key (private key)
```

## 📊 Dashboard Features

### Statistieken
- **Total Servers**: Aantal toegevoegde servers
- **Valid Certificates**: Aantal geldige certificaten
- **Expiring Soon**: Certificaten die binnen 30 dagen verlopen
- **Expired**: Verlopen certificaten

### Server Acties
- **📤 Upload**: Nieuw certificaat uploaden
- **🚀 Deploy**: Certificaat naar server deployen
- **✏️ Edit**: Server gegevens bewerken

### Recent Activity
Toont de laatste 5 deployments met status

## 🛠️ Server Management

### Server Bewerken
1. Ga naar **"Servers"**
2. Klik op **edit icoon** (✏️)
3. Pas gegevens aan
4. Klik op **"Update Server"**

### Server Verwijderen
1. Ga naar **"Servers"**
2. Klik op **delete icoon** (🗑️)
3. Bevestig verwijdering
4. ⚠️ Dit verwijdert ook alle certificaten en SSH keys!

### SSH Key Opnieuw Genereren
1. Klik opnieuw op het **sleutel icoon**
2. Een nieuwe key wordt gegenereerd
3. Vervang de oude key op de server met de nieuwe public key

## 🔐 Beveiliging

### Session Timeout
- Automatische uitlog na **5 minuten** inactiviteit
- Configureerbaar in `.env`: `session.expiration = 300`

### Wachtwoord Wijzigen
Om het admin wachtwoord te wijzigen:
```bash
# Genereer een nieuwe hash
php -r "echo password_hash('nieuw_wachtwoord', PASSWORD_DEFAULT);"

# Update in MySQL
sudo mysql -u root certmaster -e "UPDATE users SET password='[hash hier]' WHERE username='admin';"
```

### Nieuwe Gebruiker Toevoegen
```sql
INSERT INTO users (username, password) VALUES 
('nieuwegebruiker', '$2y$10$[jouw hash hier]');
```

## 📁 Belangrijke Directories

```
/var/www/html/certmaster/
├── writable/uploads/certificates/  # Geüploade certificaten
├── writable/ssh_keys/              # SSH private keys
└── writable/logs/                  # Applicatie logs
```

## 🐛 Troubleshooting

### Kan niet inloggen
- Controleer username: `admin`
- Controleer password: `password`
- Check database: `sudo mysql -u root certmaster -e "SELECT * FROM users;"`

### SSH Deploy faalt
1. Test handmatig: `ssh -p [poort] [user]@[ip]`
2. Controleer of public key in authorized_keys staat
3. Check firewall settings
4. Bekijk deploy logs in database

### Upload faalt
1. Check permissies: `ls -la writable/uploads/`
2. Moet 777 zijn: `chmod -R 777 writable/`
3. Check PHP settings: `upload_max_filesize` en `post_max_size`

### Session timeout te kort
Pas aan in `.env`:
```
session.expiration = 600  # 10 minuten
```

### Database error
Reset database:
```bash
sudo mysql -u root -e "DROP DATABASE certmaster; CREATE DATABASE certmaster;"
sudo mysql -u root certmaster < database.sql
php spark migrate
```

## 📞 Support

### Logs Bekijken
```bash
# Applicatie logs
tail -f writable/logs/log-*.log

# Apache errors
tail -f /var/log/apache2/error.log
```

### Database Query
```bash
sudo mysql -u root certmaster
```

Handige queries:
```sql
-- Alle servers
SELECT * FROM servers;

-- Actieve certificaten
SELECT s.name, c.common_name, c.valid_until 
FROM servers s 
LEFT JOIN certificates c ON c.server_id = s.id AND c.is_active = 1;

-- Deploy logs
SELECT s.name, d.status, d.message, d.created_at 
FROM deploy_logs d 
JOIN servers s ON s.id = d.server_id 
ORDER BY d.created_at DESC 
LIMIT 10;
```

## ✅ Checklist voor Productie

- [ ] Wijzig admin wachtwoord
- [ ] Configureer SSL/HTTPS voor CertMaster zelf
- [ ] Backup strategie opzetten
- [ ] Firewall rules controleren
- [ ] SSH key permissions controleren (600/700)
- [ ] Database backup automatie
- [ ] Monitoring opzetten voor verlopende certs
- [ ] Email notificaties configureren (toekomstige feature)

## 🎉 Klaar!

Je bent nu klaar om SSL certificaten te beheren voor al je servers!

Voor meer informatie, zie:
- `README.md` - Volledige documentatie
- `PROJECT_SUMMARY.md` - Technische details
- `database.sql` - Database schema
