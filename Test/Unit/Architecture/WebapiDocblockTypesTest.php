<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Test\Unit\Architecture;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * Magento's web API reads the types of a service from its docblocks, not from the PHP signature, and it
 * does not resolve `use` statements: a short "DataInterface" is looked up as a global class and fails
 * with "The "DataInterface" class doesn't exist and the namespace must be specified". The REST schema
 * of the whole shop (/rest/all/schema) then answers 500 for every service, not just this module's.
 *
 * This walks every service in etc/webapi.xml and every data interface its docblocks lead to.
 */
class WebapiDocblockTypesTest extends TestCase
{
    /**
     * A bare "array" is deliberately absent: the reflector only knows typed arrays such as int[] or
     * mixed[], and answers "The "array" class doesn't exist" for the plain word. "self", "static" and
     * "$this" are the fluent returns of setters, which the schema never reads.
     */
    private const SCALARS = [
        'string', 'int', 'integer', 'float', 'double', 'bool', 'boolean', 'mixed',
        'void', 'null', 'true', 'false', 'object', 'self', 'static', '$this',
    ];

    public function testEveryTypeTheWebApiReadsIsFullyQualified(): void
    {
        $problems = [];
        $queue = $this->serviceClasses();
        $seen = [];

        while ($queue !== []) {
            $class = array_shift($queue);
            if (isset($seen[$class])) {
                continue;
            }
            $seen[$class] = true;

            foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                foreach ($this->docblockTypes($method) as $type) {
                    if (in_array(strtolower($type), self::SCALARS, true)) {
                        continue;
                    }
                    if (!str_starts_with($type, '\\')) {
                        $problems[] = sprintf('%s::%s() uses "%s"', $class, $method->getName(), $type);
                        continue;
                    }
                    if (str_starts_with($type, '\\__VENDOR__\\__MODULE__\\') && interface_exists($type)) {
                        $queue[] = ltrim($type, '\\');
                    }
                }
            }
        }

        self::assertNotEmpty($seen, 'etc/webapi.xml declares no services');
        self::assertSame([], $problems, "Not fully qualified, so the web API cannot resolve it:\n" . implode("\n", $problems));
    }

    /**
     * @return string[]
     */
    private function serviceClasses(): array
    {
        $xml = simplexml_load_file(dirname(__DIR__, 3) . '/etc/webapi.xml');
        self::assertNotFalse($xml, 'etc/webapi.xml');

        $classes = [];
        foreach ($xml->xpath('//service') ?: [] as $service) {
            $classes[] = ltrim((string)$service['class'], '\\');
        }

        return array_values(array_unique($classes));
    }

    /**
     * The single types of every @param and @return, with array and nullable markers taken off.
     *
     * @return string[]
     */
    private function docblockTypes(ReflectionMethod $method): array
    {
        $doc = (string)$method->getDocComment();
        preg_match_all('/@(?:param|return)\s+([^\s]+)/', $doc, $matches);

        $types = [];
        foreach ($matches[1] as $union) {
            foreach (explode('|', $union) as $type) {
                $types[] = ltrim(preg_replace('/\[\]$/', '', $type), '?');
            }
        }

        return $types;
    }
}
