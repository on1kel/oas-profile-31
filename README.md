# on1kel/oas-profile-31

**Профиль OpenAPI 3.1 для `on1kel/oas-core`**

Добавляет поддержку правил и ограничений OpenAPI 3.1, включая детекцию версии, расширенную валидацию схем и CLI-утилиту `oas-validate`.

---

## Установка

```bash
composer require on1kel/oas-profile-31
```

**Требования:** PHP ≥ 8.2, пакет `on1kel/oas-core`.

---

## Использование

### CLI

Проверить спецификацию:

```bash
php vendor/bin/oas-validate openapi.yaml
```

Вывод в JSON:

```bash
php vendor/bin/oas-validate openapi.yaml --format=json
```

Код выхода:

* `0` — ошибок нет
* `1` — есть ошибки уровня Error

### PHP-API

```php
use On1kel\OAS\Profile31\Bootstrap\PipelineFactory;
use On1kel\OAS\Contract\Profile\Enum\Strictness;

$pipeline = PipelineFactory::makeDefault();

$report = $pipeline->parseAndValidate(
    __DIR__.'/openapi.yaml',
    Strictness::Strict
);

foreach ($report->all() as $error) {
    echo "{$error->severity->name}: {$error->message}\n";
}
```

---

## Основные возможности

* ✅ Профиль OAS 3.1 с декларацией поддерживаемых ключей
* ✅ Автоматическое определение версии спецификации
* ✅ Поддержка `$ref`, `jsonSchemaDialect`, `webhooks`
* ✅ Валидация и отчёт о нарушениях с уровнями Error / Warning
* ✅ CLI и API-использование
* ✅ Совместимость с JSON Schema 2020-12

---

## Дополнительные проверки (OAS 3.1)

| Правило                     | Описание                                                                                        | Уровень         |
| --------------------------- | ----------------------------------------------------------------------------------------------- | --------------- |
| **`NullableKeywordRule`**   | Обнаруживает устаревшее свойство `nullable`. Рекомендуется заменить на `type: ["...", "null"]`. | Error / Warning |
| **`JsonSchemaDialectRule`** | Проверяет корректность URI в `jsonSchemaDialect`.                                               | Error / Warning |

---

## Режимы строгости

| Режим      | Поведение                               |
| ---------- | --------------------------------------- |
| **Strict** | Ошибки блокируют сборку                 |
| **Lax**    | Нарушения отмечаются как предупреждения |

---

## Архитектура

* **OAS31Profile** — профиль спецификации 3.1
* **Profile31ValidatorFactory** — создаёт валидатор с правилами 3.1
* **PipelineFactory** — единая точка входа (детекция, парсинг, валидация)
* **ValidateCommand** — CLI-интерфейс

---

## Лицензия

MIT © on1kel

