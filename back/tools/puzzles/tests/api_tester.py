#!/usr/bin/env python3
"""
API Tester for Chess Puzzle API

This script tests the puzzle API endpoints and logs the results.
"""

import requests
import json
import logging
import time
import os
import argparse
from datetime import datetime
from config import (
    API_BASE_URL, 
    DEFAULT_MIN_RATING,
    DEFAULT_MAX_RATING,
    DEFAULT_THEMES,
    PERFORMANCE_TEST
)

# Setup logging directory
LOG_DIR = os.path.join(os.path.dirname(os.path.realpath(__file__)), 'logs')
os.makedirs(LOG_DIR, exist_ok=True)

# Configure logging
logging.basicConfig(
    filename=os.path.join(LOG_DIR, f'api_test_{datetime.now().strftime("%Y%m%d_%H%M%S")}.log'),
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)

# Add console handler to see logs in real-time
console = logging.StreamHandler()
console.setLevel(logging.INFO)
console.setFormatter(logging.Formatter('%(asctime)s - %(levelname)s - %(message)s'))
logging.getLogger('').addHandler(console)

def check_server_connectivity():
    """Check if the API server is reachable"""
    base_url = API_BASE_URL.split('/api')[0]  # Extract base URL without /api
    logging.info(f"Checking server connectivity at {base_url}")
    
    try:
        response = requests.get(base_url, timeout=5)
        logging.info(f"Server responded with status code: {response.status_code}")
        if response.status_code >= 200 and response.status_code < 500:
            logging.info("Server is reachable")
            return True
        else:
            logging.error(f"Server returned error status: {response.status_code}")
            return False
    except requests.exceptions.ConnectionError:
        logging.error(f"Could not connect to server at {base_url}")
        logging.error("Please check if the server is running and accessible")
        return False
    except Exception as e:
        logging.error(f"Error checking server connectivity: {str(e)}")
        return False

def test_random_puzzle(min_rating=DEFAULT_MIN_RATING, max_rating=DEFAULT_MAX_RATING, themes=None):
    """Test the random puzzle endpoint with specific rating range and optional themes."""
    url = f"{API_BASE_URL}/puzzles/random"
    params = {
        'min_rating': min_rating,
        'max_rating': max_rating
    }
    
    # Add themes if provided
    if themes:
        if isinstance(themes, list):
            params['themes'] = ','.join(themes)
        else:
            params['themes'] = themes
    
    # Build complete URL for logging
    query_string = '&'.join([f"{k}={v}" for k, v in params.items()])
    full_url = f"{url}?{query_string}"
    
    try:
        logging.info(f"Testing URL: {full_url}")
        logging.info(f"Testing random puzzle endpoint with params: {params}")
        start_time = time.time()
        response = requests.get(url, params=params, timeout=10)
        elapsed_time = time.time() - start_time
        
        logging.info(f"Response time: {elapsed_time:.2f} seconds")
        logging.info(f"Status code: {response.status_code}")
        
        if response.status_code == 200:
            puzzle = response.json()
            logging.info(f"Received puzzle ID: {puzzle.get('id')}")
            logging.info(f"Puzzle rating: {puzzle.get('rating')}")
            logging.info(f"Puzzle themes: {puzzle.get('themes', [])}")
            logging.info(f"Response data: {json.dumps(puzzle, indent=2)}")
            return puzzle
        else:
            logging.error(f"Error response: {response.text}")
            return None
    except requests.exceptions.ConnectionError as ce:
        logging.error(f"Connection error: {str(ce)}")
        logging.error(f"Could not connect to the server. Please check if the server is running at {API_BASE_URL}")
        return None
    except requests.exceptions.Timeout as te:
        logging.error(f"Timeout error: {str(te)}")
        logging.error("The request timed out. Server might be overloaded or not responding.")
        return None
    except Exception as e:
        logging.error(f"Exception occurred: {str(e)}")
        logging.error(f"Exception type: {type(e).__name__}")
        return None

