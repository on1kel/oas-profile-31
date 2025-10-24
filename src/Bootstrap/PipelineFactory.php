<?php

declare(strict_types=1);

namespace On1kel\OAS\Profile31\Bootstrap;

use JsonException;
use On1kel\OAS\Core\Contract\Profile\Enum\Strictness;
use On1kel\OAS\Core\Contract\Profile\SpecProfile;
use On1kel\OAS\Core\Contract\Validation\ValidationReport;
use On1kel\OAS\Core\Parsing\DocumentParser;
use On1kel\OAS\Core\Ref\{DefaultRefFetcher, InMemoryRefCache, RefResolver};
use On1kel\OAS\Core\Version\{ParseOptions, ProfileRegistry, VersionDetector};
use On1kel\OAS\Profile31\Validation\Profile31ValidatorFactory;
use RuntimeException;

final class PipelineFactory
{
    private readonly ProfileRegistry $profiles;
    private readonly VersionDetector $detector;

    public function __construct(
        ?ProfileRegistry $profiles = null,
        ?VersionDetector $detector = null,
    ) {
        $this->profiles = $profiles ?? ProfileRegistryFactory::makeDefault31();
        $this->detector = $detector ?? new VersionDetector();
    }


    public function newParser(): DocumentParser
    {
        $resolver = new RefResolver(new DefaultRefFetcher(), new InMemoryRefCache());

        return new DocumentParser($resolver);
    }

    /**
     * @param string $file
     * @param ParseOptions $opts
     * @return array<string, mixed>
     * @throws JsonException
     */
    public function parseFile(string $file, ParseOptions $opts): array
    {
        if (!is_file($file)) {
            throw new RuntimeException("Файл не найден: {$file}");
        }
        /** @var string|false $json */
        $json = file_get_contents($file);
        if ($json === false) {
            throw new RuntimeException("Ошибка чтения файла: {$file}");
        }
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new RuntimeException("Invalid JSON in {$file}");
        }
        $baseUri = 'file://' . realpath($file);
        $docArr  = $this->newParser()->parse($data, $baseUri, $opts);

        return ['doc' => $docArr, 'base' => $baseUri];
    }

    /**
     * @param array $doc
     * @return SpecProfile
     */
    public function detectProfile(array $doc): SpecProfile
    {
        return $this->detector->detect($doc, $this->profiles);
    }

    /**
     * @param array $doc
     * @param SpecProfile $profile
     * @param string $baseUri
     * @param Strictness $strictness
     * @return ValidationReport
     */
    public function validateArrayAsObject(
        array $doc,
        SpecProfile $profile,
        string $baseUri,
        Strictness $strictness = Strictness::Strict
    ): ValidationReport {
        $validator = (new Profile31ValidatorFactory())->create($profile);

        // Валидация ожидает object — оборачиваем массив (или подайте реальную модель, когда подключите денормализацию)
        return $validator->validate((object)$doc, $profile, $strictness, $baseUri);
    }
}
