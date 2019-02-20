<?php
namespace Repeka\Plugins\EmailSender\Tests\Integration;

use Repeka\Tests\Integration\ResourceWorkflow\ResourceWorkflowPluginIntegrationTest;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

class RepekaEmailSenderResourceWorkflowPluginIntegrationTest extends ResourceWorkflowPluginIntegrationTest {
    use FixtureHelpers;

    /** @before */
    public function init() {
        TestEmailSender::$sentMessages = [];
        $this->loadAllFixtures();
    }

    public function testSendingEmailOnEnterPlace() {
        $resource = $this->getPhpBookResource();
        $this->usePluginWithResource(
            $resource,
            [
                [
                    'name' => 'repekaEmailSender',
                    'config' => [
                        'email' => 'john@doe.com',
                        'subject' => 'Test subject',
                        'message' => 'Test body',
                    ],
                ],
            ]
        );
        $this->assertCount(1, TestEmailSender::$sentMessages);
        /** @var \Swift_Message $message */
        $message = TestEmailSender::$sentMessages[0];
        $this->assertEquals('john@doe.com', key($message->getTo()));
        $this->assertEquals('Test subject', $message->getSubject());
        $this->assertEquals('Test body', $message->getBody());
    }

    public function testSendingEmailRenderedFromDisplayStrategies() {
        $resource = $this->getPhpBookResource();
        $this->usePluginWithResource(
            $resource,
            [
                [
                    'name' => 'repekaEmailSender',
                    'config' => [
                        'email' => '{{ "john" ~ "@" ~ "doe.com" }}',
                        'subject' => 'Subject: {{ 2 + 2 }}',
                        'message' => 'Body: {{ r|mTytul }}',
                    ],
                ],
            ]
        );
        $this->assertCount(1, TestEmailSender::$sentMessages);
        /** @var \Swift_Message $message */
        $message = TestEmailSender::$sentMessages[0];
        $this->assertEquals('john@doe.com', key($message->getTo()));
        $this->assertEquals('Subject: 4', $message->getSubject());
        $this->assertEquals('Body: PHP - to można leczyć!', $message->getBody());
    }

    public function testSendingToMultipleRecipients() {
        $resource = $this->getPhpBookResource();
        $this->usePluginWithResource(
            $resource,
            [
                [
                    'name' => 'repekaEmailSender',
                    'config' => [
                        'email' => '{{ ["john@doe.com","jane@doe.com"] | join(",") }}',
                        'subject' => 'subject',
                        'message' => 'body',
                    ],
                ],
            ]
        );
        $this->assertCount(1, TestEmailSender::$sentMessages);
        /** @var \Swift_Message $message */
        $message = TestEmailSender::$sentMessages[0];
        $this->assertEquals(['john@doe.com', 'jane@doe.com'], array_keys($message->getTo()));
    }
}
