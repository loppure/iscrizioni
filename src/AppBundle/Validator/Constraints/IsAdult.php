<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsAdult extends Constraint
{
    public $message = "Devi essere <b>maggiorenne</b>";
}
