<?php

/*
    * To change this license header, choose License Headers in Project Properties.
    * To change this template file, choose Tools | Templates
    * and open the template in the editor.
 */

namespace AppBundle\DataFixtures\ORM\dev;

use AppBundle\Entity\Author;
use AppBundle\Tests\Utilities\AbstractDataFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Description of LoadAuthors
 *
 * @author mjoyce
 */
class LoadAuthors extends AbstractDataFixture implements OrderedFixtureInterface
{

    protected function doLoad(ObjectManager $manager) {
        $author = new Author();
        $author->setFullName('Sarah Simons');
        $author->setBirthPlace($this->getReference('place.paris'));
        $author->setDeathPlace($this->getReference('place.tuscon'));
        $author->setBirthDate('1919');
        $author->setDeathDate('1998');
        $author->setStatus($this->getReference('status.draft'));
        $author->addAlias($this->getReference('alias.alice'));
        $author->addAlias($this->getReference('alias.estelle'));
        $author->addResidence($this->getReference('place.paris'));
        $author->addPublication($this->getReference('publication.book'));
        $author->addPublication($this->getReference('publication.periodical'));
        $author->addPublication($this->getReference('publication.anthology'));
        $manager->persist($author);
        $manager->flush();
    }

    public function getOrder() {
        return 3;
    }

    protected function getEnvironments() {
        return ['dev'];
    }

}