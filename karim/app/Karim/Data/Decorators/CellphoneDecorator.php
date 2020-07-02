<?php

namespace App\Karim\Data\Decorators;

class CellphoneDecorator
{
    /* @var array<string> $cellphones */
    private $cellphones;

    public function __construct(array $cellphones = [])
    {
        $this->cellphones = $cellphones;
    }

    public function setCellphones(array $cellphones): void
    {
        $this->cellphones = $cellphones;
    }

    /**
     * @return array<string>
     */
    public function decorate(): array
    {
        $cellphones = [];
        foreach ($this->cellphones as $cellphone) {
            $c = "0" . strval($cellphone->user_id);

            // 09106802437 => 09106***437
            $cellphones[] = $this->string2Stars($c, 6, 8);
        }

        return $cellphones;
    }

    private function string2Stars($string, $first = 0, $last = 0): string
    {
        return substr($string, 0, $first) . '***' . substr($string, $last, 10);
    }
}
