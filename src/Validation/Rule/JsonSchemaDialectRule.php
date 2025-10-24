<?php

declare(strict_types=1);

namespace On1kel\OAS\Profile31\Validation\Rule;

use On1kel\OAS\Core\Contract\Validation\Enum\Severity;
use On1kel\OAS\Core\Contract\Validation\NodeValidator;
use On1kel\OAS\Core\Contract\Validation\ValidationContext;
use On1kel\OAS\Core\Contract\Validation\ValidationError;
use On1kel\OAS\Core\Model\OpenApiDocument;
use On1kel\OAS\Core\Model\Schema;

final class JsonSchemaDialectRule implements NodeValidator
{
    public function validate(string $path, object $node, ValidationContext $ctx): array
    {
        $ptr = $ctx->pointer();
        $sev = $ctx->strictness()->value === 'strict' ? Severity::Error : Severity::Warning;
        $errors = [];

        if (is_a($node, OpenApiDocument::class, true)) {
            $dialect = $node->jsonSchemaDialect;
            if ($dialect !== null) {
                if (!$this->isAbsoluteUri($dialect)) {
                    $errors[] = new ValidationError(
                        pointer: $ptr,
                        code: 'openapi.jsonSchemaDialect.invalid',
                        message: "Поле 'jsonSchemaDialect' должно быть абсолютным URI.",
                        severity: $sev,
                        sourceVersion: '3.1',
                        hint: 'Напр.: https://spec.openapis.org/oas/3.1/dialect/base'
                    );
                }
            }
        }

        // 2) Любая Schema: $schema
        if (is_a($node, Schema::class, true)) {
            $schemaUri = $node->extra('$schema');

            if ($schemaUri !== null) {
                if (!is_string($schemaUri) || !$this->isAbsoluteUri($schemaUri)) {
                    $errors[] = new ValidationError(
                        pointer: $ptr,
                        code: 'schema.dialect.invalid',
                        message: "Значение '\$schema' должно быть абсолютным URI.",
                        severity: $sev,
                        sourceVersion: '3.1',
                        hint: 'Напр.: https://json-schema.org/draft/2020-12/schema'
                    );
                }
            }
        }

        return $errors;
    }

    private function isAbsoluteUri(string $uri): bool
    {
        $p = parse_url($uri);

        return is_array($p) && !empty($p['scheme']) && !empty($p['host']);
    }
}
