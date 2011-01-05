# Prototype to easily manage page

## Installation

* Add PageBundle to your src/Bundle dir

        git submodule add git@github.com:sonata-project/PageBundle.git src/Bundle/PageBundle

* Add PageBundle to your application kernel

        // app/AppKernel.php
        public function registerBundles()
        {
            return array(
                // ...
                new Bundle\PageBundle\PageBundle(),
                // ...
            );
        }


* Add in your config.yml file

        page.config:
            class: Bundle\Sonata\PageBundle\Page\Manager
            options:
                ignore_route_patterns:
                    - /(.*)admin(.*)/   # ignore admin route, ie route containing 'admin'
                    - /_(.*)/           # ignore symfony routes

                ignore_routes:

            blocks:
                - { id: core.container, class: Bundle\Sonata\PageBundle\Block\ContainerBlockService}
                - { id: core.text,      class: Bundle\Sonata\PageBundle\Block\TextBlockService}
                - { id: core.action,    class: Bundle\Sonata\PageBundle\Block\ActionBlockService}


## Page


              page -> bloc ----
               |         |      |
               |          ------
               |
                --> template




## Licence

### MIT

    * Blueprint css - https://github.com/joshuaclayton/blueprint-css/blob/master/LICENSE
    * jQuery - http://jquery.org/license
    * jWYSIWYG - https://github.com/akzhan/jwysiwyg