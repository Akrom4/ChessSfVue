<?php

namespace App\Command;

use App\Entity\Puzzle;
use App\Entity\Theme;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:migrate-themes',
    description: 'Migrate themes from string to separate table',
    hidden: false,
)]
class MigrateThemesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Starting themes migration');

        $conn = $this->entityManager->getConnection();
        
        // Step 1: Get all unique themes
        $io->section('Step 1: Collecting unique themes');
        $sql = "SELECT DISTINCT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(themes, ' ', n.n), ' ', -1)) as theme_name
                FROM puzzle
                CROSS JOIN (
                    SELECT a.N + b.N * 10 + 1 n
                    FROM (SELECT 0 as N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a
                    CROSS JOIN (SELECT 0 as N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b
                    ORDER BY n
                ) n
                WHERE n.n <= 1 + (LENGTH(themes) - LENGTH(REPLACE(themes, ' ', '')))
                AND themes IS NOT NULL
                AND TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(themes, ' ', n.n), ' ', -1)) != ''";
        
        $themes = $conn->fetchAllAssociative($sql);
        $uniqueThemes = array_unique(array_column($themes, 'theme_name'));
        
        $io->progressStart(count($uniqueThemes));
        
        // Create Theme entities
        $themeMap = [];
        foreach ($uniqueThemes as $themeName) {
            $theme = new Theme();
            $theme->setName($themeName);
            $this->entityManager->persist($theme);
            $themeMap[$themeName] = $theme;
            $io->progressAdvance();
        }
        
        $this->entityManager->flush();
        $io->progressFinish();
        $io->success(sprintf('Created %d unique themes', count($uniqueThemes)));

        // Step 2: Create puzzle-theme relationships
        $io->section('Step 2: Creating puzzle-theme relationships');
        $sql = "SELECT id, themes FROM puzzle WHERE themes IS NOT NULL";
        $puzzles = $conn->fetchAllAssociative($sql);
        
        $io->progressStart(count($puzzles));
        $batchSize = 1000;
        $batchCount = 0;

        foreach ($puzzles as $puzzleData) {
            $themeNames = explode(' ', $puzzleData['themes']);
            $themeNames = array_map('trim', $themeNames);
            $themeNames = array_filter($themeNames); // Remove empty values

            $puzzle = $this->entityManager->getRepository(Puzzle::class)->find($puzzleData['id']);
            if ($puzzle) {
                foreach ($themeNames as $themeName) {
                    if (!empty($themeName) && isset($themeMap[$themeName])) {
                        $puzzle->addTheme($themeMap[$themeName]);
                    }
                }
            }

            $io->progressAdvance();
            $batchCount++;

            if ($batchCount % $batchSize === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();
        $io->progressFinish();
        $io->success('Created puzzle-theme relationships');

        // Step 3: Verify the migration
        $io->section('Step 3: Verifying migration');
        $themeCount = $conn->fetchOne("SELECT COUNT(*) FROM theme");
        $puzzleThemeCount = $conn->fetchOne("SELECT COUNT(*) FROM puzzle_theme");
        $io->success(sprintf(
            'Migration completed. Created %d themes and %d puzzle-theme relationships.',
            $themeCount,
            $puzzleThemeCount
        ));

        return Command::SUCCESS;
    }
} 