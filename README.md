#  EcoRide – Plateforme de covoiturage écoresponsable

Ce projet a été réalisé dans le cadre du Titre Professionnel Développeur Web et Web Mobile (TP DWWM).

🔗 Démo en ligne : [https://ecoride-app-icy-brook-3250.fly.dev](https://ecoride-app-icy-brook-3250.fly.dev)

##  Objectif

Développer une application web responsive pour promouvoir le covoiturage et réduire l'empreinte écologique des déplacements.

##  Architecture

- **Pattern :** MVC (PHP)
- **Couches :** Contrôleurs (logique), Modèles (accès aux données via PDO), Vues (rendu HTML)

##  Stack technique

- **Front-end** : HTML5, CSS3 (Bootstrap), JavaScript
- **Back-end** : PHP avec PDO
- **Base de données relationnelle** : MySQL
- **Base de données NoSQL** : MongoDB (utilisée pour le logging des connexions utilisateurs)
- **Déploiement envisagé** : l’hébergement sera effectué sur une plateforme comme Vercel, Fly.io ou Heroku selon la compatibilité et les besoins techniques.
- **Gestion de projet** : Jira

## Fonctionnalités principales

- Page d’accueil avec barre de recherche
- Liste de covoiturages avec filtres
- Détail du trajet
- Connexion / Inscription utilisateur
- Espace utilisateur : chauffeur, passager
- Création et participation aux trajets
- Historique, démarrage/arrêt du covoiturage
- Espace employé et admin (avis, statistiques, gestion des comptes)

## Structure des bases de données

- **MySQL** : gère les données principales du système (utilisateurs, trajets, véhicules, réservations, crédits, etc.)
- **MongoDB** : utilisée pour :
  - Sauvegarder les **connexions et logs des utilisateurs**
 
##  Charte Graphique & Maquettes

 [Télécharger la Charte Graphique (PDF)](./Charte%20Graphique.pdf)  
 [Voir les maquettes sur Figma](https://www.figma.com/design/IsiAZjrXlyXuE2cKIvvblP/EcoRide?node-id=0-1&t=8EigJvzm0LJZAeKt-1)


- **Wireframes (PDF)** : [Voir les wireframes Excalidraw](https://1drv.ms/b/c/8fa343be0069556b/ETqKnRgc3-hLoNia1XI1MQoBt_KrPPemt19U_XhRQ2gRYQ?e=npIaDW)

---

## Déploiement local

En local, j’utilise **Docker**. Il y a trois services :

### Services Docker
- `app` : PHP 8.2 + Apache (le site) — exposé sur **http://localhost:8080**  
- `mysql` : base MySQL — port **3306**  
- `mongodb` : base NoSQL pour les logs — port **27017**

### Étapes
1. **Cloner** le dépôt.  
2. **Copier** `.env.example` → `.env` (laisser les valeurs par défaut ou adapter).  
3. **Démarrer** :
   ```bash
   docker compose up -d --build