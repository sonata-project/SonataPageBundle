# Prototype to easily manage media

## Installation

* Add MediaBundle to your src/Bundle dir

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