def test_puzzles_by_themes(themes=DEFAULT_THEMES, min_rating=DEFAULT_MIN_RATING, 
                          max_rating=DEFAULT_MAX_RATING, page=1, limit=20):
    """Test the puzzles by themes endpoint."""
    url = f"{API_BASE_URL}/puzzles/by-themes"
    params = {
        'themes': ','.join(themes) if isinstance(themes, list) else themes,
        'min_rating': min_rating,
        'max_rating': max_rating,
        'page': page,
        'limit': limit
    }
    
    # Build complete URL for logging
    query_string = '&'.join([f"{k}={v}" for k, v in params.items()])
    full_url = f"{url}?{query_string}"
    
    try:
        logging.info(f"Testing URL: {full_url}")
        logging.info(f"Testing puzzles by themes endpoint with params: {params}")
        start_time = time.time()
        response = requests.get(url, params=params, timeout=10)
        elapsed_time = time.time() - start_time
        
        logging.info(f"Response time: {elapsed_time:.2f} seconds")
        logging.info(f"Status code: {response.status_code}")
        
        if response.status_code == 200:
            data = response.json()
            logging.info(f"Total puzzles: {data.get('total')}")
            logging.info(f"Number of items: {len(data.get('items', []))}")
            
            # Log first puzzle details (if available)
            items = data.get('items', [])
            if items:
                first_puzzle = items[0]
                logging.info(f"First puzzle ID: {first_puzzle.get('id')}")
                logging.info(f"First puzzle rating: {first_puzzle.get('rating')}")
                
            # Save full response to separate JSON file for detailed inspection
            json_file = os.path.join(LOG_DIR, f'themes_response_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json')
            with open(json_file, 'w') as f:
                json.dump(data, f, indent=2)
            logging.info(f"Full response saved to {json_file}")
            
            return data
        else:
            logging.error(f"Error response: {response.text}")
            return None
    except requests.exceptions.ConnectionError as ce:
        logging.error(f"Connection error: {str(ce)}")
        logging.error(f"Could not connect to the server. Please check if the server is running at {API_BASE_URL}")
        return None
    except requests.exceptions.Timeout as te:
        logging.error(f"Timeout error: {str(te)}")
        logging.error("The request timed out. Server might be overloaded or not responding.")
        return None
    except Exception as e:
        logging.error(f"Exception occurred: {str(e)}")
        logging.error(f"Exception type: {type(e).__name__}")
        return None

def run_performance_test(endpoint='random', iterations=10, **params):
    """Run performance test on an endpoint by making multiple requests."""
    logging.info(f"Starting performance test on {endpoint} endpoint with {iterations} iterations")
    
    total_time = 0
    successful = 0
    
    for i in range(iterations):
        logging.info(f"Performance test iteration {i+1}/{iterations}")
        
        start_time = time.time()
        if endpoint == 'random':
            # Extract themes from params if it exists
            themes = params.pop('themes', None)
            result = test_random_puzzle(themes=themes, **params)
        elif endpoint == 'by-themes':
            result = test_puzzles_by_themes(**params)
        else:
            logging.error(f"Unknown endpoint: {endpoint}")
            return
            
        elapsed_time = time.time() - start_time
        
        if result:
            successful += 1
            total_time += elapsed_time
    
    avg_time = total_time / iterations if iterations > 0 else 0
    success_rate = (successful / iterations) * 100 if iterations > 0 else 0
    
    logging.info(f"Performance test completed:")
    logging.info(f"  - Average response time: {avg_time:.2f} seconds")
    logging.info(f"  - Success rate: {success_rate:.1f}%")
    logging.info(f"  - Successful requests: {successful}/{iterations}")
    
    return {
        'endpoint': endpoint,
        'iterations': iterations,
        'avg_time': avg_time,
        'success_rate': success_rate,
        'successful': successful
    }

