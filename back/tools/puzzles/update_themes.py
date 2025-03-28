import pandas as pd
import mysql.connector
from mysql.connector import Error
import sys
from tqdm import tqdm
import warnings
from collections import defaultdict

# Silence pandas warnings
warnings.simplefilter(action='ignore', category=FutureWarning)
warnings.simplefilter(action='ignore', category=UserWarning)
pd.options.mode.chained_assignment = None  # default='warn'

def create_connection():
    try:
        connection = mysql.connector.connect(
            host='localhost',
            database='eptrainingapp',
            user='root',
            password=''
        )
        return connection
    except Error as e:
        print(f"Error connecting to MySQL: {e}")
        sys.exit(1)

def update_themes(file_path, batch_size=5000):
    # Create database connection
    connection = create_connection()
    cursor = connection.cursor()

    try:
        # Check total count in database
        cursor.execute("SELECT COUNT(*) FROM puzzle")
        db_count = cursor.fetchone()[0]
        print(f"Total puzzle count in database: {db_count}")

        # Disable checks for performance
        cursor.execute('SET FOREIGN_KEY_CHECKS=0')
        cursor.execute('SET UNIQUE_CHECKS=0')
        cursor.execute('SET autocommit=0')

        # Read CSV in chunks to manage memory
        chunks = pd.read_csv(file_path, chunksize=batch_size)
        total_rows = sum(1 for _ in open(file_path)) - 1  # Subtract header row
        print(f"Total rows in CSV: {total_rows}")

        # We'll first create a case-insensitive mapping of all puzzle IDs in the database
        print("Building ID mapping...")
        cursor.execute("SELECT id FROM puzzle")
        db_ids = cursor.fetchall()
        
        # Check if there are IDs that differ only by case
        case_variants = defaultdict(list)
        for row in db_ids:
            case_variants[row[0].lower()].append(row[0])
        
        multi_case_ids = {k: v for k, v in case_variants.items() if len(v) > 1}
        if multi_case_ids:
            print(f"Found {len(multi_case_ids)} IDs with multiple case variants!")
            print("Examples:")
            for k, v in list(multi_case_ids.items())[:5]:
                print(f"  {k}: {v}")
        
        # Create a dictionary to map lowercase IDs to their actual case in the database
        # If multiple cases exist, use the first one for updates
        id_mapping = {k.lower(): v[0] for k, v in case_variants.items()}
        print(f"Found {len(id_mapping)} unique case-insensitive puzzle IDs in database")
        
        # Track progress
        processed_rows = 0
        updated_rows = 0
        not_found = 0
        
        # Prepare batched update
        update_query = "UPDATE puzzle SET themes = %s WHERE id = %s"
        
        with tqdm(total=total_rows, desc="Updating themes") as pbar:
            for chunk in chunks:
                # Extract only puzzle ID and themes
                data = chunk[['PuzzleId', 'Themes']]
                
                # Force ID to be string and preserve exact format
                data['PuzzleId'] = data['PuzzleId'].apply(lambda x: str(x).strip())
                
                # Batch updates for performance
                batch_updates = []
                skipped_ids = []
                
                for _, row in data.iterrows():
                    if pd.notna(row['Themes']):
                        # Use lowercase lookup to find the actual ID
                        csv_id_lower = row['PuzzleId'].lower()
                        if csv_id_lower in id_mapping:
                            # Use the actual ID from the database with correct case
                            db_id = id_mapping[csv_id_lower]
                            batch_updates.append((row['Themes'], db_id))
                        else:
                            skipped_ids.append(row['PuzzleId'])
                
                # Execute batch update
                if batch_updates:
                    cursor.executemany(update_query, batch_updates)
                    updated_in_batch = cursor.rowcount
                    updated_rows += updated_in_batch
                    not_found += len(skipped_ids)
                
                # Commit every batch
                connection.commit()
                
                # Update progress
                processed_rows += len(chunk)
                pbar.update(len(chunk))
                
                # Print progress every 100k rows
                if processed_rows % 100000 == 0:
                    print(f"\nProcessed: {processed_rows}, Updated: {updated_rows}, Not found: {not_found}")
                    if skipped_ids and len(skipped_ids) <= 5:
                        print(f"Sample skipped IDs: {skipped_ids}")

        print(f"\nFinal statistics:")
        print(f"Total rows processed: {processed_rows}")
        print(f"Total puzzles updated: {updated_rows}")
        print(f"Puzzles not found: {not_found}")

    except Error as e:
        print(f"Error during theme update: {e}")
        connection.rollback()
        sys.exit(1)

    finally:
        # Re-enable checks
        cursor.execute('SET FOREIGN_KEY_CHECKS=1')
        cursor.execute('SET UNIQUE_CHECKS=1')
        cursor.execute('SET autocommit=1')
        
        # Close connections
        cursor.close()
        connection.close()

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python update_themes.py <path_to_csv>")
        sys.exit(1)
    
    file_path = sys.argv[1]
    update_themes(file_path) 