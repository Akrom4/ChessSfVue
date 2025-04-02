# Chess Learning Platform API

A comprehensive REST API for a chess learning platform, featuring chess puzzles, courses, lessons, and user management. Built with Symfony and API Platform.

## Overview

This API provides endpoints to access various chess learning resources:

### Chess Puzzles
- Retrieving individual puzzles by ID
- Getting random puzzles with optional rating filters
- Filtering puzzles by specific themes and difficulty
- Full pagination support for puzzle collections

### Chess Courses
- Course management with chapters/lessons
- Course following system for users
- Progress tracking for users going through courses
- Image upload for course materials

### User Management
- User registration and authentication
- Role-based access control
- User course enrollment tracking

## Technology Stack

- PHP 8.x
- Symfony 6.x
- API Platform
- Doctrine ORM
- MySQL/MariaDB
- JWT Authentication
- Vich Uploader Bundle for file uploads

## Installation

### Prerequisites

- PHP 8.0 or higher
- Composer
- MySQL/MariaDB database
- Symfony CLI (optional, for development)

### Setup Instructions

1. Clone the repository:
   ```
   git clone <repository-url>
   cd ChessSfVue/back
   ```

2. Install dependencies:
   ```
   composer install
   ```

3. Configure your environment variables by creating a `.env.local` file:
   ```
   DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=mariadb-10.4.21"
   JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
   JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
   JWT_PASSPHRASE=your_passphrase
   ```

4. Generate JWT keys:
   ```
   mkdir -p config/jwt
   openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
   openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
   ```

5. Create the database and run migrations:
   ```
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

6. (Optional) Load fixtures for testing:
   ```
   php bin/console doctrine:fixtures:load
   ```

7. Start the development server:
   ```
   symfony serve
   # or
   php -S localhost:8000 -t public/
   ```

## API Endpoints

### Authentication

JWT authentication is used to protect endpoints.

```
POST /api/authentication_token
```

Request body:
```json
{
  "username": "your_username",
  "password": "your_password"
}
```

### User Endpoints

```
GET /api/users                     # Get all users (admin only)
POST /api/users                    # Create a user
GET /api/users/{id}                # Get a specific user
PUT /api/users/{id}                # Update a user
DELETE /api/users/{id}             # Delete a user (admin only)
GET /api/me                        # Get current user profile
```

### Course Endpoints

```
GET /api/courses                   # Get all courses
GET /api/courses/{id}              # Get a specific course
POST /api/courses                  # Create a course (admin only)
PUT /api/courses/{id}              # Update a course (admin only)
DELETE /api/courses/{id}           # Delete a course (admin only)
POST /api/courses/{id}/upload      # Upload course image (admin only)
GET /api/courses/{id}/following    # Check if user follows course
GET /api/courses/{id}/my-chapters  # Get chapters for followed course
```

### Chapter Endpoints

```
GET /api/chapters/{id}             # Get a specific chapter
POST /api/chapters                 # Create a chapter (admin only)
PUT /api/chapters/{id}             # Update a chapter (admin only)
DELETE /api/chapters/{id}          # Delete a chapter (admin only)
GET /api/courses/{courseId}/chapters      # Get chapters for a course
GET /api/courses/{courseId}/chapters/{id} # Get specific chapter in course
```

### User Course Endpoints

```
GET /api/user_courses             # Get all user-course relations
GET /api/user_courses/{id}        # Get a specific user-course relation
POST /api/user_courses            # Create a user-course relation
PUT /api/user_courses/{id}        # Update a user-course relation
DELETE /api/user_courses/{id}     # Delete a user-course relation
```

### Puzzle Endpoints

```
GET /api/puzzles/{id}              # Get a specific puzzle
GET /api/puzzles/random            # Get a random puzzle
GET /api/puzzles/by-themes         # Get puzzles filtered by themes
```

#### Get a random puzzle
```
GET /api/puzzles/random?min_rating=1200&max_rating=1800&themes=middlegame,crushing
```

Parameters:
- `min_rating` (optional): Minimum puzzle rating (default: 0)
- `max_rating` (optional): Maximum puzzle rating (default: 3000) 
- `themes` (optional): Comma-separated list of themes to filter by

#### Get puzzles by themes
```
GET /api/puzzles/by-themes?themes=middlegame,crushing&min_rating=1200&max_rating=1800&page=1&limit=20
```

Parameters:
- `themes` (required): Comma-separated list of themes to filter by
- `min_rating` (optional): Minimum puzzle rating (default: 0)
- `max_rating` (optional): Maximum puzzle rating (default: 3000)
- `page` (optional): Page number for pagination (default: 1)
- `limit` (optional): Number of items per page (default: 20, max: 50)

## Data Structures

### User Entity

- `id` (int): Unique identifier
- `username` (string): User's username
- `email` (string): User's email
- `password` (string): Hashed password
- `roles` (array): User roles (ROLE_USER, ROLE_ADMIN)
- `created_at` (datetime): Creation timestamp
- `updated_at` (datetime): Last update timestamp
- `userCourses` (relation): Courses the user is enrolled in

### Course Entity

- `id` (int): Unique identifier
- `title` (string): Course title
- `description` (string): Course description
- `image` (string): Course image filename
- `author` (string): Course author
- `colorside` (string): Which side the student plays (white/black)
- `difficulty` (enum): Course difficulty level
- `createdat` (datetime): Creation timestamp
- `updatedat` (datetime): Last update timestamp
- `chapters` (relation): Chapters belonging to this course
- `userCourses` (relation): Users enrolled in this course

### Chapter Entity

- `id` (int): Unique identifier
- `title` (string): Chapter title
- `course` (relation): Parent course
- `pgndata` (array): Chess position data
- `rawpgn` (text): Raw PGN notation

### UserCourses Entity

- `id` (int): Unique identifier
- `userid` (relation): User enrolled in course
- `courseid` (relation): Course the user is enrolled in
- `completedChapters` (array): Chapters completed by the user
- `progress` (int): Overall progress percentage

### Puzzle Entity

- `id` (string): Unique identifier for the puzzle
- `fen` (string): FEN notation of the puzzle position
- `moves` (string): Puzzle solution in algebraic notation
- `rating` (int): Difficulty rating of the puzzle
- `ratingDeviation` (int): Statistical confidence in the rating
- `popularity` (int): Popularity score based on user feedback
- `nbPlays` (int): Number of times the puzzle has been played
- `themes` (relation): Chess themes associated with the puzzle
- `gameUrl` (string): URL to the original game
- `openingTags` (string): Opening classification

### Theme Entity

- `id` (int): Unique identifier
- `name` (string): Theme name (e.g., "middlegame", "fork", "mate in 2")

## Development Tools

### API Testing

The project includes a Python testing tool for verifying API functionality:

```
cd tools/puzzles/tests
python api_tester.py --test all
```

For more details on the testing tool, see [tools/puzzles/tests/README.md](tools/puzzles/tests/README.md).

### Migrations

When making schema changes, create and run migrations:

```
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

## Security

- API endpoints requiring authentication are protected using JWT
- Role-based access control for admin-only endpoints
- All requests to protected endpoints should include the JWT token in the Authorization header:
  ```
  Authorization: Bearer <your-token>
  ```

## Documentation

- Detailed API implementation guide: [docs/puzzle-api-implementation.md](docs/puzzle-api-implementation.md)
- Troubleshooting guide: [docs/api-troubleshooting.md](docs/api-troubleshooting.md)

## License

[Your License]

## Contributing

[Contribution guidelines] 