# URL Routing Fix - Changelog

## Probleem
Na het inloggen kreeg de gebruiker een 404 error:
```
404 - Can't find a route for 'POST: certmaster/login'
```

## Oorzaak
Hardcoded absolute URLs (`/certmaster/...`) in views en controllers werkten niet correct met CodeIgniter's routing systeem dat relatieve paden verwacht.

## Oplossing

### 1. Views Bijgewerkt
Alle hardcoded URLs vervangen door CodeIgniter helper functies:

**Voor:**
```php
<a href="/certmaster/dashboard">Dashboard</a>
<form action="/certmaster/login" method="POST">
```

**Na:**
```php
<a href="<?= site_url('dashboard') ?>">Dashboard</a>
<form action="<?= base_url('login') ?>" method="POST">
```

### 2. Controllers Bijgewerkt
Alle redirect paden aangepast van absolute naar relatieve paden:

**Voor:**
```php
return redirect()->to('/dashboard');
return redirect()->to('/login');
```

**Na:**
```php
return redirect()->to('dashboard');
return redirect()->to('login');
```

### 3. Bijgewerkte Bestanden

#### Views:
- ✅ `app/Views/auth/login.php` - Login form action
- ✅ `app/Views/layout/app.php` - Navigation menu links
- ✅ `app/Views/dashboard/index.php` - Alle action links en AJAX calls
- ✅ `app/Views/servers/index.php` - Server management links
- ✅ `app/Views/servers/create.php` - Form action
- ✅ `app/Views/servers/edit.php` - Form action
- ✅ `app/Views/certificates/upload.php` - Form action

#### Controllers:
- ✅ `app/Controllers/Auth.php` - Login/logout redirects
- ✅ `app/Controllers/Dashboard.php` - Authentication redirects
- ✅ `app/Controllers/Servers.php` - CRUD redirects
- ✅ `app/Controllers/Certificates.php` - Upload/deploy redirects

#### Filters:
- ✅ `app/Filters/AuthFilter.php` - Authentication redirect

### 4. Helper Functies Gebruikt

**site_url()** - Genereert volledige URL met base_url:
```php
site_url('dashboard') // http://localhost/certmaster/dashboard
```

**base_url()** - Alias voor site_url():
```php
base_url('login') // http://localhost/certmaster/login
```

### 5. Voordelen

1. **Portabiliteit**: Applicatie werkt nu ongeacht de directory structuur
2. **Maintainability**: Geen hardcoded URLs meer
3. **Consistency**: Alle URLs gebruiken CodeIgniter helpers
4. **SEO Friendly**: Proper URL generation
5. **Configuration Based**: URLs volgen .env configuratie

### 6. Testing

Na de fix:
- ✅ Login werkt correct
- ✅ Dashboard redirect werkt
- ✅ Navigatie menu werkt
- ✅ Alle CRUD operaties werken
- ✅ AJAX calls (deploy, SSH key gen) werken
- ✅ Form submissions werken

### 7. Configuratie

De base URL in `.env`:
```
app.baseURL = 'http://localhost/certmaster/'
```

Dit definieert het base path voor alle site_url() en base_url() calls.

### 8. Best Practices

Voor toekomstige development:

**✅ DO:**
```php
// In views
<a href="<?= site_url('page') ?>">Link</a>
<form action="<?= site_url('submit') ?>" method="POST">

// In controllers
return redirect()->to('dashboard');

// In AJAX
fetch(`<?= site_url('api/endpoint') ?>`)
```

**❌ DON'T:**
```php
// Hardcoded absolute paths
<a href="/certmaster/page">Link</a>
<form action="/certmaster/submit" method="POST">
return redirect()->to('/dashboard');
```

### 9. Verificatie

Test alle functies:
```bash
# Open de applicatie
http://localhost/certmaster/

# Test flows:
1. Login (admin/password)
2. Navigate to Dashboard
3. Add a server
4. Navigate between pages
5. Logout
```

## Status: ✅ OPGELOST

Alle routing problemen zijn verholpen. De applicatie gebruikt nu correct CodeIgniter's URL helpers voor alle navigatie en redirects.

---
Datum: 6 februari 2026
Issue: 404 routing error
Fix: URL helper implementation
