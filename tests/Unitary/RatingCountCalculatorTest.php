<?php

namespace App\Tests\Unitary;

use App\Model\Entity\NumberOfRatingPerValue;
use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\TestCase;

final class RatingCountCalculatorTest extends TestCase
{

    /**
     * @dataProvider provideData
     */
    public function testShouldCountRatingPerValue(VideoGame $videoGame, NumberOfRatingPerValue $numberOfRatingPerValue): void
    {
        $ratingHandler = new RatingHandler();
        // Calcul des stats
        $ratingHandler->countRatingsPerValue($videoGame);

        //  Vérification des résultats
        self::assertEquals($numberOfRatingPerValue, $videoGame->getNumberOfRatingsPerValue());

    }


    //  Création du jeu de reviews
    private static function createVideoGame(int ...$ratings): VideoGame
    {
        $videoGame = new VideoGame();

        foreach($ratings as $rating){
            $videoGame->getReviews()->add((new Review())->setRating($rating));
        }
        return $videoGame;
    }

    //  Génération des données de test

    /**
     * @return iterable<string, array{0: VideoGame, 1: NumberOfRatingPerValue}>
     */
    public static function provideData(): iterable
    {
        yield 'No review' => [
            new VideoGame(),
            new NumberOfRatingPerValue()
        ];
        yield 'One review' => [
            self::createVideoGame(5),
            self::createdExpectedState(five: 1),
        ];
        yield 'A lot of reviews' => [
            self::createVideoGame(1, 2, 2, 3, 3, 3, 4, 4, 4, 4, 5, 5, 5, 5, 5),
            self::createdExpectedState(1,2,3,4,5),
        ];
    }


    // Création de l'état attendu
    private static function createdExpectedState(int $one =0 , int $two = 0, int $three = 0, int $four = 0, int $five = 0):NumberOfRatingPerValue
    {
       $state = new NumberOfRatingPerValue();

       for($i=0;$i<$one;$i++){
           $state->increaseOne();
       }
       for($i=0;$i<$two;$i++){
           $state->increaseTwo();
       }
       for($i=0;$i<$three;$i++){
           $state->increaseThree();
       }
       for($i=0;$i<$four;$i++){
           $state->increaseFour();
       }
       for($i=0;$i<$five;$i++){
           $state->increaseFive();
       }
       return $state;
    }

}