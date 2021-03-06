<?php

namespace eLife\Journal\Helper;

use eLife\ApiSdk\Model\Image;
use InvalidArgumentException;

trait CreatesIiifUri
{
    final protected function iiifUri(Image $image, int $width = null, int $height = null, string $extension = null) : string
    {
        $uri = $image->getUri();
        $region = 'full';
        $size = 'full';

        if (($width && $width > ($image->getWidth() * 2)) || ($height && $height > ($image->getHeight() * 2))) {
            throw new InvalidArgumentException("Unable to scale the image $uri that large (requested width: $width, requested height: $height; actual width: {$image->getWidth()}, actual height {$image->getHeight()})");
        }

        if ($width && $height) {
            if (($width / $height) !== ($image->getWidth() / $image->getHeight())) {
                $region = IiifRegionCalculator::calculateForImage($image, $width, $height);
                $size = "$width,$height";
            } elseif ($width !== $image->getWidth()) {
                $size = "$width,";
            } elseif ($height !== $image->getHeight()) {
                $size = ",$height";
            }
        } elseif ($width && $width !== $image->getWidth()) {
            $size = "$width,";
        } elseif ($height && $height !== $image->getHeight()) {
            $size = ",$height";
        }

        if (empty($extension)) {
            $mediaType = explode(';', $image->getSource()->getMediaType(), 2);
            switch ($mediaType[0]) {
                case 'image/png':
                    $extension = 'png';
                    break;
                default:
                    $extension = 'jpg';
            }
        }

        return "$uri/$region/$size/0/default.$extension";
    }
}
