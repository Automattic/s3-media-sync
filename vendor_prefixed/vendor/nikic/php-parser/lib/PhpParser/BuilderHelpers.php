<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser;

use WPCOM_VIP\PhpParser\Node\Expr;
use WPCOM_VIP\PhpParser\Node\Identifier;
use WPCOM_VIP\PhpParser\Node\Name;
use WPCOM_VIP\PhpParser\Node\NullableType;
use WPCOM_VIP\PhpParser\Node\Scalar;
use WPCOM_VIP\PhpParser\Node\Stmt;
use WPCOM_VIP\PhpParser\Node\UnionType;
/**
 * This class defines helpers used in the implementation of builders. Don't use it directly.
 *
 * @internal
 */
final class BuilderHelpers
{
    /**
     * Normalizes a node: Converts builder objects to nodes.
     *
     * @param Node|Builder $node The node to normalize
     *
     * @return Node The normalized node
     */
    public static function normalizeNode($node) : \WPCOM_VIP\PhpParser\Node
    {
        if ($node instanceof \WPCOM_VIP\PhpParser\Builder) {
            return $node->getNode();
        } elseif ($node instanceof \WPCOM_VIP\PhpParser\Node) {
            return $node;
        }
        throw new \LogicException('Expected node or builder object');
    }
    /**
     * Normalizes a node to a statement.
     *
     * Expressions are wrapped in a Stmt\Expression node.
     *
     * @param Node|Builder $node The node to normalize
     *
     * @return Stmt The normalized statement node
     */
    public static function normalizeStmt($node) : \WPCOM_VIP\PhpParser\Node\Stmt
    {
        $node = self::normalizeNode($node);
        if ($node instanceof \WPCOM_VIP\PhpParser\Node\Stmt) {
            return $node;
        }
        if ($node instanceof \WPCOM_VIP\PhpParser\Node\Expr) {
            return new \WPCOM_VIP\PhpParser\Node\Stmt\Expression($node);
        }
        throw new \LogicException('Expected statement or expression node');
    }
    /**
     * Normalizes strings to Identifier.
     *
     * @param string|Identifier $name The identifier to normalize
     *
     * @return Identifier The normalized identifier
     */
    public static function normalizeIdentifier($name) : \WPCOM_VIP\PhpParser\Node\Identifier
    {
        if ($name instanceof \WPCOM_VIP\PhpParser\Node\Identifier) {
            return $name;
        }
        if (\is_string($name)) {
            return new \WPCOM_VIP\PhpParser\Node\Identifier($name);
        }
        throw new \LogicException('WPCOM_VIP\\Expected string or instance of Node\\Identifier');
    }
    /**
     * Normalizes strings to Identifier, also allowing expressions.
     *
     * @param string|Identifier|Expr $name The identifier to normalize
     *
     * @return Identifier|Expr The normalized identifier or expression
     */
    public static function normalizeIdentifierOrExpr($name)
    {
        if ($name instanceof \WPCOM_VIP\PhpParser\Node\Identifier || $name instanceof \WPCOM_VIP\PhpParser\Node\Expr) {
            return $name;
        }
        if (\is_string($name)) {
            return new \WPCOM_VIP\PhpParser\Node\Identifier($name);
        }
        throw new \LogicException('WPCOM_VIP\\Expected string or instance of Node\\Identifier or Node\\Expr');
    }
    /**
     * Normalizes a name: Converts string names to Name nodes.
     *
     * @param Name|string $name The name to normalize
     *
     * @return Name The normalized name
     */
    public static function normalizeName($name) : \WPCOM_VIP\PhpParser\Node\Name
    {
        return self::normalizeNameCommon($name, \false);
    }
    /**
     * Normalizes a name: Converts string names to Name nodes, while also allowing expressions.
     *
     * @param Expr|Name|string $name The name to normalize
     *
     * @return Name|Expr The normalized name or expression
     */
    public static function normalizeNameOrExpr($name)
    {
        return self::normalizeNameCommon($name, \true);
    }
    /**
     * Normalizes a name: Converts string names to Name nodes, optionally allowing expressions.
     *
     * @param Expr|Name|string $name      The name to normalize
     * @param bool             $allowExpr Whether to also allow expressions
     *
     * @return Name|Expr The normalized name, or expression (if allowed)
     */
    private static function normalizeNameCommon($name, bool $allowExpr)
    {
        if ($name instanceof \WPCOM_VIP\PhpParser\Node\Name) {
            return $name;
        } elseif (\is_string($name)) {
            if (!$name) {
                throw new \LogicException('Name cannot be empty');
            }
            if ($name[0] === '\\') {
                return new \WPCOM_VIP\PhpParser\Node\Name\FullyQualified(\substr($name, 1));
            } elseif (0 === \strpos($name, 'namespace\\')) {
                return new \WPCOM_VIP\PhpParser\Node\Name\Relative(\substr($name, \strlen('namespace\\')));
            } else {
                return new \WPCOM_VIP\PhpParser\Node\Name($name);
            }
        }
        if ($allowExpr) {
            if ($name instanceof \WPCOM_VIP\PhpParser\Node\Expr) {
                return $name;
            }
            throw new \LogicException('WPCOM_VIP\\Name must be a string or an instance of Node\\Name or Node\\Expr');
        } else {
            throw new \LogicException('WPCOM_VIP\\Name must be a string or an instance of Node\\Name');
        }
    }
    /**
     * Normalizes a type: Converts plain-text type names into proper AST representation.
     *
     * In particular, builtin types become Identifiers, custom types become Names and nullables
     * are wrapped in NullableType nodes.
     *
     * @param string|Name|Identifier|NullableType|UnionType $type The type to normalize
     *
     * @return Name|Identifier|NullableType|UnionType The normalized type
     */
    public static function normalizeType($type)
    {
        if (!\is_string($type)) {
            if (!$type instanceof \WPCOM_VIP\PhpParser\Node\Name && !$type instanceof \WPCOM_VIP\PhpParser\Node\Identifier && !$type instanceof \WPCOM_VIP\PhpParser\Node\NullableType && !$type instanceof \WPCOM_VIP\PhpParser\Node\UnionType) {
                throw new \LogicException('Type must be a string, or an instance of Name, Identifier, NullableType or UnionType');
            }
            return $type;
        }
        $nullable = \false;
        if (\strlen($type) > 0 && $type[0] === '?') {
            $nullable = \true;
            $type = \substr($type, 1);
        }
        $builtinTypes = ['array', 'callable', 'string', 'int', 'float', 'bool', 'iterable', 'void', 'object', 'mixed'];
        $lowerType = \strtolower($type);
        if (\in_array($lowerType, $builtinTypes)) {
            $type = new \WPCOM_VIP\PhpParser\Node\Identifier($lowerType);
        } else {
            $type = self::normalizeName($type);
        }
        if ($nullable && (string) $type === 'void') {
            throw new \LogicException('void type cannot be nullable');
        }
        if ($nullable && (string) $type === 'mixed') {
            throw new \LogicException('mixed type cannot be nullable');
        }
        return $nullable ? new \WPCOM_VIP\PhpParser\Node\NullableType($type) : $type;
    }
    /**
     * Normalizes a value: Converts nulls, booleans, integers,
     * floats, strings and arrays into their respective nodes
     *
     * @param Node\Expr|bool|null|int|float|string|array $value The value to normalize
     *
     * @return Expr The normalized value
     */
    public static function normalizeValue($value) : \WPCOM_VIP\PhpParser\Node\Expr
    {
        if ($value instanceof \WPCOM_VIP\PhpParser\Node\Expr) {
            return $value;
        } elseif (\is_null($value)) {
            return new \WPCOM_VIP\PhpParser\Node\Expr\ConstFetch(new \WPCOM_VIP\PhpParser\Node\Name('null'));
        } elseif (\is_bool($value)) {
            return new \WPCOM_VIP\PhpParser\Node\Expr\ConstFetch(new \WPCOM_VIP\PhpParser\Node\Name($value ? 'true' : 'false'));
        } elseif (\is_int($value)) {
            return new \WPCOM_VIP\PhpParser\Node\Scalar\LNumber($value);
        } elseif (\is_float($value)) {
            return new \WPCOM_VIP\PhpParser\Node\Scalar\DNumber($value);
        } elseif (\is_string($value)) {
            return new \WPCOM_VIP\PhpParser\Node\Scalar\String_($value);
        } elseif (\is_array($value)) {
            $items = [];
            $lastKey = -1;
            foreach ($value as $itemKey => $itemValue) {
                // for consecutive, numeric keys don't generate keys
                if (null !== $lastKey && ++$lastKey === $itemKey) {
                    $items[] = new \WPCOM_VIP\PhpParser\Node\Expr\ArrayItem(self::normalizeValue($itemValue));
                } else {
                    $lastKey = null;
                    $items[] = new \WPCOM_VIP\PhpParser\Node\Expr\ArrayItem(self::normalizeValue($itemValue), self::normalizeValue($itemKey));
                }
            }
            return new \WPCOM_VIP\PhpParser\Node\Expr\Array_($items);
        } else {
            throw new \LogicException('Invalid value');
        }
    }
    /**
     * Normalizes a doc comment: Converts plain strings to PhpParser\Comment\Doc.
     *
     * @param Comment\Doc|string $docComment The doc comment to normalize
     *
     * @return Comment\Doc The normalized doc comment
     */
    public static function normalizeDocComment($docComment) : \WPCOM_VIP\PhpParser\Comment\Doc
    {
        if ($docComment instanceof \WPCOM_VIP\PhpParser\Comment\Doc) {
            return $docComment;
        } elseif (\is_string($docComment)) {
            return new \WPCOM_VIP\PhpParser\Comment\Doc($docComment);
        } else {
            throw new \LogicException('WPCOM_VIP\\Doc comment must be a string or an instance of PhpParser\\Comment\\Doc');
        }
    }
    /**
     * Adds a modifier and returns new modifier bitmask.
     *
     * @param int $modifiers Existing modifiers
     * @param int $modifier  Modifier to set
     *
     * @return int New modifiers
     */
    public static function addModifier(int $modifiers, int $modifier) : int
    {
        \WPCOM_VIP\PhpParser\Node\Stmt\Class_::verifyModifier($modifiers, $modifier);
        return $modifiers | $modifier;
    }
}
