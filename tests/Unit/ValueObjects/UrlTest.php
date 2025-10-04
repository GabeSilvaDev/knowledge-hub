<?php

use App\ValueObjects\Url;

const EXAMPLE_HTTPS_URL = 'https://example.com';
const EXAMPLE_HTTP_URL = 'http://example.com';

describe('Url Value Object', function (): void {
    it('creates url with valid address', function (): void {
        $url = Url::from(EXAMPLE_HTTPS_URL);

        expect($url->getValue())->toBe(EXAMPLE_HTTPS_URL)
            ->and((string) $url)->toBe(EXAMPLE_HTTPS_URL);
    });

    it('throws exception for empty url', function (): void {
        expect(fn (): Url => Url::from(''))
            ->toThrow(InvalidArgumentException::class, 'URL cannot be empty');
    });

    it('throws exception for invalid url format', function ($invalidUrl): void {
        expect(fn (): Url => Url::from($invalidUrl))
            ->toThrow(InvalidArgumentException::class);
    })->with([
        'not-a-url',
        'just-text',
        'http://',
    ]);

    it('throws exception for non http/https protocol', function (): void {
        expect(fn (): Url => Url::from('ftp://invalid'))
            ->toThrow(InvalidArgumentException::class, 'URL must use HTTP or HTTPS protocol');
    });

    it('allows valid url formats', function ($validUrl): void {
        $url = Url::from($validUrl);
        expect($url->getValue())->toBe($validUrl);
    })->with([
        EXAMPLE_HTTPS_URL,
        'http://test.com',
        'https://subdomain.example.com',
        'https://example.com/path',
        'https://example.com/path?query=value',
    ]);

    it('gets domain from url', function (): void {
        $url = Url::from('https://example.com/path');

        expect($url->getDomain())->toBe('example.com');
    });

    it('gets scheme from url', function (): void {
        $httpsUrl = Url::from(EXAMPLE_HTTPS_URL);
        $httpUrl = Url::from(EXAMPLE_HTTP_URL);

        expect($httpsUrl->getScheme())->toBe('https')
            ->and($httpUrl->getScheme())->toBe('http');
    });

    it('checks if url is secure', function (): void {
        $httpsUrl = Url::from(EXAMPLE_HTTPS_URL);
        $httpUrl = Url::from(EXAMPLE_HTTP_URL);

        expect($httpsUrl->isSecure())->toBeTrue()
            ->and($httpUrl->isSecure())->toBeFalse();
    });

    it('checks if url equals another url', function (): void {
        $url1 = Url::from(EXAMPLE_HTTPS_URL);
        $url2 = Url::from(EXAMPLE_HTTPS_URL);
        $url3 = Url::from('https://different.com');

        expect($url1->equals($url2))->toBeTrue()
            ->and($url1->equals($url3))->toBeFalse();
    });

    it('throws exception for zero string', function (): void {
        expect(fn (): Url => Url::from('0'))
            ->toThrow(InvalidArgumentException::class, 'URL cannot be empty');
    });

    it('throws exception for url too long', function (): void {
        $longUrl = 'https://example.com/' . str_repeat('a', 2048);

        expect(fn (): Url => Url::from($longUrl))
            ->toThrow(InvalidArgumentException::class, 'URL is too long');
    });

    it('creates url using constructor', function (): void {
        $url = new Url(EXAMPLE_HTTPS_URL);

        expect($url->getValue())->toBe(EXAMPLE_HTTPS_URL)
            ->and((string) $url)->toBe(EXAMPLE_HTTPS_URL);
    });

    it('implements Stringable interface', function (): void {
        $url = Url::from(EXAMPLE_HTTPS_URL);

        expect($url)->toBeInstanceOf(Stringable::class);
    });

    it('handles urls with complex paths and parameters', function (): void {
        $complexUrl = 'https://api.example.com/v1/users/123?include=profile&sort=name#section';
        $url = Url::from($complexUrl);

        expect($url->getValue())->toBe($complexUrl)
            ->and($url->getDomain())->toBe('api.example.com')
            ->and($url->getScheme())->toBe('https')
            ->and($url->isSecure())->toBeTrue();
    });

    it('handles urls with ports', function (): void {
        $urlWithPort = 'http://localhost:8080/api';
        $url = Url::from($urlWithPort);

        expect($url->getValue())->toBe($urlWithPort)
            ->and($url->getDomain())->toBe('localhost')
            ->and($url->getScheme())->toBe('http')
            ->and($url->isSecure())->toBeFalse();
    });

    it('handles edge case urls', function (): void {
        $ipUrl = 'http://192.168.1.1';
        $url = Url::from($ipUrl);

        expect($url->getValue())->toBe($ipUrl)
            ->and($url->getDomain())->toBe('192.168.1.1')
            ->and($url->getScheme())->toBe('http');
    });
});
