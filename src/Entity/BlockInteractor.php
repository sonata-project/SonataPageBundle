<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sonata\PageBundle\Model\BlockInteractorInterface;
use Sonata\PageBundle\Model\BlockManagerInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class BlockInteractor implements BlockInteractorInterface
{
    /**
     * @var array<bool>
     */
    private array $pageBlocksLoaded = [];

    private ManagerRegistry $registry;

    private BlockManagerInterface $blockManager;

    public function __construct(ManagerRegistry $registry, BlockManagerInterface $blockManager)
    {
        $this->blockManager = $blockManager;
        $this->registry = $registry;
    }

    public function getBlock($id): ?PageBlockInterface
    {
        $blocks = $this->getEntityManager()->createQueryBuilder()
            ->select('b')
            ->from($this->blockManager->getClass(), 'b')
            ->where('b.id = :id')
            ->setParameters([
              'id' => $id,
            ])
            ->getQuery()
            ->execute();

        return $blocks[0] ?? null;
    }

    public function getBlocksById(PageInterface $page): array
    {
        $blocks = $this->getEntityManager()
            ->createQuery(sprintf('SELECT b FROM %s b INDEX BY b.id WHERE b.page = :page ORDER BY b.position ASC', $this->blockManager->getClass()))
            ->setParameters([
                 'page' => $page->getId(),
            ])
            ->execute();

        return $blocks;
    }

    public function saveBlocksPosition(array $data = [], bool $partial = true): bool
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();

        try {
            foreach ($data as $block) {
                if (!isset($block['id'], $block['position'], $block['parent_id'], $block['page_id'])) {
                    continue;
                }

                $this->blockManager->updatePosition($block['id'], $block['position'], $block['parent_id'], $block['page_id'], $partial);
            }

            $em->flush();
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();

            throw $e;
        }

        return true;
    }

    public function createNewContainer(array $values): PageBlockInterface
    {
        $container = $this->blockManager->create();
        $container->setEnabled($values['enabled'] ?? true);
        $container->setCreatedAt(new \DateTime());
        $container->setUpdatedAt(new \DateTime());
        $container->setType('sonata.page.block.container');

        if (isset($values['page'])) {
            $container->setPage($values['page']);
        }

        if (isset($values['name'])) {
            $container->setName($values['name']);
        } else {
            $container->setName($values['code'] ?? 'No name defined');
        }

        $container->setSettings(['code' => $values['code'] ?? 'no code defined']);
        $container->setPosition($values['position'] ?? 1);

        if (isset($values['parent'])) {
            $container->setParent($values['parent']);
        }

        $this->blockManager->save($container);

        return $container;
    }

    public function loadPageBlocks(PageInterface $page): array
    {
        if (isset($this->pageBlocksLoaded[$page->getId()])) {
            return [];
        }

        $blocks = $this->getBlocksById($page);

        foreach ($blocks as $block) {
            $parent = $block->getParent();

            if (null === $parent) {
                $page->addBlock($block);

                continue;
            }

            $parentId = $parent->getId();
            \assert(null !== $parentId);

            $blocks[$parentId]->addChild($block);
        }

        $id = $page->getId();
        \assert(null !== $id);

        $this->pageBlocksLoaded[$id] = true;

        return $blocks;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        $entityManager = $this->registry->getManagerForClass($this->blockManager->getClass());
        \assert($entityManager instanceof EntityManagerInterface);

        return $entityManager;
    }
}
