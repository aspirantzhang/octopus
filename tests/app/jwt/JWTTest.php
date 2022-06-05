<?php

declare(strict_types=1);

namespace tests\app\jwt;

use app\jwt\exception\TokenExpiredException;
use app\jwt\exception\TokenInvalidException;
use app\jwt\JWT;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Mockery as m;

class JWTTest extends \tests\TestCase
{
    public function setUp(): void
    {
        $mock = m::mock('overload:think\facade\Config');
        $mock->shouldReceive('get')->with('jwt.key')->once()->andReturn('fake_key');
        $mock->shouldReceive('get')->with('jwt.iss')->once()->andReturn('fake_iss');
        $mock->shouldReceive('get')->with('jwt.aud')->once()->andReturn('fake_aud');
        $mock->shouldReceive('get')->with('jwt.exp')->once()->andReturn(30);
        $mock->shouldReceive('get')->with('jwt.renew')->once()->andReturn(10000);
        CarbonImmutable::setTestNow(Carbon::parse(1600000000));
    }

    public function testGetAddClaim()
    {
        $refreshExpire = 1600009999;
        $result = (new JWT())->addClaim('exp', $refreshExpire)->addClaim('foo', 'bar')->getClaims();
        $this->assertEquals(1600009999, $result['exp']);
        $this->assertEquals('bar', $result['foo']);
    }

    public function testGetClaimsReturnDefaultClaims()
    {
        $result = (new JWT())->getClaims();

        $this->assertEquals('fake_iss', $result['iss']);
        $this->assertEquals('fake_aud', $result['aud']);
        $this->assertEquals($result['nbf'], $result['iat']);
        $this->assertEquals($result['iat'] + 30, $result['exp']);
    }

    public function testGetClaimForDefaultValue()
    {
        $result = (new JWT())->getClaim('exp');
        $this->assertEquals(1600000030, $result);
    }

    public function testGetClaimForExtraValue()
    {
        $jwt = (new JWT())->addClaim('foo', 'bar');
        $result = $jwt->getClaim('foo');

        $this->assertEquals('bar', $result);
    }

    public function testCheckTokenWithValidTokenString()
    {
        CarbonImmutable::setTestNow();
        $token = (new JWT())->addClaim('foo', 'bar')->getAccessToken();
        $result = (new JWT())->checkToken($token);
        $this->assertEquals('fake_iss', $result['iss']);
        $this->assertEquals('fake_aud', $result['aud']);
        $this->assertEquals('bar', $result['foo']);
    }

    public function testCheckTokenWithExpiredTokenShouldThrowError()
    {
        $this->expectException(TokenExpiredException::class);
        $this->expectExceptionMessage('token expired');

        $token = (new JWT())->addClaim('foo', 'bar')->getAccessToken();
        (new JWT())->checkToken($token);
    }

    public function testCheckTokenWithInvalidTokenShouldThrowError()
    {
        $this->expectException(TokenInvalidException::class);
        $this->expectExceptionMessage('token invalid');

        (new JWT())->checkToken('invalid');
    }
}
