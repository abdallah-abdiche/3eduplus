# 3edu+ - Guide d'implémentation complet

## Vue d'ensemble du projet
Ce guide détaille l'implémentation complète du système 3edu+ avec authentification, gestion des formations, panier, paiements et reçus.

---

## Phase 1: Configuration de la base de données

### 1. Exécuter le schéma de base de données
Exécutez le fichier `database_schema.sql` dans phpMyAdmin:

```sql
-- Les tables suivantes seront créées:
- formations (courses)
- inscriptions (enrollments)
- panier (cart)
- paiements (payments)
- recus (receipts)
- paiement_formations (junction table)
```

### 2. Tables existantes à modifier
Assurez-vous que la table `utilisateurs` contient une colonne `role`:

```sql
ALTER TABLE utilisateurs ADD COLUMN role ENUM('Apprenant', 'Admin', 'Commercial', 'Pédagogique', 'Marketing') DEFAULT 'Apprenant';
```

---

## Phase 2: Fichiers PHP créés/modifiés

### Fichiers créés:
1. **auth.php** - Middleware d'authentification et contrôle d'accès
   - Fonction `checkAuth()` - Vérifier si l'utilisateur est connecté
   - Fonction `checkRole($roles)` - Vérifier les rôles
   - Fonction `redirectByRole()` - Rediriger selon le rôle
   - Fonction `getCurrentUser()` - Récupérer les infos de l'utilisateur

2. **formation.php** - Page de formations dynamique
   - Récupère les formations de la BD
   - Filtrage par catégorie, niveau, prix
   - Recherche globale
   - Ajouter au panier (AJAX)

3. **courses.php** - Affichage des formations avec panier
   - Récupère les formations de la BD
   - Gère l'ajout au panier
   - Affiche le compte du panier

4. **cart.php** - Gestion du panier
   - Affiche les articles du panier
   - Permet de supprimer des articles
   - Calcule le total avec TVA
   - Redirection vers le paiement

5. **payment.php** - Paiement simulé
   - Sélection de la méthode de paiement
   - Traitement de la transaction
   - Génération du reçu
   - Création des inscriptions

6. **receipt.php** - Affichage du reçu
   - Affiche les détails du paiement
   - Possibilité d'imprimer
   - Lien vers le tableau de bord

7. **logout.php** - Déconnexion
   - Détruit la session
   - Redirection vers l'accueil

### Fichiers modifiés:
1. **signup.php** - Authentification améliorée
   - Stockage du rôle dans la session
   - Redirection basée sur le rôle
   - Intégration avec auth.php

2. **login.php** - Login avec session et rôle

---

## Phase 3: Flux utilisateur (Apprenant)

### 1. Inscription
```
signup.php → Enregistrement → utilisateurs (role = 'Apprenant')
```

### 2. Connexion
```
login.php → Vérification → Session stockée → Redirection au dashboard
```

### 3. Parcourir les formations
```
formation.php (BD) → Filtrage → Résultats dynamiques
```

### 4. Ajouter au panier
```
add_to_cart (AJAX) → $_SESSION['cart'] → cart-count mis à jour
```

### 5. Panier
```
cart.php → Affiche les articles → Calcul du total → Paiement
```

### 6. Paiement
```
payment.php → Choix méthode → Traitement → Transaction créée
           → Inscriptions créées → Reçu généré
```

### 7. Reçu
```
receipt.php → Affiche les détails → Impression possible
           → Lien vers dashboard
```

---

## Phase 4: Flux utilisateur (Admin/Commercial/etc.)

Les utilisateurs avec d'autres rôles seront redirigés vers:
- **Admin**: `/3eduplus/dashboard/admin/index.php`
- **Commercial**: `/3eduplus/dashboard/commercial/index.php`
- **Pédagogique**: `/3eduplus/dashboard/pedagogique/index.php`
- **Marketing**: `/3eduplus/dashboard/marketing/index.php`

---

## Intégration au projet existant

### 1. Mettre à jour les liens de navigation

Modifiez les liens HTML pour pointer vers les fichiers PHP:

