<?php

/*
 * This file is part of the Imagine package.
 *
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Joomlatools\Imagine\Filter\Advanced;

use Joomlatools\Imagine\Filter\FilterInterface;
use Joomlatools\Imagine\Image\ImageInterface;
use Joomlatools\Imagine\Image\Point;

/**
 * The Grayscale filter calculates the gray-value based on RGB.
 */
class Grayscale extends OnPixelBased implements FilterInterface
{
    public function __construct()
    {
        parent::__construct(function (ImageInterface $image, Point $point) {
            $color = $image->getColorAt($point);
            $image->draw()->dot($point, $color->grayscale());
        });
    }
}
