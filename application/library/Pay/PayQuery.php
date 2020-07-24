<?php
namespace Pay;

class PayQuery
{

    private $class_mode;

    /**
     * 初始时，传入具体的策略对象
     * @param $mode
     */
    public function __construct($mode)
    {
        $this->class_mode = $mode;
    }

    /**
     * 提交支付
     * @param $money
     */
    public function commit($data, $event)
    {
        return $this->class_mode->commit($data, $event);
    }

}
