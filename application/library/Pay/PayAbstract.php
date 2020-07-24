<?php
namespace Pay;

abstract class PayAbstract
{
    abstract public function commit($data, $event);
}
