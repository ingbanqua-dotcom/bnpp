# Guide d'Installation - BNP Paribas Fortis CR

## 📋 Prérequis

- Un navigateur web moderne

---

## 🏠 Installation Locale

### Avec XAMPP / WAMP / LARAGON

1. **Cloner/copier les fichiers** dans `htdocs/` (XAMPP) ou `www/` (WAMP)
2. **Lancer le serveur local**
3. **Accéder à** `http://localhost/bnp/index.html`

### Ouverture directe

L'ouverture directe de `index.html` permet de consulter l'interface, mais l'envoi EmailJS peut échouer depuis une adresse `file://`. Pour tester les e-mails, démarrez Apache dans XAMPP et utilisez `http://localhost/bnp/index.html`.

---

## 🚀 Déploiement en Production

Le projet est 100 % statique (HTML/CSS/JS). Il peut être hébergé sur n'importe quel hébergeur statique :

- **Netlify** : glisser-déposer le dossier ou connecter un repo GitHub
- **GitHub Pages**, **Vercel**, **OVH**, etc.

Aucun backend ni configuration supplémentaire n'est nécessaire.
