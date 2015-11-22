<?php

namespace GP\CompanyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompanyRole
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="GP\CompanyBundle\Entity\CompanyRoleRepository")
 */
class CompanyRole
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
