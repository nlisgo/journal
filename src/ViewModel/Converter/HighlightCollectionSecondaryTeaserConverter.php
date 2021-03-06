<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Highlight;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Meta;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class HighlightCollectionSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesDate;
    use CreatesTeaserImage;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Highlight $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var Collection $collection */
        $collection = $object->getItem();

        $curatedBy = 'Curated by '.$collection->getSelectedCurator()->getDetails()->getPreferredName();
        if ($collection->selectedCuratorEtAl()) {
            $curatedBy .= ' et al.';
        }

        return ViewModel\Teaser::secondary(
            $object->getTitle(),
            $this->urlGenerator->generate('collection', [$collection]),
            $curatedBy,
            $this->createContextLabel($collection),
            $object->getThumbnail() ? $this->smallTeaserImage($object) : null,
            ViewModel\TeaserFooter::forNonArticle(
                Meta::withLink(
                    new ViewModel\Link('Collection', $this->urlGenerator->generate('collections')),
                    $this->simpleDate($collection, $context)
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Highlight && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null) && $object->getItem() instanceof Collection;
    }
}
