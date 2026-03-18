# Deux sites : Frontend (site 1) + Backend (site 2)

## Idée

- **Site 1** = Frontend (public) : landing, pages statiques, marketing. Pas de login ni admin.
- **Site 2** = Backend (platform) : login, admin, client — kolchi dyal l’app (auth, dashboards, API).

Un seul projet Laravel, une seule base (main_raqmicash). L’app y’ferrq 3la **domain/host** bach t’servi routes m’khtalfin.

---

## Tableau récap

|                | Site 1 (Frontend)     | Site 2 (Backend)           |
|----------------|------------------------|----------------------------|
| **Rôle**       | Public, vitrine        | App complète               |
| **Exemples**   | your-domain.com, www         | app.your-domain.com, platform…   |
| **Contenu**    | Welcome, pages fixes   | Login, Admin, Client, API  |
| **Auth**       | Non                    | Oui                        |

---

## Ce qu’il faut définir

1. **Nom exact du Site 1** (frontend) : ex. `your-domain.com`, `www.your-domain.com`.
2. **Nom exact du Site 2** (backend) : ex. `app.your-domain.com`, `platform.your-domain.com`, ou domaine séparé.
3. **Sur le Site 1** : ghir landing + lien « Connexion » / « S’inscrire » qui redirige vers le Site 2, wla 7aja khra ?

---

## Implémentation (résumé)

### 1. Nginx

- Deux blocs `server` (ou deux `server_name` f nfs bloc) :
  - Un pour le **domain du Site 1** → `root` = même Laravel `public/`.
  - Un pour le **domain du Site 2** → même `root`.
- SSL (Certbot) pour les deux domains.

### 2. Laravel

- **Routes** : selon `request()->getHost()` :
  - Host = Site 1 → enregistrer uniquement les routes frontend (welcome, pages statiques).
  - Host = Site 2 → enregistrer routes auth, admin, client, API.
- **Liens** : boutons « Connexion » / « S’inscrire » sur le Site 1 pointent vers `https://[SITE_2]/login`, `https://[SITE_2]/register`.
- **.env** : `APP_URL` peut rester celui du Site 2 (backend) ; pour le Site 1 on s’en sert peu sauf si tu génères des URLs. Option : variable `FRONTEND_URL` pour les redirections.

### 3. Session / cookies

- Par défaut la session est liée au domain du Site 2 (backend). Pas de login sur le Site 1, donc pas besoin de partager la session entre les deux domains.

### 4. Base de données

- Une seule DB (main_raqmicash), partagée. Rien à changer côté MySQL.

---

## Fichiers à toucher (quand tu donnes les 2 noms de sites)

- `routes/web.php` (ou `routes/frontend.php` + `routes/backend.php`) : condition 3la host.
- Nginx : `/etc/nginx/sites-available/` (vhosts Site 1 et Site 2).
- `.env` : `APP_URL`, option `FRONTEND_URL`.
- Vues frontend : liens vers Site 2 pour login/register.

---

## Prochaine étape

Dès que tu fixes les **deux noms** (Site 1 et Site 2), on peut détailler les commandes Nginx et les extraits de code Laravel (routes + exemples de liens).
