import pandas as pd
import mysql.connector
from mysql.connector import Error
import sys
from tqdm import tqdm
import time

def create_connection():
    try:
        connection = mysql.connector.connect(
            host='localhost',
            database='eptrainingapp',
            user='root',
            password='',
            buffered=True,
            pool_size=10,
            pool_name="mypool",
            pool_reset_session=True
        )
        return connection
    except Error as e:
        print(f"Error connecting to MySQL: {e}")
        sys.exit(1)

def migrate_themes():
    start_time = time.time()
    # Create database connection
    connection = create_connection()
    cursor = connection.cursor()

    try:
        # Verify tables exist
        cursor.execute("SHOW TABLES LIKE 'theme'")
        theme_exists = cursor.fetchone() is not None
        
        cursor.execute("SHOW TABLES LIKE 'puzzle_theme'")
        puzzle_theme_exists = cursor.fetchone() is not None
        
        if not theme_exists or not puzzle_theme_exists:
            print("Error: theme or puzzle_theme table does not exist")
            return
        
        print("Tables verified. Starting migration...")

        # Step 1: Get all unique themes
        print("\nStep 1: Collecting unique themes")
        # Use a separate cursor for this large query
        theme_cursor = connection.cursor()
        theme_cursor.execute("SELECT DISTINCT themes FROM puzzle WHERE themes IS NOT NULL AND themes != ''")
        
        # Extract unique themes
        all_themes = set()
        for (themes,) in theme_cursor:
            if themes:
                theme_list = [t.strip() for t in themes.split() if t.strip()]
                all_themes.update(theme_list)
        
        theme_cursor.close()
        
        # Create theme entries
        theme_map = {}
        print(f"Found {len(all_themes)} unique themes")
        
        theme_batch_size = 1000
        theme_batches = [list(all_themes)[i:i + theme_batch_size] for i in range(0, len(all_themes), theme_batch_size)]
        
        for batch in tqdm(theme_batches, desc="Creating theme entries"):
            # Prepare values for batch insert
            values_list = [(name,) for name in batch]
            
            # Insert themes in batch
            cursor.executemany("INSERT IGNORE INTO theme (name) VALUES (%s)", values_list)
            connection.commit()
        
        # Get all themes and their IDs in one query
        cursor.execute("SELECT id, name FROM theme")
        for theme_id, theme_name in cursor.fetchall():
            theme_map[theme_name] = theme_id
            
        print(f"Loaded {len(theme_map)} themes from database")

        # Step 2: Create puzzle-theme relationships in batches
        print("\nStep 2: Creating puzzle-theme relationships")
        
        # Disable keys for faster inserts
        cursor.execute('SET FOREIGN_KEY_CHECKS=0')
        cursor.execute('SET UNIQUE_CHECKS=0')
        cursor.execute('SET autocommit=0')
        
        # Process puzzles in batches to avoid memory issues
        batch_size = 10000
        offset = 0
        total_processed = 0
        total_relationships = 0
        
        # Get total count for progress bar
        cursor.execute("SELECT COUNT(*) FROM puzzle WHERE themes IS NOT NULL AND themes != ''")
        total_puzzles = cursor.fetchone()[0]
        
        with tqdm(total=total_puzzles, desc="Processing puzzles") as pbar:
            while True:
                # Get batch of puzzles
                cursor.execute(
                    "SELECT id, themes FROM puzzle WHERE themes IS NOT NULL AND themes != '' LIMIT %s OFFSET %s", 
                    (batch_size, offset)
                )
                puzzles = cursor.fetchall()
                
                if not puzzles:
                    break  # No more puzzles to process
                
                # Process batch
                relations_batch = []
                for puzzle_id, themes in puzzles:
                    if themes:
                        theme_list = [t.strip() for t in themes.split() if t.strip()]
                        for theme_name in theme_list:
                            if theme_name in theme_map:
                                relations_batch.append((puzzle_id, theme_map[theme_name]))
                
                # Insert relations in sub-batches to avoid too large queries
                relation_batch_size = 5000
                for i in range(0, len(relations_batch), relation_batch_size):
                    sub_batch = relations_batch[i:i + relation_batch_size]
                    cursor.executemany(
                        "INSERT IGNORE INTO puzzle_theme (puzzle_id, theme_id) VALUES (%s, %s)",
                        sub_batch
                    )
                    total_relationships += len(sub_batch)
                
                connection.commit()
                
                # Update progress
                processed = len(puzzles)
                total_processed += processed
                pbar.update(processed)
                
                # Print progress info
                if total_processed % 100000 == 0:
                    elapsed = time.time() - start_time
                    relations_per_sec = total_relationships / elapsed if elapsed > 0 else 0
                    print(f"\nProcessed {total_processed:,}/{total_puzzles:,} puzzles, "
                          f"Created {total_relationships:,} relationships, "
                          f"Rate: {relations_per_sec:.2f} relations/sec")
                
                # Move to next batch
                offset += batch_size
        
        # Re-enable keys
        cursor.execute('SET FOREIGN_KEY_CHECKS=1')
        cursor.execute('SET UNIQUE_CHECKS=1')
        cursor.execute('SET autocommit=1')

        # Step 3: Verify the migration
        print("\nStep 3: Verifying migration")
        cursor.execute("SELECT COUNT(*) FROM theme")
        theme_count = cursor.fetchone()[0]
        cursor.execute("SELECT COUNT(*) FROM puzzle_theme")
        puzzle_theme_count = cursor.fetchone()[0]
        
        elapsed = time.time() - start_time
        print(f"Created {theme_count:,} themes and {puzzle_theme_count:,} puzzle-theme relationships")
        print(f"Total time: {elapsed:.2f} seconds ({elapsed/60:.2f} minutes)")
        print(f"Average rate: {puzzle_theme_count/elapsed:.2f} relationships per second")

        connection.commit()

    except Error as e:
        print(f"Error during migration: {e}")
        connection.rollback()
        sys.exit(1)

    finally:
        cursor.close()
        connection.close()

if __name__ == "__main__":
    migrate_themes() 