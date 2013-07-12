UPGRADE FROM 2.1 to 2.2
=======================

With the new render strategy, you'll need to update your config to add the following line:

    sonata_page:
        ignore_uri_patterns:
            - ^/_(.*)  # ignore symfony routes
    
