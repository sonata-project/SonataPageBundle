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