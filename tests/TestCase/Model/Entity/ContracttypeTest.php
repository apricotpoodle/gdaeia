<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Contracttype;
use Cake\TestSuite\TestCase;

class ContracttypeTest extends TestCase
{
    public function testCodesContrat(): void
    {
        $this->assertSame('cdi', Contracttype::CODE_CDI);
        $this->assertSame('cdd', Contracttype::CODE_CDD);
        $this->assertSame('interim', Contracttype::CODE_INTERIM);
        $this->assertSame('appr.', Contracttype::CODE_APPR);
        $this->assertSame('stage', Contracttype::CODE_STAGE);
        $this->assertSame('pige', Contracttype::CODE_PIGE);
        $this->assertSame('fact', Contracttype::CODE_FACT);
    }
}
