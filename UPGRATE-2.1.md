UPGRADE FROM 2.0 to 2.1
=======================

### Schema

    * Mapped Block::$settings to doctrine JsonType (previously an ArrayType)

        Add the new dependency sonata/doctrine-extensions :

            php composer.phar update

        Migration command :

            php app/console sonata:page:migrate-block-json --table page__bloc
            php app/console sonata:page:migrate-block-json --table page__bloc_audit
