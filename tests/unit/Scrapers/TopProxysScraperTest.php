<?php declare(strict_types=1);

namespace Vantoozz\ProxyScraper\UnitTests\Scrapers;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Vantoozz\ProxyScraper\Enums\Metrics;
use Vantoozz\ProxyScraper\Exceptions\HttpClientException;
use Vantoozz\ProxyScraper\Exceptions\ScraperException;
use Vantoozz\ProxyScraper\HttpClient\HttpClientInterface;
use Vantoozz\ProxyScraper\Proxy;
use Vantoozz\ProxyScraper\Scrapers\TopProxysScraper;

/**
 * Class TopProxysScraperTest
 * @package Vantoozz\ProxyScraper\Scrapers
 */
final class TopProxysScraperTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_an_exception_on_http_client_error(): void
    {
        $this->expectException(ScraperException::class);
        $this->expectExceptionMessage('error message');

        /** @var HttpClientInterface|MockObject $httpClient */
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects(static::once())
            ->method('get')
            ->willThrowException(new HttpClientException('error message'));

        $scraper = new TopProxysScraper($httpClient);
        $scraper->get()->current();
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_unknown_XML_got(): void
    {
        $this->expectException(ScraperException::class);
        $this->expectExceptionMessage('Invalid XML');

        /** @var HttpClientInterface|MockObject $httpClient */
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects(static::once())
            ->method('get')
            ->willReturn('Invalid XML');

        $scraper = new TopProxysScraper($httpClient);
        $scraper->get()->current();
    }

    /**
     * @test
     */
    public function it_returns_source_metric(): void
    {
        /** @var HttpClientInterface|MockObject $httpClient */
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects(static::once())
            ->method('get')
            ->willReturn(file_get_contents(__DIR__ . '/../../fixtures/topProxysBlogger.xml'));

        $scraper = new TopProxysScraper($httpClient);
        $proxy = $scraper->get()->current();

        static::assertInstanceOf(Proxy::class, $proxy);
        /** @var Proxy $proxy */
        static::assertSame(Metrics::SOURCE, $proxy->getMetrics()[0]->getName());
        static::assertSame(TopProxysScraper::class, $proxy->getMetrics()[0]->getValue());
    }

    /**
     * @test
     */
    public function it_returns_a_proxy(): void
    {
        /** @var HttpClientInterface|MockObject $httpClient */
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects(static::once())
            ->method('get')
            ->willReturn(file_get_contents(__DIR__ . '/../../fixtures/topProxysBlogger.xml'));

        $scraper = new TopProxysScraper($httpClient);
        $proxy = $scraper->get()->current();

        static::assertInstanceOf(Proxy::class, $proxy);
        static::assertSame('1.0.134.189:443', (string)$proxy);
    }

}
