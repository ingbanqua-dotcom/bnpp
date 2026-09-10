# Guide d'Installation et Déploiement - BNP Paribas Fortis Demo

## 📋 Prérequis

- PHP 7.4 ou supérieur (pour les scripts backend)
- Un navigateur web moderne
- Un serveur web avec PHP (pour les fonctionnalités email)

---

## 🏠 Installation Locale (Développement)

### Avec XAMPP / WAMP / LARAGON

1. **Cloner/copier les fichiers** dans `htdocs/` (XAMPP) ou `www/` (WAMP)
2. **Lancer le serveur local**
3. **Accéder à** `http://localhost/bnp/bnp.html`

### Avec PHP intégré

```bash
cd c:\Users\Rodeck-WHITNEY\Downloads\bnp
php -S localhost:8000
```

Puis accédez à `http://localhost:8000/bnp.html`

---

## 🚀 Déploiement en Production

### ⚠️ Important : Netlify ne supporte pas PHP

Netlify est un service d'hébergement **statique** uniquement. Les fichiers PHP ne s'exécuteront **pas** sur Netlify.

### **Option 1 : Héberger le HTML sur Netlify + PHP sur serveur séparé** ✅ (Recommandé)

#### Étape 1 : Déployer le HTML sur Netlify

1. Créer un dossier `public/` contenant :
   - `bnp.html`
   - `BNP.png`
   - `photo.jpg`

2. Pousser sur GitHub

3. Connecter Netlify à votre repo GitHub

4. Netlify va déployer le site automatiquement

#### Étape 2 : Déployer le PHP sur un serveur séparé

**Serveurs recommandés :**

- **Heroku** (gratuit avec limitations)
- **Railway** (gratuit avec crédit)
- **Render** 
- **Digital Ocean** (5$ / mois)
- **OVH / 1&1** (hébergement mutualisé PHP)

**Exemple avec Railway :**

1. Pousser le dossier PHP sur GitHub
2. Connecter Railway à votre repo
3. Railway détecte PHP automatiquement
4. Configurer la variable `PHP_URL` dans Netlify :

```javascript
// Dans bnp.html, en haut du fichier JavaScript :
const API_URL = 'https://votre-app-railway.up.railway.app';

// Dans la fonction prepareTransfer() :
fetch(API_URL + '/send-transfer.php', {...})
```

---

### **Option 2 : Utiliser un service Email externe** ✅

Au lieu de PHP mail(), utilisez une API email :

#### Mailgun (Recommandé)

1. Créer un compte [Mailgun](https://www.mailgun.com/)
2. Obtenir votre API Key
3. Modifier `config.php` :

```php
define('EMAIL_SERVICE', 'mailgun');
define('MAILGUN_API_KEY', 'YOUR_API_KEY');
define('MAILGUN_DOMAIN', 'sandboxXXXX.mailgun.org');
```

4. Utiliser la nouvelle fonction dans `send-transfer.php` :

```php
// Au lieu de mail() :
$result = sendViaMailgun($to, $subject, $messageBody);
```

#### SendGrid

1. Créer un compte [SendGrid](https://sendgrid.com/)
2. Obtenir une API Key
3. Intégration similaire à Mailgun

#### Brevo (ex-Sendinblue)

Service français, très simple d'utilisation

---

### **Option 3 : Convertir en Netlify Functions** (Avancé)

Convertir le PHP en Node.js pour Netlify Functions.

```javascript
// netlify/functions/send-transfer.js
const nodemailer = require('nodemailer');

exports.handler = async (event) => {
  // Code Node.js ici
  return { statusCode: 200, body: JSON.stringify({success: true}) };
};
```

---

## ⚙️ Configuration pour Production

### 1. Mettre à jour `config.php`

```php
// Passage à production
define('ENVIRONMENT', 'production');
define('DEBUG', false);

// Configurer l'email
define('EMAIL_FROM', 'noreply@votre-domaine.com');
define('EMAIL_FROM_NAME', 'BNP Paribas Fortis');

// Si vous utilisez SMTP :
define('SMTP_HOST', 'smtp.votre-serveur.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'votre-email@votre-domaine.com');
define('SMTP_PASSWORD', 'votre-mot-de-passe');
```

### 2. Obtenir un nom de domaine

- **Namecheap** ($8.88/an)
- **OVH**
- **Godaddy**
- **Google Domains**

### 3. Configurer les DNS

Si vous utilisez Netlify + Serveur PHP séparé :
- `example.com` → Netlify
- `api.example.com` → Votre serveur PHP

---

## 🔒 Sécurité - Checklist Production

- [ ] DEBUG = false dans config.php
- [ ] HTTPS activé (Let's Encrypt gratuit)
- [ ] Valider ALL inputs côté serveur
- [ ] Rate limiting sur send-transfer.php
- [ ] CORS configuré correctement
- [ ] Logs sécurisés (pas en dossier public)
- [ ] Variables sensibles en .env (pas en dur)

### Exemple .env

```
SMTP_HOST=smtp.gmail.com
SMTP_USER=votre-email@gmail.com
SMTP_PASSWORD=votre-mot-passe
API_KEY=votre-cle-secrete
```

Charger avec :
```php
$env = parse_ini_file('.env');
define('SMTP_USER', $env['SMTP_USER']);
```

---

## 📊 Architecture Recommandée

```
Navigateur (HTML sur Netlify)
        ↓
Netlify (statique)
        ↓
API PHP (Railway/Heroku) ← send-transfer.php
        ↓
Service Email (Mailgun/SendGrid/SMTP)
        ↓
Email utilisateur
```

---

## 🧪 Tester en Local

1. Lancer XAMPP/WAMP
2. Accéder à `http://localhost/bnp/bnp.html`
3. Remplir le formulaire de virement
4. Cliquer "Continuer"
5. Vérifier que l'email est envoyé ou sauvegardé

### Déboguer

- Ouvrir la console du navigateur (F12)
- Vérifier les logs PHP : `tail -f logs/app_*.log`
- Vérifier que les dossiers ont les permissions d'écriture

---

## 📞 Support & Ressources

- **PHP mail()** : https://www.php.net/manual/en/function.mail.php
- **Mailgun** : https://documentation.mailgun.com/
- **Netlify** : https://docs.netlify.com/
- **Railway** : https://docs.railway.app/

---

## ✅ Résumé

| Solution | Coût | Facilité | Recommandé |
|----------|------|---------|-----------|
| Netlify + Heroku PHP | ~5$ / mois | Moyen | ✅ |
| Netlify + Mailgun | ~15$ / mois | Facile | ✅ |
| OVH Hébergement | ~3$ / mois | Facile | ✅ |
| Solution full-Netlify | Gratuit | Difficile | ⚠️ |

**Pour débuter : OVH Hébergement Mutualisé PHP** = Simplest solution

Good luck! 🚀
