<?php

namespace App\Tests\Unitary;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\TestCase;

final class CalculateAverageRatingTest extends TestCase
{

    /**
     * @dataProvider provideData
     */
   public function testShouldCalculateAverageRating(VideoGame $videoGame, ?int $expectedAverageRating): void
   {
       $ratingHandler = new RatingHandler();
       $ratingHandler->calculateAverage($videoGame);

       self::assertSame($expectedAverageRating, $videoGame->getAverageRating());

   }



   private static function createVideoGame(int ...$ratings): VideoGame
   {
       $videoGame = new VideoGame();

       foreach($ratings as $rating){
           $videoGame->getReviews()->add((new Review())->setRating($rating));
       }
       return $videoGame;
   }
    public static function provideData(): iterable
    {
        yield 'No review' => [new VideoGame(), null];
        yield 'One review' => [self::createVideoGame(5), 5];
        yield 'A lot of reviews' => [
            self::createVideoGame(1, 2, 2, 3, 3, 3, 4, 4, 4, 4, 5, 5, 5, 5, 5),
            4,
        ];
    }

}