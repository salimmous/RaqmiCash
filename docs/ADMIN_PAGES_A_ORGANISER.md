# Admin – Pages à organiser / m9ad

Résumé après revue des vues admin. Ce qui a été corrigé et ce qui reste à faire.

---

## Déjà corrigé

- **Balances** (`/admin/balances`)  
  - Carte « Total Commission » supprimée (doublon de Total Bonus).  
  - Couleur avatar basée sur le code POS (plus de couleur aléatoire à chaque chargement).

- **Alimentations** (`/admin/alimentations`)  
  - `@section('title')` ajouté.  
  - En-tête unifié : titre + sous-titre + bouton « Voir l'Historique ».

- **Commissions** (`/admin/commissions`)  
  - Déjà mis à jour : bloc logique de calcul, pagination, formulaires.

- **Dashboard**  
  - Déjà mis à jour : stats 4+3, actions rapides 3 colonnes.

---

## À organiser / harmoniser

| Page | Route | Problème | Action suggérée |
|------|--------|----------|-----------------|
| **Recharges** | `/admin/recharges` | Tout en **arabe** (titres, filtres, tableau). | Passer en **français** pour cohérence avec le reste de l’admin, ou utiliser les clés de traduction comme les autres pages. |
| **Subscriptions** | `/admin/subscriptions` | Tout en **arabe**. | Idem : français ou traduction. |
| **Outlets** | `/admin/outlets` | Beaucoup de **styles inline** dans `thead` (padding, font-size, etc.). | Extraire les styles dans des classes (ex. `.table th`) ou réutiliser les classes du layout. |
| **Alimentations** | `/admin/alimentations` | Encore des **styles inline** dans le header et ailleurs. | Remplacer par des classes (ex. `.page-header`, `.section-title`) pour un style commun avec le reste de l’admin. |
| **Products** | `/admin/products` | Mix **Bootstrap** (row, col-md-3, card) et **styles inline**. | Soit tout Bootstrap, soit tout en classes métier + CSS commun. |
| **Reports** | `/admin/reports` | Fichier très long (900+ lignes), beaucoup de CSS local. | Découper en partials ou composants, déplacer le CSS dans un fichier commun ou dans le layout. |
| **commissions.blade.php** (racine) | — | Vue **inutilisée** (la route utilise `commissions/index.blade.php`). | Supprimer `resources/views/admin/commissions.blade.php` pour éviter la confusion. |

---

## Structure commune recommandée

Pour que les pages soient **m9adin** et **kdhamin** :

1. **Titre** : `@section('title', '...')` sur chaque page.
2. **En-tête** :  
   `<div class="page-header">` avec `<h1>`, `<p class="text-muted">` et bouton d’action si besoin.
3. **Contenu** : cartes / tableaux dans des `<div class="content-card">` ou équivalent du layout.
4. **Langue** : **français** partout en admin (ou clés `__('admin.xxx')`).
5. **Styles** : éviter les styles inline ; utiliser des classes du layout ou un fichier CSS admin commun.

---

## Fichiers principaux

- Layout admin : `resources/views/layouts/admin.blade.php`
- Vues : `resources/views/admin/*.blade.php` et `resources/views/admin/*/*.blade.php`
- Traductions : `lang/fr/admin.php`, `lang/ar/admin.php`
