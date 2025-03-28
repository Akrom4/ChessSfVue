"""
Configuration file for the Chess Puzzle API Tester
"""

# API Base URL - Change this based on your environment
# Try different options based on your server configuration
API_BASE_URL = "http://127.0.0.1:8000/api"  # Default local API URL

# Uncomment one of these if the default doesn't work
# API_BASE_URL = "http://localhost:8000/api"
# API_BASE_URL = "http://localhost/api"
# API_BASE_URL = "http://127.0.0.1/api"

# Default test parameters
DEFAULT_MIN_RATING = 1200
DEFAULT_MAX_RATING = 1800
DEFAULT_THEMES = ["fork", "deflection", "equality"]

# Performance test settings
PERFORMANCE_TEST = {
    "random": {
        "iterations": 5,
        "min_rating": 1200,
        "max_rating": 1800,
        "themes": ["fork", "deflection", "equality"]  # Optional themes for random puzzle test
    },
    "by-themes": {
        "iterations": 3,
        "themes": ["endgame"],
        "min_rating": 1200,
        "max_rating": 1800,
        "page": 1,
        "limit": 20
    }
} 