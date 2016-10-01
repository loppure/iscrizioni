<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsAdult extends Constraint"
{
    public $message = "Se sei minorenne devi scaricare questo modulo <a href= \" \" title= \"modulo iscrizioni minorenni\" target=\"_blank\">clicca qui</a>.";
}
