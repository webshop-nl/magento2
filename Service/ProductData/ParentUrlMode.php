<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Service\ProductData;

/**
 * What a child does with the link of its parent.
 *
 * The admin option behind this is not the same everywhere: some modules offer No/Yes, others
 * No/Yes/Yes-with-Auto-Link, so the stored 1 means "plain" in one and "with the hash" in another.
 * The collector used to read that number directly and put the hash on the wrong setting. Each
 * module maps its own option onto this enum instead, and the shared collector reads meaning.
 */
enum ParentUrlMode: int
{
    /**
     * The child keeps its own link.
     */
    case No = 0;

    /**
     * The child is published on the parent's page.
     */
    case Plain = 1;

    /**
     * The parent's page, with the attribute hash that selects this child on arrival.
     */
    case AutoLink = 2;

    /**
     * Whether the child is published on its parent's page at all.
     *
     * @return bool
     */
    public function usesParentUrl(): bool
    {
        return $this !== self::No;
    }
}
