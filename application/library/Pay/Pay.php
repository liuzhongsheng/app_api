<?php

namespace Pay;

class Pay
{
    public function commit($data, $event = 'pay')
    {

        $className = 'Pay\\' . $data['pay_class_name'];
        $obj       = new PayQuery(new $className());
        return $obj->commit($data, $event);
    }
}
