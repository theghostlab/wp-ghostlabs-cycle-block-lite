<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use Generator;

class Utils
{
    /**
     * @param array<array-key, array<string, mixed>> $blocks parse_blocks() output, which
     *        is keyed int|string rather than strictly int.
     * @return list<array<string, mixed>>
     */
    public function findAllBlocksByName(array $blocks, string $searchBlockName): array
    {
        $found = [];

        foreach ($blocks as $block) {
            // Check if current block matches the search
            if ($block['blockName'] === $searchBlockName) {
                $found[] = $block;
            }

            // If this block has inner blocks, search them recursively
            if (!empty($block['innerBlocks'])) {
                $found = array_merge($found, $this->findAllBlocksByName($block['innerBlocks'], $searchBlockName));
            }
        }

        return $found;
    }

    /**
     * @param mixed $data Raw request body: a JSON string, or an already-decoded array.
     * @return array<mixed>
     */
    public function setPayload($data) {
        $payload = is_string($data) ? json_decode(stripslashes($data), true) : $data;

        return $this->arrayMapRecursive('sanitize_text_field', $payload);
    }

    /**
     * @param string $directory
     * @param class-string|null $interfaceName Only yield classes implementing this.
     * @return Generator<int, class-string>
     */
    public function getClasses(string $directory, ?string $interfaceName = null): \Generator {
        // Helper function to recursively scan directories
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($files as $file) {
            if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                // Extract the class name from the file name
                $className = pathinfo($file, PATHINFO_FILENAME);

                // Parse the file for the namespace
                $namespace = $this->getNamespace($file->getPathname());

                // Fully qualified class name
                $fullClassName = $namespace ? $namespace . '\\' . $className : $className;

                // Work out the name BEFORE including, so an already-declared
                // symbol is skipped rather than redeclared. This walk now runs
                // over every root SourceRoots reports, and a site that happened
                // to have a second copy of a class on one of them would
                // otherwise take the whole request down with "cannot declare
                // class ... already in use" — a fatal, not a warning.
                if (! $this->isDeclared($fullClassName)) {
                    require_once $file;
                }

                if( $interfaceName ) {

                    if (class_exists($fullClassName) && is_subclass_of($fullClassName, $interfaceName)) {
                        /** @var class-string $fullClassName */
                        yield $fullClassName;
                    }
                } else {

                    if (class_exists($fullClassName)) {
                        /** @var class-string $fullClassName */
                        yield $fullClassName;
                    }
                }
            }
        }
    }

    /**
     * Is this symbol already loaded? Autoloading is deliberately off — the
     * question is whether including the file would redeclare it, not whether
     * the name is resolvable.
     */
    private function isDeclared(string $symbol): bool
    {
        return class_exists($symbol, false)
            || interface_exists($symbol, false)
            || trait_exists($symbol, false);
    }

    /**
     * @param string $filePath
     */
    private function getNamespace($filePath): ?string {
        $namespace = null;
        $lines = file($filePath);

        // file() returns false on an unreadable file. getClasses() walks every PHP
        // file under inc/ on the way to registering the REST routes and AJAX
        // endpoints, so one bad permission bit here used to warn its way through
        // plugin boot rather than simply skipping the file.
        if ($lines === false) {
            return null;
        }

        foreach ($lines as $line) {
            if (preg_match('/^namespace\s+(.+?);$/', $line, $matches)) {
                $namespace = $matches[1];
                break;
            }
        }

        return $namespace;
    }

    /**
     * @param iterable<mixed> $generator
     * @return Generator<mixed>
     */
    public function generator($generator): Generator
    {
        foreach ($generator as $dt) {
            yield $dt;
        }
    }

    /**
     * @param callable(mixed): mixed $callback
     * @param array<mixed>|null $array
     * @return array<mixed>
     */
    function arrayMapRecursive( $callback, $array )
    {
        if(empty($array)) return [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->arrayMapRecursive($callback, $value);
            }
            else {
                $array[$key] = call_user_func($callback, $value);
            }
        }

        return $array;
    }

    /**
     * @param array<int, array<string, mixed>> $arr
     * @param mixed $value
     * @return array<string, mixed>|null
     */
    public function searchArrayByProperty(array $arr, string $property, $value) {
        foreach ($arr as $item) {
            if (isset($item[$property]) && $item[$property] === $value) {
                return $item;
            }
        }
        return null; // Return null if no matching item is found
    }

    /**
     * @param iterable<array<string, mixed>> $arr
     * @param string $property
     * @param mixed $value
     * @param mixed $newItem Appended when nothing matched.
     * @return list<mixed>
     */
    function updateArrayItemByProperty($arr, $property, $value, $newItem): array
    {
        $updatedArray = [];
        $itemUpdated = false;

        foreach ($arr as $item) {
            if (isset($item[$property]) && $item[$property] === $value) {
                $updatedArray[] = $newItem;
                $itemUpdated = true;
            } else {
                $updatedArray[] = $item;
            }
        }

        // If no item was updated, you might want to decide whether to handle it differently.
        if (!$itemUpdated) {
            $updatedArray[] = $newItem; // Optionally add the new item if not found
        }

        return $updatedArray;
    }

    /**
     * Filters an array to remove empty values and strings containing only whitespace
     *
     * @param array<mixed> $array The input array to filter
     * @param bool $recursive Whether to recursively filter nested arrays (default: false)
     * @return array<mixed> The filtered array
     */
    public function filterEmptyValues(array $array, bool $recursive = false): array
    {
        $filteredArray = [];

        foreach ($array as $key => $value) {
            // Handle nested arrays if recursive flag is true
            if (is_array($value) && $recursive) {
                $filteredNestedArray = $this->filterEmptyValues($value, true);

                // Only add the nested array if it's not empty after filtering
                if (!empty($filteredNestedArray)) {
                    $filteredArray[$key] = $filteredNestedArray;
                }
                continue;
            }

            // Skip null values
            if ($value === null) {
                continue;
            }

            // Skip empty strings and strings with only whitespace
            if (is_string($value) && trim($value) === '') {
                continue;
            }

            // Skip empty arrays
            if (is_array($value) && empty($value)) {
                continue;
            }

            // Add values that passed all checks
            $filteredArray[$key] = $value;
        }

        return $filteredArray;
    }
}