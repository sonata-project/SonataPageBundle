<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Block;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;

/**
 * PageExtension
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class RssBlockService extends BaseBlockService
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Rss Reader';
    }

    /**
     * @return array
     */
    public function getDefaultSettings()
    {
        return array(
            'url'     => false,
            'title'   => 'Insert the rss title'
        );
    }

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerInterface $manager
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    public function buildEditForm(CmsManagerInterface $manager, FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('url', 'url', array('required' => false)),
                array('title', 'text', array('required' => false)),
            )
        ));
    }

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerInterface $manager
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    function validateBlock(CmsManagerInterface $manager, ErrorElement $errorElement, BlockInterface $block)
    {
        $errorElement
            ->with('settings.url')
                ->assertNotNull(array())
                ->assertNotBlank()
            ->end()
            ->with('settings.title')
                ->assertNotNull(array())
                ->assertNotBlank()
                ->assertMaxLength(array('limit' => 50))
            ->end();
    }

    /**
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     * @return string
     */
    public function execute(CmsManagerInterface $manager, BlockInterface $block, PageInterface $page, Response $response = null)
    {
        // merge settings
        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());

        $feeds = false;
        if ($settings['url']) {
            $options = array(
                'http' => array(
                    'user_agent' => 'Sonata/RSS Reader',
                    'timeout' => 2,
                )
            );

            // retrieve contents with a specific stream context to avoid php errors
            $content = @file_get_contents($settings['url'], false, stream_context_create($options));

            if ($content) {
                // generate a simple xml element
                try {
                    $feeds = new \SimpleXMLElement($content);
                    $feeds = $feeds->channel->item;
                } catch(\Exception $e) {
                    // silently fail error
                }
            }
        }

        return $this->renderResponse('SonataPageBundle:Block:block_core_rss.html.twig', array(
            'feeds'     => $feeds,
            'block'     => $block,
            'settings'  => $settings
        ), $response);
    }
}