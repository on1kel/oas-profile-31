<?php

declare(strict_types=1);

namespace On1kel\OAS\Profile31\Validation\Rule;

use On1kel\OAS\Core\Contract\Validation\Enum\Severity;
use On1kel\OAS\Core\Contract\Validation\NodeValidator;
use On1kel\OAS\Core\Contract\Validation\ValidationContext;
use On1kel\OAS\Core\Contract\Validation\ValidationError;
use On1kel\OAS\Core\Model\Schema;

final class NullableKeywordRule implements NodeValidator
{
    /**
     * @param string $path
     * @param object $node
     * @param ValidationContext $ctx
     * @return array<int, ValidationError>|ValidationError[]
     *
     */
    public function validate(string $path, object $node, ValidationContext $ctx): array
    {
        // Точка применения — Schema
        if (!is_a($node, Schema::class,true)) {
            return [];
        }

        $ptr  = $ctx->pointer();
        $sev  = $ctx->strictness()->value === 'strict' ? Severity::Error : Severity::Warning;
        // Попробуем подсказать корректную замену для простых случаев
        $hint = null;
        $hasType = $node->type;
        if ($hasType !== null) {
            if (is_string($hasType) && $hasType !== '') {
                $hint = "Замените: remove 'nullable' и установите type: [\"{$hasType}\", \"null\"].";
            } elseif (is_array($hasType)) {
                $types = array_values(array_unique(array_map('strval', $hasType)));
                if (!in_array('null', $types, true)) {
                    $types[] = 'null';
                    $hint = 'Удалите nullable и добавьте null в type: ' . json_encode($types, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }
        }

        return [
            new ValidationError(
                pointer: $ptr,
                code: 'schema.nullable.deprecated-31',
                message: "В OAS 3.1 ключевое слово 'nullable' не используется. Применяйте JSON Schema «null»-тип.",
                severity: $sev,
                sourceVersion: '3.1',
                hint: $hint
            )
        ];
    }
}
