<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Model\Entity\VideoGame;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ShowTest extends FunctionalTestCase
{
    // Test pour l'affichage du jeu vidéo
    public function testShouldShowVideoGame(): void
    {
        $this->get('/jeu-video-1');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Jeu vidéo 1');
    }


    // Test pour poster un avis
    public function testShouldPostReview(): void
    {
        $this->login();
        $this->get('/jeu-video-1');
        self::assertResponseIsSuccessful();
        $crawler = $this->client->request(Request::METHOD_GET, '/jeu-video-1');
        $link = $crawler->selectLink('Avis')->link();
        $this->client->click($link);
        self::assertSelectorTextContains('#pane-reviews h2', 'Avis des lecteurs');
        $this->submit(
            'Poster',
            self::getFormData()
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();
        $videoGame = $this->getEntityManager()->getRepository(VideoGame::class)->findOneBy(['slug' => 'jeu-video-1']);
        self::assertNotNull($videoGame);
        $reviews = null;
        foreach ($videoGame->getReviews() as $review) {
            if($review->getUser()->getEmail() === 'user+0@email.com') {
                $reviews = $review;
                break;
            }
        }
        self::assertNotNull($reviews);
        self::assertSame(4, $reviews->getRating());
        self::assertSame('Mon commentaire', $reviews->getComment());

        self::assertSelectorTextContains('div.list-group-item:last-child h3', 'user+0');
        self::assertSelectorTextContains('div.list-group-item:last-child p', 'Mon commentaire');
        self::assertSelectorTextContains('div.list-group-item:last-child span.value', '4');
        self::assertSelectorNotExists('form[name="review"]');


    }


    // Test pour ne pas afficher le formulaire en tant qu'invité
    public function testFormReviewNotShowForeGuest():void
    {
        $this->get('/jeu-video-1');
        self::assertResponseIsSuccessful();
        $crawler = $this->client->request(Request::METHOD_GET, '/jeu-video-1');
        $link = $crawler->selectLink('Avis')->link();
        $this->client->click($link);
        self::assertSelectorTextContains('#pane-reviews h2', 'Avis des lecteurs');
        self::assertSelectorNotExists('form[name="review"]');

    }


    // Test pour ne pas soumettre une requete POST en tant qu'invité
    public function testGuestCannotSubmitFormReview():void
    {
        $this->get('/jeu-video-1');
        $crawler = $this->client->request(Request::METHOD_GET, '/jeu-video-1');
        $link = $crawler->selectLink('Avis')->link();
        $this->client->click($link);
        $formData= self::getFormData();
        $this->client->request(
            Request::METHOD_POST,
            '/jeu-video-1',
            $formData,
        );
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }


    // Test pour envoyer un formulaire invalide

    /**
     * @dataProvider provideInvalidFormData
     * @param array<string, string> $formData
     */
    public function testShouldNotPostReview(array $formData): void
    {
        $this->login();
        $this->get('/jeu-video-1');
        self::assertResponseIsSuccessful();
        $crawler = $this->client->request(Request::METHOD_GET, '/jeu-video-1');
        $link = $crawler->selectLink('Avis')->link();
        $this->client->click($link);
        self::assertSelectorTextContains('#pane-reviews h2', 'Avis des lecteurs');
        $this->submit('Poster', $formData);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

    }


    // Créer des données invalides
    /**
     * @return iterable<string, array<int, array<string, mixed>>>
     */
    public static function provideInvalidFormData(): iterable
    {
        yield 'too long comment' => [self::getFormData(['review[comment]' => str_repeat('a', 1001)])];
        yield 'empty rating' => [['review[comment]' => 'Un commentaire sans note']];
    }

    // Créer des données valides
    /**
     * @param array<string, mixed> $overrideData
     * @return array<string, mixed>
     */
    public static function getFormData(array $overrideData = []): array
    {
        return $overrideData + [
                'review[rating]' => '4',
                'review[comment]' => 'Mon commentaire',
            ];
    }


}
