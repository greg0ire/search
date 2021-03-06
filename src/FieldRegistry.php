<?php

declare(strict_types=1);

/*
 * This file is part of the RollerworksSearch package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Search;

use Rollerworks\Component\Search\Exception\ExceptionInterface;
use Rollerworks\Component\Search\Exception\InvalidArgumentException;
use Rollerworks\Component\Search\Exception\UnexpectedTypeException;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class FieldRegistry implements FieldRegistryInterface
{
    /**
     * Extensions.
     *
     * @var SearchExtensionInterface[]
     */
    private $extensions = [];

    /**
     * @var ResolvedFieldTypeInterface[]
     */
    private $types = [];

    /**
     * @var ResolvedFieldTypeFactoryInterface
     */
    private $resolvedTypeFactory;

    /**
     * Constructor.
     *
     * @param SearchExtensionInterface[]        $extensions          An array of SearchExtensionInterface
     * @param ResolvedFieldTypeFactoryInterface $resolvedTypeFactory The factory for resolved field types
     *
     * @throws UnexpectedTypeException if an extension does not implement SearchExtensionInterface
     */
    public function __construct(array $extensions, ResolvedFieldTypeFactoryInterface $resolvedTypeFactory)
    {
        foreach ($extensions as $extension) {
            if (!$extension instanceof SearchExtensionInterface) {
                throw new UnexpectedTypeException($extension, SearchExtensionInterface::class);
            }
        }

        $this->extensions = $extensions;
        $this->resolvedTypeFactory = $resolvedTypeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(string $name): ResolvedFieldTypeInterface
    {
        if (!isset($this->types[$name])) {
            $type = null;

            foreach ($this->extensions as $extension) {
                if ($extension->hasType($name)) {
                    $type = $extension->getType($name);

                    break;
                }
            }

            if (!$type) {
                // Support fully-qualified class names.
                if (!class_exists($name) || !in_array(FieldTypeInterface::class, class_implements($name), true)) {
                    throw new InvalidArgumentException(sprintf('Could not load type "%s"', $name));
                }

                $type = new $name();
            }

            $this->types[$name] = $this->resolveType($type);
        }

        return $this->types[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasType(string $name): bool
    {
        if (isset($this->types[$name])) {
            return true;
        }

        try {
            $this->getType($name);
        } catch (ExceptionInterface $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    private function resolveType(FieldTypeInterface $type): ResolvedFieldTypeInterface
    {
        $parentType = $type->getParent();
        $fqcn = get_class($type);

        $typeExtensions = [];

        foreach ($this->extensions as $extension) {
            $typeExtensions = array_merge(
                $typeExtensions,
                $extension->getTypeExtensions($fqcn)
            );
        }

        return $this->resolvedTypeFactory->createResolvedType(
            $type,
            $typeExtensions,
            $parentType ? $this->getType($parentType) : null
        );
    }
}
