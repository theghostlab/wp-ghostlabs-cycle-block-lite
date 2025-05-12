<?php

namespace THEGHOSTLAB\CYCLE\Services;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class DTOMapper {
    /**
     * Maps array data to a DTO with proper type casting
     *
     * @param array $data The source data (like $_POST)
     * @param string $dtoClass The fully qualified class name of the DTO
     * @return object The populated DTO instance
     * @throws ReflectionException
     */
    public static function map(array $data, string $dtoClass): object {
        $dto = new $dtoClass();
        $reflection = new ReflectionClass($dtoClass);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            // Skip if the property doesn't exist in data
            if (!array_key_exists($propertyName, $data)) {
                continue;
            }

            $value = $data[$propertyName];

            // Get the type of the property
            $type = $property->getType();
            if ($type !== null) {
                $typeName = $type->getName();

                // Cast value to the appropriate type
                switch ($typeName) {
                    case 'int':
                        $value = (int)$value;
                        break;
                    case 'float':
                        $value = (float)$value;
                        break;
                    case 'bool':
                        $value = (bool)$value;
                        break;
                    case 'array':
                        if (!is_array($value)) {
                            $value = [$value];
                        }
                        break;
                    case 'string':
                        $value = (string)$value;
                        break;
                    default:
                        // Handle complex types or leave as is
                        if (class_exists($typeName) && is_array($value)) {
                            $value = self::map($value, $typeName);
                        }
                        break;
                }
            }

            $dto->$propertyName = $value;
        }

        return $dto;
    }

    /**
     * Maps from nested arrays to nested DTOs
     *
     * @param array $data The source data
     * @param string $dtoClass The main DTO class
     * @param array $nestedMappings Associative array of property => DTO class mappings
     * @return object The populated DTO with nested DTOs
     * @throws ReflectionException
     */
    public static function mapNested(array $data, string $dtoClass, array $nestedMappings): object {
        $dto = self::map($data, $dtoClass);

        foreach ($nestedMappings as $property => $mapping) {
            if (!isset($data[$property]) || !is_array($data[$property])) {
                continue;
            }

            if (is_array($mapping)) {
                // Handle collection of DTOs
                $collection = [];
                foreach ($data[$property] as $item) {
                    $collection[] = self::map($item, $mapping[0]);
                }
                $dto->$property = $collection;
            } else {
                // Handle single nested DTO
                $dto->$property = self::map($data[$property], $mapping);
            }
        }

        return $dto;
    }

    /**
     * Validates a DTO against a set of rules
     *
     * @param object $dto The DTO to validate
     * @param array $rules Array of property => validation rules
     * @return array Array of validation errors or empty if valid
     */
    public static function validate(object $dto, array $rules): array {
        $errors = [];

        foreach ($rules as $property => $propertyRules) {
            if (!property_exists($dto, $property)) {
                continue;
            }

            $value = $dto->$property;

            foreach ($propertyRules as $rule => $ruleValue) {
                switch ($rule) {
                    case 'required':
                        if ($ruleValue && empty(strlen((string)$value))) {
                            $errors[$property][] = "$property is required";
                        }
                        break;
                    case 'min':
                        if (is_string($value) && strlen($value) < $ruleValue) {
                            $errors[$property][] = "$property must be at least $ruleValue characters";
                        } elseif (is_numeric($value) && $value < $ruleValue) {
                            $errors[$property][] = "$property must be at least $ruleValue";
                        }
                        break;
                    case 'max':
                        if (is_string($value) && strlen($value) > $ruleValue) {
                            $errors[$property][] = "$property must not exceed $ruleValue characters";
                        } elseif (is_numeric($value) && $value > $ruleValue) {
                            $errors[$property][] = "$property must not exceed $ruleValue";
                        }
                        break;
                    case 'email':
                        if ($ruleValue && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$property][] = "$property must be a valid email";
                        }
                        break;
                    case 'regex':
                        if (!preg_match($ruleValue, $value)) {
                            $errors[$property][] = "$property has an invalid format";
                        }
                        break;
                }
            }
        }

        return $errors;
    }
}