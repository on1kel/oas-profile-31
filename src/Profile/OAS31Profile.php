<?php

declare(strict_types=1);

namespace On1kel\OAS\Profile31\Profile;

use On1kel\OAS\Core\Contract\Profile\FeatureSet;
use On1kel\OAS\Core\Contract\Profile\SpecProfile;
use On1kel\OAS\Core\Contract\Validation\NodeValidator;

final class OAS31Profile implements SpecProfile
{
    public function majorMinor(): string
    {
        return '3.1';
    }

    public function features(): FeatureSet
    {
        return new FeatureSet(
            jsonSchemaDraft2020_12: true,
            webhooksSupported: true,
            examplesAtMediaTypeLevel: true,
            extra: [
                'pruneEmpty' => true,
                'preserveEmpty.servers' => true,
                'preserveEmpty.security' => true,
                'preserveEmpty.tags' => true,
            ]
        );
    }

    /** @return array<int,string> */
    public function allowedKeysFor(string $nodeType): array
    {
        return match ($nodeType) {
            'OpenApiDocument' => [
                'openapi','info','jsonSchemaDialect','servers','paths','webhooks','components','security','tags','externalDocs','x-',
            ],
            'Info' => ['title','summary','description','termsOfService','contact','license','version','x-'],
            'Contact' => ['name','url','email','x-'],
            'License' => ['name','identifier','url','x-'],
            'Server' => ['url','description','variables','x-'],
            'ServerVariable' => ['enum','default','description','x-'],
            'Components' => [
                'schemas','responses','parameters','examples','requestBodies','headers','securitySchemes','links','callbacks','pathItems','x-',
            ],
            'Schema' => [
                // JSON Schema 2020-12 core + applicator + validation + metadata + unevaluated + OAS-надстройки
                '$id','$schema','$anchor','$ref','$defs','$comment','$dynamicRef','$dynamicAnchor',
                'type','properties','patternProperties','additionalProperties','items',
                'allOf','anyOf','oneOf','not','const','enum','required',
                'minimum','maximum','exclusiveMinimum','exclusiveMaximum','multipleOf',
                'minLength','maxLength','pattern','format',
                'contentMediaType','contentEncoding','contentSchema',
                'minItems','maxItems','uniqueItems','contains',
                'minProperties','maxProperties','dependentSchemas','dependentRequired',
                'prefixItems','unevaluatedItems','unevaluatedProperties',
                'if','then','else','propertyNames',
                'default','examples','title','description','readOnly','writeOnly','deprecated',
                // OAS-специфика
                'discriminator','xml','externalDocs','example',
                // нет 'nullable' в 3.1
                'x-',
            ],
            'Xml' => ['name','namespace','prefix','attribute','wrapped','x-'],
            'ExternalDocumentation' => ['description','url','x-'],
            'PathItem' => [
                '$ref','summary','description','get','put','post','delete','options','head','patch','trace','servers','parameters','x-'
            ],
            'Operation' => ['tags','summary','description','externalDocs','operationId','parameters','requestBody','responses','callbacks','deprecated','security','servers','x-'],
            'Parameter' => ['name','in','description','required','deprecated','allowEmptyValue','style','explode','allowReserved','schema','example','examples','content','x-'],
            'RequestBody' => ['description','content','required','x-'],
            'MediaType' => ['schema','example','examples','encoding','x-'],
            'Encoding' => ['contentType','headers','style','explode','allowReserved','x-'],
            'Header' => ['description','required','deprecated','allowEmptyValue','style','explode','allowReserved','schema','example','examples','content','x-'],
            'Response' => ['description','headers','content','links','x-'],
            'Example' => ['summary','description','value','externalValue','x-'],
            'Link' => ['operationRef','operationId','parameters','requestBody','description','server','x-'],
            'Callback' => ['x-'],
            'SecurityScheme' => ['type','description','name','in','scheme','bearerFormat','flows','openIdConnectUrl','x-'],
            'OAuthFlows' => ['implicit','password','clientCredentials','authorizationCode','x-'],
            'OAuthFlow' => ['authorizationUrl','tokenUrl','refreshUrl','scopes','x-'],
            'Tag' => ['name','description','externalDocs','x-'],
            default => ['x-'],
        };
    }

    /** @return array<int,string> */
    public function requiredKeysFor(string $nodeType): array
    {
        return match ($nodeType) {
            'OpenApiDocument' => ['openapi','info'],
            'Info'            => ['title','version'],
            'ServerVariable'  => ['default'],
            'Response'        => ['description'],
            default           => [],
        };
    }

    public function normalizeKey(string $nodeType, string $key): string
    {
        return $key;
    }

    /** @return array<int, NodeValidator> */
    public function extraValidators(): array
    {
        return [
            new \On1kel\OAS\Profile31\Validation\Rule\NullableKeywordRule(),
            new \On1kel\OAS\Profile31\Validation\Rule\JsonSchemaDialectRule(),
        ];
    }
}
