<?php
namespace TransferAccounts;
class Index
{

    public function query($data,$event='query')
    {
        $className = 'TransferAccounts\\' . $data['class_name'];
        require $data['class_name'].'.php';
        $obj       = new $className();

        return $obj->$event($data);
    }
}