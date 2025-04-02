# ChessSfVue - Application d'échecs avec moteur Stockfish

Une application moderne d'échecs basée sur Vue.js intégrant le puissant moteur d'analyse Stockfish, avec une interface utilisateur intuitive pour l'analyse de positions, l'apprentissage et la résolution de puzzles.

## Fonctionnalités

### Interface d'échecs complète
- Échiquier interactif avec contrôles intuitifs drag-and-drop
- Visualisation des coups légaux
- Mise en évidence du dernier coup joué
- Support de la notation FEN pour les positions
- Possibilité de retourner l'échiquier (vue des Noirs/Blancs)

### Analyse avec Stockfish
- Intégration du moteur Stockfish 17 via WebAssembly
- Analyse en temps réel des positions
- Affichage de l'évaluation numérique (en centipions)
- Visualisation des meilleures lignes d'analyse (jusqu'à 3 variantes)
- Détection des mats
- Gestion des erreurs avec redémarrage automatique

### Affichage PGN
- Lecteur de fichiers PGN avec support pour les notations standards
- Navigation intuitive dans les parties
- Support des commentaires
- Interface pour visualiser les coups de la partie

### Puzzles d'échecs
- Mode puzzle avec entraînement tactique
- Interface de solution avec étapes progressives
- Chargement de nouveaux puzzles
- Mode solution pour visualiser les réponses correctes

### Cours et leçons
- Organisation hiérarchique des leçons et chapitres
- Suivi des cours pour les utilisateurs authentifiés
- Visualisation interactive des positions d'entraînement

## Architecture technique

L'application est basée sur une architecture modulaire avec une séparation claire entre la logique métier et l'interface utilisateur. La logique des échecs est implémentée dans des classes TypeScript, tandis que l'interface utilisateur est construite avec Vue.js.

### Modèles de données (src/chess/models/)
Le cœur de la logique d'échecs est encapsulé dans des classes TypeScript bien structurées :

- `Board.ts` - La classe principale gérant l'état de l'échiquier, les règles, la validation des coups et les conversions FEN
- `Piece.ts` - Classe de base pour toutes les pièces d'échecs avec leurs comportements communs
- `Pawn.ts`, `King.ts`, `Rook.ts`, etc. - Classes dérivées pour les types de pièces nécessitant des règles spécifiques
- `Position.ts` - Gestion des coordonnées sur l'échiquier
- `Pgn.ts` - Analyse et interprétation des notations PGN

### Composants Vue (src/chess/components/)
Les composants Vue agissent comme une interface entre la logique métier et l'affichage :

- `ChessApp.vue` - Composant principal orchestrant tous les éléments de l'application d'échecs
- `Referee.vue` - Fait le lien entre l'interface graphique et la logique métier en TypeScript, en transmettant les actions utilisateur au modèle Board et en répercutant les changements d'état sur l'interface
- `Chessboard.vue` - Interface graphique de l'échiquier gérant le drag-and-drop et l'affichage
- `PgnReader.vue` - Interface de lecture et navigation des fichiers PGN
- `StockfishAnalysis.vue` - Interface pour l'analyse avec le moteur Stockfish

### Intégration de Stockfish
- Utilisation du moteur Stockfish via Web Workers pour des performances optimales
- Communication via le protocole UCI (Universal Chess Interface)
- Gestion asynchrone des commandes et des réponses
- Traitement des erreurs et des limitations mémoire
- Configuration optimisée pour les navigateurs web

### Système d'authentification et d'API
L'application utilise un système d'authentification JWT (JSON Web Tokens) pour sécuriser l'accès aux ressources :

- Intégration avec une API RESTful via Axios pour toutes les communications serveur
- Gestion automatique des tokens d'authentification dans les requêtes HTTP
- Interception des réponses pour gérer les erreurs d'authentification (401)
- Support des cookies sécurisés pour stocker les informations d'authentification

#### Client API avec Axios
L'application utilise Axios comme client HTTP pour communiquer avec le backend :

- Configuration centralisée dans `src/api/index.ts`
- Intercepteurs pour la gestion automatique des en-têtes et des erreurs
- Support des types de contenu spécifiques à l'API (application/ld+json)
- Redirection automatique vers la page de connexion en cas d'expiration du token

```typescript
// Exemple de configuration d'Axios
const api = axios.create({
  baseURL: API_URL,
  withCredentials: true, // Ensure cookies are sent with requests
  headers: {
    'Content-Type': 'application/ld+json',
    'Accept': 'application/ld+json'
  }
})
```

