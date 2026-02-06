# CertMaster - SSL Certificate Management System

Een moderne webapplicatie voor het beheren van SSL-certificaten op meerdere Linux Apache/HTTP servers.

## 🚀 Functies

- **Dashboard**: Overzicht van alle servers met realtime certificaatstatus
- **Server Management**: Servers toevoegen, bewerken en verwijderen
- **Certificate Upload**: PEM en KEY bestanden uploaden
- **Automatische Validatie**: Certificaat vervaldatum wordt automatisch uitgelezen
- **SSH Key Management**: RSA key pairs genereren voor veilige SSH authenticatie
- **One-Click Deploy**: Certificaten deployen via SSH met één klik
- **Activity Logs**: Deployment geschiedenis bijhouden
- **Session Management**: Automatische timeout na 5 minuten inactiviteit
- **Modern UI**: Responsive design met Tailwind CSS

## 📋 Vereisten

- PHP 8.0 of hoger
- MySQL 5.7 of hoger
- Apache webserver
- Composer
- SSH toegang tot doelservers
- OpenSSL PHP extensie

## 🔧 Installatie

### 1. Database Setup

Voer het SQL script uit om de database aan te maken:

```bash
sudo mysql -u root < database.sql
```

### 2. Permissies

Zorg dat de webserver schrijfrechten heeft:

```bash
chmod -R 777 writable/
```

### 3. Apache Configuratie

Zorg dat mod_rewrite enabled is:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## 🎯 Gebruik

### Login

Navigeer naar: http://localhost/certmaster/

**Standaard credentials:**
- Username: admin
- Password: password

### Workflow

1. Voeg een server toe
2. Genereer SSH key en installeer op doelserver
3. Upload certificaat (PEM + KEY)
4. Deploy naar server met één klik

## 🔐 Beveiliging

- Session timeout: 5 minuten
- Password hashing met bcrypt
- RSA 4096-bit SSH keys
- Input validatie op alle formulieren

## 📊 Database

Database: certmaster
Username: root
Password: (geen)

Tabellen:
- users
- servers
- certificates
- ssh_keys
- deploy_logs
- ci_sessions

## 🎨 Tech Stack

- PHP 8+ met CodeIgniter 4
- MySQL Database
- Tailwind CSS
- Font Awesome Icons
- SSH2 & OpenSSL

## 📝 Licentie

CertMaster v1.0 - 2026
