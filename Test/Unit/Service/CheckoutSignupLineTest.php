<?php declare(strict_types=1);

namespace PixelPerfect\NewsletterSignupHyvaCheckout\Test\Unit\Service;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PixelPerfect\NewsletterSignup\Api\Service\CheckoutSignupInterface;
use PixelPerfect\NewsletterSignupHyvaCheckout\Service\CheckoutSignupLine;
use Psr\Log\LoggerInterface;

class CheckoutSignupLineTest extends TestCase
{
    private const CART_ID = 42;

    /** @var array<string, string|null> */
    private array $config = [
        'newsletter/general/active' => '1',
        'newsletter/signup_checkout/enabled' => '1',
        'newsletter/signup_checkout/benefit_text' => 'Get news first.',
    ];

    /** Whether core invites the email address on the cart */
    private bool $invited = true;

    /** Choice stored on the cart; null = never answered */
    private ?int $storedChoice = null;

    private ?\Throwable $failure = null;

    private int $quoteId = self::CART_ID;

    private LoggerInterface&MockObject $logger;

    private CheckoutSignupLine $line;

    protected function setUp(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(fn (string $path): ?string => $this->config[$path] ?? null);
        $scopeConfig->method('isSetFlag')->willReturnCallback(
            fn (string $path): bool => (bool)($this->config[$path] ?? false)
        );

        $quote = $this->createMock(Quote::class);
        $quote->method('getId')->willReturnCallback(fn (): int => $this->quoteId);
        $quote->method('getData')->willReturnCallback(
            fn (string $key = ''): ?int => $key === 'pixelperfect_newsletter_opt_in' ? $this->storedChoice : null
        );
        $session = $this->createMock(CheckoutSession::class);
        $session->method('getQuote')->willReturnCallback(function () use ($quote): Quote {
            if ($this->failure !== null) {
                throw $this->failure;
            }
            return $quote;
        });

        $signup = $this->createMock(CheckoutSignupInterface::class);
        $signup->method('isInvited')->willReturnCallback(fn (int $cartId): bool => $this->invitedFor($cartId));
        $signup->method('setOptIn')->willReturnCallback(function (int $cartId, bool $optIn): bool {
            $stored = $optIn && $this->invitedFor($cartId);
            $this->storedChoice = $stored ? 1 : 0;
            return $stored;
        });

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->line = new CheckoutSignupLine($session, $signup, $scopeConfig, $this->logger);
    }

    public function testTheLineShowsWhenTheCartsEmailIsInvited(): void
    {
        $this->assertTrue($this->line->isShown());
    }

    public function testTheLineIsHiddenWhenTheCartsEmailIsNotInvited(): void
    {
        $this->invited = false;

        $this->assertFalse($this->line->isShown());
    }

    public function testTheLineIsHiddenWhenItIsSwitchedOff(): void
    {
        $this->config['newsletter/signup_checkout/enabled'] = '0';

        $this->assertFalse($this->line->isShown());
    }

    public function testTheLineIsHiddenWhenTheNewsletterIsSwitchedOff(): void
    {
        $this->config['newsletter/general/active'] = '0';

        $this->assertFalse($this->line->isShown());
    }

    public function testTheLineIsHiddenWithoutACart(): void
    {
        $this->quoteId = 0;

        $this->assertFalse($this->line->isShown());
    }

    public function testTheLineIsHiddenAndTheErrorLoggedWhenTheCartCannotBeRead(): void
    {
        $this->failure = new NoSuchEntityException(__('No such cart'));
        $this->logger->expects($this->once())->method('error');

        $this->assertFalse($this->line->isShown());
    }

    public function testTheLineIsNeverTickedBeforeTheShopperAnswers(): void
    {
        $this->assertFalse($this->line->isOptedIn());
    }

    public function testATickedChoiceIsStoredOnTheCart(): void
    {
        $this->assertTrue($this->line->setOptIn(true));

        $this->assertSame(1, $this->storedChoice);
        $this->assertTrue($this->line->isOptedIn());
    }

    public function testUntickingClearsTheChoice(): void
    {
        $this->line->setOptIn(true);

        $this->assertFalse($this->line->setOptIn(false));

        $this->assertSame(0, $this->storedChoice);
        $this->assertFalse($this->line->isOptedIn());
    }

    public function testATickIsRefusedWhenTheCartsEmailIsNotInvited(): void
    {
        $this->invited = false;

        $this->assertFalse($this->line->setOptIn(true));

        $this->assertSame(0, $this->storedChoice);
    }

    public function testATickWithoutACartStoresNothing(): void
    {
        $this->quoteId = 0;

        $this->assertFalse($this->line->setOptIn(true));

        $this->assertNull($this->storedChoice);
    }

    public function testATickThatCannotBeStoredIsLoggedAndCountsAsUnticked(): void
    {
        $this->failure = new \RuntimeException('Cart save failed');
        $this->logger->expects($this->once())->method('error');

        $this->assertFalse($this->line->setOptIn(true));
    }

    public function testAnUnreadableChoiceIsLoggedAndCountsAsUnticked(): void
    {
        $this->storedChoice = 1;
        $this->failure = new \RuntimeException('Session gone');
        $this->logger->expects($this->once())->method('error');

        $this->assertFalse($this->line->isOptedIn());
    }

    public function testTheBenefitTextComesFromTheStoreViewSetting(): void
    {
        $this->assertSame('Get news first.', $this->line->getBenefitText());
    }

    public function testAMissingBenefitTextIsEmpty(): void
    {
        $this->config['newsletter/signup_checkout/benefit_text'] = null;

        $this->assertSame('', $this->line->getBenefitText());
    }

    /**
     * Core's answer for the cart; only the session's own cart is ever asked about
     */
    private function invitedFor(int $cartId): bool
    {
        $this->assertSame(self::CART_ID, $cartId);
        return $this->invited;
    }
}
