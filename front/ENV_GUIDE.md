# Environment Variables Guide

This document explains how to use environment variables in the ChessSfVue frontend project.

## Available Environment Files

The project uses three environment files:

1. `.env` - Base environment variables used across all environments
2. `.env.development` - Variables specific to the development environment (overrides .env)
3. `.env.production` - Variables specific to production environment (overrides .env)

## Key Environment Variables

| Variable | Purpose | Example |
|----------|---------|---------|
| `VITE_API_URL` | URL of the backend API | `http://localhost:8000/api` |
| `VITE_ASSETS_URL` | URL for loading assets (images, etc.) | `http://localhost:8000` |
| `VITE_ENABLE_API_LOGGING` | Toggle API request/response logging | `true` or `false` |

## How to Use Environment Variables in Code

In your Vue components or services, you can access environment variables using Vite's import.meta.env:

```typescript
// Direct usage in a component
const apiUrl = import.meta.env.VITE_API_URL

// Or through a composable/helper
import { ASSETS_URL } from '../api'
```

## Asset URL Helpers

We've created a composable to help with asset URLs:

```typescript
import { useAssets } from '../composables/useAssets'

// In your component setup
const { getAssetUrl, getCourseImageUrl, getUserAvatarUrl } = useAssets()

// Usage examples
const fullImageUrl = getCourseImageUrl(course.image)
const avatarUrl = getUserAvatarUrl(user.avatar)
```

## Setting Up for Production

Before deploying to production:

1. Create a `.env.production.local` file (not tracked by git) with your actual production URLs:

```
VITE_API_URL=https://your-production-api.com/api
VITE_ASSETS_URL=https://your-production-api.com
VITE_ENABLE_API_LOGGING=false
```

2. Build your application with `npm run build` or `pnpm build`, which will use production environment variables.

## Local Development

For local development, the default `.env.development` file should work if your backend is running on `localhost:8000`.

If your development environment uses a different setup, you can create a `.env.development.local` file with your custom settings. 