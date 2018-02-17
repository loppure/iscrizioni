<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * YearsPaid
 *
 * @ORM\Table(name="years_paid")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YearsPaidRepository")
 */
class YearsPaid
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="user", type="string", length=255)
     * @ORM\OneToOne(targetEntity="User", mappedBy="email")
     */
    private $user;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="year", type="integer")
     */
    private $year;

    /**
     * @var \DateTime
     * @ORM\Column(name="payedAt", type="datetime")
     */
    private $payedAt;


    public function __construct($email, $year)
    {
        $this->user = $email;
        $this->year = $year;
        $this->payedAt = new \DateType();
    }

    /**
     * Set user.
     *
     * @param string $user
     *
     * @return YearsPaid
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set year.
     *
     * @param int $year
     *
     * @return YearsPaid
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year.
     *
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }
}
