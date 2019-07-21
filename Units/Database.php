<?php
/**
 * Created by PhpStorm.
 * User: XiaoLin
 * Date: 2019/7/21
 * Time: 22:18
 */

class Database
{
    private $db_file = '';

    public function __construct()
    {
        $this->db_file = __DIR__ . '/../Data/' . date('Ymd') . '.json';
        if (!file_exists($this->db_file))
        {
            file_put_contents($this->db_file,json_encode([
                'update_time' => time(),
                'orders' => [],
            ]));
        }

        return $this;
    }

    private function getData()
    {
        return json_decode(file_get_contents($this->db_file),true)['orders'];
    }

    private function writeFile($data)
    {
        if (file_put_contents($this->db_file, json_encode([
            'update_time' => time(),
            'orders' => $data,
        ])))
            return true;
        else
            return false;
    }

    public function insertOrder($id,$price,$payment,$raw_order_id,$redirect_url)
    {
        $data = $this->getData();
        $data[$id] = [
            'id' => $id,
            'price' => $price,
            'payment' => $payment,
            'raw_order_id' => $raw_order_id,
            'redirect_url' => $redirect_url,
            'status' => -1,
            'time' => time(),
        ];

        if ($this->writeFile($data))
            return true;
        else
            return false;
    }

    public function completeOrder($id)
    {
        $data = $this->getData();
        if (isset($data[$id]) && $data[$id]['status'] != 0)
        {
            $data[$id]['status'] = 0;

            if ($this->writeFile($data))
                return true;
        }
        return false;
    }

    public function cancelOrder($id)
    {
        $data = $this->getData();
        if (isset($data[$id]))
        {
            $data[$id]['status'] = -2;

            if ($this->writeFile($data))
                return true;
        }
        return false;
    }
}