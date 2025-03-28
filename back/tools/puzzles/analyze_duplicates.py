import pandas as pd
from collections import Counter
import sys
from tqdm import tqdm

def analyze_duplicates(file_path):
    print("Analyzing CSV file for duplicate IDs...")
    
    # Read the CSV file in chunks
    chunks = pd.read_csv(file_path, chunksize=100000)
    total_rows = sum(1 for _ in open(file_path)) - 1  # Subtract header row
    print(f"Total rows in CSV: {total_rows}")
    
    # Track all IDs and their occurrences
    all_ids = []
    processed_rows = 0
    
    with tqdm(total=total_rows, desc="Reading CSV") as pbar:
        for chunk in chunks:
            # Convert IDs to strings and strip whitespace
            ids = chunk['PuzzleId'].astype(str).str.strip()
            all_ids.extend(ids.tolist())
            processed_rows += len(chunk)
            pbar.update(len(chunk))
    
    # Count occurrences of each ID (case-sensitive)
    id_counts = Counter(all_ids)
    
    # Count occurrences of each ID (case-insensitive)
    id_counts_lower = Counter(id.lower() for id in all_ids)
    
    # Find duplicates (IDs that appear more than once)
    duplicates = {id: count for id, count in id_counts.items() if count > 1}
    duplicates_lower = {id: count for id, count in id_counts_lower.items() if count > 1}
    
    # Print summary
    print("\nAnalysis Results:")
    print(f"Total unique IDs (case-sensitive): {len(id_counts)}")
    print(f"Total unique IDs (case-insensitive): {len(id_counts_lower)}")
    print(f"Total duplicate IDs (case-sensitive): {len(duplicates)}")
    print(f"Total duplicate IDs (case-insensitive): {len(duplicates_lower)}")
    
    # Check for potential case-sensitivity issues
    if len(id_counts) != len(id_counts_lower):
        print("\nPotential case-sensitivity issues found!")
        print(f"Difference in unique IDs: {len(id_counts) - len(id_counts_lower)}")
        
        # Find IDs that differ only by case
        id_lower_map = {}
        for id in all_ids:
            lower_id = id.lower()
            if lower_id not in id_lower_map:
                id_lower_map[lower_id] = set()
            id_lower_map[lower_id].add(id)
        
        case_variants = {lower_id: variants for lower_id, variants in id_lower_map.items() if len(variants) > 1}
        if case_variants:
            print("\nFound IDs with case variants:")
            for lower_id, variants in list(case_variants.items())[:10]:
                print(f"Lowercase ID: {lower_id}")
                print(f"Variants: {sorted(variants)}")
                print()
    
    # Save detailed analysis to file
    with open('duplicate_analysis.txt', 'w') as f:
        f.write("Detailed Duplicate Analysis\n")
        f.write("=========================\n\n")
        
        f.write("Case-Sensitive Analysis:\n")
        f.write("------------------------\n")
        f.write(f"Total unique IDs: {len(id_counts)}\n")
        f.write(f"Total duplicate IDs: {len(duplicates)}\n")
        if duplicates:
            f.write("\nTop 10 most duplicated IDs:\n")
            for id, count in sorted(duplicates.items(), key=lambda x: x[1], reverse=True)[:10]:
                f.write(f"ID: {id}, Occurrences: {count}\n")
        
        f.write("\nCase-Insensitive Analysis:\n")
        f.write("-------------------------\n")
        f.write(f"Total unique IDs: {len(id_counts_lower)}\n")
        f.write(f"Total duplicate IDs: {len(duplicates_lower)}\n")
        if duplicates_lower:
            f.write("\nTop 10 most duplicated IDs (case-insensitive):\n")
            for id, count in sorted(duplicates_lower.items(), key=lambda x: x[1], reverse=True)[:10]:
                f.write(f"ID: {id}, Occurrences: {count}\n")
        
        if case_variants:
            f.write("\nCase Variants Found:\n")
            f.write("-------------------\n")
            for lower_id, variants in sorted(case_variants.items()):
                f.write(f"Lowercase ID: {lower_id}\n")
                f.write(f"Variants: {sorted(variants)}\n")
                f.write("\n")
    
    print("\nDetailed analysis saved to 'duplicate_analysis.txt'")

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python analyze_duplicates.py <path_to_csv>")
        sys.exit(1)
    
    file_path = sys.argv[1]
    analyze_duplicates(file_path) 