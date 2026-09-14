<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use Generator;

if ( ! defined( 'ABSPATH' ) ) exit;

class Utils
{
    
    public function findAllBlocksByName(array $blocks, string $searchBlockName): array
    {
        $found = [];

        foreach ($blocks as $block) {
            
            if ($block['blockName'] === $searchBlockName) {
                $found[] = $block;
            }

            if (!empty($block['innerBlocks'])) {
                $found = array_merge($found, $this->findAllBlocksByName($block['innerBlocks'], $searchBlockName));
            }
        }

        return $found;
    }

    public function setPayload($data) {
        $payload = is_string($data) ? json_decode(stripslashes($data), true) : $data;

        return $this->arrayMapRecursive('sanitize_text_field', $payload);
    }

    public function getClasses(string $directory, ?string $interfaceName = null): \Generator {
        
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($files as $file) {
            if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                
                $className = pathinfo($file, PATHINFO_FILENAME);

                $namespace = $this->getNamespace($file->getPathname());

                $fullClassName = $namespace ? $namespace . '\\' . $className : $className;

                if (! $this->isDeclared($fullClassName)) {
                    require_once $file;
                }

                if( $interfaceName ) {

                    if (class_exists($fullClassName) && is_subclass_of($fullClassName, $interfaceName)) {
                        
                        yield $fullClassName;
                    }
                } else {

                    if (class_exists($fullClassName)) {
                        
                        yield $fullClassName;
                    }
                }
            }
        }
    }

    private function isDeclared(string $symbol): bool
    {
        return class_exists($symbol, false)
            || interface_exists($symbol, false)
            || trait_exists($symbol, false);
    }

    private function getNamespace($filePath): ?string {
        $namespace = null;
        $lines = file($filePath);

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

    public function generator($generator): Generator
    {
        foreach ($generator as $dt) {
            yield $dt;
        }
    }

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

    public function searchArrayByProperty(array $arr, string $property, $value) {
        foreach ($arr as $item) {
            if (isset($item[$property]) && $item[$property] === $value) {
                return $item;
            }
        }
        return null; 
    }

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

        if (!$itemUpdated) {
            $updatedArray[] = $newItem; 
        }

        return $updatedArray;
    }

    public function filterEmptyValues(array $array, bool $recursive = false): array
    {
        $filteredArray = [];

        foreach ($array as $key => $value) {
            
            if (is_array($value) && $recursive) {
                $filteredNestedArray = $this->filterEmptyValues($value, true);

                if (!empty($filteredNestedArray)) {
                    $filteredArray[$key] = $filteredNestedArray;
                }
                continue;
            }

            if ($value === null) {
                continue;
            }

            if (is_string($value) && trim($value) === '') {
                continue;
            }

            if (is_array($value) && empty($value)) {
                continue;
            }

            $filteredArray[$key] = $value;
        }

        return $filteredArray;
    }
}