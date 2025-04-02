# ChessSfVue - Chess Application with Stockfish Engine

A modern chess application based on Vue.js integrating the powerful Stockfish analysis engine, with an intuitive user interface for position analysis, learning, and puzzle solving.

## Features

### Complete Chess Interface
- Interactive chessboard with intuitive drag-and-drop controls
- Visualization of legal moves
- Highlighting of the last move played
- Support for FEN notation for positions
- Ability to flip the board (Black/White view)

### Analysis with Stockfish
- Integration of Stockfish 17 engine via WebAssembly
- Real-time position analysis
- Display of numerical evaluation (in centipawns)
- Visualization of best analysis lines (up to 3 variations)
- Mate detection
- Error handling with automatic restart

### PGN Display
- PGN file reader with support for standard notations
- Intuitive game navigation
- Comment support
- Interface for visualizing game moves

### Chess Puzzles
- Puzzle mode with tactical training
- Solution interface with progressive steps
- Loading of new puzzles
- Solution mode to visualize correct answers

### Courses and Lessons
- Hierarchical organization of lessons and chapters
- Course tracking for authenticated users
- Interactive visualization of training positions

## Technical Architecture

The application is based on a modular architecture with a clear separation between business logic and user interface. Chess logic is implemented in TypeScript classes, while the user interface is built with Vue.js.

### Data Models (src/chess/models/)
The core of the chess logic is encapsulated in well-structured TypeScript classes:

- `Board.ts` - The main class managing the board state, rules, move validation, and FEN conversions
- `Piece.ts` - Base class for all chess pieces with their common behaviors
- `Pawn.ts`, `King.ts`, `Rook.ts`, etc. - Derived classes for piece types requiring specific rules
- `Position.ts` - Management of coordinates on the chessboard
- `Pgn.ts` - Analysis and interpretation of PGN notations

### Vue Components (src/chess/components/)
Vue components act as an interface between business logic and display:

- `ChessApp.vue` - Main component orchestrating all elements of the chess application
- `Referee.vue` - Links the graphical interface with the TypeScript business logic, transmitting user actions to the Board model and reflecting state changes on the interface
- `Chessboard.vue` - Graphical interface of the chessboard managing drag-and-drop and display
- `PgnReader.vue` - Interface for reading and navigating PGN files
- `StockfishAnalysis.vue` - Interface for analysis with the Stockfish engine

### Stockfish Integration
- Use of the Stockfish engine via Web Workers for optimal performance
- Communication via the UCI protocol (Universal Chess Interface)
- Asynchronous management of commands and responses
- Error handling and memory limitations management
- Optimized configuration for web browsers

### Authentication and API System
The application uses a JWT (JSON Web Tokens) authentication system to secure access to resources:

- Integration with a RESTful API via Axios for all server communications
- Automatic management of authentication tokens in HTTP requests
- Interception of responses to handle authentication errors (401)
- Support for secure cookies to store authentication information

#### API Client with Axios
The application uses Axios as an HTTP client to communicate with the backend:

- Centralized configuration in `src/api/index.ts`
- Interceptors for automatic header and error management
- Support for API-specific content types (application/ld+json)
- Automatic redirection to the login page in case of token expiration

```typescript
// Example of Axios configuration
const api = axios.create({
  baseURL: API_URL,
  withCredentials: true, // Ensure cookies are sent with requests
  headers: {
    'Content-Type': 'application/ld+json',
    'Accept': 'application/ld+json'
  }
})
```

### Routing System with Vue Router
The application uses Vue Router to manage navigation between different views:

- Route configuration in `src/router/index.ts`
- Protection of authenticated routes through navigation guards
- Support for lazy loading of components to optimize performance
- Management of redirections after authentication
- Support for nested routes for complex interfaces

```typescript
// Example of route configuration
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // Public routes
    {
      path: '/login',
      name: 'Login',
      component: LoginView,
      meta: { requiresAuth: false }
    },
    // Protected routes
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
        // Other routes...
      ]
    }
  ]
})
```

### Data Flow
1. The user interacts with the interface (moving a piece, loading a FEN position)
2. The Referee component captures this interaction and transmits it to the Board model
3. The business logic in TypeScript classes validates the action and updates the game state
4. Changes are reflected on the Vue.js user interface reactively
5. In parallel, the Stockfish engine analyzes the position and sends its evaluations

## Installation

### Prerequisites
- Node.js (v14+)
- NPM or Yarn

### Configuration
1. Clone the repository
   ```
   git clone [repo-url]
   cd ChessSfVue
   ```

2. Install dependencies
   ```
   npm install
   ```

3. Launch the application in development mode
   ```
   npm run dev
   ```

4. For production, compile and minify
   ```
   npm run build
   ```

## File Structure
```
ChessSfVue/
├── public/
│   └── stockfish/           # Stockfish WebAssembly files
├── src/
│   ├── api/                 # Axios configuration and API services
│   ├── chess/               # Chess logic and components
│   │   ├── assets/          # Images and resources
│   │   ├── components/      # Vue components
│   │   ├── models/          # TypeScript data models
│   │   └── utils/           # Utilities (including stockfish.js)
│   ├── components/          # General UI components
│   ├── composables/         # Reusable hooks (including useAuth)
│   ├── layouts/             # Application layouts
│   ├── router/              # Routing configuration
│   ├── views/               # Application pages
│   └── App.vue              # Root component
└── README.md                # Documentation
```

## Usage

### Analysis with Stockfish
1. Access a chess position in the application
2. Activate the "Stockfish" switch in the analysis panel
3. Wait for the engine initialization (visual indicator available)
4. Consult the evaluation and proposed analysis lines

### Game Navigation
1. Load a game in PGN format
2. Use the navigation buttons to browse through moves
3. Click on a specific move to see the corresponding position

### Puzzle Solving
1. Access the "Puzzles" section
2. Try to solve the presented tactical puzzle
3. Use the "See solution" button if needed
4. Load a new puzzle with the corresponding button

### Authentication and Course Access
1. Log in via the login page to access protected features
2. Navigate to the "Courses" section to see available lessons
3. Choose a course and browse its chapters
4. Interactive chess positions are automatically loaded from the API

### Authentication Error Handling
- Expired JWT tokens are automatically detected
- The user is redirected to the login page in case of authentication issues
- After reconnection, the user is redirected to the page they were trying to reach

## Error Management
The application incorporates a robust error management system, particularly for the Stockfish engine which can sometimes encounter memory limitations in the WebAssembly environment. In case of a "memory access out of bounds" error, the application automatically restarts the engine while preserving the user experience.

## License
This project is distributed under the MIT license.


---

Developed by Fabrice Chaplain
