<?php

namespace Hyperf\Zby\Annotation\Validation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_METHOD)]
class Max extends Validator
{
   public function __construct(public int $length, string $message = '', string $group = '')
   {
       $this->message = $message;
       $this->group = $group;
   }
}