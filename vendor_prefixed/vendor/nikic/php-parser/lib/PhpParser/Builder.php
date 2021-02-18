<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser;

interface Builder
{
    /**
     * Returns the built node.
     *
     * @return Node The built node
     */
    public function getNode() : \WPCOM_VIP\PhpParser\Node;
}
