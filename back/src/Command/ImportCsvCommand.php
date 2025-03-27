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
    name: 'app:import-csv',
    description: 'Import data from a CSV file into the database'
)]
class ImportCsvCommand extends Command
{
    private $connection;

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');
        $tableName = $input->getArgument('table');

        if (!file_exists($filePath)) {
            $io->error('File not found: ' . $filePath);
            return Command::FAILURE;
        }

        try {
            // Get the first line of the CSV to determine columns
            $handle = fopen($filePath, 'r');
            $headers = fgetcsv($handle);
            fclose($handle);

            if (!$headers) {
                $io->error('Could not read CSV headers');
                return Command::FAILURE;
            }

            // Prepare the LOAD DATA INFILE query
            $columns = implode(',', array_map(function($header) {
                return '`' . trim($header) . '`';
            }, $headers));

            $sql = <<<SQL
                LOAD DATA INFILE :filepath
                INTO TABLE $tableName
                FIELDS TERMINATED BY ','
                ENCLOSED BY '"'
                LINES TERMINATED BY '\n'
                IGNORE 1 LINES
                ($columns)
            SQL;

            // Execute the query
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue('filepath', $filePath);
            $stmt->executeStatement();

            $io->success('Data imported successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Error importing data: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 