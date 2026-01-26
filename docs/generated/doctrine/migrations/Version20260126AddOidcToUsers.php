<?php
declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add OIDC columns to user table for Keycloak integration
 */
final class Version20260126AddOidcToUsers extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add OIDC columns to users table (oidc_sub, oidc_iss, last_login_at)';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('user');
        
        if (!$table->hasColumn('oidc_sub')) {
            $table->addColumn('oidc_sub', 'string', ['length' => 255, 'notnull' => false]);
        }
        
        if (!$table->hasColumn('oidc_iss')) {
            $table->addColumn('oidc_iss', 'string', ['length' => 255, 'notnull' => false]);
        }
        
        if (!$table->hasColumn('last_login_at')) {
            $table->addColumn('last_login_at', 'datetime_immutable', ['notnull' => false]);
        }
        
        if (!$table->hasIndex('uq_user_oidc')) {
            $table->addUniqueIndex(['oidc_sub', 'oidc_iss'], 'uq_user_oidc');
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('user');
        
        if ($table->hasIndex('uq_user_oidc')) {
            $table->dropIndex('uq_user_oidc');
        }
        
        if ($table->hasColumn('last_login_at')) {
            $table->dropColumn('last_login_at');
        }
        
        if ($table->hasColumn('oidc_iss')) {
            $table->dropColumn('oidc_iss');
        }
        
        if ($table->hasColumn('oidc_sub')) {
            $table->dropColumn('oidc_sub');
        }
    }
}
