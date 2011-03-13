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
            ignore_route_patterns:
                - /(.*)admin(.*)/   # ignore admin route, ie route containing 'admin'
                - /^_(.*)/           # ignore symfony routes

            ignore_routes:

            ignore_uri_patterns:
                - /(.*)\/admin(.*)/   # ignore admin route, ie route containing 'admin'


## Page


              page -> bloc ----
               |         |      |
               |          ------
               |
                --> template




## Licence

### MIT

    * jWYSIWYG - https://github.com/akzhan/jwysiwyg