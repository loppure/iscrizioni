<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Payment as BasePayment;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class Payment extends BasePayment
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(name="loppure_user_id", referencedColumnName="id")
     */
    protected $loppureUser;

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
     * Set loppureUser
     *
     * @param \AppBundle\Entity\User $loppureUser
     *
     * @return Payment
     */
    public function setLoppureUser(\AppBundle\Entity\User $loppureUser = null)
    {
        $this->loppureUser = $loppureUser;

        return $this;
    }

    /**
     * Get loppureUserId
     *
     * @return \AppBundle\Entity\User
     */
    public function getLoppureUser()
    {
        return $this->loppureUser;
    }
}
