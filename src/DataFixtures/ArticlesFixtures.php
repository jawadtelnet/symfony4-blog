<?php

namespace App\DataFixtures;

use App\Entity\ArticleLike;
use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\CommentLike;
use App\Repository\ArticleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ArticlesFixtures extends Fixture
{

    /**
     * Encodeur de mot de passe
     *
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder, ArticleRepository $articleRepo)
    {
        $this->encoder = $encoder;
        $this->articleRepo = $articleRepo;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        //users
        $users = [];
        for ($i = 1; $i < 10; $i++) {
            $user = new User;
            $user->setEmail('user' . $i . '@gmail.com')
                ->setUsername($faker->userName)
                ->setPassword($this->encoder->encodePassword($user, 'password'));
            $manager->persist($user);
            $users[] = $user;
        }

        //Categories
        $categoriesArray = ['Informatique', 'Reseaux telecom', 'Commerce'];
        $categories = [];
        foreach ($categoriesArray as $cat) {
            $category = new Category();
            $category->setLabel($cat);
            $manager->persist($category);
            $categories[] = $category;
        }

        //Articles
        $articles = [];
        for ($i = 0; $i < 6; $i++) {
            $article = new Article();
            $article->setTitle($faker->jobTitle)
                ->setContent($faker->text)
                ->setPicture('https://www.w3schools.com/images/w3schools_green.jpg')
                ->setIsPublished(true)
                ->setPublicationDate(new \DateTime())
                ->setLastUpdateDate(new \DateTime())
                ->addCategory($faker->randomElement($categories));
            $manager->persist($article);
            $articles[] = $article;
        }

        $comments = [];
        foreach ($articles as $article) {

            for ($j = 0; $j < 10; $j++) {
                //like
                $like = new ArticleLike();
                $like->setArticle($article)
                    ->setUser($faker->randomElement($users));
                $manager->persist($like);

                //commentaires                
                $comment = new Comment();
                $comment->setUser($faker->randomElement($users))
                    ->setContent($faker->text(50))
                    ->setCreatedAt(new \DateTime())
                    ->setArticle($article);
                $manager->persist($comment);
                $comments[] = $comment;
            }
        }

        foreach ($comments as $comment) {
            for ($i = 0; $i < mt_rand(0, 12); $i++) {
                $commentLike = new CommentLike();
                $commentLike->setComment($comment)
                    ->setUser($faker->randomElement($users));
                $manager->persist($commentLike);
            }
        }


        $manager->flush();
    }
}
