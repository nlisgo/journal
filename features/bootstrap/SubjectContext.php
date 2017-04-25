<?php

use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class SubjectContext extends Context
{
    private $emptyArticles;
    private $numberOfSubjects;
    private $numberOfArticles;
    private $numberOfHighlightedArticles;
    private $numberOfPodcastEpisodes;

    /**
     * @BeforeScenario
     */
    public function resetEmptyArticles()
    {
        $this->emptyArticles = true;
    }

    /**
     * @Given /^there are (\d+) subjects$/
     */
    public function thereAreSubjects(int $number)
    {
        $this->numberOfSubjects = $number;

        $subjects = [];

        for ($i = $number; $i > 0; --$i) {
            $subjects[] = [
                'id' => 'subject'.$i,
                'name' => 'Subject '.$i.' name',
                'impactStatement' => 'Subject '.$i.' impact statement.',
                'image' => [
                    'banner' => [
                        'uri' => "https://www.example.com/iiif/banner$i",
                        'alt' => '',
                        'source' => [
                            'mediaType' => 'image/jpeg',
                            'uri' => "https://www.example.com/banner$i.jpg",
                            'filename' => "banner$i.jpg",
                        ],
                        'size' => [
                            'width' => 1800,
                            'height' => 1600,
                        ],
                    ],
                    'thumbnail' => [
                        'uri' => "https://www.example.com/iiif/thumbnail$i",
                        'alt' => '',
                        'source' => [
                            'mediaType' => 'image/jpeg',
                            'uri' => "https://www.example.com/thumbnail$i.jpg",
                            'filename' => "thumbnail$i.jpg",
                        ],
                        'size' => [
                            'width' => 800,
                            'height' => 600,
                        ],
                    ],
                ],
            ];
        }

        foreach (array_chunk($subjects, 100) as $i => $subjectsChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/subjects?page=$page&per-page=100&order=asc",
                    ['Accept' => 'application/vnd.elife.subject-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.subject-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => $subjectsChunk,
                    ])
                )
            );
        }
    }

    private function mockSubject(string $subject)
    {
        $subjectId = $this->createSubjectId($subject);

        static::mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/subjects/$subjectId",
                [
                    'Accept' => 'application/vnd.elife.subject+json; version=1',
                ]
            ),
            new Response(
                200,
                [
                    'Content-Type' => 'application/vnd.elife.subject+json; version=1',
                ],
                json_encode([
                    'id' => $subjectId,
                    'name' => $subject,
                    'impactStatement' => "$subject impact statement.",
                    'image' => [
                        'banner' => [
                            'uri' => 'https://www.example.com/iiif/banner',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/banner.jpg',
                                'filename' => 'banner.jpg',
                            ],
                            'size' => [
                                'width' => 1800,
                                'height' => 1600,
                            ],
                        ],
                        'thumbnail' => [
                            'uri' => 'https://www.example.com/iiif/thumbnail',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/thumbnail.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 800,
                                'height' => 600,
                            ],
                        ],
                    ],
                ])
            )
        );
    }

    /**
     * @Given /^there are (\d+) articles with the MSA \'([^\']*)\'$/
     */
    public function thereAreArticlesWithTheMSA(int $number, string $subject)
    {
        $this->emptyArticles = false;
        $this->numberOfArticles = $number;

        $articles = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        $subjectId = $this->createSubjectId($subject);

        $this->mockSubject($subject);

        for ($i = $number; $i > 0; --$i) {
            $articles[] = [
                'type' => 'collection',
                'id' => "$i",
                'title' => 'Collection '.$i.' title',
                'published' => $today->format(ApiSdk::DATE_FORMAT),
                'image' => [
                    'banner' => [
                        'uri' => 'https://www.example.com/iiif/banner',
                        'alt' => '',
                        'source' => [
                            'mediaType' => 'image/jpeg',
                            'uri' => 'https://www.example.com/banner.jpg',
                            'filename' => 'banner.jpg',
                        ],
                        'size' => [
                            'width' => 1800,
                            'height' => 1600,
                        ],
                    ],
                    'thumbnail' => [
                        'uri' => 'https://www.example.com/iiif/image',
                        'alt' => '',
                        'source' => [
                            'mediaType' => 'image/jpeg',
                            'uri' => 'https://www.example.com/image.jpg',
                            'filename' => 'image.jpg',
                        ],
                        'size' => [
                            'width' => 800,
                            'height' => 600,
                        ],
                    ],
                ],
                'subjects' => [
                    [
                        'id' => $subjectId,
                        'name' => $subject,
                    ],
                ],
                'selectedCurator' => [
                    'id' => "$i",
                    'type' => 'senior-editor',
                    'name' => [
                        'preferred' => 'Person '.$i,
                        'index' => $i.', Person',
                    ],
                ],
                'curators' => [
                    [
                        'id' => "$i",
                        'type' => 'senior-editor',
                        'name' => [
                            'preferred' => 'Person '.$i,
                            'index' => $i.', Person',
                        ],
                    ],
                ],
                'content' => [
                    [
                        'type' => 'blog-article',
                        'id' => "$i",
                        'title' => 'Blog article '.$i.' title',
                        'published' => $today->format(ApiSdk::DATE_FORMAT),
                    ],
                ],
            ];
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&subject[]=$subjectId&type[]=research-article&type[]=research-advance&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&use-date=default",
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => array_map(function (array $collection) {
                        unset($collection['image']['banner']);
                        unset($collection['curators']);
                        unset($collection['content']);

                        return $collection;
                    }, [$articles[0]]),
                    'subjects' => [
                        [
                            'id' => $subjectId,
                            'name' => $subject,
                            'results' => count($articles),
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-exchange' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => $this->numberOfArticles,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        foreach (array_chunk($articles, 6) as $i => $articleChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/search?for=&page=$page&per-page=6&sort=date&order=desc&subject[]=$subjectId&type[]=research-article&type[]=research-advance&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&use-date=default",
                    ['Accept' => 'application/vnd.elife.search+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => array_map(function (array $collection) {
                            unset($collection['image']['banner']);
                            unset($collection['curators']);
                            unset($collection['content']);

                            return $collection;
                        }, $articleChunk),
                        'subjects' => [
                            [
                                'id' => $subjectId,
                                'name' => $subject,
                                'results' => count($articles),
                            ],
                        ],
                        'types' => [
                            'correction' => 0,
                            'editorial' => 0,
                            'feature' => 0,
                            'insight' => 0,
                            'research-advance' => 0,
                            'research-article' => 0,
                            'research-exchange' => 0,
                            'retraction' => 0,
                            'registered-report' => 0,
                            'replication-study' => 0,
                            'short-report' => 0,
                            'tools-resources' => 0,
                            'blog-article' => 0,
                            'collection' => $this->numberOfArticles,
                            'interview' => 0,
                            'labs-experiment' => 0,
                            'podcast-episode' => 0,
                        ],
                    ])
                )
            );
        }
    }

    /**
     * @Given /^there are (\d+) podcast episodes with the MSA \'([^\']*)\'$/
     */
    public function thereArePodcastEpisodesWithTheMSA(int $number, string $subject)
    {
        $this->numberOfPodcastEpisodes = $number;

        $episodes = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        $subjectId = $this->createSubjectId($subject);

        $this->mockSubject($subject);

        for ($i = $number; $i > 0; --$i) {
            $episodes[] = [
                'type' => 'podcast-episode',
                'number' => $i,
                'title' => "Episode $i title",
                'impactStatement' => "Episode $i impact statement",
                'published' => $today->format(ApiSdk::DATE_FORMAT),
                'image' => [
                    'banner' => [
                        'uri' => 'https://www.example.com/iiif/banner',
                        'alt' => '',
                        'source' => [
                            'mediaType' => 'image/jpeg',
                            'uri' => 'https://www.example.com/banner.jpg',
                            'filename' => 'banner.jpg',
                        ],
                        'size' => [
                            'width' => 1800,
                            'height' => 1600,
                        ],
                    ],
                    'thumbnail' => [
                        'uri' => 'https://www.example.com/iiif/image',
                        'alt' => '',
                        'source' => [
                            'mediaType' => 'image/jpeg',
                            'uri' => 'https://www.example.com/image.jpg',
                            'filename' => 'image.jpg',
                        ],
                        'size' => [
                            'width' => 800,
                            'height' => 600,
                        ],
                    ],
                ],
                'sources' => [
                    [
                        'mediaType' => 'audio/mpeg',
                        'uri' => $this->locatePath('/audio-file'),
                    ],
                ],
                'subjects' => [
                    [
                        'id' => $subjectId,
                        'name' => $subject,
                    ],
                ],
            ];
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&subject[]=$subjectId&type[]=podcast-episode&use-date=default",
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => [$episodes[0]],
                    'subjects' => [
                        [
                            'id' => $subjectId,
                            'name' => $subject,
                            'results' => count($episodes),
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-exchange' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => $this->numberOfPodcastEpisodes,
                    ],
                ])
            )
        );
    }

    /**
     * @Given /^there are (\d+) highlighted articles with the MSA \'([^\']*)\'$/
     */
    public function thereAreHighlightedArticlesWithTheMSA(int $number, string $subject)
    {
        $this->numberOfHighlightedArticles = $number;

        $articles = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        $subjectId = $this->createSubjectId($subject);

        $this->mockSubject($subject);

        for ($i = $number; $i > 0; --$i) {
            $articles[] = [
                'title' => "Collection $i highlight title",
                'item' => [
                    'type' => 'collection',
                    'id' => "$i",
                    'title' => 'Collection '.$i.' title',
                    'published' => $today->format(ApiSdk::DATE_FORMAT),
                    'image' => [
                        'thumbnail' => [
                            'uri' => 'https://www.example.com/iiif/image',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/image.jpg',
                                'filename' => 'image.jpg',
                            ],
                            'size' => [
                                'width' => 800,
                                'height' => 600,
                            ],
                        ],
                    ],
                    'subjects' => [
                        [
                            'id' => $subjectId,
                            'name' => $subject,
                        ],
                    ],
                    'selectedCurator' => [
                        'id' => "$i",
                        'type' => 'senior-editor',
                        'name' => [
                            'preferred' => 'Person '.$i,
                            'index' => $i.', Person',
                        ],
                    ],
                ],
            ];
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/highlights/$subjectId",
                ['Accept' => 'application/vnd.elife.highlights+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.highlights+json; version=1'],
                json_encode($articles)
            )
        );
    }

    /**
     * @When /^I go the Subjects page$/
     */
    public function iGoTheSubjectsPage()
    {
        $this->visitPath('/subjects');
    }

    /**
     * @When /^I go the MSA \'([^\']*)\' page$/
     */
    public function iGoTheMSAPage(string $subject)
    {
        $subjectId = $this->createSubjectId($subject);

        if ($this->emptyArticles) {
            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/search?for=&page=1&per-page=6&sort=date&order=desc&subject[]=$subjectId&type[]=research-article&type[]=research-advance&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&use-date=default",
                    ['Accept' => 'application/vnd.elife.search+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                    json_encode([
                        'total' => 0,
                        'items' => [],
                        'subjects' => [
                            [
                                'id' => $subjectId,
                                'name' => $subject,
                                'results' => 0,
                            ],
                        ],
                        'types' => [
                            'correction' => 0,
                            'editorial' => 0,
                            'feature' => 0,
                            'insight' => 0,
                            'research-advance' => 0,
                            'research-article' => 0,
                            'research-exchange' => 0,
                            'retraction' => 0,
                            'registered-report' => 0,
                            'replication-study' => 0,
                            'short-report' => 0,
                            'tools-resources' => 0,
                            'blog-article' => 0,
                            'collection' => 0,
                            'interview' => 0,
                            'labs-experiment' => 0,
                            'podcast-episode' => 0,
                        ],
                    ])
                )
            );
        }

        $this->visitPath("/subjects/$subjectId");
    }

    /**
     * @When /^I load more articles$/
     */
    public function iLoadMoreArticles()
    {
        $this->getSession()->getPage()->clickLink('More articles');
    }

    /**
     * @Then /^I should see the (\d+) subjects\.$/
     */
    public function iShouldSeeTheSubjects(int $number)
    {
        $this->assertSession()->elementsCount('css', '.grid-listing > .grid-listing-item', $number);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfSubjects - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.grid-listing > .grid-listing-item:nth-child('.$nthChild.')',
                'Subject '.$expectedNumber.' name'
            );
        }
    }

    /**
     * @Then /^I should see the latest (\d+) items with the MSA \'([^\']*)\' in the 'Latest articles' list$/
     */
    public function iShouldSeeTheLatestItemsWithTheMSAInTheLatestArticlesList(int $number, string $subject)
    {
        $this->spin(function () use ($number, $subject) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest articles") + .listing-list > .listing-list__item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);
                $expectedNumber = ($this->numberOfArticles - $nthChild + 1);

                $this->assertSession()->elementContains(
                    'css',
                    '.list-heading:contains("Latest articles") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                    'Collection '.$expectedNumber.' title'
                );
                $this->assertSession()->elementContains(
                    'css',
                    '.list-heading:contains("Latest articles") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                    $subject
                );
            }
        });
    }

    private function createSubjectId(string $subjectName) : string
    {
        return md5($subjectName);
    }

    /**
     * @Then /^I should see the latest podcast episode with the MSA \'([^\']*)\' in the 'Highlights' list$/
     */
    public function iShouldSeeTheLatestPodcastEpisodeWithTheMSAInTheList(string $subject)
    {
        $expectedNumber = $this->numberOfPodcastEpisodes;

        $this->assertSession()->elementContains(
            'css',
            '.list-heading:contains("Highlights") + .listing-list > .listing-list__item:nth-child(1)',
            "Episode $expectedNumber title"
        );
        $this->assertSession()->elementContains(
            'css',
            '.list-heading:contains("Highlights") + .listing-list > .listing-list__item:nth-child(1)',
            $subject
        );
    }

    /**
     * @Given /^I should see the latest (\d+) highlighted articles with the MSA \'([^\']*)\' in the 'Highlights' list$/
     */
    public function iShouldSeeTheLatestHighlightedArticlesWithTheMSAInTheList(int $number, string $subject)
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Highlights") + .listing-list > .listing-list__item', $number + 1);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 2);
            $expectedNumber = ($this->numberOfHighlightedArticles - $nthChild + 2);

            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Highlights") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                "Collection $expectedNumber highlight title"
            );
            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Highlights") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                $subject
            );
        }
    }
}