```html
<!-- Ancien -->
<a href="formation.html">Formations</a>
<!-- Nouveau -->
<a href="formation.php">Formations</a>

<!-- Ancien -->
<a href="login.html">Connexion</a>
<!-- Nouveau -->
<a href="login.php">Connexion</a>

<!-- Ajouter -->
<a href="cart.php">Panier</a>
<a href="logout.php">Déconnexion</a>
```

### 2. Ajouter des données de test

Insérez des données dans la table `formations`:

```sql
INSERT INTO Formations (titre, categorie, prix, niveau, duree, createur_id) VALUES
('Développement Web Full Stack', 'Développement Web', 599.00, 'Intermédiaire', '12 semaines', 1),
('Design UX/UI Avancé', 'Design & UX', 499.00, 'Avancé', '8 semaines', 1),
('Marketing Digital Complet', 'Marketing Digital', 699.00, 'Débutant', '10 semaines', 1);
```

### 3. Structure des dossiers (à créer)

```
3eduplus/
├── dashboard/
│   ├── admin/
│   │   └── index.php
│   ├── apprenant/
│   │   └── index.php
│   ├── commercial/
│   │   └── index.php
│   ├── pedagogique/
│   │   └── index.php
│   └── marketing/
│       └── index.php
├── formation.php (nouveau)
├── courses.php (nouveau)
├── cart.php (nouveau)
├── payment.php (nouveau)
├── receipt.php (nouveau)
├── logout.php (nouveau)
├── auth.php (nouveau)
└── ... (autres fichiers existants)
```

---

## Fonctionnalités clés

### Authentification
- ✅ Inscription avec hachage de mot de passe
- ✅ Connexion avec vérification
- ✅ Stockage du rôle en session
- ✅ Redirection basée sur le rôle
- ✅ Déconnexion

### Formations
- ✅ Affichage dynamique depuis BD
- ✅ Filtrage par catégorie, niveau, prix
- ✅ Recherche en temps réel
- ✅ Tri par prix, récent, etc.

### Panier
- ✅ Ajout/suppression d'articles
- ✅ Persistance en session
- ✅ Calcul du total avec TVA
- ✅ Validation avant paiement

### Paiement
- ✅ Sélection de méthode de paiement
- ✅ Simulation sécurisée
- ✅ Traitement de transaction
- ✅ Génération de numéro de reçu

### Inscriptions
- ✅ Création automatique après paiement
- ✅ Liaison formation-utilisateur
- ✅ Suivi du statut

### Reçus
- ✅ Génération HTML
- ✅ Stockage en BD
- ✅ Impression possible
- ✅ Accès sécurisé

---

## Tests à effectuer

### 1. Test de flux complet (Apprenant)
```
1. Créer un compte
2. Se connecter
3. Consulter les formations
4. Ajouter une formation au panier
5. Aller au panier
6. Procéder au paiement
7. Afficher le reçu
8. Imprimer le reçu
9. Se déconnecter
```

### 2. Test de filtrage
```
1. Filtrer par catégorie
2. Filtrer par niveau
3. Filtrer par prix
4. Combiner les filtres
5. Rechercher par titre
6. Trier par prix (croissant/décroissant)
```

### 3. Test de sécurité
```
1. Essayer d'accéder sans authentification (redirection)
2. Essayer d'accéder avec mauvais rôle (403)
3. Essayer de modifier l'URL d'un reçu d'un autre utilisateur (non accessible)
```

---

## Notes importantes

1. **Session PHP**: Les données du panier sont stockées en `$_SESSION['cart']`
2. **Sécurité**: Toutes les entrées utilisateur sont échappées avec `htmlspecialchars()`
3. **TVA**: Calculée à 17% sur le total (ajustable)
4. **Transactions BD**: Utilisées pour garantir l'intégrité des paiements
5. **Reçus**: Stockés en HTML dans la BD pour archivage

---

## Prochaines améliorations

- [ ] Intégration paiement réel (Stripe, PayPal)
- [ ] Module d'apprentissage (vidéos, quiz)
- [ ] Certificats téléchargeables
- [ ] Système de commentaires et avis
- [ ] Emails de confirmation
- [ ] Dashboard complet pour Admin
- [ ] Rapports de ventes et statistiques
- [ ] Intégration CRM
- [ ] Support multilingue complet
- [ ] Dark mode persistant

---

## Support & Contact

Pour toute question ou problème, veuillez contacter: contact@3eduplus.fr
