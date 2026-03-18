# Centralisation des Transactions POS

Ce document détaille le système de centralisation des transactions dans la table `transactions_pos`. L'objectif est de garantir que **chaque opération** effectuée sur la plateforme, qu'elle soit financière ou opérationnelle, soit tracée de manière unique et cohérente.

## 1. Structure de Référence (REF)
Toutes les transactions génèrent désormais un `TransactionNumber` unique avec des préfixes spécifiques au type d'opération. Cela facilite le débogage et la synchronisation avec des serveurs externes.

| Préfixe | Type d'Opération | Description |
| :--- | :--- | :--- |
| **RE** | Recharge Normale | Recharge de crédit classique. |
| **PA** | Pass | Activation de pass (Internet, etc.). |
| **RD** | Recharge Dealer | Opérations spécifiques au compte Dealer. |
| **CA** | Carte | Achat de cartes de recharge (IAM, Orange, Inwi). |
| **SI** | SIM | Demandes d'activation de cartes SIM. |
| **PR** | Produit | Commandes de produits physiques / Checkout. |
| **SB** | Abonnement | Frais et commissions liés aux abonnements. |
| **TR** | Transfert | Transferts de solde entre points de vente. |
| **BC** | Bonus Collect | Demandes d'encaissement de bonus accumulé. |
| **RF** | Refund | Remboursements d'opérations rejetées. |
| **TX** | General | Autres types d'opérations système. |

**Format** : `[PREFIXE][YYMMDDHHIISS][RAND3]` (ex: `SI260217133707123`)

---

## 2. Opérations Journalisées
Toutes les routes suivantes enregistrent désormais systématiquement une entrée dans `transactions_pos` :

### A. Opérations Immédiates (Statut: `success`)
- **Recharges** : Enregistrement du coût et de l'opérateur.
- **Cartes** : Tracement du numéro de série et de la valeur.
- **Transferts** : Double journalisation (Sortant pour l'expéditeur, Entrant pour le destinataire).
- **Alimentations** : Ajouts de fonds manuels par l'administrateur.

### B. Opérations Différées (Statut: `pending` → `success/failed`)
Ces opérations sont créées en état "Attente" et synchronisées lors de l'action de l'administrateur.
- **Abonnements** : Journalisation des frais de service lors de la création.
- **Activations SIM** : Création d'une trace lors de la demande.
- **Commandes Produits** : Suivi du panier global (Batch) dès le checkout.
- **Collecte Bonus** : Suivi du montant demandé et du mode (Solde ou Produit).

---

## 3. Synchronisation des États (Admin)
Lorsqu'un administrateur valide ou rejette une demande, le système met à jour automatiquement l'entrée correspondante dans `transactions_pos` :

1. **Recherche de la Transaction** : Utilisation du message ou de la référence liée (ex: #ID de commande).
2. **Mise à jour du Statut** : Le champ `State` passe à `success` ou `failed`.
3. **Notes Administrateur** : Le champ `OperatorMessage` est rempli avec le motif du rejet le cas échéant.
4. **Dates de Clôture** : Les champs `ClosedOn` et `ClosedAt` sont renseignés pour marquer la fin de l'opération.

---

## 4. Implémentation Technique
L'essentiel de la logique réside dans :
*   `App\Services\TransactionService::createTransaction` : Le point d'entrée unique pour créer des logs.
*   **Contrôleurs Api** : Déclenchent la création lors de la soumission utilisateur.
*   **Contrôleurs Admin** : Déclenchent la mise à jour lors du traitement de la demande.

## 5. Compatibilité Serveur Externe
Le schéma de la table `transactions_pos` est optimisé pour être exporté ou lu par un service tiers :
- **idx** : Clé primaire incrémentielle.
- **TransactionNumber** : Référence unique humaine et machine.
- **poscode** : Identifiant du point de vente concerné.
- **UserName** : Auteur de l'opération.
- **State** : État actuel (`pending`, `success`, `failed`).
- **AddedOn / AddedAt** : Date et heure de création.
- **ClosedOn / ClosedAt** : Date et heure de traitement final.

---

*Ce système garantit une intégrité totale des données pour la comptabilité et le reporting.*
