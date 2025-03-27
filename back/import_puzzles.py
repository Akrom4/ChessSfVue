import pandas as pd
import mysql.connector
from mysql.connector import Error
import sys
from tqdm import tqdm

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

def import_csv(file_path, batch_size=1000):
    # Create database connection
    connection = create_connection()
    cursor = connection.cursor()

    try:
        # Check initial count
        cursor.execute("SELECT COUNT(*) FROM puzzle")
        initial_count = cursor.fetchone()[0]
        print(f"Initial count in puzzle table: {initial_count}")

        # Disable foreign key checks and unique checks
        cursor.execute('SET FOREIGN_KEY_CHECKS=0')
        cursor.execute('SET UNIQUE_CHECKS=0')
        cursor.execute('SET autocommit=0')

        # Read CSV in chunks
        chunks = pd.read_csv(file_path, chunksize=batch_size)
        total_rows = sum(1 for _ in open(file_path)) - 1  # Subtract header row
        print(f"Total rows in CSV: {total_rows}")

        # Process each chunk
        processed_rows = 0
        skipped_rows = 0
        duplicate_ids = set()
        invalid_data = []
        skipped_ids = set()  # Track skipped IDs
        
        with tqdm(total=total_rows, desc="Importing puzzles") as pbar:
            for chunk in chunks:
                # Prepare data for insertion
                data = chunk.rename(columns={
                    'PuzzleId': 'id',
                    'FEN': 'fen',
                    'Moves': 'moves',
                    'Rating': 'rating',
                    'RatingDeviation': 'rating_deviation',
                    'Popularity': 'popularity',
                    'NbPlays': 'nb_plays',
                    'Themes': 'themes',
                    'GameUrl': 'game_url',
                    'OpeningTags': 'opening_tags'
                })

                # Force ID to be string and preserve exact format
                data['id'] = data['id'].apply(lambda x: str(x).strip())

                # Check for duplicate IDs in current chunk
                duplicates = data[data.duplicated(subset=['id'], keep=False)]
                if not duplicates.empty:
                    duplicate_ids.update(duplicates['id'].tolist())
                    if len(duplicate_ids) <= 5:  # Only print first 5 duplicates
                        print(f"\nFound duplicate IDs: {duplicates['id'].head().tolist()}")

                # Check for invalid data
                invalid_rows = data[
                    (data['fen'].isna()) |
                    (data['moves'].isna()) |
                    (data['rating'].isna()) |
                    (data['rating_deviation'].isna()) |
                    (data['popularity'].isna()) |
                    (data['nb_plays'].isna()) |
                    (data['themes'].isna())
                ]
                if not invalid_rows.empty:
                    invalid_data.extend(invalid_rows['id'].tolist())
                    if len(invalid_data) <= 5:  # Only print first 5 invalid rows
                        print(f"\nFound invalid data in rows: {invalid_rows['id'].head().tolist()}")

                # Prepare SQL query
                columns = ', '.join(data.columns)
                placeholders = ', '.join(['%s'] * len(data.columns))
                query = f"INSERT IGNORE INTO puzzle ({columns}) VALUES ({placeholders})"

                # Insert data
                values = [tuple(x) for x in data.values]
                try:
                    cursor.executemany(query, values)
                    affected_rows = cursor.rowcount
                    skipped_in_batch = len(values) - affected_rows
                    skipped_rows += skipped_in_batch
                    
                    # Track skipped IDs
                    if skipped_in_batch > 0:
                        # Get IDs that were skipped
                        cursor.execute("SELECT id FROM puzzle WHERE id IN (" + ",".join(["%s"] * len(values)) + ")", [v[0] for v in values])
                        existing_ids = {row[0] for row in cursor.fetchall()}
                        skipped_ids.update(v[0] for v in values if v[0] not in existing_ids)
                        
                        if len(skipped_ids) <= 5:  # Only print first 5 skipped IDs
                            print(f"\nSample of skipped IDs: {list(skipped_ids)[:5]}")
                except Error as e:
                    print(f"\nError inserting batch: {e}")
                    print("First problematic row:", values[0])

                connection.commit()

                # Update progress
                processed_rows += len(chunk)
                pbar.update(len(chunk))

                # Print progress every 100k rows
                if processed_rows % 100000 == 0:
                    print(f"\nProcessed: {processed_rows}, Skipped: {skipped_rows}")
                    print(f"Unique duplicate IDs found: {len(duplicate_ids)}")
                    print(f"Invalid data rows found: {len(invalid_data)}")
                    print(f"Unique skipped IDs: {len(skipped_ids)}")

        # Check final count
        cursor.execute("SELECT COUNT(*) FROM puzzle")
        final_count = cursor.fetchone()[0]
        print(f"\nFinal count in puzzle table: {final_count}")
        print(f"Rows added: {final_count - initial_count}")
        print(f"Rows processed from CSV: {processed_rows}")
        print(f"Rows skipped: {skipped_rows}")
        print(f"Total unique duplicate IDs: {len(duplicate_ids)}")
        print(f"Total invalid data rows: {len(invalid_data)}")
        print(f"Total unique skipped IDs: {len(skipped_ids)}")
        if duplicate_ids:
            print("Sample of duplicate IDs:", list(duplicate_ids)[:5])
        if invalid_data:
            print("Sample of invalid data IDs:", invalid_data[:5])
        if skipped_ids:
            print("Sample of skipped IDs:", list(skipped_ids)[:5])

    except Error as e:
        print(f"Error during import: {e}")
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
        print("Usage: python import_puzzles.py <path_to_csv>")
        sys.exit(1)
    
    file_path = sys.argv[1]
    import_csv(file_path) 