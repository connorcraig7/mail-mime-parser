<?php
namespace ZBateson\MailMimeParser;

use PHPUnit_Framework_TestCase;
use org\bovigo\vfs\vfsStream;

/**
 * Description of MessageTest
 *
 * @group MessageClass
 * @group Base
 * @covers ZBateson\MailMimeParser\Message
 * @author Zaahid Bateson
 */
class MessageTest extends PHPUnit_Framework_TestCase
{
    protected $mockHeaderFactory;
    protected $mockPartFilterFactory;
    protected $vfs;

    protected function setUp()
    {
        $this->vfs = vfsStream::setup('root');
        $this->mockHeaderFactory = $this->getMockBuilder('ZBateson\MailMimeParser\Header\HeaderFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockPartFilterFactory = $this->getMockBuilder('ZBateson\MailMimeParser\Message\PartFilterFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }
    
    protected function getMockedParameterHeader($name, $value, $parameterValue = null)
    {
        $header = $this->getMockBuilder('ZBateson\MailMimeParser\Header\ParameterHeader')
            ->disableOriginalConstructor()
            ->setMethods(['getValue', 'getName', 'getValueFor', 'hasParameter'])
            ->getMock();
        $header->method('getName')->willReturn($name);
        $header->method('getValue')->willReturn($value);
        $header->method('getValueFor')->willReturn($parameterValue);
        $header->method('hasParameter')->willReturn(true);
        return $header;
    }
    
    protected function getMockedPartBuilder()
    {
        return $this->getMockBuilder('ZBateson\MailMimeParser\Message\Part\PartBuilder')
            ->disableOriginalConstructor()
            ->getMock();
    }
    
    protected function getMockedPartBuilderWithChildren()
    {
        $pb = $this->getMockedPartBuilder();
        $children = [
            $this->getMockedPartBuilder(),
            $this->getMockedPartBuilder(),
            $this->getMockedPartBuilder()
        ];
        
        $nestedMimePart = $this->getMockBuilder('ZBateson\MailMimeParser\Message\Part\MimePart')
            ->disableOriginalConstructor()
            ->getMock();
        $nestedMimePart
            ->method('getMessageObjectId')
            ->willReturn('nested');
        
        $nested = $this->getMockedPartBuilder();
        $nested->method('createMessagePart')
            ->willReturn($nestedMimePart);
        $children[0]->method('getChildren')
            ->willReturn([$nested]);
        
        foreach ($children as $key => $child) {
            $childMimePart = $this->getMockBuilder('ZBateson\MailMimeParser\Message\Part\MimePart')
            ->disableOriginalConstructor()
            ->getMock();
            $childMimePart->
                method('getMessageObjectId')
                ->willReturn('child' . $key);
            
            if ($key === 0) {
                $childMimePart
                    ->method('getAllParts')
                    ->willReturn([$childMimePart, $nestedMimePart]);
            } else {
                $childMimePart
                    ->method('getAllParts')
                    ->willReturn([$childMimePart]);
            }
            
            $child->method('createMessagePart')
                ->willReturn($childMimePart);
        }
        $pb->method('getChildren')
            ->willReturn($children);
        return $pb;
    }
    
    public function testInstance()
    {
        $message = new Message(
            $this->mockHeaderFactory,
            $this->mockPartFilterFactory,
            'sweet massage',
            $this->getMockedPartBuilder()
        );
        $this->assertNotNull($message);
        $this->assertInstanceOf('ZBateson\MailMimeParser\Message', $message);
    }
    
    public function testGetTextPartAndTextPartCount()
    {
        $filterMock = $this->getMockBuilder('ZBateson\MailMimeParser\Message\PartFilter')
            ->disableOriginalConstructor()
            ->setMethods(['filter'])
            ->getMock();
        $filterMock
            ->method('filter')
            ->willReturnOnConsecutiveCalls(
                false, true, false, true, false,
                false, true, false, true, false,
                false, true, false, true, false,
                false, true, false, true, false,
                false, true, false, true, false,
                false, true, false, true, false,
                false, true, false, true, false,
                false, true, false, true, false
            );
        $this->mockPartFilterFactory
            ->method('newFilterFromInlineContentType')
            ->willReturn($filterMock);
        
        $message = new Message(
            $this->mockHeaderFactory,
            $this->mockPartFilterFactory,
            'habibi',
            $this->getMockedPartBuilderWithChildren()
        );
        
        $parts = $message->getAllParts();
        $parts[1]->method('getContentResourceHandle')
            ->willReturn('oufa baloufa!');
        $parts[1]->method('getContent')
            ->willReturn('shabadabada...');
        
        $this->assertEquals(2, $message->getTextPartCount());
        $this->assertEquals($parts[1], $message->getTextPart());
        $this->assertEquals($parts[3], $message->getTextPart(1));
        $this->assertNull($message->getTextPart(2));
        $this->assertNull($message->getTextStream(2));
        $this->assertNull($message->getTextContent(2));
        $this->assertEquals('oufa baloufa!', $message->getTextStream());
        $this->assertEquals('shabadabada...', $message->getTextContent());
    }
    
    public function testGetHtmlPartAndHtmlPartCount()
    {
        $filterMock = $this->getMockBuilder('ZBateson\MailMimeParser\Message\PartFilter')
            ->disableOriginalConstructor()
            ->setMethods(['filter'])
            ->getMock();
        $filterMock
            ->method('filter')
            ->willReturnOnConsecutiveCalls(
                false, true, false, true, false,
                false, true, false, true, false,
                false, true, false, true, false,
                false, true, false, true, false,
                false, true, false, true, false,
                false, true, false, true, false,
                false, true, false, true, false,
                false, true, false, true, false
            );
        $this->mockPartFilterFactory
            ->method('newFilterFromInlineContentType')
            ->willReturn($filterMock);
        
        $message = new Message(
            $this->mockHeaderFactory,
            $this->mockPartFilterFactory,
            'habibi',
            $this->getMockedPartBuilderWithChildren()
        );
        
        $parts = $message->getAllParts();
        $parts[1]->method('getContentResourceHandle')
            ->willReturn('oufa baloufa!');
        $parts[1]->method('getContent')
            ->willReturn('shabadabada...');
        
        $this->assertEquals(2, $message->getHtmlPartCount());
        $this->assertEquals($parts[1], $message->getHtmlPart());
        $this->assertEquals($parts[3], $message->getHtmlPart(1));
        $this->assertNull($message->getHtmlPart(2));
        $this->assertNull($message->getHtmlStream(2));
        $this->assertNull($message->getHtmlContent(2));
        $this->assertEquals('oufa baloufa!', $message->getHtmlStream());
        $this->assertEquals('shabadabada...', $message->getHtmlContent());
    }
    
    public function testGetContentPart()
    {
        $filterMock = $this->getMockBuilder('ZBateson\MailMimeParser\Message\PartFilter')
            ->disableOriginalConstructor()
            ->setMethods(['filter'])
            ->getMock();
        $filterMock->expects($this->exactly(6))
            ->method('filter')
            ->willReturnOnConsecutiveCalls(
                false, false, true,     // true for getHtmlPart
                false, true,            // true for getTextPart
                true                    // true for getPartByMimeType
            );
        // getPartByMimeType for multipart/alternative
        $this->mockPartFilterFactory
            ->expects($this->exactly(3))
            ->method('newFilterFromContentType')
            ->with('multipart/alternative')
            ->willReturn($filterMock);
        // getTextPart and getHtmlPart
        $this->mockPartFilterFactory
            ->expects($this->exactly(3))
            ->method('newFilterFromInlineContentType')
            ->willReturn($filterMock);
        
        $message = new Message(
            $this->mockHeaderFactory,
            $this->mockPartFilterFactory,
            'habibi',
            $this->getMockedPartBuilder()
        );
        
        $this->assertEquals($message, $message->getContentPart());
        $this->assertEquals($message, $message->getContentPart());
        $this->assertEquals($message, $message->getContentPart());
    }
    
    public function testGetMessageStringForSignatureVerificationWithoutChildren()
    {
        $message = new Message(
            $this->mockHeaderFactory,
            $this->mockPartFilterFactory,
            'habibi',
            $this->getMockedPartBuilder()
        );
        $this->assertNull($message->getMessageStringForSignatureVerification());
    }
    
    public function testGetMessageStringForSignatureVerification()
    {
        $message = new Message(
            $this->mockHeaderFactory,
            $this->mockPartFilterFactory,
            'habibi',
            $this->getMockedPartBuilderWithChildren()
        );
        $content = vfsStream::newFile('part')->at($this->vfs);
        $content->withContent("mucha\ragua\ny\r\npollo\r\n\r\n");
        $handle = fopen($content->url(), 'r');
        
        $child = $message->getChild(0);
        $child->method('getHandle')
            ->willReturn($handle);
        
        $this->assertEquals("mucha\r\nagua\r\ny\r\npollo\r\n", $message->getMessageStringForSignatureVerification());        
        fclose($handle);
    }
    
    public function testGetAttachmentParts()
    {
        $filterMock = $this->getMockBuilder('ZBateson\MailMimeParser\Message\PartFilter')
            ->disableOriginalConstructor()
            ->setMethods(['filter'])
            ->getMock();
        $filterMock
            ->method('filter')
            ->willReturnOnConsecutiveCalls(
                false, true, false, true, false,
                false, true, false, true, false,
                false, true, false, true, false,
                false, true, false, true, false
            );
        $this->mockPartFilterFactory
            ->method('newFilterFromArray')
            ->willReturn($filterMock);
        
        $message = new Message(
            $this->mockHeaderFactory,
            $this->mockPartFilterFactory,
            'habibi',
            $this->getMockedPartBuilderWithChildren()
        );
        
        $parts = $message->getAllParts();
        $parts[1]->method('isTextPart')
            ->willReturn(true);
        $parts[1]->method('getHeaderValue')
            ->with('Content-Disposition', 'inline')
            ->willReturn('attachment');
        $parts[3]->method('isTextPart')
            ->willReturn(true);
        $parts[3]->method('getHeaderValue')
            ->with('Content-Disposition', 'inline')
            ->willReturn('inline');

        $this->assertEquals(1, $message->getAttachmentCount());
        $this->assertEquals([$parts[1]], $message->getAllAttachmentParts());
        $this->assertEquals($parts[1], $message->getAttachmentPart(0));
        $this->assertNull($message->getAttachmentPart(1));
    }
    
    public function testIsNotMime()
    {
        $message = new Message(
            $this->mockHeaderFactory,
            $this->mockPartFilterFactory,
            'habibi',
            $this->getMockedPartBuilder()
        );
        $this->assertFalse($message->isMime());
    }
    
    public function testIsMimeWithContentType()
    {
        $hf = $this->mockHeaderFactory;
        $header = $this->getMockedParameterHeader('Content-Type', 'text/plain', 'utf-8');
        
        $pb = $this->getMockedPartBuilder();
        $pb->method('getContentType')
            ->willReturn($header);
        $pb->method('getRawHeaders')
            ->willReturn(['contenttype' => ['Blah', 'Blah']]);

        $message = new Message(
            $hf,
            $this->mockPartFilterFactory,
            'habibi',
            $pb
        );
        $this->assertTrue($message->isMime());
    }
    
    public function testIsMimeWithMimeVersion()
    {
        $hf = $this->mockHeaderFactory;
        $header = $this->getMockedParameterHeader('Mime-Version', '4.3');
        $hf->method('newInstance')
            ->willReturn($header);
        
        $pb = $this->getMockedPartBuilder();
        $pb->method('getRawHeaders')
            ->willReturn(['mimeversion' => ['Mime-Version', '4.3']]);

        $message = new Message(
            $hf,
            $this->mockPartFilterFactory,
            'habibi',
            $pb
        );
        $this->assertTrue($message->isMime());
    }
    
    public function testSaveAndToString()
    {
        $content = vfsStream::newFile('part')->at($this->vfs);
        $content->withContent('Demigorgon');
        
        $pb = $this->getMockedPartBuilder();
        $pb->method('getStreamPartFilename')
            ->willReturn($content->url());
        $message = new Message(
            $this->mockHeaderFactory,
            $this->mockPartFilterFactory,
            'habibi',
            $pb
        );
        
        $handle = fopen('php://temp', 'r+');
        $message->save($handle);
        rewind($handle);
        $str = stream_get_contents($handle);
        fclose($handle);
        
        $this->assertEquals('Demigorgon', $str);
        $this->assertEquals('Demigorgon', $message->__toString());
    }
}