### Système de routage avec Vue Router
L'application utilise Vue Router pour gérer la navigation entre les différentes vues :

- Configuration des routes dans `src/router/index.ts`
- Protection des routes authentifiées par des guards de navigation
- Support du chargement paresseux (lazy loading) des composants pour optimiser les performances
- Gestion des redirections après authentification
- Support des routes imbriquées pour les interfaces complexes

```typescript
// Exemple de configuration des routes
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // Routes publiques
    {
      path: '/login',
      name: 'Login',
      component: LoginView,
      meta: { requiresAuth: false }
    },
    // Routes protégées
    {
      path: '/',
      component: MainLayout,
      children: [
        {
          path: 'chess',
          name: 'Chess',
          component: () => import('../views/ChessView.vue'),
          meta: { requiresAuth: true }
        },
        // Autres routes...
      ]
    }
  ]
})
```

### Flux de données
1. L'utilisateur interagit avec l'interface (déplacement d'une pièce, chargement d'une position FEN)
2. Le composant Referee capture cette interaction et la transmet au modèle Board
3. La logique métier dans les classes TypeScript valide l'action et met à jour l'état du jeu
4. Les changements sont répercutés sur l'interface utilisateur Vue.js de manière réactive
5. En parallèle, le moteur Stockfish analyse la position et envoie ses évaluations

## Installation

### Prérequis
- Node.js (v14+)
- NPM ou Yarn

### Configuration
1. Cloner le dépôt
   ```
   git clone [url-du-repo]
   cd ChessSfVue
   ```

2. Installer les dépendances
   ```
   npm install
   ```

3. Lancer l'application en mode développement
   ```
   npm run dev
   ```

4. Pour la production, compiler et minifier
   ```
   npm run build
   ```

## Structure des fichiers
```
ChessSfVue/
├── public/
│   └── stockfish/           # Fichiers WebAssembly de Stockfish
├── src/
│   ├── api/                 # Configuration Axios et services API
│   ├── chess/               # Logique et composants d'échecs
│   │   ├── assets/          # Images et ressources
│   │   ├── components/      # Composants Vue
│   │   ├── models/          # Modèles de données en TypeScript
│   │   └── utils/           # Utilitaires (dont stockfish.js)
│   ├── components/          # Composants généraux de l'UI
│   ├── composables/         # Hooks réutilisables (dont useAuth)
│   ├── layouts/             # Layouts de l'application
│   ├── router/              # Configuration du routage
│   ├── views/               # Pages de l'application
│   └── App.vue              # Composant racine
└── README.md                # Documentation
```

## Utilisation

### Analyse avec Stockfish
1. Accédez à une position d'échecs dans l'application
2. Activez l'interrupteur "Stockfish" dans le panneau d'analyse
3. Attendez l'initialisation du moteur (indicateur visuel disponible)
4. Consultez l'évaluation et les lignes d'analyse proposées

### Navigation dans une partie
1. Chargez une partie au format PGN
2. Utilisez les boutons de navigation pour parcourir les coups
3. Cliquez sur un coup spécifique pour voir la position correspondante

### Résolution de puzzles
1. Accédez à la section "Puzzles"
2. Tentez de résoudre le puzzle tactique présenté
3. Utilisez le bouton "Voir la solution" si nécessaire
4. Chargez un nouveau puzzle avec le bouton correspondant

### Authentification et accès aux cours
1. Connectez-vous via la page de connexion pour accéder aux fonctionnalités protégées
2. Naviguez vers la section "Cours" pour voir les leçons disponibles
3. Choisissez un cours et parcourez ses chapitres
4. Les positions d'échecs interactives sont automatiquement chargées depuis l'API

### Gestion des erreurs d'authentification
- Les tokens JWT expirés sont automatiquement détectés
- L'utilisateur est redirigé vers la page de connexion en cas de problème d'authentification
- Après reconnexion, l'utilisateur est redirigé vers la page qu'il tentait d'atteindre

## Gestion des erreurs
L'application intègre un système robuste de gestion des erreurs, particulièrement pour le moteur Stockfish qui peut parfois rencontrer des limitations de mémoire dans l'environnement WebAssembly. En cas d'erreur "memory access out of bounds", l'application redémarre automatiquement le moteur tout en préservant l'expérience utilisateur.

## Licence
Ce projet est distribué sous licence MIT.


---

Développé par Fabrice Chaplain
