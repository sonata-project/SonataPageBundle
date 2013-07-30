UPGRADE FROM 2.0 to 2.1
=======================

### Schema

    * Mapped Block::$settings to doctrine JsonType (previously an ArrayType)

        Add the new dependency sonata/doctrine-extensions :

            php composer.phar update

        Migration command :

            php app/console sonata:page:migrate-block-json --table page__bloc
            php app/console sonata:page:migrate-block-json --table page__bloc_audit

    * Block::$settings "name" property is now a "code" property.

        Database Migration: (replace table name)

            ALTER TABLE page__bloc ADD name VARCHAR2(255) DEFAULT NULL;

        Migration command: (change entity class as required)

            php app/console sonata:page:migrate-block-setting --class="Application\Sonata\PageBundle\Entity\Block"

        Optionally, set the --update-name parameter to true to update the "name" field with the old "name" setting.

            php app/console sonata:page:migrate-block-setting --update-name=true
