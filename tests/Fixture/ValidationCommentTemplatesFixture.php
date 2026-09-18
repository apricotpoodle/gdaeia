<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/** Jeu de données isolé pour le catalogue des commentaires de validation. */
class ValidationCommentTemplatesFixture extends TestFixture
{
    /**
     * @var string
     */
    public string $table = 'validation_comment_templates';

    /** Initialise un catalogue vide. */
    public function init(): void
    {
        $this->records = [];

        parent::init();
    }
}
