# Prototype to easily manage page

## Installation

* Add PageBundle to your src/Bundle dir

        git submodule add git@github.com:sonata-project/PageBundle.git src/Sonata/PageBundle

* Add PageBundle to your application kernel

        // app/AppKernel.php
        public function registerBundles()
        {
            return array(
                // ...
                new Sonata\PageBundle\SonataPageBundle(),
                // ...
            );
        }

* Add these configuration into the routing file

    homepage:
        pattern:  /
        defaults: { _controller: SonataPageBundle:Page:homepage }

    page_block:
        resource: '@SonataPageBundle/Resources/config/routing/block.xml'
        prefix: /page/block

* Add in your config.yml file

        sonata_page:
            class: Sonata\PageBundle\Page\Manager
            options:
                ignore_route_patterns:
                    - /(.*)admin(.*)/   # ignore admin route, ie route containing 'admin'
                    - /^_(.*)/           # ignore symfony routes

                ignore_routes:

                ignore_uri_patterns:
                    - /(.*)\/admin(.*)/   # ignore admin route, ie route containing 'admin'

            blocks:
                - { id: core.container, class: Sonata\PageBundle\Block\ContainerBlockService}
                - { id: core.text,      class: Sonata\PageBundle\Block\TextBlockService}
                - { id: core.action,    class: Sonata\PageBundle\Block\ActionBlockService}

* Add this in your admin.yml

        page:
            label:      Page
            group:      CMS
            class:      Sonata\PageBundle\Admin\PageAdmin
            entity:     Application\Sonata\PageBundle\Entity\Page
            controller: SonataPageBundle:PageAdmin
            children:
                block:
                    label:      Block
                    group:      CMS
                    class:      Sonata\PageBundle\Admin\BlockAdmin
                    entity:     Application\Sonata\PageBundle\Entity\Block
                    controller: SonataPageBundle:BlockAdmin

        block:
            label:      Block
            group:      CMS
            class:      Sonata\PageBundle\Admin\BlockAdmin
            entity:     Application\Sonata\PageBundle\Entity\Block
            controller: SonataPageBundle:BlockAdmin

        template:
            label:      Template
            group:      CMS
            class:      Sonata\PageBundle\Admin\TemplateAdmin
            entity:     Application\Sonata\PageBundle\Entity\Template
            controller: SonataPageBundle:TemplateAdmin
            options:
                show_in_dashboard: false


## Page


              page -> bloc ----
               |         |      |
               |          ------
               |
                --> template




## Licence

### MIT

    * jWYSIWYG - https://github.com/akzhan/jwysiwyg