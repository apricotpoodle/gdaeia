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

    public function testSynchroniseLeNomHistoriqueDepuisLeNomEtLePrenom(): void
    {
        $applicationform = new Applicationform([
            'applicant_first_name' => ' Ada ',
            'applicant_last_name' => ' Lovelace ',
            'applicantname' => 'Ancienne valeur',
        ]);

        $applicationform->synchronizeApplicantName();

        $this->assertSame('Ada Lovelace', $applicationform->applicantname);
    }

    public function testPreserveLeNomHistoriqueDesDemandesSansChampsV3(): void
    {
        $applicationform = new Applicationform(['applicantname' => 'Marie Curie']);

        $applicationform->synchronizeApplicantName();

        $this->assertSame('Marie Curie', $applicationform->applicantname);
    }

    public function testEffaceLesChampsExternesQuandUnCollaborateurEstSelectionne(): void
    {
        $applicationform = new Applicationform([
            'collaborator_id' => 12,
            'applicant_first_name' => 'Ancien',
            'applicant_last_name' => 'Candidat',
            'applicantname' => 'Collaborateur sélectionné',
        ]);

        $applicationform->synchronizeApplicantName();

        $this->assertSame('Collaborateur sélectionné', $applicationform->applicantname);
        $this->assertNull($applicationform->applicant_first_name);
        $this->assertNull($applicationform->applicant_last_name);
    }

    public function testEffaceLeNomHistoriqueLorsqueLesChampsV3SontVides(): void
    {
        $applicationform = new Applicationform([
            'applicant_first_name' => '',
            'applicant_last_name' => '',
            'applicantname' => 'Ancienne valeur',
        ]);

        $applicationform->synchronizeApplicantName();

        $this->assertSame('', $applicationform->applicantname);
    }

    public function testUneSelectionDeCollaborateurVideConserveLeCandidatExterne(): void
    {
        $applicationform = new Applicationform([
            'collaborator_id' => '',
            'applicant_first_name' => 'Ada',
            'applicant_last_name' => 'Lovelace',
            'applicantname' => '',
        ]);

        $applicationform->synchronizeApplicantName();

        $this->assertSame('Ada Lovelace', $applicationform->applicantname);
        $this->assertSame('Ada', $applicationform->applicant_first_name);
        $this->assertSame('Lovelace', $applicationform->applicant_last_name);
    }
}
