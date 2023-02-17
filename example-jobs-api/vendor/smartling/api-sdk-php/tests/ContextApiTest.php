<?php

namespace Smartling\Tests;
use Smartling\Context\ContextApi;
use Smartling\Context\Params\UploadContextParameters;


/**
 * Test class for Smartling\Context\ContextApi.
 */
class ContextApiTest extends ApiTestAbstract
{

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->prepareContextApiMock();
    }

    private function prepareContextApiMock()
    {
        $this->object = $this->getMockBuilder('Smartling\Context\ContextApi')
            ->setMethods(['readFile'])
            ->setConstructorArgs([
                $this->projectId,
                $this->client,
                null,
                ContextApi::ENDPOINT_URL,
            ])
            ->getMock();

        $this->object->expects(self::any())
            ->method('readFile')
            ->willReturn($this->streamPlaceholder);

        $this->invokeMethod(
            $this->object,
            'setAuth',
            [
                $this->authProvider
            ]
        );
    }

    /**
     * @covers \Smartling\Context\ContextApi::uploadContext
     */
    public function testUploadContext() {
        $params = new UploadContextParameters();
        $params->setContextFileUri('./tests/resources/context.html');
        $endpointUrl = vsprintf('%s/%s/contexts', [
            ContextApi::ENDPOINT_URL,
            $this->projectId
        ]);

        $this->client
            ->expects(self::once())
            ->method('createRequest')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                    'Content-Type' => 'application/json',
                    'X-SL-Context-Source' => $this->invokeMethod($this->object, 'getXSLContextSourceHeader'),
                ],
                'exceptions' => FALSE,
                'body' => [
                    'content' => $this->streamPlaceholder,
                ],
            ])
            ->willReturn($this->requestMock);

        $this->client->expects(self::once())
            ->method('send')
            ->with($this->requestMock)
            ->willReturn($this->responseMock);

        $this->object->uploadContext($params);
    }

    /**
     * @covers \Smartling\Context\ContextApi::uploadContext
     */
    public function testMatchContext() {
        $contextUid = 'someContextUid';
        $endpointUrl = vsprintf('%s/%s/contexts/%s/match/async', [
            ContextApi::ENDPOINT_URL,
            $this->projectId,
            $contextUid
        ]);

        $this->client
            ->expects(self::once())
            ->method('createRequest')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                    'Content-Type' => 'application/json',
                    'X-SL-Context-Source' => $this->invokeMethod($this->object, 'getXSLContextSourceHeader'),
              ],
              'exceptions' => FALSE,
              'body' => '',
            ])
            ->willReturn($this->requestMock);

        $this->client->expects(self::once())
            ->method('send')
            ->with($this->requestMock)
            ->willReturn($this->responseMock);

        $this->object->matchContext($contextUid);
    }

}
