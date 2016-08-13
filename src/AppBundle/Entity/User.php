<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Omar Polo <yum1096@gmail.com>
 *
 * @ORM\Entity()
 * @ORM\Table(name="loppure_users")
 */
class User
{

    const SUPERIORI  = 0;
    const UNIVERSITA = 1;
    const LAVORATORE = 2;

    /**
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @ORM\Id
     */
    private $id;

    /**
     * @var String
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $firstname;

    /**
     * @var String
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $lastname;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birth", type="datetime")
     * @Assert\NotBlank()
     */
    private $birth;

    /**
     * @var String
     *
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var Array
     *
     * @ORM\Column(name="address", type="json_array")
     */
    private $address;

    /**
     * @var String
     *
     * @ORM\Column(name="job", type="smallint")
     */
    private $job;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Get Full name
     *
     * @return String
     */
    public function getFullName()
    {
        return $this->firstname . " " . $this->lastname;
    }

    /**
     * Set birth
     *
     * @param \DateTime $birth
     *
     * @return User
     */
    public function setBirth($birth)
    {
        $this->birth = $birth;

        return $this;
    }

    /**
     * Get birth
     *
     * @return \DateTime
     */
    public function getBirth()
    {
        return $this->birth;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set address
     *
     * @param array $address
     *
     * @return User
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return array
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Get address string
     *
     * @return string
     */
    public function getAddressString()
    {
        return implode(', ', $this->address);
    }

    /**
     * Set job
     *
     * @param string $job
     *
     * @return User
     */
    public function setJob($job)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * Get job
     *
     * @return string
     */
    public function getJob()
    {
        switch ($this->job) {
            case self::SUPERIORI:
                return 'Studente (superiori)';
                break;

            case self::UNIVERSITA:
                return 'Studente (università)';
                break;

            case self::UNIVERSITA:
                return 'Lavoratore';
                break;
        }
    }
}
