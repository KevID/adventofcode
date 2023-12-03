# Projet Advent of Code avec Symfony

## Introduction
Ce projet est une implémentation en Symfony des défis quotidiens proposés par [Advent of Code](https://adventofcode.com/). Chaque jour, un nouveau problème (en 2 parties) est résolu en utilisant les fonctionnalités et les avantages du framework Symfony.

## Installation
1. **Cloner le Répertoire**
   ```
   git clone adventofcode
   cd adventofcode
   ```

2. **Installer les Dépendances**
   ```
   composer install
   ```

3. **Configuration de l'Environnement**
    - Configurer le fichier `.env` ou `.env.local` avec les paramètres nécessaires (base de données, variables d'environnement spécifiques, etc.).


4. **Démarrer le Serveur**
   ```
   symfony server:start
   ```

## Utilisation
- Pour exécuter une solution pour un jour spécifique, utilisez la commande Symfony appropriée. Par exemple :
  ```
  // symfony console app:YYYY:DD:(A|B)
  symfony console app:2023:01:A
  ```
  
## Structure du Projet
- **import/**: Fichiers d'importation de données, si nécessaire.
- **src/**
    - **Command/**: Contient les commandes Symfony pour exécuter les solutions.
    - **Service/**: Services utilisés pour les logiques de résolution des problèmes.


## Crédits
- Développé par [Votre Nom].
- [Advent of Code](https://adventofcode.com/) pour les défis quotidiens.


## Licence
[MIT](https://choosealicense.com/licenses/mit/)