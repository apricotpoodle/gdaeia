<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use Cake\TestSuite\TestCase;

class ApplicationformTest extends TestCase
{
    public function testLeNomDuCandidatPrivilegieLaSaisiePuisLeCollaborateurEtLeRepli(): void
    {
        $this->assertSame('Marie Curie', (new Applicationform(['applicantname' => ' Marie Curie ']))->candidate_name);
        $this->assertSame('Ada Lovelace', (new Applicationform([
            'collaborator' => new User(['firstname' => 'Ada', 'lastname' => 'Lovelace']),
        ]))->candidate_name);
        $this->assertSame('-', (new Applicationform())->candidate_name);
    }
}
