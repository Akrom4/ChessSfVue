<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:show-csv-headers',
    description: 'Show headers from a CSV file'
)]
class ShowCsvHeadersCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the CSV file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');

        if (!file_exists($filePath)) {
            $io->error('File not found: ' . $filePath);
            return Command::FAILURE;
        }

        try {
            $handle = fopen($filePath, 'r');
            $headers = fgetcsv($handle);
            fclose($handle);

            if (!$headers) {
                $io->error('Could not read CSV headers');
                return Command::FAILURE;
            }

            $io->section('CSV Headers');
            
            // Display headers with their position
            foreach ($headers as $index => $header) {
                $io->writeln(sprintf(
                    '<info>%d</info>: %s',
                    $index + 1,
                    trim($header)
                ));
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Error reading CSV: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 