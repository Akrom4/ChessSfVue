<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-csv-alternative',
    description: 'Import data from a CSV file into the database using batch inserts'
)]
class ImportCsvAlternativeCommand extends Command
{
    private $connection;
    // Reduce batch size further
    private const BATCH_SIZE = 10;
    // Commit more frequently
    private const COMMIT_EVERY = 10;

    // Column mapping from CSV headers to database columns
    private const COLUMN_MAP = [
        'PuzzleId' => 'id',
        'FEN' => 'fen',
        'Moves' => 'moves',
        'Rating' => 'rating',
        'RatingDeviation' => 'rating_deviation',
        'Popularity' => 'popularity',
        'NbPlays' => 'nb_plays',
        'Themes' => 'themes',
        'GameUrl' => 'game_url',
        'OpeningTags' => 'opening_tags'
    ];

    public function __construct(Connection $connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the CSV file')
            ->addArgument('table', InputArgument::REQUIRED, 'Target table name');
    }

    private function clearMemory(): void
    {
        // Close any open transactions
        if ($this->connection->isTransactionActive()) {
            $this->connection->commit();
        }
        
        // Clear PHP's memory
        gc_collect_cycles();
        
        // Force garbage collection
        if (function_exists('gc_mem_caches')) {
            gc_mem_caches();
        }
        
        // Clear memory usage
        if (function_exists('memory_get_usage')) {
            $memoryUsage = memory_get_usage(true);
            if ($memoryUsage > 500 * 1024 * 1024) { // If memory usage is over 500MB
                gc_collect_cycles();
                if (function_exists('gc_mem_caches')) {
                    gc_mem_caches();
                }
            }
        }
        
        // Start fresh transaction
        $this->connection->beginTransaction();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Increase memory limit and execution time
        ini_set('memory_limit', '2G');
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');
        $tableName = $input->getArgument('table');

        if (!file_exists($filePath)) {
            $io->error('File not found: ' . $filePath);
            return Command::FAILURE;
        }

        try {
            // First, ask if user wants to clear existing data
            if ($io->confirm('Do you want to clear existing data from the table before importing?', false)) {
                $this->connection->executeStatement("TRUNCATE TABLE $tableName");
                $io->note('Table cleared.');
            }

            // Get total number of lines for progress bar
            $totalLines = $this->countLines($filePath) - 1; // -1 for header
            $io->note(sprintf('Found %d lines to process', $totalLines));

            // Disable foreign key checks and unique checks for faster inserts
            $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
            $this->connection->executeStatement('SET UNIQUE_CHECKS=0');
            $this->connection->executeStatement('SET autocommit=0');

            $handle = fopen($filePath, 'r');
            $headers = fgetcsv($handle);
            
            if (!$headers) {
                $io->error('Could not read CSV headers');
                fclose($handle);
                return Command::FAILURE;
            }

            // Map CSV headers to database columns
            $columnIndexes = [];
            $dbColumns = [];
            foreach ($headers as $index => $header) {
                if (isset(self::COLUMN_MAP[$header])) {
                    $columnIndexes[] = $index;
                    $dbColumns[] = self::COLUMN_MAP[$header];
                }
            }

            // Prepare the multi-value insert query
            $rowPlaceholders = '(' . implode(',', array_fill(0, count($dbColumns), '?')) . ')';
            $baseQuery = sprintf(
                'INSERT IGNORE INTO %s (%s) VALUES ',
                $tableName,
                implode(',', $dbColumns)
            );

            $rowCount = 0;
            $batchCount = 0;
            $batch = [];
            $lastProgressUpdate = 0;

            $io->progressStart($totalLines);
            $this->connection->beginTransaction();

            while (($data = fgetcsv($handle)) !== FALSE) {
                // Map only the columns we want
                $mappedData = [];
                foreach ($columnIndexes as $index) {
                    $value = $data[$index] ?? null;
                    // Ensure ID is treated as string
                    if ($index === array_search('id', $dbColumns)) {
                        $value = (string)$value;
                    }
                    $mappedData[] = $value;
                }
                $batch[] = $mappedData;
                $rowCount++;

                if (count($batch) >= self::BATCH_SIZE) {
                    try {
                        $this->insertBatchMultiValue($baseQuery, $rowPlaceholders, $batch);
                        $batchCount++;

                        // Commit and clear memory every X batches
                        if ($batchCount % self::COMMIT_EVERY === 0) {
                            $this->clearMemory();
                            
                            // Update progress
                            if ($rowCount - $lastProgressUpdate >= 1000) {
                                $io->progressAdvance($rowCount - $lastProgressUpdate);
                                $lastProgressUpdate = $rowCount;
                            }
                        }
                    } catch (\Exception $e) {
                        $io->error(sprintf('Error at row %d: %s', $rowCount, $e->getMessage()));
                        continue;
                    }
                    $batch = [];
                }

                // Force PHP to clean up memory periodically
                if ($rowCount % 50000 === 0) {
                    $this->clearMemory();
                }
            }

            // Insert remaining rows
            if (!empty($batch)) {
                try {
                    $this->insertBatchMultiValue($baseQuery, $rowPlaceholders, $batch);
                } catch (\Exception $e) {
                    $io->error(sprintf('Error in final batch: %s', $e->getMessage()));
                }
                $io->progressAdvance($rowCount - $lastProgressUpdate);
            }

            // Final commit
            if ($this->connection->isTransactionActive()) {
                $this->connection->commit();
            }

            // Re-enable checks
            $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
            $this->connection->executeStatement('SET UNIQUE_CHECKS=1');
            $this->connection->executeStatement('SET autocommit=1');

            fclose($handle);
            $io->progressFinish();

            // Final memory cleanup
            gc_collect_cycles();
            if (function_exists('gc_mem_caches')) {
                gc_mem_caches();
            }

            $io->success(sprintf(
                'Import completed: %d total rows processed',
                $rowCount
            ));
            return Command::SUCCESS;

        } catch (\Exception $e) {
            if (isset($handle)) {
                fclose($handle);
            }
            // Re-enable checks in case of error
            $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
            $this->connection->executeStatement('SET UNIQUE_CHECKS=1');
            $this->connection->executeStatement('SET autocommit=1');
            
            $io->error('Error importing data: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function insertBatchMultiValue(string $baseQuery, string $rowPlaceholders, array $batch): void
    {
        // Create multi-value query
        $values = [];
        $params = [];
        
        foreach ($batch as $row) {
            $values[] = $rowPlaceholders;
            foreach ($row as $value) {
                $params[] = $value;
            }
        }
        
        $sql = $baseQuery . implode(',', $values);
        
        try {
            $this->connection->executeStatement($sql, $params);
        } catch (\Exception $e) {
            throw new \Exception('Database error: ' . $e->getMessage());
        }
    }

    private function countLines(string $file): int
    {
        $f = fopen($file, 'rb');
        $lines = 0;
        $buffer = 16384; // Read in chunks

        while (!feof($f)) {
            $lines += substr_count(fread($f, $buffer), "\n");
        }

        fclose($f);
        return $lines;
    }
} 