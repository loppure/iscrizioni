<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsAdultValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $today = new \DateTime('now');
        $diff = $value->diff($today);
        if ($diff->y < 18) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
                ;
        }
    }
}
