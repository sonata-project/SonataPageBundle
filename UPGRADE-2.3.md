UPGRADE FROM 2.2 to 2.3
=======================

The inline edition (moving block around) from the front website has been deprecated. This has been done for differents reasons:
* if the html is not valid the javascript can mess up the code.
* the layout management does not provide a good user experience.

The code will be remove in further version of PageBundle. if you want the old behavior you need to set to true the is_inline_edition_on key sonata_page configuration

The ``use_streamed_response`` is deprecated, the option is still available to avoid BC break