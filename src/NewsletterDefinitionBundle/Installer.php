<?php
/**
 * Newsletter Definition.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2016-2017 W-Vision (http://www.w-vision.ch)
 * @license    https://github.com/w-vision/NewsletterDefinition/blob/master/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace NewsletterDefinitionBundle;

use Doctrine\DBAL\Migrations\Version;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Extension\Bundle\Installer\MigrationInstaller;

class Installer extends MigrationInstaller
{
    /**
     * {@inheritdoc}
     */
    public function migrateInstall(Schema $schema, Version $version)
    {
        $table = $schema->createTable('newsletter_definition');

        $table->addColumn('id', 'integer')
            ->setAutoincrement(true);
        $table->addColumn('name', 'string');
        $table->addColumn('class', 'string');
        $table->addColumn('filters', 'array');
        $table->setPrimaryKey(array("id"));
    }

    /**
     * {@inheritdoc}
     */
    public function migrateUninstall(Schema $schema, Version $version)
    {
        if ($schema->hasTable('newsletter_definition')) {
            $schema->dropTable('newsletter_definition');
        }
    }
}
