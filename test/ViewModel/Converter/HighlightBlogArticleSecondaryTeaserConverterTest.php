<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\HighlightBlogArticleSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use Traversable;

final class HighlightBlogArticleSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['highlight'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new HighlightBlogArticleSecondaryTeaserConverter($this->stubUrlGenerator());
    }

    /**
     * @param Highlight $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getItem() instanceof BlogArticle) {
            yield $model;
        }
    }
}
