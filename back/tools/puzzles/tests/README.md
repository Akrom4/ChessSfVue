# Chess Puzzle API Tester

This tool tests the Chess Puzzle API endpoints and logs results for analysis.

## Setup

1. Make sure you have Python 3.6+ installed
2. Install required packages:
   ```
   pip install requests
   ```
3. Make sure the API server is running

## Configuration

All configuration is stored in `config.py`:

- `API_BASE_URL`: Base URL for the API (default: http://127.0.0.1:8000/api)
- `DEFAULT_MIN_RATING`: Default minimum puzzle rating (default: 1200)
- `DEFAULT_MAX_RATING`: Default maximum puzzle rating (default: 1800)
- `DEFAULT_THEMES`: Default themes for testing
- `PERFORMANCE_TEST`: Settings for performance tests

## Usage

Run all tests with default settings:
```
python api_tester.py
```

Run specific tests:
```
# Test only the random puzzle endpoint
python api_tester.py --test random

# Test only the puzzles by themes endpoint
python api_tester.py --test themes

# Test retrieving an individual puzzle by ID
python api_tester.py --test puzzle --puzzle-id YOUR_PUZZLE_ID

# Run performance test on the random puzzle endpoint
python api_tester.py --test perf-random

# Run performance test on the puzzles by themes endpoint
python api_tester.py --test perf-themes
```

Customize test parameters:
```
# Test with custom rating range
python api_tester.py --min-rating 800 --max-rating 1400

# Test with specific themes
python api_tester.py --themes "middlegame,endgame,crushing"

# Test random puzzle with specific themes
python api_tester.py --test random --themes "middlegame,crushing"

# Run performance tests with more iterations
python api_tester.py --iterations 10
```

## Results

- Logs are saved in the `logs/` directory with timestamps
- Full JSON responses from the themes endpoint are also saved in the `logs/` directory
- Results are displayed in the console in real-time

## Example Output

```
2023-05-30 15:22:34,123 - INFO - ===== API Tester Starting =====
2023-05-30 15:22:34,124 - INFO - ----- Test 1: Random Puzzle -----
2023-05-30 15:22:34,125 - INFO - Testing random puzzle endpoint with params: {'min_rating': 1200, 'max_rating': 1800}
2023-05-30 15:22:34,789 - INFO - Response time: 0.66 seconds
2023-05-30 15:22:34,790 - INFO - Status code: 200
2023-05-30 15:22:34,792 - INFO - Received puzzle ID: abc123
2023-05-30 15:22:34,793 - INFO - Puzzle rating: 1500
...
``` 