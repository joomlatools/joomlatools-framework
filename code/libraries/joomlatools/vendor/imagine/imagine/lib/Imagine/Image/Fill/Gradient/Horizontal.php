<?php

/*
 * This file is part of the Imagine package.
 *
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Joomlatools\Imagine\Image\Fill\Gradient;

use Joomlatools\Imagine\Image\PointInterface;

/**
 * Horizontal gradient fill
 */
final class Horizontal extends Linear
{
    /**
     * {@inheritdoc}
     */
    public function getDistance(PointInterface $position)
    {
        return $position->getX();
    }
}
