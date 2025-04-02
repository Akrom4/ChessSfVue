# ChessSfVue - Admin Panel

## Overview

ChessSfVue Admin is a Vue.js-based administration panel for a chess learning platform. It provides tools for managing chess courses, chapters, and PGN (Portable Game Notation) content. The application connects to a Symfony backend API for data persistence.

## Features

- **Course Management**: Create, edit, and delete chess courses with details like title, description, difficulty level, and side (White/Black)
- **Chapter Management**: Organize course content into chapters with PGN data
- **PGN Import**: Upload PGN files or paste PGN text to automatically create chapters
- **PGN Parser**: Built-in parser to extract moves, comments, and variations from PGN notation
- **Authentication**: Secure admin access with role-based permissions

## Tech Stack

- **Frontend**:
  - Vue.js 3 with Composition API
  - TypeScript for type safety
  - PrimeVue for UI components
  - Vue Router for navigation
  - Axios for API communication

- **Backend**:
  - Symfony PHP framework
  - API Platform for RESTful API
  - Doctrine ORM for database access
  - VichUploader for file uploads

## Project Structure

```
admin/                         # Admin panel root
├── src/                       # Source code
│   ├── assets/                # Static assets
│   ├── components/            # Reusable UI components
│   ├── composables/           # Composition API functions
│   │   ├── useCourseService.ts   # Course-related API functions
│   │   ├── useChapterService.ts  # Chapter-related API functions
│   │   └── usePgnParser.ts       # PGN parsing utilities
│   ├── lib/                   # Utilities and helpers
│   │   ├── axios.ts           # API client configuration
│   │   └── chess/             # Chess-specific utilities
│   │       └── models/        # Chess data models
│   │           └── Pgn.ts     # PGN parser implementation
│   ├── router/                # Route definitions
│   ├── views/                 # Page components
│   │   ├── courses/           # Course management views
│   │   └── chapters/          # Chapter management views
│   └── main.js                # Application entry point
├── public/                    # Public assets
└── package.json               # Dependencies and scripts
```

## Getting Started

### Prerequisites

- Node.js (v14 or later)
- npm or pnpm
- Symfony backend API running

### Installation

1. Clone the repository
   ```bash
   git clone https://your-repository-url/ChessSfVue.git
   cd ChessSfVue/admin
   ```

2. Install dependencies
   ```bash
   npm install
   # or
   pnpm install
   ```

3. Configure environment variables
   ```
   # .env.development
   VITE_API_BASE_URL=http://localhost:8000
   ```

4. Start the development server
   ```bash
   npm run dev
   # or
   pnpm run dev
   ```

## Development Guidelines

### PGN Data Structure

The application uses a structured format for PGN data. When working with chapters, be aware of the two data formats:

#### Current Format (New)
```javascript
pgndata: [{
  FEN: string,
  Title: string,
  Number: number,
  Moves: Array,
  Comments: Array,
  Variations: Array
}]
```

#### Legacy Format (Old)
```javascript
pgndata: {
  title: string,
  chapter: [{
    FEN: string,
    Title: string,
    Number: number,
    Moves: Array,
    Comments: Array,
    Variations: Array
  }]
}
```

The application automatically converts from the old format to the new format when saving chapters.

### Adding New Components

1. Follow the existing component structure
2. Use PrimeVue components for UI consistency
3. Implement proper TypeScript interfaces
4. Add appropriate error handling

## Deployment

1. Build the application
   ```bash
   npm run build
   # or
   pnpm run build
   ```

2. Deploy the contents of the `dist` directory to your web server

3. Configure the web server to redirect all routes to `index.html` for SPA routing

## API Documentation

The backend API documentation is available at:
```
http://localhost:8000/api/docs
```

## Troubleshooting

### CORS Issues
If you encounter CORS errors, ensure the Symfony backend has proper CORS configuration in `nelmio_cors.yaml`:

```yaml
nelmio_cors:
    defaults:
        allow_credentials: true
        allow_origin: ['http://localhost:5174']
        allow_headers: ['Content-Type', 'Authorization']
        allow_methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']
```

### PGN Parsing Issues
If chapters aren't importing correctly, check the PGN format and ensure it follows standard notation rules.

## Authors

- Fabrice Chaplain - [GitHub](https://github.com/akrom4) 