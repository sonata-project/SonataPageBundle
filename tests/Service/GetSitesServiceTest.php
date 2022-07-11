<?php

namespace Sonata\PageBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Exception\ParameterNotAllowedException;
use Sonata\PageBundle\Model\Site;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Service\GetSitesService;

class GetSitesServiceTest extends TestCase
{
    /**
     * @dataProvider getProvidedData
     */
    public function testItWillGetAllSites(array $ids, int $findAllWasCalledExactly, int $findByWasCalledExactly)
    {
        //Mock
        $siteMock = $this->createMock(Site::class);
        $siteManagerMock = $this->createMock(SiteManagerInterface::class);
        $siteManagerMock
            ->expects(static::exactly($findAllWasCalledExactly))
            ->method('findAll')
            ->willReturn([$siteMock]);

        $siteManagerMock
            ->expects(static::exactly($findByWasCalledExactly))
            ->method('findBy')
            ->willReturn([$siteMock]);

        //Run code
        $getSitesService =  new GetSitesService($siteManagerMock);
        $result = $getSitesService->findSitesById($ids);

        //Asserts
        static::assertEquals([$siteMock], $result);
    }

    public function getProvidedData(): array
    {
        return [
            [['all'], 1, 0],
            [[1, 2], 0, 1],
        ];
    }

    /**
     * @testdox It won't accept any string value different of "all".
     */
    public function testDoNotAcceptAnyStringDifferentOfAll()
    {
        //Mock
        $siteManagerMock = $this->createMock(SiteManagerInterface::class);
        $siteManagerMock
            ->expects(static::any())
            ->method('findAll');

        $siteManagerMock
            ->expects(static::any())
            ->method('findBy');


        //Assert
        static::expectException(ParameterNotAllowedException::class);
        static::expectExceptionMessage('The parameter "otherValueDifferentOfAll" is not allowed.');

        //Run code
        $getSitesService =  new GetSitesService($siteManagerMock);
        $getSitesService->findSitesById(['otherValueDifferentOfAll']);
    }
}