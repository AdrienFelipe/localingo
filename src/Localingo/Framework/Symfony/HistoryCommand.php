<?php

declare(strict_types=1);

namespace App\Localingo\Framework\Symfony;

use App\Localingo\Domain\History\HistoryEntry;
use App\Localingo\Domain\History\HistoryFileInterface;
use App\Localingo\Domain\User\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:history', description: 'Review the exercises that were asked')]
class HistoryCommand extends Command
{
    private HistoryFileInterface $history;

    public function __construct(HistoryFileInterface $history)
    {
        parent::__construct();
        $this->history = $history;
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'How many entries to read', '50')
            ->addOption('user', 'u', InputOption::VALUE_REQUIRED, 'Whose history to read', 'anonymous')
            ->addOption('stats', 's', InputOption::VALUE_NONE, 'Show distributions instead of the entries');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = max(1, (int) $input->getOption('limit'));
        $entries = $this->history->readLast(new User((string) $input->getOption('user')), $limit);

        if ($entries === []) {
            $io->warning('No history recorded yet.');

            return Command::SUCCESS;
        }

        if ($input->getOption('stats')) {
            $this->renderStats($io, $entries);

            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($entries as $entry) {
            $rows[] = [
                $entry->at->format('m-d H:i'),
                $entry->episode,
                $entry->type,
                $entry->correct ? 'ok' : 'KO',
                $entry->word ?? '',
                $entry->declined ?? '',
                $entry->declination ?? '',
                $entry->number ?? '',
                $entry->gender ?? '',
                implode(',', array_map(strval(...), $entry->answers)),
            ];
        }
        $io->table(['when', 'episode', 'type', '', 'word', 'declined', 'declination', 'number', 'gender', 'answered'], $rows);
        $io->writeln(sprintf('%d entries (most recent first).', count($entries)));

        return Command::SUCCESS;
    }

    /**
     * @param HistoryEntry[] $entries
     */
    private function renderStats(SymfonyStyle $io, array $entries): void
    {
        foreach (['type', 'declination', 'number', 'gender'] as $field) {
            /** @var array<string, int> $counts */
            $counts = [];
            foreach ($entries as $entry) {
                $value = (string) ($entry->$field ?? '-');
                $counts[$value] = ($counts[$value] ?? 0) + 1;
            }
            arsort($counts);
            $rows = [];
            foreach ($counts as $value => $count) {
                $rows[] = [$value, $count, sprintf('%.0f%%', (float) $count / (float) count($entries) * 100.0)];
            }
            $io->section($field);
            $io->table([$field, 'count', 'share'], $rows);
        }

        // Same word asked twice or more in a row: the repetition the selection should avoid.
        $streaks = [];
        $previous = null;
        $length = 0;
        foreach (array_reverse($entries) as $entry) {
            $current = ($entry->word ?? '-').'/'.$entry->type;
            $length = $current === $previous ? $length + 1 : 1;
            $previous = $current;
            if ($length > 1) {
                $streaks[$current] = max($streaks[$current] ?? 0, $length);
            }
        }
        arsort($streaks);
        if ($streaks !== []) {
            $io->section('consecutive repeats');
            $io->table(['word/type', 'in a row'], array_map(null, array_keys($streaks), array_values($streaks)));
        }
    }
}
