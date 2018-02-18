<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserToken
 *
 * @ORM\Table(name="user_token")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserTokenRepository")
 */
class UserToken
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="email", type="string", length=255)
     * @ORM\OneToOne(targetEntity="User")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="token", type="string", length=128)
     */
    private $token;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;


    /**
     * Initialise
     */
    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return UserToken
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set token.
     *
     * @param string $token
     *
     * @return UserToken
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
