<?php
use PHPUnit\Framework\TestCase;

require_once realpath('C:\Users\04574440961\vendor/autoload.php');


class PergamumTest extends TestCase
{
    public function testThesisYearCount()
    {
        $p = new Pergamum();
        $t = $p->getAllThesis(1945);
        $this->assertEquals(0, count($t));

        $t = $p->getAllThesis(2016);
        $this->assertGreaterThan(0, count($t));
    }

    public function testThesisTonini()
    {
        $p = new Pergamum();
        $t = $p->getAllThesis(2014);
        $count=0;
        $results = array_filter($t, function($tese) {
            if ($tese['autor'] == 'TONINI, Gustavo Alexssandro.'){
                $count++;
            }
        });
        $this->assertEquals(1, $count);
    }
}
?>