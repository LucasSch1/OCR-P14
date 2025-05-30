<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;

final class FilterTest extends FunctionalTestCase
{
    //  Test l'accès à la liste des jeux
    public function testShouldListTenVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->clickLink('2');
        self::assertResponseIsSuccessful();
    }

    // Test le filtrage par recherche
    public function testShouldFilterVideoGamesBySearch(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->submitForm('Filtrer', ['filter[search]' => 'Jeu vidéo 49'], 'GET');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'article.game-card');
    }



    // Test le filtrage des jeux vidéos par tags
    /**
     * @dataProvider provideTagFilterData
     * @param array<string> $tagLabels
     */
    public function testShouldFilterVideoGamesByTag(array $tagLabels, int $expectedCount): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');


        $crawler = $this->client->getCrawler();
        $tagIds = [];
        foreach ($tagLabels as $label) {
            $labelNode = $crawler->filter("label:contains(\"$label\")");
            if ($labelNode->count() === 0) {

                continue;
            }
            $for = $labelNode->attr('for');
            $inputNode = $crawler->filter("#$for");
            if ($inputNode->count() === 1) {
                $tagIds[] = $inputNode->attr('value');
            }
        }

        if (empty($tagIds)) {
            $this->client->submitForm('Filtrer', [], 'GET');
        } else {
            $this->client->submitForm('Filtrer', ['filter' => ['tags' => $tagIds]], 'GET');
        }
        self::assertResponseIsSuccessful();

        if ($expectedCount > 0) {
            self::assertSelectorCount($expectedCount, 'article.game-card');
        } else {
            self::assertSelectorTextContains('body', 'Aucun résultat');
        }
    }


    // Jeu de données comprennant différent phase de test
    /**
     * @return array<string, array{0: array<string>, 1: int}>
     */
    public function provideTagFilterData(): array
    {
        return [
            'aucun tag' => [[], 10],
            'un tag existant' => [['Tag 0'], 10],
            'plusieurs tags existants' => [['Tag 0', 'Tag 1'], 8],
            'tag inexistant' => [['Tag Inexistant'], 10],
            'mix existant/inexistant' => [['Tag 0', 'Tag Inexistant'], 10],
        ];
    }



}
