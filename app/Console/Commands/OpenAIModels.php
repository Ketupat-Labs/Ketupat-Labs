<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OpenAIModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openai:models {--filter=gpt : Filter by substring (default: gpt)} {--limit=100 : Max results to print}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List available OpenAI models for the configured OPENAI_API_KEY';

    public function handle(): int
    {
        $apiKey = env('OPENAI_API_KEY');
        if (!$apiKey || strlen($apiKey) < 20) {
            $this->error('OPENAI_API_KEY is not configured. Please add it to your .env file.');
            return self::FAILURE;
        }

        $filter = (string)$this->option('filter');
        $limit = (int)$this->option('limit');
        if ($limit <= 0) {
            $limit = 100;
        }

        $ch = curl_init('https://api.openai.com/v1/models');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $this->error('Connection error: ' . $curlError);
            return self::FAILURE;
        }

        $data = json_decode((string)$response, true);

        if ($httpCode !== 200) {
            $msg = $data['error']['message'] ?? ('HTTP ' . $httpCode);
            $this->error('OpenAI API error: ' . $msg);
            return self::FAILURE;
        }

        $models = $data['data'] ?? [];
        if (!is_array($models) || count($models) === 0) {
            $this->warn('No models returned.');
            return self::SUCCESS;
        }

        $ids = [];
        foreach ($models as $m) {
            $id = $m['id'] ?? null;
            if (!is_string($id) || $id === '') {
                continue;
            }
            if ($filter !== '' && stripos($id, $filter) === false) {
                continue;
            }
            $ids[] = $id;
        }

        $ids = array_values(array_unique($ids));
        sort($ids);

        if (count($ids) === 0) {
            $this->warn("No models matched filter '{$filter}'. Try --filter= (empty) to show all.");
            return self::SUCCESS;
        }

        $this->info('Available OpenAI models (filtered):');

        $shown = 0;
        foreach ($ids as $id) {
            $this->line('- ' . $id);
            $shown++;
            if ($shown >= $limit) {
                $remaining = count($ids) - $shown;
                if ($remaining > 0) {
                    $this->warn("... and {$remaining} more (use --limit to show more)");
                }
                break;
            }
        }

        return self::SUCCESS;
    }
}