def check_individual_puzzle(puzzle_id):
    """Test retrieving a single puzzle by ID."""
    url = f"{API_BASE_URL}/puzzles/{puzzle_id}"
    
    try:
        logging.info(f"Testing URL: {url}")
        logging.info(f"Testing individual puzzle endpoint for puzzle ID: {puzzle_id}")
        start_time = time.time()
        response = requests.get(url, timeout=10)
        elapsed_time = time.time() - start_time
        
        logging.info(f"Response time: {elapsed_time:.2f} seconds")
        logging.info(f"Status code: {response.status_code}")
        
        if response.status_code == 200:
            puzzle = response.json()
            logging.info(f"Received puzzle ID: {puzzle.get('id')}")
            logging.info(f"Puzzle rating: {puzzle.get('rating')}")
            logging.info(f"Puzzle themes: {puzzle.get('themes', [])}")
            logging.info(f"Response data: {json.dumps(puzzle, indent=2)}")
            return puzzle
        else:
            logging.error(f"Error response: {response.text}")
            return None
    except requests.exceptions.ConnectionError as ce:
        logging.error(f"Connection error: {str(ce)}")
        logging.error(f"Could not connect to the server. Please check if the server is running at {API_BASE_URL}")
        return None
    except requests.exceptions.Timeout as te:
        logging.error(f"Timeout error: {str(te)}")
        logging.error("The request timed out. Server might be overloaded or not responding.")
        return None
    except Exception as e:
        logging.error(f"Exception occurred: {str(e)}")
        logging.error(f"Exception type: {type(e).__name__}")
        return None

def parse_args():
    """Parse command line arguments."""
    parser = argparse.ArgumentParser(description='Test Chess Puzzle API endpoints')
    parser.add_argument('--test', choices=['random', 'themes', 'puzzle', 'perf-random', 'perf-themes', 'all'],
                       default='all', help='Which test to run (default: all)')
    parser.add_argument('--min-rating', type=int, default=DEFAULT_MIN_RATING,
                       help=f'Minimum rating (default: {DEFAULT_MIN_RATING})')
    parser.add_argument('--max-rating', type=int, default=DEFAULT_MAX_RATING,
                       help=f'Maximum rating (default: {DEFAULT_MAX_RATING})')
    parser.add_argument('--themes', type=str, default=','.join(DEFAULT_THEMES),
                       help=f'Comma-separated themes (default: {",".join(DEFAULT_THEMES)})')
    parser.add_argument('--iterations', type=int, default=5,
                       help='Number of iterations for performance tests (default: 5)')
    parser.add_argument('--puzzle-id', type=str, 
                       help='Puzzle ID to test individual puzzle endpoint')
    
    return parser.parse_args()

if __name__ == "__main__":
    args = parse_args()
    logging.info("===== API Tester Starting =====")
    
    # Check server connectivity first
    if not check_server_connectivity():
        logging.error("Server connectivity check failed. Tests may not succeed.")
        logging.error(f"Please verify the server is running at {API_BASE_URL}")
        logging.error("You can change the API_BASE_URL in config.py if needed.")
    
    # Parse themes from comma-separated string
    themes = [t.strip() for t in args.themes.split(',') if t.strip()]
    
    run_all = args.test == 'all'
    
    # Test 1: Basic random puzzle test
    if run_all or args.test == 'random':
        logging.info("\n----- Test 1: Random Puzzle -----")
        test_random_puzzle(args.min_rating, args.max_rating, themes)
    
    # Test 2: Puzzles by themes
    if run_all or args.test == 'themes':
        logging.info("\n----- Test 2: Puzzles by Themes -----")
        test_puzzles_by_themes(themes, args.min_rating, args.max_rating)
    
    # Test 3: Individual puzzle by ID
    if (run_all and args.puzzle_id) or args.test == 'puzzle':
        if args.puzzle_id:
            logging.info("\n----- Test 3: Individual Puzzle -----")
            check_individual_puzzle(args.puzzle_id)
        else:
            logging.warning("Skipping individual puzzle test: No puzzle ID provided")
            logging.info("To test individual puzzle endpoint, use --puzzle-id parameter")
    
    # Test 4: Performance test on random puzzle endpoint
    if run_all or args.test == 'perf-random':
        logging.info("\n----- Test 4: Performance Test (Random Puzzle) -----")
        perf_params = PERFORMANCE_TEST['random'].copy()
        perf_params['iterations'] = args.iterations
        run_performance_test('random', **perf_params)
    
    # Test 5: Performance test on puzzles by themes endpoint
    if run_all or args.test == 'perf-themes':
        logging.info("\n----- Test 5: Performance Test (Puzzles by Themes) -----")
        perf_params = PERFORMANCE_TEST['by-themes'].copy()
        perf_params['iterations'] = args.iterations
        run_performance_test('by-themes', **perf_params)
    
    logging.info("===== API Tester Completed =====") 