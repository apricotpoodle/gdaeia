<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddFulltextIndexToApplicationforms extends BaseMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('applicationforms');
        $table->addIndex(
            ['jobtitle', 'applicantname', 'qualification', 'reasonforreplacement'],
            [
                'name' => 'ft_applicationforms_global',
                'type' => 'fulltext',
            ]
        )->update();
    }
}