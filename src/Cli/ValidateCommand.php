<?php

declare(strict_types=1);

namespace On1kel\OAS\Profile31\Cli;

use On1kel\OAS\Core\Contract\Profile\Enum\Strictness;
use On1kel\OAS\Core\Contract\Validation\ValidationReport;
use On1kel\OAS\Core\Version\ParseOptions;
use On1kel\OAS\Profile31\Bootstrap\PipelineFactory;
use Throwable;

final class ValidateCommand
{
    /**
     * @param array{0:array{lenient:bool,no-external-refs:bool,max-ref-depth:int,report:string},1:list<string>} $argv
     * @return int
     */
    public static function run(array $argv): int
    {
        [$opts, $files] = self::parseArgs($argv);
        if (empty($files)) {
            self::usage();

            return 2;
        }

        $resolveExternal = !$opts['no-external-refs'];
        $maxDepth = (int)$opts['max-ref-depth'];
        $strict   = $opts['lenient'] ? Strictness::Lenient : Strictness::Strict;
        $reportFmt = $opts['report'];

        $pipeline = new PipelineFactory();

        $exit = 0;
        foreach ($files as $file) {
            try {
                $parsed = $pipeline->parseFile($file, new ParseOptions($strict, $resolveExternal, $maxDepth));
                $doc    = $parsed['doc'];
                $base   = $parsed['base'];
                $profile = $pipeline->detectProfile($doc);
                $report  = $pipeline->validateArrayAsObject($doc, $profile, $base, $strict);

                if ($reportFmt === 'json') {
                    echo self::toJsonReport($file, $report) . PHP_EOL;
                } else {
                    echo self::toTextReport($file, $report) . PHP_EOL;
                }

                if (!$report->isOk()) {
                    $exit = 1;
                }
            } catch (Throwable $e) {
                fwrite(STDERR, "[fail] {$file}: {$e->getMessage()}\n");
                $exit = 1;
            }
        }

        return $exit;
    }

    private static function usage(): void
    {
        $u = <<<TXT
Usage: oas-validate [options] <file1.json> [file2.json ...]

Options:
  --lenient                  Use lenient mode (warnings instead of some errors)
  --no-external-refs         Do not resolve external \$ref (only local #/...)
  --max-ref-depth=N          Limit \$ref resolution depth (default: 64)
  --report=txt|json          Output format (default: txt)

TXT;
        fwrite(STDERR, $u);
    }

    /**
     * @return array{0:array{lenient:bool,no-external-refs:bool,max-ref-depth:int,report:string},1:list<string>}
     */
    private static function parseArgs(array $argv): array
    {
        $opts = [
            'lenient' => false,
            'no-external-refs' => false,
            'max-ref-depth' => 64,
            'report' => 'txt',
        ];
        $files = [];

        foreach (array_slice($argv, 1) as $arg) {
            if ($arg === '--lenient') {
                $opts['lenient'] = true;
                continue;
            }
            if ($arg === '--no-external-refs') {
                $opts['no-external-refs'] = true;
                continue;
            }
            if (str_starts_with($arg, '--max-ref-depth=')) {
                $opts['max-ref-depth'] = (int)substr($arg, strlen('--max-ref-depth='));
                continue;
            }
            if (str_starts_with($arg, '--report=')) {
                $val = substr($arg, strlen('--report='));
                $opts['report'] = in_array($val, ['txt','json'], true) ? $val : 'txt';
                continue;
            }
            if (str_starts_with($arg, '-')) {
                // неизвестный ключ — игнорируем/можно бросить
                continue;
            }
            $files[] = $arg;
        }

        return [$opts, $files];
    }

    private static function toTextReport(string $file, ValidationReport $report): string
    {
        if ($report->isOk()) {
            return "[ok] {$file}";
        }

        $lines = ["[fail] {$file}"];
        foreach ($report->all() as $e) {
            $sev = $e->severity->name;
            $hint = $e->hint ? " | hint: {$e->hint}" : '';
            $src  = $e->sourceVersion ? " [{$e->sourceVersion}]" : '';
            $lines[] = "  - {$sev}{$src} {$e->pointer} {$e->code}: {$e->message}{$hint}";
        }

        return implode(PHP_EOL, $lines);
    }

    private static function toJsonReport(string $file, ValidationReport $report): string
    {
        $payload = [
            'file' => $file,
            'ok'   => $report->isOk(),
            'errors' => array_map(
                fn ($e) => [
                    'pointer' => $e->pointer,
                    'code'    => $e->code,
                    'message' => $e->message,
                    'severity' => $e->severity->name,
                    'sourceVersion' => $e->sourceVersion,
                    'hint'    => $e->hint,
                ],
                $report->all()
            ),
        ];

        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
