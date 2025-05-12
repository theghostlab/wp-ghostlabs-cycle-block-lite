<?php

namespace THEGHOSTLAB\CYCLE\Services;

use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Utils
{
	/**
	 * @param string $directory
	 * @param string|null $interfaceName
	 * @return Generator
	 */
	public function getClasses(string $directory, ?string $interfaceName = null): Generator {
		// Helper function to recursively scan directories
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

		foreach ($files as $file) {
			if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
				// Include the file to load the class
				require_once $file;

				// Extract the class name from the file name
				$className = pathinfo($file, PATHINFO_FILENAME);

				// Parse the file for the namespace
				$namespace = $this->getNamespace($file->getPathname());

				// Fully qualified class name
				$fullClassName = $namespace ? $namespace . '\\' . $className : $className;

				if( $interfaceName ) {

					if (class_exists($fullClassName) && is_subclass_of($fullClassName, $interfaceName)) {
						/** @var class-string $class */
						yield $fullClassName;
					}
				} else {

					if (class_exists($fullClassName)) {
						/** @var class-string $class */
						yield $fullClassName;
					}
				}
			}
		}
	}

	private function getNamespace($filePath): ?string {
		$namespace = null;
		$lines = file($filePath);

		foreach ($lines as $line) {
			if (preg_match('/^namespace\s+(.+?);$/', $line, $matches)) {
				$namespace = $matches[1];
				break;
			}
		}

		return $namespace;
	}

	public function setPayload($data) {
		$payload = is_string($data) ? json_decode(stripslashes($data), true) : $data;

		return $this->arrayMapRecursive('sanitize_text_field', $payload);
	}

	/**
	 * Filters an array to remove empty values and strings containing only whitespace
	 *
	 * @param array $array The input array to filter
	 * @param bool $recursive Whether to recursively filter nested arrays (default: false)
	 * @return array The filtered array
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
        return null; // Return null if no matching item is found
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

        // If no item was updated, you might want to decide whether to handle it differently.
        if (!$itemUpdated) {
            $updatedArray[] = $newItem; // Optionally add the new item if not found
        }

        return $updatedArray;
    }
}