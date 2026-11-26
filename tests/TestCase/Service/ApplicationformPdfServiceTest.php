<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Entity\Applicationform;
use App\Service\Metadata\FieldMetadataProviderInterface;
use App\Service\Pdf\ApplicationformPdfService;
use Authorization\IdentityInterface;
use Cake\ORM\Entity;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class ApplicationformPdfServiceTest extends TestCase
{
    public function testLaValidationDuControleDeGestionConserveSesLignesDansLePdf(): void
    {
        $validation = "Validation acceptée.\nDétail budgétaire et décision finale.";
        $html = $this->renderHtml($this->applicationform([
            'id' => 12,
            'control_management_validation' => $validation,
        ]));

        $this->assertStringContainsString('Validation Contrôle de gestion', $html);
        $this->assertMatchesRegularExpression(
            '/Validation acceptée\.<br>\s*Détail budgétaire et décision finale\./',
            $html,
        );
        $this->assertStringContainsString('min-height:2.8em', $html);
    }

    public function testLaValidationEstMasqueeQuandLeChampEstInterdit(): void
    {
        $html = $this->renderHtml(
            $this->applicationform([
                'id' => 12,
                'control_management_validation' => 'Validation réservée',
            ]),
            ['control_management_validation' => 'NONE'],
        );

        $this->assertStringNotContainsString('Validation réservée', $html);
    }

    public function testLePdfMasqueLesChampsSansRegleDansUneMatriceActive(): void
    {
        $html = $this->renderHtml(
            $this->applicationform([
                'id' => 12,
                'control_management_validation' => 'Validation sans règle explicite',
            ]),
            ['base' => 'NONE'],
        );

        $this->assertStringNotContainsString('Validation sans règle explicite', $html);
    }

    public function testLePdfAfficheUnChampExplicitementAutoriseDansUneMatriceActive(): void
    {
        $html = $this->renderHtml(
            $this->applicationform([
                'id' => 12,
                'control_management_validation' => 'Validation autorisée',
            ]),
            [
                'base' => 'NONE',
                'control_management_validation' => 'VIEW',
            ],
        );

        $this->assertStringContainsString('Validation autorisée', $html);
    }

    public function testLePdfMasqueLesChampsReservesSiLaZoneNestPasVisible(): void
    {
        $html = $this->renderHtml(
            $this->applicationform([
                'id' => 12,
                'qualification' => 'Information réservée au rôle autorisé',
                'control_management_validation' => 'Validation confidentielle',
            ]),
            [],
            reservesVisible: false,
        );

        $this->assertStringNotContainsString('Information réservée au rôle autorisé', $html);
        $this->assertStringNotContainsString('Validation confidentielle', $html);
    }

    public function testLePdfOmetLaValidationDuControleDeGestionSansValeur(): void
    {
        $html = $this->renderHtml($this->applicationform(['id' => 12]));

        $this->assertStringNotContainsString('Validation Contrôle de gestion', $html);
    }

    public function testLePdfResteImprimableQuandLesAssociationsOptionnellesSontAbsentes(): void
    {
        $html = $this->renderHtml($this->applicationform([
            'id' => 12,
            'department' => null,
            'contracttype' => null,
            'hiringreason' => null,
            'professionalcategory' => null,
            'worktime' => null,
            'budgetfeature' => null,
            'period' => null,
        ]));

        $this->assertStringNotContainsString('budgetfeature_id', $html);
        $this->assertStringNotContainsString('period_id', $html);
    }

    /**
     * @param array<string, string> $schema
     */
    private function renderHtml(
        Applicationform $applicationform,
        array $schema = [],
        bool $reservesVisible = true,
    ): string {
        $identity = $this->createStub(IdentityInterface::class);
        $identity->method('can')->willReturnCallback(
            static fn(string $ability, Applicationform $subject): bool => (
                $ability !== 'viewZoneReserves' || $reservesVisible
            ),
        );
        $metadata = $this->createStub(FieldMetadataProviderInterface::class);
        $metadata->method('label')->willReturnCallback(
            static fn(string $resource, string $field): string => $field,
        );
        $renderer = new ReflectionMethod(ApplicationformPdfService::class, 'renderHtml');

        return $renderer->invoke(new ApplicationformPdfService($metadata), $applicationform, $schema, [], $identity);
    }

    /** @param array<string, mixed> $fields */
    private function applicationform(array $fields): Applicationform
    {
        return new Applicationform($fields + [
            'department' => new Entity(['name' => 'Service', 'code' => 'SVC']),
            'contracttype' => new Entity(['name' => 'CDI', 'code' => 'CDI']),
            'hiringreason' => new Entity(['name' => 'Remplacement']),
            'professionalcategory' => new Entity(['name' => 'Catégorie']),
            'worktime' => new Entity(['name' => 'Temps complet']),
            'budgetfeature' => new Entity(['name' => 'Budget']),
            'period' => new Entity(['name' => 'Mois']),
        ]);
    }
}
